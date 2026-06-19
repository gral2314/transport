<?php

declare(strict_types=1);

use app\components\BaseMigration;

/**
 * Agregar columnas assigned_position_code y assigned_axle_code a {{%tire}}
 * para registrar la posición y eje exacto donde está montada la llanta
 * cuando está asignada a un vehículo.
 *
 * Estas columnas son informativas (redundancia controlada) para evitar
 * tener que consultar vehicle_tire cada vez que se necesita saber
 * la posición de una llanta.
 */
class m260610_000004_add_tire_position_fields extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $schema = $this->db->getTableSchema('{{%tire}}');
        if ($schema === null) {
            return;
        }

        // ─── assigned_position_code ───────────────────────────────────────
        if (!isset($schema->columns['assigned_position_code'])) {
            $this->addColumnWithLog(
                '{{%tire}}',
                'assigned_position_code',
                "VARCHAR(50) NULL DEFAULT NULL COMMENT 'Posición asignada en el vehículo (LI, LO, RI, RO, LS, RS, etc.)'"
            );
        }

        // ─── assigned_axle_code ───────────────────────────────────────────
        if (!isset($schema->columns['assigned_axle_code'])) {
            $this->addColumnWithLog(
                '{{%tire}}',
                'assigned_axle_code',
                "VARCHAR(50) NULL DEFAULT NULL COMMENT 'Eje asignado en el vehículo'"
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumnWithLog('{{%tire}}', 'assigned_position_code');
        $this->dropColumnWithLog('{{%tire}}', 'assigned_axle_code');
    }
}
