<?php

declare(strict_types=1);

use app\components\BaseMigration;

/**
 * Migracion para agregar columnas de odometro a doc_tire_movement.
 *
 * Cambios:
 *  1. doc_tire_movement - odometer_initial (BIGINT, null) - Odometro inicial al crear la OT
 *  2. doc_tire_movement - odometer_final (BIGINT, null) - Odometro final al finalizar la OT
 */
class m260615_000005_add_odometer_to_doc_tire_movement extends BaseMigration
{
    public function safeUp(): void
    {
        $tableSchema = $this->db->schema->getTableSchema('{{%doc_tire_movement}}', true);

        if ($tableSchema === null) {
            return;
        }

        // ==========================================
        // COLUMNAS: doc_tire_movement
        // ==========================================

        if (!isset($tableSchema->columns['odometer_initial'])) {
            $this->addColumnWithLog(
                '{{%doc_tire_movement}}',
                'odometer_initial',
                $this->bigInteger()->null()->comment('Odometro (km) al momento de crear la orden')
            );
        }

        if (!isset($tableSchema->columns['odometer_final'])) {
            $this->addColumnWithLog(
                '{{%doc_tire_movement}}',
                'odometer_final',
                $this->bigInteger()->null()->comment('Odometro (km) al momento de finalizar la orden')
            );
        }
    }

    public function safeDown(): void
    {
        // FK / INDICES

        // COLUMNAS (orden inverso)
        $this->dropColumnWithLog('{{%doc_tire_movement}}', 'odometer_final');
        $this->dropColumnWithLog('{{%doc_tire_movement}}', 'odometer_initial');
    }
}
