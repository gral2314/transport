<?php

declare(strict_types=1);

use app\components\BaseMigration;

/**
 * Migracion para limpiar duplicados en doc_tire_movement:
 *   1. Eliminar columna assigned_to_user_id (duplicada de technician_user_id)
 *   2. Limpiar ENUM status: eliminar valores viejos (PLAN, EXEC, VAL, CLOSE)
 *      que ya fueron migrados a los nuevos (PLANNED, CLOSED, etc.)
 *
 * Cambios:
 *  1. doc_tire_movement - DROP FK, INDEX y COLUMN assigned_to_user_id
 *  2. doc_tire_movement - MODIFY ENUM status solo con valores nuevos
 */
final class m260613_000003_cleanup_duplicate_status_and_assigned extends BaseMigration
{
    public function safeUp(): void
    {
        $tableSchema = $this->db->schema->getTableSchema('{{%doc_tire_movement}}', true);

        if ($tableSchema === null) {
            echo "    > Tabla doc_tire_movement no encontrada. Omitiendo.\n";
            return;
        }

        // ==========================================
        // 1. ELIMINAR assigned_to_user_id (duplicado)
        // ==========================================
        if (isset($tableSchema->columns['assigned_to_user_id'])) {
            // 1a. Eliminar FK
            $this->dropForeignKey(
                'fk_doc_tire_movement_assigned_to_user_id',
                '{{%doc_tire_movement}}'
            );

            // 1b. Eliminar indice
            $this->dropIndex(
                'idx_doc_tire_movement_assigned_to_user_id',
                '{{%doc_tire_movement}}'
            );

            // 1c. Eliminar columna
            $this->dropColumnWithLog('{{%doc_tire_movement}}', 'assigned_to_user_id');

            echo "    > Columna assigned_to_user_id eliminada de doc_tire_movement.\n";
        } else {
            echo "    > Columna assigned_to_user_id ya no existe. Omitiendo.\n";
        }

        // ==========================================
        // 2. LIMPIAR ENUM status (valores viejos)
        // ==========================================
        if (isset($tableSchema->columns['status'])) {
            $currentStatus = $tableSchema->columns['status']->dbType;

            // Si aun contiene valores viejos (PLAN, EXEC, VAL, CLOSE), los quitamos
            if (strpos($currentStatus, "'PLAN'") !== false) {
                $this->execute(
                    "ALTER TABLE {{%doc_tire_movement}} MODIFY COLUMN `status` "
                    . "ENUM('PLANNED','RELEASED','IN_PROGRESS','PENDING_VALIDATION','CLOSED','CANCELLED') "
                    . "NOT NULL DEFAULT 'PLANNED' "
                    . "COMMENT 'Estado operativo (PLANNED=Planeado, RELEASED=Liberado, IN_PROGRESS=En progreso, PENDING_VALIDATION=Pendiente validacion, CLOSED=Cerrado, CANCELLED=Cancelado)'"
                );
                echo "    > ENUM status limpiado (valores viejos eliminados).\n";
            } else {
                echo "    > ENUM status ya esta limpio. Omitiendo.\n";
            }
        }
    }

    public function safeDown(): void
    {
        $tableSchema = $this->db->schema->getTableSchema('{{%doc_tire_movement}}', true);

        if ($tableSchema === null) {
            echo "    > Tabla doc_tire_movement no encontrada. Omitiendo.\n";
            return;
        }

        // ==========================================
        // RESTAURAR ENUM status con valores viejos
        // ==========================================
        if (isset($tableSchema->columns['status'])) {
            $currentStatus = $tableSchema->columns['status']->dbType;
            if (strpos($currentStatus, "'PLANNED'") !== false) {
                $this->execute(
                    "ALTER TABLE {{%doc_tire_movement}} MODIFY COLUMN `status` "
                    . "ENUM('PLAN','EXEC','VAL','CLOSE','PLANNED','RELEASED','IN_PROGRESS','PENDING_VALIDATION','CANCELLED') "
                    . "NOT NULL DEFAULT 'PLANNED' "
                    . "COMMENT 'Estado operativo (PLAN=Planeado, EXEC=Ejecutado, VAL=Validado, CLOSE=Cerrado, PLANNED=Planeado(nuevo), RELEASED=Liberado, IN_PROGRESS=En progreso, PENDING_VALIDATION=Pendiente validacion, CANCELLED=Cancelado)'"
                );
                echo "    > ENUM status restaurado con valores viejos.\n";
            }
        }

        // ==========================================
        // RESTAURAR assigned_to_user_id
        // ==========================================
        if (!isset($tableSchema->columns['assigned_to_user_id'])) {
            $this->addColumnWithLog(
                '{{%doc_tire_movement}}',
                'assigned_to_user_id',
                $this->bigInteger()->null()->comment('Tecnico responsable (FK users.id)')
            );

            $this->createIndex(
                'idx_doc_tire_movement_assigned_to_user_id',
                '{{%doc_tire_movement}}',
                'assigned_to_user_id'
            );

            $this->addForeignKey(
                'fk_doc_tire_movement_assigned_to_user_id',
                '{{%doc_tire_movement}}',
                'assigned_to_user_id',
                '{{%users}}',
                'id',
                'RESTRICT',
                'CASCADE'
            );

            echo "    > Columna assigned_to_user_id restaurada.\n";
        }
    }
}
