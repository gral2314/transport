<?php

declare(strict_types=1);

use app\components\BaseMigration;

/**
 * Limpieza de modulos de taller obsoletos tras fusionar validation y work-order en view.
 *
 * Elimina:
 *  1. Permisos asociados a taller/validation
 *  2. Entrada de modulo taller/validation de {{%modules}}
 */
final class m260724_000001_cleanup_taller_validation_module extends BaseMigration
{
    public function safeUp(): void
    {
        $modulesSchema = $this->db->schema->getTableSchema('{{%modules}}', true);
        if ($modulesSchema === null) {
            return;
        }

        $pages = ['taller/validation'];

        // Obtener IDs de los modulos a eliminar
        $moduleIds = (new \yii\db\Query())
            ->select(['id'])
            ->from('{{%modules}}')
            ->where(['page' => $pages])
            ->column($this->db);

        // Eliminar permisos asociados
        if (!empty($moduleIds)) {
            $this->delete('{{%permissions}}', ['idmodulo' => $moduleIds]);
        }

        // Eliminar entradas de modulo
        foreach ($pages as $page) {
            $this->delete('{{%modules}}', ['page' => $page]);
        }
    }

    public function safeDown(): void
    {
        // No se recrea el modulo obsoleto; safeDown es no-op intencional.
    }
}
