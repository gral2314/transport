<?php

declare(strict_types=1);

use app\components\BaseMigration;

/**
 * Migracion para agregar trazabilidad temporal y asignacion de responsables
 * a la tabla doc_tire_movement y doc_tire_movement_detail.
 *
 * Cambios:
 *  1. doc_tire_movement - nuevas columnas de timestamps (released_at, started_at,
 *     completed_at, validated_at, cancelled_at) y responsables (assigned_to_user_id,
 *     validated_by_user_id)
 *  2. doc_tire_movement - modificacion del ENUM status con nuevos valores:
 *     PLANNED, RELEASED, IN_PROGRESS, PENDING_VALIDATION, CLOSED, CANCELLED
 *  3. doc_tire_movement_detail - nueva columna deviation_notes (TEXT)
 *  4. Registros en modules para menu Taller (movil)
 */
final class m260613_000001_tire_movement_timeline extends BaseMigration
{
    public function safeUp(): void
    {
        $tableSchema = $this->db->schema->getTableSchema('{{%doc_tire_movement}}', true);

        // ==========================================
        // COLUMNAS: doc_tire_movement
        // ==========================================
        if ($tableSchema !== null) {
            if (!isset($tableSchema->columns['released_at'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_movement}}',
                    'released_at',
                    $this->dateTime()->null()->comment('Fecha/hora en que el Admin libera la orden')
                );
            }

            if (!isset($tableSchema->columns['started_at'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_movement}}',
                    'started_at',
                    $this->dateTime()->null()->comment('Fecha/hora en que el Tecnico inicia el trabajo')
                );
            }

            if (!isset($tableSchema->columns['completed_at'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_movement}}',
                    'completed_at',
                    $this->dateTime()->null()->comment('Fecha/hora en que el Tecnico finaliza y envia a validacion')
                );
            }

            if (!isset($tableSchema->columns['validated_at'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_movement}}',
                    'validated_at',
                    $this->dateTime()->null()->comment('Fecha/hora en que el Supervisor cierra/valida')
                );
            }

            if (!isset($tableSchema->columns['cancelled_at'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_movement}}',
                    'cancelled_at',
                    $this->dateTime()->null()->comment('Fecha/hora de cancelacion (si aplica)')
                );
            }

            if (!isset($tableSchema->columns['assigned_to_user_id'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_movement}}',
                    'assigned_to_user_id',
                    $this->bigInteger()->null()->comment('Tecnico responsable (FK users.id)')
                );
            }

            if (!isset($tableSchema->columns['validated_by_user_id'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_movement}}',
                    'validated_by_user_id',
                    $this->bigInteger()->null()->comment('Supervisor que aprueba (FK users.id)')
                );
            }
        }

        // ==========================================
        // MODIFICACION ENUM status
        // ==========================================
        // Cambiar ENUM de ('PLAN','EXEC','VAL','CLOSE') a
        // ('PLANNED','RELEASED','IN_PROGRESS','PENDING_VALIDATION','CLOSED','CANCELLED')
        // Se usa ALTER TABLE con MODIFY COLUMN (idempotente: solo si aun existe el ENUM viejo)
        if ($tableSchema !== null && isset($tableSchema->columns['status'])) {
            $currentStatus = $tableSchema->columns['status']->dbType;
            // Si aun contiene el valor 'PLAN' (viejo), migramos
            if (strpos($currentStatus, "'PLAN'") !== false) {
                // 1. Agregar nuevos valores como parte del ENUM
                $this->execute("ALTER TABLE {{%doc_tire_movement}} MODIFY COLUMN `status` ENUM('PLAN','EXEC','VAL','CLOSE','PLANNED','RELEASED','IN_PROGRESS','PENDING_VALIDATION','CANCELLED') NOT NULL DEFAULT 'PLAN' COMMENT 'Estado operativo (PLAN=Planeado, EXEC=Ejecutado, VAL=Validado, CLOSE=Cerrado, PLANNED=Planeado(nuevo), RELEASED=Liberado, IN_PROGRESS=En progreso, PENDING_VALIDATION=Pendiente validacion, CANCELLED=Cancelado)'");

                // 2. Migrar datos viejos a nuevos valores
                $this->execute("UPDATE {{%doc_tire_movement}} SET `status` = 'PLANNED' WHERE `status` = 'PLAN'");
                $this->execute("UPDATE {{%doc_tire_movement}} SET `status` = 'CLOSED' WHERE `status` = 'CLOSE'");

                // 3. Cambiar a ENUM final solo con valores nuevos
                $this->execute("ALTER TABLE {{%doc_tire_movement}} MODIFY COLUMN `status` ENUM('PLANNED','RELEASED','IN_PROGRESS','PENDING_VALIDATION','CLOSED','CANCELLED') NOT NULL DEFAULT 'PLANNED' COMMENT 'Estado operativo (PLANNED=Planeado, RELEASED=Liberado, IN_PROGRESS=En progreso, PENDING_VALIDATION=Pendiente validacion, CLOSED=Cerrado, CANCELLED=Cancelado)'");
            }
        }

        // ==========================================
        // INDICES: doc_tire_movement
        // ==========================================
        if ($tableSchema !== null) {
            $this->createIndex(
                'idx_doc_tire_movement_assigned_to_user_id',
                '{{%doc_tire_movement}}',
                'assigned_to_user_id'
            );
            $this->createIndex(
                'idx_doc_tire_movement_validated_by_user_id',
                '{{%doc_tire_movement}}',
                'validated_by_user_id'
            );
        }

        // ==========================================
        // FOREIGN KEYS: doc_tire_movement
        // ==========================================
        $this->addForeignKey(
            'fk_doc_tire_movement_assigned_to_user_id',
            '{{%doc_tire_movement}}',
            'assigned_to_user_id',
            '{{%users}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_doc_tire_movement_validated_by_user_id',
            '{{%doc_tire_movement}}',
            'validated_by_user_id',
            '{{%users}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // ==========================================
        // COLUMNAS: doc_tire_movement_detail
        // ==========================================
        $detailSchema = $this->db->schema->getTableSchema('{{%doc_tire_movement_detail}}', true);
        if ($detailSchema !== null && !isset($detailSchema->columns['deviation_notes'])) {
            $this->addColumnWithLog(
                '{{%doc_tire_movement_detail}}',
                'deviation_notes',
                $this->text()->null()->comment('Notas del tecnico explicando desviaciones respecto al plan'
            ));
        }

        // ==========================================
        // SEEDS / REGISTROS INICIALES
        // Menu: Taller (Movil)
        // ==========================================
        $modulesSchema = $this->db->schema->getTableSchema('{{%modules}}', true);
        if ($modulesSchema !== null) {
            // Modulo padre: Taller
            $parentId = $this->upsertModule(
                name: 'Taller',
                description: 'Modulo de taller para dispositivos moviles',
                idparent: 0,
                page: '',
                icon: 'fa-solid fa-wrench',
                order: 720,
                type: 'M'
            );

            if ($parentId !== null) {
                // Submenu: Mis Ordenes de Trabajo
                $childId = $this->upsertModule(
                    name: 'Mis Ordenes',
                    description: 'Ordenes de trabajo asignadas al tecnico',
                    idparent: $parentId,
                    page: 'taller/index',
                    icon: 'fa-solid fa-list-check',
                    order: 1,
                    type: 'S'
                );

                // Submenu: Validacion (Supervisor)
                $validationId = $this->upsertModule(
                    name: 'Validar Trabajos',
                    description: 'Validacion de trabajos reportados por tecnicos',
                    idparent: $parentId,
                    page: 'taller/validation',
                    icon: 'fa-solid fa-clipboard-check',
                    order: 2,
                    type: 'S'
                );

                // Asignar permisos a usuario 1 y grupo 1 para los nuevos modulos
                $date = date('Y-m-d');
                $time = date('H:i:s');
                $moduleIds = array_filter([$parentId, $childId, $validationId]);
                foreach ($moduleIds as $mid) {
                    $this->grantModulePermission($mid, 1, null, $date, $time);   // usuario 1
                    $this->grantModulePermission($mid, null, 1, $date, $time);   // grupo 1
                }
            }
        }
    }

    public function safeDown(): void
    {
        // ==========================================
        // SAFE DOWN: orden inverso
        // ==========================================

        // Eliminar FK
        $this->dropForeignKey(
            'fk_doc_tire_movement_assigned_to_user_id',
            '{{%doc_tire_movement}}'
        );
        $this->dropForeignKey(
            'fk_doc_tire_movement_validated_by_user_id',
            '{{%doc_tire_movement}}'
        );

        // Eliminar indices
        $this->dropIndex('idx_doc_tire_movement_assigned_to_user_id', '{{%doc_tire_movement}}');
        $this->dropIndex('idx_doc_tire_movement_validated_by_user_id', '{{%doc_tire_movement}}');

        // Restaurar ENUM status a valores originales
        $tableSchema = $this->db->schema->getTableSchema('{{%doc_tire_movement}}', true);
        if ($tableSchema !== null && isset($tableSchema->columns['status'])) {
            $currentStatus = $tableSchema->columns['status']->dbType;
            if (strpos($currentStatus, "'PLANNED'") !== false) {
                // Migrar datos nuevos a viejos
                $this->execute("UPDATE {{%doc_tire_movement}} SET `status` = 'PLAN' WHERE `status` = 'PLANNED'");
                $this->execute("UPDATE {{%doc_tire_movement}} SET `status` = 'CLOSE' WHERE `status` = 'CLOSED'");
                // Restaurar ENUM original
                $this->execute("ALTER TABLE {{%doc_tire_movement}} MODIFY COLUMN `status` ENUM('PLAN','EXEC','VAL','CLOSE') NOT NULL DEFAULT 'PLAN' COMMENT 'Estado operativo (PLAN=Planeado, EXEC=Ejecutado, VAL=Validado, CLOSE=Cerrado)'");
            }
        }

        // Eliminar columnas de doc_tire_movement
        $columnsToDrop = [
            'released_at',
            'started_at',
            'completed_at',
            'validated_at',
            'cancelled_at',
            'assigned_to_user_id',
            'validated_by_user_id',
        ];
        foreach ($columnsToDrop as $col) {
            if ($tableSchema !== null && isset($tableSchema->columns[$col])) {
                $this->dropColumnWithLog('{{%doc_tire_movement}}', $col);
            }
        }

        // Eliminar columna deviation_notes de doc_tire_movement_detail
        $detailSchema = $this->db->schema->getTableSchema('{{%doc_tire_movement_detail}}', true);
        if ($detailSchema !== null && isset($detailSchema->columns['deviation_notes'])) {
            $this->dropColumnWithLog('{{%doc_tire_movement_detail}}', 'deviation_notes');
        }

        // Eliminar permisos asociados a estos modulos
        $pages = ['taller/index', 'taller/work-order', 'taller/validation'];
        $moduleIds = (new \yii\db\Query())
            ->select(['id'])
            ->from('{{%modules}}')
            ->where(['page' => $pages])
            ->column($this->db);

        if (!empty($moduleIds)) {
            $this->delete('{{%permissions}}', ['idmodulo' => $moduleIds]);
        }

        // Eliminar registros de menu (en orden inverso: hijos primero)
        $this->delete('{{%modules}}', ['page' => 'taller/validation']);
        $this->delete('{{%modules}}', ['page' => 'taller/work-order']);
        $this->delete('{{%modules}}', ['page' => 'taller/index']);
    }

    /**
     * Inserta un permiso en la tabla permissions si no existe ya.
     */
    private function grantModulePermission(int $idmodulo, ?int $iduser, ?int $idgroup, string $date, string $time): void
    {
        $condition = ['idmodulo' => $idmodulo, 'admit' => 'Y'];
        if ($iduser !== null) {
            $condition['iduser'] = $iduser;
        } else {
            $condition['idgroup'] = $idgroup;
        }

        $exists = (new \yii\db\Query())
            ->from('{{%permissions}}')
            ->where($condition)
            ->exists($this->db);

        if (!$exists) {
            $this->insert('{{%permissions}}', [
                'iduser'    => $iduser,
                'idgroup'   => $idgroup,
                'idmodulo'  => $idmodulo,
                'admit'     => 'Y',
                'createdate' => $date,
                'createtime' => $time,
                'createuser' => 1,
                'updatedate' => null,
                'updatetime' => null,
                'updateuser' => 0,
            ]);
        }
    }

    /**
     * Inserta o actualiza un modulo y retorna su ID.
     */
    private function upsertModule(
        string $name,
        string $description,
        int $idparent,
        string $page,
        string $icon,
        int $order,
        string $type
    ): ?int {
        $existing = (new \yii\db\Query())
            ->from('{{%modules}}')
            ->where(['page' => $page])
            ->one($this->db);

        if ($existing !== false) {
            return (int)$existing['id'];
        }

        $this->insert('{{%modules}}', [
            'name'        => $name,
            'description' => $description,
            'idparent'    => $idparent,
            'page'        => $page,
            'icon'        => $icon,
            'active'      => 'Y',
            'order'       => $order,
            'type'        => $type,
            'createdate'  => date('Y-m-d'),
            'createtime'  => date('H:i:s'),
            'createuser'  => 1,
        ]);

        $newId = (int)$this->db->getLastInsertID();
        return $newId;
    }
}
