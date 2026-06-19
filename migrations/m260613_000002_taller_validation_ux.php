<?php

declare(strict_types=1);

use app\components\BaseMigration;

/**
 * Agrega columna rejection_notes a doc_tire_movement para notas de rechazo del supervisor.
 */
class m260613_000002_taller_validation_ux extends BaseMigration
{
    public function safeUp(): void
    {
        $table = '{{%doc_tire_movement}}';
        $schema = $this->db->getTableSchema($this->db->schema->getRawTableName($table));

        if ($schema !== null && !isset($schema->columns['rejection_notes'])) {
            $this->addColumnWithLog($table, 'rejection_notes', $this->text()->null()->comment('Notas de rechazo del supervisor'));
            echo "    > Columna rejection_notes agregada a doc_tire_movement.\n";
        } else {
            echo "    > Columna rejection_notes ya existe o tabla no encontrada. Omitiendo.\n";
        }
    }

    public function safeDown(): void
    {
        $table = '{{%doc_tire_movement}}';
        $schema = $this->db->getTableSchema($this->db->schema->getRawTableName($table));

        if ($schema !== null && isset($schema->columns['rejection_notes'])) {
            $this->dropColumnWithLog($table, 'rejection_notes');
            echo "    > Columna rejection_notes eliminada de doc_tire_movement.\n";
        }
    }
}
