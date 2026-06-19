<?php

declare(strict_types=1);

use app\components\BaseMigration;

/**
 * Agregar columna is_default a doc_series para marcar la serie
 * predeterminada por objeto (object_name).
 *
 * Solo una serie por object_name puede tener is_default = 'Y'.
 * La unicidad se controla desde SeriesServices::save() ya que
 * MariaDB no soporta índices UNIQUE parciales (WHERE).
 */
class m260610_000003_add_is_default_to_doc_series extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        // ─── Agregar columna is_default (solo si no existe) ───────────────
        $schema = $this->db->getTableSchema('{{%doc_series}}');
        if ($schema !== null && !isset($schema->columns['is_default'])) {
            $this->addColumnWithLog(
                '{{%doc_series}}',
                'is_default',
                "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Indica si es la serie predeterminada para el objeto: Y=Default, N=No default'"
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        // Eliminar columna
        $this->dropColumnWithLog('{{%doc_series}}', 'is_default');
    }
}
