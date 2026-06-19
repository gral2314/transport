<?php

declare(strict_types=1);

/**
 * Migración: Agrega columna user_id a {{%employee}} con FK a {{%users}}
 * 
 * Permite asignar un usuario de sistema a cada empleado para filtrar
 * registros según el tipo de empleado y usuario logueado.
 */

use app\components\BaseMigration;

class m260613_000004_add_user_id_to_employee extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $schema = $this->db->getSchema();

        // Agregar columna user_id si no existe
        $table = $schema->getTableSchema('{{%employee}}', true);
        if ($table !== null && !isset($table->columns['user_id'])) {
            $this->addColumn('{{%employee}}', 'user_id', $this->bigInteger()->null()->comment('FK al usuario de sistema asignado (users.id)'));
            $this->addForeignKey(
                'fk_employee_user',
                '{{%employee}}',
                'user_id',
                '{{%users}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
            $this->createIndex('idx_employee_user_id', '{{%employee}}', 'user_id');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $schema = $this->db->getSchema();
        $table = $schema->getTableSchema('{{%employee}}', true);

        if ($table !== null && isset($table->columns['user_id'])) {
            $this->dropForeignKey('fk_employee_user', '{{%employee}}');
            $this->dropIndex('idx_employee_user_id', '{{%employee}}');
            $this->dropColumn('{{%employee}}', 'user_id');
        }
    }
}
