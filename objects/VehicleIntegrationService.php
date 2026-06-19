<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\DocTireMovementVehicle;
use app\models\tables\Vehicle;
use yii\db\Transaction;

/**
 * VehicleIntegrationService — Orquestador que integra cambios de documentos
 * hacia la tabla Vehicle (kilometraje, estados, etc.).
 *
 * Es llamado desde los métodos close() de los servicios de documentos
 * que afectan vehículos (movimientos de llantas, etc.).
 */
class VehicleIntegrationService
{
    private VehicleService $vehicleService;

    public function __construct()
    {
        $this->vehicleService = new VehicleService();
    }

    /**
     * Procesa la integración de kilometraje al cerrar un documento de movimiento.
     * Toma los registros DocTireMovementVehicle y actualiza Vehicle.current_km.
     *
     * @param int $docentry
     * @param Transaction|null $transaction
     * @return array ['Success' => 'Ok'|'Error', 'Msg' => '...']
     */
    public function onMovementClosed(int $docentry, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $vehicles = DocTireMovementVehicle::find()
                ->where(['docentry' => $docentry])
                ->all();

            if (empty($vehicles)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'No hay vehículos involucrados en el documento'];
            }

            $errors = [];
            $updated = 0;

            foreach ($vehicles as $vehicle) {
                if ($vehicle->vehicle_km === null) {
                    continue;
                }

                $result = $this->vehicleService->updateKm(
                    $vehicle->vehicle_code,
                    (float)$vehicle->vehicle_km,
                    $tx
                );

                if ($result['Success'] !== 'Ok') {
                    $errors[] = "Vehículo {$vehicle->vehicle_code}: {$result['Msg']}";
                    continue;
                }

                $updated++;
            }

            if (!empty($errors)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return [
                    'Success' => 'Error',
                    'Msg' => 'Errores en integración de vehículos: ' . implode('; ', $errors),
                ];
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => "{$updated} vehículo(s) actualizado(s)"];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error en integración de vehículos';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }
}
