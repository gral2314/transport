<?php

declare(strict_types=1);

use app\components\BaseMigration;
use yii\db\ColumnSchemaBuilder;
use yii\db\Schema;

/**
 * Crear tabla doc_series para gestión de series/numeración de documentos.
 *
 * Esta tabla actúa como generador de números únicos para documentos,
 * permitiendo configurar prefijos, sufijos, padding y consecutivos
 * por combinación única de objeto (object_name), prefijo y sufijo.
 */
class m260610_000001_create_doc_series_table extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        // ─── Tabla principal ───────────────────────────────────────────────
        $this->createTableWithLog('{{%doc_series}}', [
            'id' => $this->primaryKey()->comment('ID único de la serie'),
            'name' => $this->string(50)->notNull()->comment('Nombre descriptivo de la serie'),
            'object_name' => $this->string(50)->notNull()->comment('Nombre del objeto/entidad que usa la serie (ej: DocTireMovement, DocTireDisposal)'),
            'prefix' => $this->string(10)->notNull()->defaultValue('')->comment('Prefijo del número de documento'),
            'suffix' => $this->string(10)->notNull()->defaultValue('')->comment('Sufijo del número de documento'),
            'padding_length' => $this->integer()->notNull()->defaultValue(6)->comment('Longitud del relleno con ceros del consecutivo'),
            'current_consecutive' => $this->integer()->notNull()->defaultValue(0)->comment('Valor actual del consecutivo'),
            'max_consecutive' => $this->integer()->notNull()->defaultValue(999999)->comment('Valor máximo del consecutivo'),
            'is_active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Indica si la serie está activa: Y=Activo, N=Inactivo'",
        ], pkColumns: ['id']);

        // ─── Índices ──────────────────────────────────────────────────────
        $this->createIndex(
            'ux_doc_series_object_prefix_suffix',
            '{{%doc_series}}',
            ['object_name', 'prefix', 'suffix'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTableWithLog('{{%doc_series}}');
    }
}
