<?php

declare(strict_types=1);

namespace app\models\objects;

use app\models\tables\Series;
use Throwable;
use Yii;
use yii\db\Connection;
use yii\db\Exception;
use yii\db\Transaction;

/**
 * Servicio para la gestión de series de numeración de documentos.
 *
 * Proporciona operaciones CRUD y la generación atómica de números
 * consecutivos basados en la configuración de cada serie.
 */
class SeriesServices
{
    /**
     * Listar todas las series.
     *
     * @return array{Success: string, Msg: string, Data: array}
     */
    public function list(): array
    {
        try {
            $models = Series::find()
                ->orderBy(['name' => SORT_ASC])
                ->all();

            $data = array_map(function (Series $model): array {
                return $this->mapRow($model);
            }, $models);

            return [
                'Success' => 'Ok',
                'Msg' => 'Series listadas correctamente',
                'Data' => $data,
            ];
        } catch (Throwable $e) {
            return [
                'Success' => 'Error',
                'Msg' => 'Error al listar series: ' . $e->getMessage(),
                'Data' => [],
            ];
        }
    }

    /**
     * Obtener una serie por ID.
     *
     * @param int $id
     * @return array{Success: string, Msg: string, Data: array|null}
     */
    public function get(int $id): array
    {
        try {
            $model = Series::findOne($id);
            if ($model === null) {
                return [
                    'Success' => 'Error',
                    'Msg' => 'Serie no encontrada',
                    'Data' => null,
                ];
            }

            return [
                'Success' => 'Ok',
                'Msg' => 'Serie encontrada',
                'Data' => $this->mapRow($model),
            ];
        } catch (Throwable $e) {
            return [
                'Success' => 'Error',
                'Msg' => 'Error al obtener serie: ' . $e->getMessage(),
                'Data' => null,
            ];
        }
    }

    /**
     * Guardar (crear o actualizar) una serie.
     *
     * @param array $data
     * @return array{Success: string, Msg: string, Data: array|null}
     */
    public function save(array $data): array
    {
        try {
            $id = (int)($data['id'] ?? 0);
            $model = $id > 0 ? Series::findOne($id) : new Series();

            if ($id > 0 && $model === null) {
                return [
                    'Success' => 'Error',
                    'Msg' => 'Serie no encontrada',
                    'Data' => null,
                ];
            }

            // Cargar atributos
            $model->name = $data['name'] ?? $model->name;
            $model->object_name = $data['object_name'] ?? $model->object_name;
            $model->prefix = $data['prefix'] ?? $model->prefix;
            $model->suffix = $data['suffix'] ?? $model->suffix;
            $model->padding_length = (int)($data['padding_length'] ?? $model->padding_length);
            $model->current_consecutive = (int)($data['current_consecutive'] ?? $model->current_consecutive);
            $model->max_consecutive = (int)($data['max_consecutive'] ?? $model->max_consecutive);
            $model->is_active = $data['is_active'] ?? $model->is_active;
            $model->is_default = $data['is_default'] ?? $model->is_default;

            if (!$model->save()) {
                $errors = $model->getErrors();
                $msg = 'Error de validación';
                foreach ($errors as $attr => $errs) {
                    $msg .= " [$attr: " . implode(', ', $errs) . ']';
                }
                return [
                    'Success' => 'Error',
                    'Msg' => $msg,
                    'Data' => null,
                ];
            }

            // Si esta serie se marcó como default, asegurar que sea la única
            // default para su object_name
            if ($model->is_default === Series::IS_DEFAULT_Y) {
                Series::updateAll(
                    ['is_default' => Series::IS_DEFAULT_N],
                    [
                        'and',
                        ['object_name' => $model->object_name],
                        ['!=', 'id', $model->id],
                        ['is_default' => Series::IS_DEFAULT_Y],
                    ]
                );
            }

            return [
                'Success' => 'Ok',
                'Msg' => $id > 0 ? 'Serie actualizada correctamente' : 'Serie creada correctamente',
                'Data' => $this->mapRow($model),
            ];
        } catch (Throwable $e) {
            return [
                'Success' => 'Error',
                'Msg' => 'Error al guardar serie: ' . $e->getMessage(),
                'Data' => null,
            ];
        }
    }

    /**
     * Eliminar una serie por ID.
     *
     * @param int $id
     * @return array{Success: string, Msg: string, Data: null}
     */
    public function delete(int $id): array
    {
        try {
            $model = Series::findOne($id);
            if ($model === null) {
                return [
                    'Success' => 'Error',
                    'Msg' => 'Serie no encontrada',
                    'Data' => null,
                ];
            }

            if (!$model->delete()) {
                return [
                    'Success' => 'Error',
                    'Msg' => 'No se pudo eliminar la serie',
                    'Data' => null,
                ];
            }

            return [
                'Success' => 'Ok',
                'Msg' => 'Serie eliminada correctamente',
                'Data' => null,
            ];
        } catch (Throwable $e) {
            return [
                'Success' => 'Error',
                'Msg' => 'Error al eliminar serie: ' . $e->getMessage(),
                'Data' => null,
            ];
        }
    }

    /**
     * Obtener opciones para formularios (Select2).
     *
     * @return array{Success: string, Msg: string, Data: array}
     */
    public function getFormOptions(): array
    {
        try {
            $objectNames = Series::find()
                ->select(['object_name'])
                ->where(['is_active' => Series::ACTIVE_Y])
                ->groupBy(['object_name'])
                ->orderBy(['object_name' => SORT_ASC])
                ->column();

            return [
                'Success' => 'Ok',
                'Msg' => 'Opciones obtenidas correctamente',
                'Data' => [
                    'objectNames' => $objectNames,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'Success' => 'Error',
                'Msg' => 'Error al obtener opciones: ' . $e->getMessage(),
                'Data' => [],
            ];
        }
    }

    /**
     * Obtener el siguiente número de documento sin incrementar (solo lectura).
     *
     * Útil para mostrar el número en la vista de creación sin quemar el consecutivo.
     * NO incrementa `current_consecutive`. Para generar y consumir use `getNextNumber()`.
     *
     * @param string $objectName Nombre del objeto (ej: DocTireMovement)
     * @param int $seriesId ID de la serie
     * @return array{Success: string, Msg: string, Data: array|null}
     *         Data: {seriesId: int, docNum: string, consecutive: int}
     */
    public function peekNextNumber(string $objectName, int $seriesId): array
    {
        try {
            $model = Series::find()
                ->where([
                    'id' => $seriesId,
                    'object_name' => $objectName,
                    'is_active' => Series::ACTIVE_Y,
                ])
                ->one();

            if ($model === null) {
                return [
                    'Success' => 'Error',
                    'Msg' => 'Serie no encontrada o inactiva para el objeto: ' . $objectName,
                    'Data' => null,
                ];
            }

            // Validar que no haya alcanzado el máximo
            if ($model->current_consecutive >= $model->max_consecutive) {
                return [
                    'Success' => 'Error',
                    'Msg' => 'La serie "' . $model->name . '" ha alcanzado el límite máximo de consecutivos (' . $model->max_consecutive . ')',
                    'Data' => null,
                ];
            }

            // Solo lectura: el siguiente consecutivo es current + 1
            $nextConsecutive = $model->current_consecutive + 1;
            $docNum = $model->prefix . $nextConsecutive . $model->suffix;

            return [
                'Success' => 'Ok',
                'Msg' => 'Número obtenido correctamente',
                'Data' => [
                    'seriesId' => (int)$model->id,
                    'docNum' => $docNum,
                    'consecutive' => $nextConsecutive,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'Success' => 'Error',
                'Msg' => 'Error al obtener número: ' . $e->getMessage(),
                'Data' => null,
            ];
        }
    }

    /**
     * Generar y consumir el siguiente número de documento de forma atómica.
     *
     * Incrementa `current_consecutive` con bloqueo FOR UPDATE y devuelve
     * el número formateado. Solo debe llamarse al momento de guardar
     * el documento (NO en vistas previas).
     *
     * @param string $objectName Nombre del objeto (ej: DocTireMovement)
     * @param int $seriesId ID de la serie
     * @return array{Success: string, Msg: string, Data: array|null}
     *         Data: {seriesId: int, docNum: string, consecutive: int}
     */
    public function getNextNumber(string $objectName, int $seriesId): array
    {
        /** @var Connection $db */
        $db = Yii::$app->db;
        /** @var Transaction|null $transaction */
        $transaction = null;

        try {
            // Usar transacción para bloqueo atómico
            $transaction = $db->beginTransaction();

            // Bloquear la fila con FOR UPDATE vía SQL directo
            // Yii2 ~2.0.54 no tiene lockForUpdate() ni forUpdate()
            $sql = "SELECT * FROM {{%doc_series}} WHERE id = :id AND object_name = :obj AND is_active = :act FOR UPDATE";
            $row = $db->createCommand($sql, [
                ':id' => $seriesId,
                ':obj' => $objectName,
                ':act' => Series::ACTIVE_Y,
            ])->queryOne();

            if ($row === false) {
                $transaction->rollBack();
                return [
                    'Success' => 'Error',
                    'Msg' => 'Serie no encontrada o inactiva para el objeto: ' . $objectName,
                    'Data' => null,
                ];
            }

            // Obtener modelo ActiveRecord para usar save()
            $model = Series::findOne($row['id']);
            if ($model === null) {
                $transaction->rollBack();
                return [
                    'Success' => 'Error',
                    'Msg' => 'Serie no encontrada después del bloqueo',
                    'Data' => null,
                ];
            }

            // Validar que no haya alcanzado el máximo
            if ($model->current_consecutive >= $model->max_consecutive) {
                $transaction->rollBack();
                return [
                    'Success' => 'Error',
                    'Msg' => 'La serie "' . $model->name . '" ha alcanzado el límite máximo de consecutivos (' . $model->max_consecutive . ')',
                    'Data' => null,
                ];
            }

            // Incrementar consecutivo
            $model->current_consecutive++;
            if (!$model->save()) {
                $transaction->rollBack();
                return [
                    'Success' => 'Error',
                    'Msg' => 'Error al actualizar consecutivo de la serie',
                    'Data' => null,
                ];
            }

            $transaction->commit();

            // Formatear número
            $consecutive = $model->current_consecutive;
            $docNum = $model->prefix . $consecutive . $model->suffix;

            return [
                'Success' => 'Ok',
                'Msg' => 'Número generado correctamente',
                'Data' => [
                    'seriesId' => (int)$model->id,
                    'docNum' => $docNum,
                    'consecutive' => $consecutive,
                ],
            ];
        } catch (Throwable $e) {
            if ($transaction !== null && $transaction->isActive) {
                $transaction->rollBack();
            }
            return [
                'Success' => 'Error',
                'Msg' => 'Error al generar número: ' . $e->getMessage(),
                'Data' => null,
            ];
        }
    }

    /**
     * Mapear un modelo Series a un array plano.
     *
     * @param Series $model
     * @return array
     */
    private function mapRow(Series $model): array
    {
        return [
            'id' => (int)$model->id,
            'name' => $model->name,
            'object_name' => $model->object_name,
            'prefix' => $model->prefix,
            'suffix' => $model->suffix,
            'padding_length' => (int)$model->padding_length,
            'current_consecutive' => (int)$model->current_consecutive,
            'max_consecutive' => (int)$model->max_consecutive,
            'is_active' => $model->is_active,
            'is_default' => $model->is_default,
        ];
    }
}
