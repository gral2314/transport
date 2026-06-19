<?php

declare(strict_types=1);

use app\components\BaseMigration;
use yii\db\ColumnSchemaBuilder;
use yii\db\Schema;

/**
 * Agregar columna series_id a las tablas de documentos de llantas
 * (doc_tire_movement, doc_tire_disposal, doc_tire_repair) para
 * asociar cada documento con su serie de numeración.
 */
class m260610_000002_add_series_id_to_tire_docs extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        // ─── doc_tire_movement ────────────────────────────────────────────
        $this->addColumnWithLog(
            '{{%doc_tire_movement}}',
            'series_id',
            $this->integer()->null()->comment('ID de la serie de numeración (FK doc_series)')
        );
        $this->addForeignKey(
            'fk_doc_tire_movement_series',
            '{{%doc_tire_movement}}',
            'series_id',
            '{{%doc_series}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // ─── doc_tire_disposal ────────────────────────────────────────────
        $this->addColumnWithLog(
            '{{%doc_tire_disposal}}',
            'series_id',
            $this->integer()->null()->comment('ID de la serie de numeración (FK doc_series)')
        );
        $this->addForeignKey(
            'fk_doc_tire_disposal_series',
            '{{%doc_tire_disposal}}',
            'series_id',
            '{{%doc_series}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // ─── doc_tire_repair ──────────────────────────────────────────────
        $this->addColumnWithLog(
            '{{%doc_tire_repair}}',
            'series_id',
            $this->integer()->null()->comment('ID de la serie de numeración (FK doc_series)')
        );
        $this->addForeignKey(
            'fk_doc_tire_repair_series',
            '{{%doc_tire_repair}}',
            'series_id',
            '{{%doc_series}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        // doc_tire_movement
        $fkMv = $this->getDb()->getSchema()->getTableSchema('{{%doc_tire_movement}}');
        if ($fkMv && isset($fkMv->foreignKeys['fk_doc_tire_movement_series'])) {
            $this->dropForeignKey('fk_doc_tire_movement_series', '{{%doc_tire_movement}}');
        }
        $this->dropColumnWithLog('{{%doc_tire_movement}}', 'series_id');

        // doc_tire_disposal
        $fkDs = $this->getDb()->getSchema()->getTableSchema('{{%doc_tire_disposal}}');
        if ($fkDs && isset($fkDs->foreignKeys['fk_doc_tire_disposal_series'])) {
            $this->dropForeignKey('fk_doc_tire_disposal_series', '{{%doc_tire_disposal}}');
        }
        $this->dropColumnWithLog('{{%doc_tire_disposal}}', 'series_id');

        // doc_tire_repair
        $fkRp = $this->getDb()->getSchema()->getTableSchema('{{%doc_tire_repair}}');
        if ($fkRp && isset($fkRp->foreignKeys['fk_doc_tire_repair_series'])) {
            $this->dropForeignKey('fk_doc_tire_repair_series', '{{%doc_tire_repair}}');
        }
        $this->dropColumnWithLog('{{%doc_tire_repair}}', 'series_id');
    }
}
