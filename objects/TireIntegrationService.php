<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\DocTireMovement;
use app\models\tables\DocTireMovementDetail;
use app\models\tables\DocTireMovementVehicle;
use app\models\tables\DocTireRepair;
use app\models\tables\DocTireRepairDetail;
use app\models\tables\DocTireDisposal;
use app\models\tables\DocTireDisposalDetail;
use app\models\tables\Tire;
use yii\db\Transaction;

/**
 * TireIntegrationService — Orquestador que integra los cambios de documentos
 * de llantas (movimiento, reparación, disposición) hacia las tablas del sistema:
 *   - Tire (estados, km, desgaste)
 *   - VehicleTire (asignación/desasignación)
 *   - Vehicle (km actualizado)
 *
 * Es llamado desde los métodos close() de cada DocTire*Services.
 */
class TireIntegrationService
{
    private TireServices $tireServices;
    private VehicleService $vehicleService;

    public function __construct()
    {
        $this->tireServices = new TireServices();
        $this->vehicleService = new VehicleService();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Integración: Documento de Movimiento (asignación/rotación/retiro/etc.)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Procesa la integración al cerrar un documento de movimiento.
     * Itera los detalles con line_status = PENDING y aplica cambios.
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
            $details = DocTireMovementDetail::find()
                ->where(['docentry' => $docentry, 'line_status' => DocTireMovementDetail::LINE_STATUS_PENDING])
                ->all();

            if (empty($details)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'No hay líneas pendientes por procesar'];
            }

            $errors = [];
            $processed = 0;

            foreach ($details as $detail) {
                // 1. Aplicar cambio de estado a la llanta
                $result = $this->tireServices->applyMovement(
                    $detail->tire_code,
                    $detail->movement_type,
                    [
                        'vehicle_code_from' => $detail->vehicle_code_from,
                        'vehicle_code_to' => $detail->vehicle_code_to,
                        'position_from' => $detail->position_from,
                        'position_to' => $detail->position_to,
                        'axle_code_to' => $detail->axle_code_to ?? null,
                    ],
                    $tx
                );

                if ($result['Success'] !== 'Ok') {
                    $errors[] = "Línea {$detail->linenum}: {$result['Msg']}";
                    continue;
                }

                // 2. Actualizar vehicle_tire según el tipo de movimiento
                switch ($detail->movement_type) {
                    case DocTireMovementDetail::MOVEMENT_TYPE_ASSIGN:
                    case DocTireMovementDetail::MOVEMENT_TYPE_TRANSFER:
                    case DocTireMovementDetail::MOVEMENT_TYPE_REPAIR_RETURN:
                        // Asignar llanta al vehículo destino
                        $this->tireServices->updateVehicleTire(
                            $detail->tire_code,
                            $detail->vehicle_code_to,
                            $detail->position_to,
                            $detail->axle_code_to ?? null,
                            null,
                            $tx
                        );
                        break;

                    case DocTireMovementDetail::MOVEMENT_TYPE_ROTATE:
                        // Actualizar posición en el mismo vehículo
                        $this->tireServices->updateVehicleTire(
                            $detail->tire_code,
                            $detail->vehicle_code_from,
                            $detail->position_to,
                            $detail->axle_code_to ?? null,
                            null,
                            $tx
                        );
                        break;

                    case DocTireMovementDetail::MOVEMENT_TYPE_REMOVE:
                        // Desasignar llanta del vehículo
                        $this->tireServices->updateVehicleTire(
                            $detail->tire_code,
                            null,
                            null,
                            null,
                            null,
                            $tx
                        );
                        break;

                    case DocTireMovementDetail::MOVEMENT_TYPE_REPAIR_SEND:
                        // Desasignar del vehículo (va a taller)
                        $this->tireServices->updateVehicleTire(
                            $detail->tire_code,
                            null,
                            null,
                            null,
                            null,
                            $tx
                        );
                        break;

                    case DocTireMovementDetail::MOVEMENT_TYPE_SCRAP:
                        // Desasignar del vehículo (va a desecho)
                        $this->tireServices->updateVehicleTire(
                            $detail->tire_code,
                            null,
                            null,
                            null,
                            null,
                            $tx
                        );
                        break;
                }

                // 3. Marcar línea como ejecutada
                $detail->line_status = DocTireMovementDetail::LINE_STATUS_EXECUTED;
                if (!$detail->save()) {
                    $errors[] = "Línea {$detail->linenum}: Error al marcar como ejecutada";
                    continue;
                }

                $processed++;
            }

            // 4. Actualizar km de vehículos involucrados
            $vehicles = DocTireMovementVehicle::find()
                ->where(['docentry' => $docentry])
                ->all();

            foreach ($vehicles as $vehicle) {
                if ($vehicle->vehicle_km !== null) {
                    $this->vehicleService->updateKm(
                        $vehicle->vehicle_code,
                        (float)$vehicle->vehicle_km,
                        $tx
                    );
                }
            }

            if (!empty($errors)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return [
                    'Success' => 'Error',
                    'Msg' => 'Errores en integración: ' . implode('; ', $errors),
                ];
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => "{$processed} línea(s) procesadas correctamente"];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error en integración de movimiento';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Integración: Documento de Reparación
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Procesa la integración al cerrar un documento de reparación.
     *
     * @param int $docentry
     * @param Transaction|null $transaction
     * @return array
     */
    public function onRepairClosed(int $docentry, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $details = DocTireRepairDetail::find()
                ->where(['docentry' => $docentry])
                ->all();

            if (empty($details)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'No hay líneas de reparación por procesar'];
            }

            $errors = [];
            $processed = 0;

            foreach ($details as $detail) {
                $tire = Tire::findOne(['tire_code' => $detail->tire_code]);
                if ($tire === null) {
                    $errors[] = "Línea {$detail->linenum}: Llanta {$detail->tire_code} no encontrada";
                    continue;
                }

                // Registrar reparación
                $result = $this->tireServices->registerRepair($detail->tire_code, $tx);
                if ($result['Success'] !== 'Ok') {
                    $errors[] = "Línea {$detail->linenum}: {$result['Msg']}";
                    continue;
                }

                // Actualizar km si se registró
                if ($detail->tire_km !== null && $detail->tire_km > 0) {
                    $this->tireServices->updateKm($detail->tire_code, (float)$detail->tire_km, $tx);
                }

                // Actualizar desgaste si se registró
                if ($detail->tread_depth !== null) {
                    $this->tireServices->updateDesgaste(
                        $detail->tire_code,
                        (float)$detail->tread_depth,
                        null,
                        $tx
                    );
                }

                $processed++;
            }

            if (!empty($errors)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return [
                    'Success' => 'Error',
                    'Msg' => 'Errores en integración de reparación: ' . implode('; ', $errors),
                ];
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => "{$processed} reparación(es) procesada(s)"];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error en integración de reparación';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Integración: Documento de Disposición (desecho)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Procesa la integración al cerrar un documento de disposición.
     *
     * @param int $docentry
     * @param Transaction|null $transaction
     * @return array
     */
    public function onDisposalClosed(int $docentry, ?Transaction $transaction = null): array
    {
        $ownTx = ($transaction === null);
        $tx = $ownTx ? \Yii::$app->db->beginTransaction() : $transaction;

        try {
            $details = DocTireDisposalDetail::find()
                ->where(['docentry' => $docentry])
                ->all();

            if (empty($details)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return ['Success' => 'Error', 'Msg' => 'No hay líneas de disposición por procesar'];
            }

            $errors = [];
            $processed = 0;

            foreach ($details as $detail) {
                // Marcar llanta como desechada
                $result = $this->tireServices->markAsDisposed($detail->tire_code, $tx);
                if ($result['Success'] !== 'Ok') {
                    $errors[] = "Línea {$detail->linenum}: {$result['Msg']}";
                    continue;
                }

                $processed++;
            }

            if (!empty($errors)) {
                if ($ownTx) {
                    $tx->rollBack();
                }
                return [
                    'Success' => 'Error',
                    'Msg' => 'Errores en integración de disposición: ' . implode('; ', $errors),
                ];
            }

            if ($ownTx) {
                $tx->commit();
            }
            return ['Success' => 'Ok', 'Msg' => "{$processed} disposición(es) procesada(s)"];
        } catch (\Throwable $e) {
            if ($ownTx) {
                $tx->rollBack();
            }
            $msg = 'Error en integración de disposición';
            if (YII_DEBUG) {
                $msg .= ': ' . $e->getMessage();
            }
            \Yii::error($e->getMessage(), __METHOD__);
            return ['Success' => 'Error', 'Msg' => $msg];
        }
    }
}
