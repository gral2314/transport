<?php

declare(strict_types=1);

use app\components\BaseMigration;

/**
 * Migración para extender el flujo de trabajo de doc_tire_repair:
 *  - Nuevos estados operativos (7-state workshop flow)
 *  - Timestamps de trazabilidad (released_at, started_at, completed_at,
 *    validated_at, cancelled_at)
 *  - Columnas de responsables (technician_user_id, validated_by_user_id)
 *  - rejection_notes para rechazo del supervisor
 *  - Columnas de medición en detalle (tire_km, tread_depth, deviation_notes)
 *
 * Flujo: PLAN → LIB → TALLER → EXEC → VAL → CLOSE  (+ CANCELLED)
 */
final class m260617_000001_add_repair_workflow_fields extends BaseMigration
{
    public function safeUp(): void
    {
        // ═══════════════════════════════════════════════════════════════════
        // doc_tire_repair — HEADER
        // ═══════════════════════════════════════════════════════════════════
        $headerSchema = $this->db->schema->getTableSchema('{{%doc_tire_repair}}', true);

        if ($headerSchema !== null) {
            // ── Timestamps de trazabilidad ─────────────────────────────
            if (!isset($headerSchema->columns['released_at'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair}}',
                    'released_at',
                    $this->dateTime()->null()->comment('Fecha/hora en que el Admin libera la orden')
                );
                echo "    > released_at agregado a doc_tire_repair.\n";
            }

            if (!isset($headerSchema->columns['started_at'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair}}',
                    'started_at',
                    $this->dateTime()->null()->comment('Fecha/hora en que el Tecnico inicia el trabajo')
                );
                echo "    > started_at agregado a doc_tire_repair.\n";
            }

            if (!isset($headerSchema->columns['completed_at'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair}}',
                    'completed_at',
                    $this->dateTime()->null()->comment('Fecha/hora en que el Tecnico finaliza y envia a validacion')
                );
                echo "    > completed_at agregado a doc_tire_repair.\n";
            }

            if (!isset($headerSchema->columns['validated_at'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair}}',
                    'validated_at',
                    $this->dateTime()->null()->comment('Fecha/hora en que el Supervisor cierra/valida')
                );
                echo "    > validated_at agregado a doc_tire_repair.\n";
            }

            if (!isset($headerSchema->columns['cancelled_at'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair}}',
                    'cancelled_at',
                    $this->dateTime()->null()->comment('Fecha/hora de cancelacion (si aplica)')
                );
                echo "    > cancelled_at agregado a doc_tire_repair.\n";
            }

            // ── Responsables ───────────────────────────────────────────
            if (!isset($headerSchema->columns['technician_user_id'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair}}',
                    'technician_user_id',
                    $this->bigInteger()->null()->comment('Tecnico asignado (FK users.id)')
                );
                echo "    > technician_user_id agregado a doc_tire_repair.\n";
            }

            if (!isset($headerSchema->columns['validated_by_user_id'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair}}',
                    'validated_by_user_id',
                    $this->bigInteger()->null()->comment('Supervisor que aprueba (FK users.id)')
                );
                echo "    > validated_by_user_id agregado a doc_tire_repair.\n";
            }

            // ── rejection_notes ────────────────────────────────────────
            if (!isset($headerSchema->columns['rejection_notes'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair}}',
                    'rejection_notes',
                    $this->text()->null()->comment('Notas de rechazo del supervisor')
                );
                echo "    > rejection_notes agregado a doc_tire_repair.\n";
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // MODIFICACIÓN ENUM status: 4 valores → 7 valores
        // ═══════════════════════════════════════════════════════════════════
        if ($headerSchema !== null && isset($headerSchema->columns['status'])) {
            $currentStatus = $headerSchema->columns['status']->dbType;
            // Si aún contiene solo los 4 valores originales, migrar
            if (strpos($currentStatus, "'PLAN'") !== false
                && strpos($currentStatus, "'LIB'") === false
            ) {
                // 1. Agregar nuevos valores como parte del ENUM (conservando los viejos)
                $this->execute(
                    "ALTER TABLE {{%doc_tire_repair}} MODIFY COLUMN `status` "
                    . "ENUM('PLAN','EXEC','VAL','CLOSE','LIB','TALLER','CANCELLED') "
                    . "NOT NULL DEFAULT 'PLAN' "
                    . "COMMENT 'Estado operativo (PLAN=Planeado, LIB=Liberado, TALLER=En Taller, EXEC=Ejecutado, VAL=En Validacion, CLOSE=Cerrado, CANCELLED=Cancelado)'"
                );
                echo "    > ENUM status extendido en doc_tire_repair.\n";
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // ÍNDICES: doc_tire_repair
        // ═══════════════════════════════════════════════════════════════════
        if ($headerSchema !== null) {
            $this->createIndex(
                'idx_doc_tire_repair_technician_user_id',
                '{{%doc_tire_repair}}',
                'technician_user_id'
            );
            $this->createIndex(
                'idx_doc_tire_repair_validated_by_user_id',
                '{{%doc_tire_repair}}',
                'validated_by_user_id'
            );
        }

        // ═══════════════════════════════════════════════════════════════════
        // FOREIGN KEYS: doc_tire_repair
        // ═══════════════════════════════════════════════════════════════════
        $this->addForeignKey(
            'fk_doc_tire_repair_technician',
            '{{%doc_tire_repair}}',
            'technician_user_id',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_doc_tire_repair_validated',
            '{{%doc_tire_repair}}',
            'validated_by_user_id',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // ═══════════════════════════════════════════════════════════════════
        // doc_tire_repair_detail — DETALLE
        // ═══════════════════════════════════════════════════════════════════
        $detailSchema = $this->db->schema->getTableSchema('{{%doc_tire_repair_detail}}', true);

        if ($detailSchema !== null) {
            if (!isset($detailSchema->columns['tire_km'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair_detail}}',
                    'tire_km',
                    $this->integer()->null()->comment('Kilometraje de la llanta al momento de la reparacion')
                );
                echo "    > tire_km agregado a doc_tire_repair_detail.\n";
            }

            if (!isset($detailSchema->columns['tread_depth'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair_detail}}',
                    'tread_depth',
                    $this->decimal(5, 2)->null()->comment('Profundidad de banda de rodamiento (mm) al reparar')
                );
                echo "    > tread_depth agregado a doc_tire_repair_detail.\n";
            }

            if (!isset($detailSchema->columns['deviation_notes'])) {
                $this->addColumnWithLog(
                    '{{%doc_tire_repair_detail}}',
                    'deviation_notes',
                    $this->text()->null()->comment('Notas del tecnico explicando desviaciones respecto al plan')
                );
                echo "    > deviation_notes agregado a doc_tire_repair_detail.\n";
            }
        }
    }

    public function safeDown(): void
    {
        // ═══════════════════════════════════════════════════════════════════
        // doc_tire_repair_detail — revertir columnas nuevas
        // ═══════════════════════════════════════════════════════════════════
        $detailSchema = $this->db->schema->getTableSchema('{{%doc_tire_repair_detail}}', true);
        if ($detailSchema !== null) {
            if (isset($detailSchema->columns['deviation_notes'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair_detail}}', 'deviation_notes');
                echo "    > deviation_notes eliminado de doc_tire_repair_detail.\n";
            }
            if (isset($detailSchema->columns['tread_depth'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair_detail}}', 'tread_depth');
                echo "    > tread_depth eliminado de doc_tire_repair_detail.\n";
            }
            if (isset($detailSchema->columns['tire_km'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair_detail}}', 'tire_km');
                echo "    > tire_km eliminado de doc_tire_repair_detail.\n";
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // doc_tire_repair — revertir FKs
        // ═══════════════════════════════════════════════════════════════════
        $headerSchema = $this->db->schema->getTableSchema('{{%doc_tire_repair}}', true);
        if ($headerSchema !== null) {
            if (isset($headerSchema->foreignKeys['fk_doc_tire_repair_validated'])) {
                $this->dropForeignKey('fk_doc_tire_repair_validated', '{{%doc_tire_repair}}');
            }
            if (isset($headerSchema->foreignKeys['fk_doc_tire_repair_technician'])) {
                $this->dropForeignKey('fk_doc_tire_repair_technician', '{{%doc_tire_repair}}');
            }

            // ── Revertir índices ───────────────────────────────────────
            $this->dropIndex('idx_doc_tire_repair_validated_by_user_id', '{{%doc_tire_repair}}');
            $this->dropIndex('idx_doc_tire_repair_technician_user_id', '{{%doc_tire_repair}}');

            // ── Revertir ENUM status ───────────────────────────────────
            if (isset($headerSchema->columns['status'])) {
                $currentStatus = $headerSchema->columns['status']->dbType;
                // Solo revertir si tiene los valores nuevos
                if (strpos($currentStatus, "'LIB'") !== false) {
                    $this->execute(
                        "ALTER TABLE {{%doc_tire_repair}} MODIFY COLUMN `status` "
                        . "ENUM('PLAN','EXEC','VAL','CLOSE') "
                        . "NOT NULL DEFAULT 'PLAN' "
                        . "COMMENT 'Estado operativo (PLAN=Planeado, EXEC=Ejecutado, VAL=Validado, CLOSE=Cerrado)'"
                    );
                    echo "    > ENUM status revertido en doc_tire_repair.\n";
                }
            }

            // ── Revertir columnas ──────────────────────────────────────
            if (isset($headerSchema->columns['rejection_notes'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair}}', 'rejection_notes');
                echo "    > rejection_notes eliminado de doc_tire_repair.\n";
            }
            if (isset($headerSchema->columns['validated_by_user_id'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair}}', 'validated_by_user_id');
                echo "    > validated_by_user_id eliminado de doc_tire_repair.\n";
            }
            if (isset($headerSchema->columns['technician_user_id'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair}}', 'technician_user_id');
                echo "    > technician_user_id eliminado de doc_tire_repair.\n";
            }
            if (isset($headerSchema->columns['cancelled_at'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair}}', 'cancelled_at');
                echo "    > cancelled_at eliminado de doc_tire_repair.\n";
            }
            if (isset($headerSchema->columns['validated_at'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair}}', 'validated_at');
                echo "    > validated_at eliminado de doc_tire_repair.\n";
            }
            if (isset($headerSchema->columns['completed_at'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair}}', 'completed_at');
                echo "    > completed_at eliminado de doc_tire_repair.\n";
            }
            if (isset($headerSchema->columns['started_at'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair}}', 'started_at');
                echo "    > started_at eliminado de doc_tire_repair.\n";
            }
            if (isset($headerSchema->columns['released_at'])) {
                $this->dropColumnWithLog('{{%doc_tire_repair}}', 'released_at');
                echo "    > released_at eliminado de doc_tire_repair.\n";
            }
        }
    }
}
