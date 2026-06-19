<?php

declare(strict_types=1);

namespace app\models\objects;

/**
 * Wrapper facade para compatibilidad: expone la misma API que VehicleService
 * y delega la lógica al servicio existente `VehicleService`.
 */
class VehicleServices
{
    private VehicleService $svc;
    private VehicleTypeServices $typeSvc;

    public function __construct()
    {
        $this->svc = new VehicleService();
        $this->typeSvc = new VehicleTypeServices();
    }

    public function list(array $filters = []): array { return $this->svc->list($filters); }

    public function get(string $pk): array { return $this->svc->get($pk); }

    /**
     * Guarda payload compuesto: vehicle + vehicle_document[] + vehicle_tire[]
     * Ejecuta todo dentro de una transacción y delega a VehicleService.
     * 
     * @param array $data Datos del formulario
     * @param array $files Archivos subidos ($_FILES)
     * @return array
     */
    public function save(array $data, array $files = []): array
    {
        $tx = \Yii::$app->db->beginTransaction();
        $uploadedFiles = []; // Track para rollback
        
        try {
            $vehiclePayload = $data['vehicle'] ?? $data;
            $res = $this->svc->save($vehiclePayload, $tx);
            if (($res['Success'] ?? '') !== 'Ok') {
                $tx->rollBack();
                $this->deleteUploadedFiles($uploadedFiles);
                return $res;
            }
            $vehicleCode = $res['Data']['vehicle_code'] ?? ($vehiclePayload['vehicle_code'] ?? null);
            if (empty($vehicleCode)) {
                $tx->rollBack();
                $this->deleteUploadedFiles($uploadedFiles);
                return ['Success' => 'Error', 'Msg' => 'vehicle_code no disponible', 'Data' => []];
            }

            // Sincronizar documentos: deleteAll + reinsertar CON archivos
            if (isset($data['vehicle_document']) && is_array($data['vehicle_document'])) {
                \app\models\tables\VehicleDocument::deleteAll(['vehicle_code' => $vehicleCode]);
                
                foreach ($data['vehicle_document'] as $index => $doc) {
                    $doc['vehicle_code'] = $vehicleCode;
                    
                    // Procesar archivo si existe
                    $fileKey = 'vehicle_document';
                    if (isset($files[$fileKey]['tmp_name'][$index]['attach_file']) && 
                        !empty($files[$fileKey]['tmp_name'][$index]['attach_file'])) {
                        
                        $uploadResult = $this->saveDocumentFile(
                            $files[$fileKey]['tmp_name'][$index]['attach_file'],
                            $files[$fileKey]['name'][$index]['attach_file'],
                            $files[$fileKey]['error'][$index]['attach_file'],
                            $vehicleCode
                        );
                        
                        if ($uploadResult['Success'] === 'Ok') {
                            $doc['attach'] = $uploadResult['Data']['relativePath'];
                            $uploadedFiles[] = $uploadResult['Data']['fullPath'];
                        } else {
                            $tx->rollBack();
                            $this->deleteUploadedFiles($uploadedFiles);
                            return ['Success' => 'Error', 'Msg' => 'Error al subir archivo: ' . $uploadResult['Msg'], 'Data' => []];
                        }
                    }
                    
                    $r = $this->svc->saveDocument($doc, $tx);
                    if (($r['Success'] ?? '') !== 'Ok') {
                        $tx->rollBack();
                        $this->deleteUploadedFiles($uploadedFiles);
                        return $r;
                    }
                }
            }

            // Sincronizar llantas
            // ✅ MEJORA 1: Auto-generar configuración si viene vacío
            // ✅ MEJORA 2: Validar que no haya llantas asignadas antes de modificar
            if (isset($data['vehicle_tire'])) {
                // Si viene como string JSON, decodificar
                $tireData = is_string($data['vehicle_tire']) ? json_decode($data['vehicle_tire'], true) : $data['vehicle_tire'];
                
                // Si viene vacío PERO hay vehicle_type_code, auto-generar configuración
                if (empty($tireData) && !empty($vehiclePayload['vehicle_type_code'])) {
                    $configResult = $this->typeSvc->getAxleConfiguration($vehiclePayload['vehicle_type_code'], $vehicleCode);
                    
                    if ($configResult['Success'] === 'Ok') {
                        $tireData = $configResult['Data']['positions'];
                    } else {
                        // Error: tipo no válido o tiene llantas asignadas
                        $tx->rollBack();
                        $this->deleteUploadedFiles($uploadedFiles);
                        return $configResult;
                    }
                }
                
                // Si hay datos para guardar, validar y sincronizar
                if (!empty($tireData) && is_array($tireData)) {
                    // ✅ VALIDACIÓN CRÍTICA: Verificar que NO haya llantas asignadas
                    $hasTiresAssigned = \app\models\tables\VehicleTire::find()
                        ->where(['vehicle_code' => $vehicleCode])
                        ->andWhere(['IS NOT', 'tire_code', null])
                        ->andWhere(['<>', 'tire_code', ''])
                        ->exists();
                    
                    if ($hasTiresAssigned) {
                        $tx->rollBack();
                        $this->deleteUploadedFiles($uploadedFiles);
                        return [
                            'Success' => 'Error', 
                            'Msg' => 'No se puede modificar la configuración de llantas porque tiene llantas asignadas. Debe desasignar todas las llantas primero.', 
                            'Data' => []
                        ];
                    }
                    
                    // ✅ Borrar registros anteriores y crear nuevos (dentro de transacción)
                    \app\models\tables\VehicleTire::deleteAll(['vehicle_code' => $vehicleCode]);
                    
                    foreach ($tireData as $t) {
                        $t['vehicle_code'] = $vehicleCode;
                        $r = $this->svc->saveTireLine($t, $tx);
                        if (($r['Success'] ?? '') !== 'Ok') {
                            $tx->rollBack();
                            $this->deleteUploadedFiles($uploadedFiles);
                            return $r;
                        }
                    }
                }
            }

            $tx->commit();
            return ['Success' => 'Ok', 'Msg' => 'Unidad guardada correctamente', 'Data' => ['vehicle_code' => $vehicleCode]];
        } catch (\Throwable $e) {
            $tx->rollBack();
            $this->deleteUploadedFiles($uploadedFiles);
            
            \Yii::error([
                'message' => 'Error en VehicleServices::save',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ], __METHOD__);
            
            return ['Success' => 'Error', 'Msg' => 'Error al guardar la unidad. Verifique los datos e intente nuevamente.', 'Data' => []];
        }
    }
    
    /**
     * Guarda un archivo de documento de vehículo
     * 
     * @param string $tmpName Ruta temporal del archivo
     * @param string $fileName Nombre original del archivo
     * @param int $error Código de error del upload
     * @param string $vehicleCode Código del vehículo
     * @return array
     */
    private function saveDocumentFile(string $tmpName, string $fileName, int $error, string $vehicleCode): array
    {
        if ($error !== UPLOAD_ERR_OK) {
            return ['Success' => 'Error', 'Msg' => 'Error al subir archivo (código: ' . $error . ')', 'Data' => []];
        }
        
        // Validar extensión
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            return ['Success' => 'Error', 'Msg' => 'Tipo de archivo no permitido: ' . $fileExtension, 'Data' => []];
        }
        
        // Validar tamaño (10MB máximo)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if (filesize($tmpName) > $maxSize) {
            return ['Success' => 'Error', 'Msg' => 'El archivo excede el tamaño máximo de 10MB', 'Data' => []];
        }
        
        // Crear directorio si no existe: web/public/docs_vehicule/<vehicle_code>/
        $webRoot = \Yii::getAlias('@webroot');
        $uploadDir = $webRoot . '/public/docs_vehicule/' . $vehicleCode;
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['Success' => 'Error', 'Msg' => 'No se pudo crear el directorio de documentos', 'Data' => []];
            }
        }
        
        // Generar nombre único para evitar colisiones
        $uniqueName = time() . '_' . uniqid() . '.' . $fileExtension;
        $fullPath = $uploadDir . '/' . $uniqueName;
        $relativePath = 'public/docs_vehicule/' . $vehicleCode . '/' . $uniqueName;
        
        // Mover archivo
        if (!move_uploaded_file($tmpName, $fullPath)) {
            return ['Success' => 'Error', 'Msg' => 'Error al guardar el archivo en el servidor', 'Data' => []];
        }
        
        return [
            'Success' => 'Ok', 
            'Msg' => 'Archivo guardado correctamente',
            'Data' => [
                'fullPath' => $fullPath,
                'relativePath' => $relativePath,
                'fileName' => $uniqueName
            ]
        ];
    }
    
    /**
     * Elimina archivos subidos (para rollback)
     * 
     * @param array $filePaths Array de rutas completas de archivos
     * @return void
     */
    private function deleteUploadedFiles(array $filePaths): void
    {
        foreach ($filePaths as $path) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }

    public function delete(string $pk): array { return $this->svc->delete($pk); }

    public function saveDocument(array $data): array { return $this->svc->saveDocument($data); }
    public function deleteDocument(string $vehicleCode, int $lineNum): array { return $this->svc->deleteDocument($vehicleCode, $lineNum); }

    public function saveTireLine(array $data): array { return $this->svc->saveTireLine($data); }
    public function deleteTireLine(string $vehicleCode, int $lineNum): array { return $this->svc->deleteTireLine($vehicleCode, $lineNum); }

    public function getFormOptions(): array { return $this->svc->getFormOptions(); }
}
