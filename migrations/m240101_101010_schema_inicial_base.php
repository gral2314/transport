<?php

declare(strict_types=1);

use app\components\BaseMigration;
use yii\db\Query;

/**
 * Migracion base de esquema inicial del sistema.
 *
 * Tablas incluidas:
 * 1. appsettings
 * 2. company
 * 3. usersgroup
 * 4. users
 * 5. modules
 * 6. permissions
 *
 * Nota: Se conservan nombres legacy de columnas/tablas para compatibilidad
 * con modulos existentes del sistema.
 */
class m240101_101010_schema_inicial_base extends BaseMigration
{
    public function safeUp(): void
    {
        $date = date('Y-m-d');
        $time = date('H:i:s');

        // ==========================================
        // TABLAS
        // ==========================================
        $this->createBaseTables();

        // ==========================================
        // INDICES
        // ==========================================
        $this->createIndexes();

        // ==========================================
        // FOREIGN KEYS
        // ==========================================
        $this->createForeignKeys();

        // ==========================================
        // SEEDS / REGISTROS INICIALES
        // ==========================================
        $this->seedAppSettings();
        $this->seedCompany();
        $this->seedUsersGroup($date, $time);
        $this->seedAdminUser($date, $time);
        $this->seedModules($date, $time);
        $this->seedPermissions($date, $time);
    }

    public function safeDown(): void
    {
        // Orden inverso: tablas dependientes -> tablas base.
        $this->dropTableWithLog('{{%permissions}}');
        $this->dropTableWithLog('{{%modules}}');
        $this->dropTableWithLog('{{%users}}');
        $this->dropTableWithLog('{{%usersgroup}}');
        $this->dropTableWithLog('{{%company}}');
        $this->dropTableWithLog('{{%appsettings}}');
    }

    private function createBaseTables(): void
    {
        // Nota: En esta migracion se preserva nomenclatura legacy de BD.
        if (!$this->tableExists('{{%appsettings}}')) {
            $this->createTableWithLog('{{%appsettings}}', [
                'id' => $this->primaryKey()->comment('ID unico del registro'),
                'nameapp' => $this->string(100)->null()->comment('Nombre de la aplicacion'),
                'titlepage' => $this->string(100)->null()->comment('Titulo de la pagina principal'),
                'logosm' => $this->string(100)->null()->comment('Logo pequeno de la aplicacion'),
                'logolg' => $this->string(100)->null()->comment('Logo grande de la aplicacion'),
                'navbarcolor' => $this->string(50)->null()->comment('Color de la barra de navegacion'),
                'sidebarcolor' => $this->string(50)->null()->comment('Color del menu lateral'),
                'imglogin' => $this->string(100)->null()->comment('Imagen de fondo para login'),
                'sidebarstartup' => $this->string(50)->notNull()->defaultValue('sidebar-collapse')->comment('Estado inicial del sidebar'),
                'sap_server' => $this->text()->null()->comment('Servidor SL SAP. Ej: https://host:50000'),
                'sap_db' => $this->text()->null()->comment('Base de datos de conexion SL SAP'),
                'sap_user' => $this->text()->null()->comment('Usuario de conexion SL SAP'),
                'sap_password' => $this->text()->null()->comment('Contrasena de conexion SL SAP'),
            ], pkColumns: ['id']);
        }

        if (!$this->tableExists('{{%company}}')) {
            $this->createTableWithLog('{{%company}}', [
                'id' => $this->primaryKey()->notNull()->comment('ID unico de compania'),
                'name' => $this->string(250)->null()->comment('Razon social de la compania'),
                'namecomercial' => $this->string(250)->null()->comment('Nombre comercial de la compania'),
                'calle' => $this->string(100)->null()->comment('Calle de la direccion fiscal'),
                'noext' => $this->string(50)->null()->comment('Numero exterior'),
                'noint' => $this->string(50)->null()->comment('Numero interior'),
                'colonia' => $this->string(100)->null()->comment('Colonia de la direccion'),
                'delegacion' => $this->string(100)->null()->comment('Delegacion o municipio'),
                'codigopostal' => $this->integer()->null()->comment('Codigo postal'),
                'ciudad' => $this->string(100)->null()->comment('Ciudad'),
                'estado' => $this->string(100)->null()->comment('Estado'),
                'pais' => $this->string(50)->null()->comment('Pais'),
                'rfc' => $this->string(13)->null()->comment('Registro Federal de Contribuyentes'),
                'regimen' => $this->string(3)->null()->comment('Regimen fiscal SAT'),
            ], pkColumns: ['id']);
        }

        if (!$this->tableExists('{{%usersgroup}}')) {
            $this->createTableWithLog('{{%usersgroup}}', [
                'id' => $this->primaryKey()->comment('ID unico del grupo de usuarios'),
                'name' => $this->string(50)->notNull()->comment('Nombre del grupo'),
                'description' => $this->string(100)->notNull()->comment('Descripcion del grupo'),
                'createdate' => $this->date()->null()->comment('Fecha de creacion'),
                'createtime' => $this->time()->null()->comment('Hora de creacion'),
                'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
                'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
                'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
                'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
            ], pkColumns: ['id']);
        }

        if (!$this->tableExists('{{%users}}')) {
            $this->createTableWithLog('{{%users}}', [
                'id' => $this->bigPrimaryKey()->comment('ID unico del usuario'),
                'usercode' => $this->string(50)->notNull()->comment('Codigo de acceso del usuario'),
                'username' => $this->string(100)->notNull()->comment('Nombre completo del usuario'),
                'last_name' => $this->string(100)->null()->comment('Apellido del usuario'),
                'idgroup' => $this->integer()->notNull()->comment('Grupo del usuario (FK usersgroup.id)'),
                'codeemploye' => $this->integer()->null()->comment('Codigo interno de empleado'),
                'imagen' => $this->string(250)->notNull()->defaultValue('avatar_gen')->comment('Avatar o imagen de perfil'),
                'email' => $this->string(200)->null()->comment('Correo electronico'),
                'phone' => $this->string(20)->null()->comment('Telefono de contacto'),
                'password' => $this->string(100)->notNull()->comment('Contrasena cifrada'),
                'authkey' => $this->string(250)->notNull()->comment('Llave de autenticacion'),
                'accesstoken' => $this->string(250)->notNull()->comment('Token de acceso API'),
                'fcm_token' => $this->string(500)->null()->comment('Token FCM para notificaciones push'),
                'fcm_expire' => $this->date()->null()->comment('Fecha de expiracion del token FCM'),
                'token_reset_pass' => $this->string(254)->null()->comment('Token para restablecer contrasena'),
                'token_rest_time' => $this->dateTime()->null()->comment('Fecha y hora de expiracion del token de reset'),
                'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Y=Activo, N=Inactivo'",
                'locked' => "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Y=Bloqueado, N=Desbloqueado'",
                'online' => "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Y=Conectado, N=Desconectado'",
                'last_ip' => $this->string(45)->null()->comment('Ultima direccion IP de acceso'),
                'lastconection' => $this->dateTime()->null()->comment('Fecha y hora de ultima conexion'),
                'failed_login_attempts'  => $this->integer()->defaultValue(0)->notNull()->comment('Intentos fallidos acumulados de autenticacion'),
                'blocked_until' => $this->dateTime()->null()->comment('Fecha y hora hasta la que permanece bloqueado'),
                'createdate' => $this->date()->null()->comment('Fecha de creacion'),
                'createtime' => $this->time()->null()->comment('Hora de creacion'),
                'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
                'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
                'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
                'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
            ], pkColumns: ['id']);
        }

        if (!$this->tableExists('{{%modules}}')) {
            $this->createTableWithLog('{{%modules}}', [
                'id' => $this->primaryKey()->comment('ID unico del modulo'),
                'name' => $this->string(50)->notNull()->comment('Nombre del modulo o submenu'),
                'description' => $this->string(100)->notNull()->comment('Descripcion del modulo'),
                'idparent' => $this->integer()->notNull()->defaultValue(0)->comment('ID del modulo padre'),
                'page' => $this->string(200)->notNull()->comment('Ruta o URL de navegacion'),
                'icon' => $this->string(100)->notNull()->defaultValue('fas fa-circle-notch')->comment('Clase CSS del icono'),
                'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Y=Activo, N=Inactivo'",
                'order' => $this->integer()->notNull()->comment('Orden de visualizacion del menu'),
                'type' => $this->string(1)->notNull()->defaultValue('S')->comment('S=Submenu, M=Modulo'),
                'createdate' => $this->date()->null()->comment('Fecha de creacion'),
                'createtime' => $this->time()->null()->comment('Hora de creacion'),
                'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
                'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
                'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
                'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
            ], pkColumns: ['id']);
        }

        if (!$this->tableExists('{{%permissions}}')) {
            $this->createTableWithLog('{{%permissions}}', [
                'id' => $this->primaryKey()->comment('ID unico del permiso'),
                'iduser' => $this->bigInteger()->null()->comment('Usuario al que aplica el permiso'),
                'idgroup' => $this->integer()->null()->comment('Grupo al que aplica el permiso'),
                'idmodulo' => $this->integer()->notNull()->comment('Modulo al que aplica el permiso'),
                'admit' => "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Y=Permitido, N=Denegado'",
                'createdate' => $this->date()->null()->comment('Fecha de creacion'),
                'createtime' => $this->time()->null()->comment('Hora de creacion'),
                'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
                'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
                'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
                'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
            ], pkColumns: ['id']);
        }
    }

    private function createIndexes(): void
    {
        $this->createIndexIfNotExists('uq_users_usercode', '{{%users}}', 'usercode', true);
        $this->createIndexIfNotExists('idx_users_idgroup', '{{%users}}', 'idgroup');
        $this->createIndexIfNotExists('idx_users_email', '{{%users}}', 'email');

        $this->createIndexIfNotExists('idx_modules_parent', '{{%modules}}', 'idparent');
        $this->createIndexIfNotExists('idx_modules_order', '{{%modules}}', 'order');
        $this->createIndexIfNotExists('idx_modules_active', '{{%modules}}', 'active');

        $this->createIndexIfNotExists('idx_permissions_user', '{{%permissions}}', 'iduser');
        $this->createIndexIfNotExists('idx_permissions_group', '{{%permissions}}', 'idgroup');
        $this->createIndexIfNotExists('idx_permissions_module', '{{%permissions}}', 'idmodulo');
        $this->createIndexIfNotExists('idx_permissions_admit', '{{%permissions}}', 'admit');
        $this->createIndexIfNotExists('idx_permissions_user_module', '{{%permissions}}', ['iduser', 'idmodulo']);
        $this->createIndexIfNotExists('idx_permissions_group_module', '{{%permissions}}', ['idgroup', 'idmodulo']);
    }

    private function createForeignKeys(): void
    {
        $this->ensurePermissionsUserColumnType();

        $this->addForeignKeyIfNotExists(
            'fk_users_idgroup_usersgroup_id',
            '{{%users}}',
            'idgroup',
            '{{%usersgroup}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKeyIfNotExists(
            'fk_permissions_iduser_users_id',
            '{{%permissions}}',
            'iduser',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKeyIfNotExists(
            'fk_permissions_idgroup_usersgroup_id',
            '{{%permissions}}',
            'idgroup',
            '{{%usersgroup}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKeyIfNotExists(
            'fk_permissions_idmodulo_modules_id',
            '{{%permissions}}',
            'idmodulo',
            '{{%modules}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    private function ensurePermissionsUserColumnType(): void
    {
        $permissionsTable = $this->db->schema->getTableSchema('{{%permissions}}', true);
        if ($permissionsTable === null || !isset($permissionsTable->columns['iduser'])) {
            return;
        }

        $idUserColumn = $permissionsTable->columns['iduser'];
        if ($idUserColumn->dbType === 'bigint(20)') {
            return;
        }

        $this->alterColumn(
            '{{%permissions}}',
            'iduser',
            $this->bigInteger()->null()->comment('Usuario al que aplica el permiso')
        );
    }

    private function seedAppSettings(): void
    {
        $exists = (new Query())
            ->from('{{%appsettings}}')
            ->where(['id' => 1])
            ->exists($this->db);

        if (!$exists) {
            $this->insert('{{%appsettings}}', [
                'id' => 1,
                'nameapp' => 'Intercompany',
                'titlepage' => 'Intercompany',
                'logosm' => 'logosm.png',
                'logolg' => 'logoxl.png',
                'navbarcolor' => 'danger',
                'sidebarcolor' => 'light',
                'imglogin' => 'backgrond_login',
                'sidebarstartup' => 'sidebar-collapse',
            ]);
        }
    }

    private function seedCompany(): void
    {
        $exists = (new Query())
            ->from('{{%company}}')
            ->where(['id' => 1])
            ->exists($this->db);

        if (!$exists) {
            $this->insert('{{%company}}', [
                'id' => 1,
                'name' => 'Transport One',
                'namecomercial' => 'Transport One',
                'calle' => null,
                'noext' => null,
                'noint' => null,
                'colonia' => null,
                'delegacion' => null,
                'codigopostal' => null,
                'ciudad' => null,
                'estado' => null,
                'pais' => null,
                'rfc' => null,
                'regimen' => null,
            ]);
        }
    }

    private function seedUsersGroup(string $date, string $time): void
    {
        $exists = (new Query())
            ->from('{{%usersgroup}}')
            ->where(['id' => 1])
            ->exists($this->db);

        if (!$exists) {
            $this->insert('{{%usersgroup}}', [
                'id' => 1,
                'name' => 'Administrador',
                'description' => 'Administrador',
                'createdate' => $date,
                'createtime' => $time,
                'createuser' => null,
                'updatedate' => null,
                'updatetime' => null,
                'updateuser' => null,
            ]);
        }
    }

    private function seedAdminUser(string $date, string $time): void
    {
        $exists = (new Query())
            ->from('{{%users}}')
            ->where(['usercode' => 'admin'])
            ->exists($this->db);

        if (!$exists) {
            $this->insert('{{%users}}', [
                'usercode' => 'admin',
                'username' => 'Administrador de Sistemas',
                'last_name' => 'Transport One',
                'idgroup' => 1,
                'codeemploye' => null,
                'imagen' => 'av1.jpg',
                'email' => 'admin@admin',
                'phone' => '123456',
                'password' => '9348402dc898392d7a0d95fcb6208f5f',
                'authkey' => 'b67b93f8afc53524de5b4085076241359419d0db5ae821fdec05fbd92818b33021660db68698b5394d787a2af78e83484f65acd6ecfcf6c1736b48ede29103955aa48aa83cd061d339590a5906dc807e975449bbe331aee51520b1c7e52002ccf177dc18',
                'accesstoken' => '123456',
                'fcm_token' => null,
                'fcm_expire' => null,
                'token_reset_pass' => null,
                'token_rest_time' => null,
                'active' => 'Y',
                'online' => 'Y',
                'lastconection' => $date . ' ' . $time,
                'createdate' => $date,
                'createtime' => $time,
                'createuser' => 1,
                'updatedate' => null,
                'updatetime' => null,
                'updateuser' => null,
            ]);
        }
    }

    private function seedModules(string $date, string $time): void
    {
        $modulesData = [
            [100, 'Dashboard', 'Dashboard General', 0, 'site/dashboard', 'ph ph-squares-four', 'Y', 100, 'S', $date, $time, 1, null, null, 0],
            [200, 'Ventas', 'Modulo Principal de Ventas', 0, '#', 'fas fa-shopping-cart', 'Y', 200, 'M', $date, $time, 1, null, null, 0],
            [300, 'Compras', 'Modulo Principal de Compras', 0, '#', 'fas fa-shopping-bag', 'Y', 300, 'M', $date, $time, 1, null, null, 0],
            [400, 'Inventario', 'Gestion de Almacen e Inventarios', 0, '#', 'fas fa-warehouse', 'Y', 400, 'M', $date, $time, 1, null, null, 0],
            [401, 'Gestion de Items', 'Administracion de articulos', 400, 'items/index', 'fa-solid fa-angle-right', 'Y', 1, 'S', $date, $time, 1, null, null, 0],
            [500, 'Socios de Negocio', 'Administracion de Terceros', 0, '#', 'fas fa-address-book', 'Y', 500, 'M', $date, $time, 1, null, null, 0],
            [501, 'Gestion de Socios de Negocio', 'Directorio de socios', 500, 'sn/index', 'fa-solid fa-angle-right', 'Y', 1, 'S', $date, $time, 1, null, null, 0],
            [600, 'Flotilla', 'Modulo de Gestion de Unidades', 0, '', 'fa-solid fa-truck', 'Y', 600, 'M', $date, $time, 1, null, null, 0],
            [601, 'Gestion de Unidades', 'Control de vehiculos', 600, 'fleet/units', 'fa-solid fa-angle-right', 'Y', 1, 'S', $date, $time, 1, null, null, 0],
            [700, 'Llantas', 'Control y ciclo de vida de llantas', 0, '', 'ph ph-tire me-2', 'Y', 700, 'M', $date, $time, 1, null, null, 0],
            [701, 'Gestion de Llantas', 'Inventario de llantas', 700, 'fleet/tires', 'fa-solid fa-angle-right', 'Y', 1, 'S', $date, $time, 1, null, null, 0],
            [702, 'Asignacion', 'Asignacion a unidades', 700, 'doc-tire-assignment/index', 'fa-solid fa-angle-right', 'Y', 2, 'S', $date, $time, 1, null, null, 0],
            [703, 'Mantenimiento', 'Historial de servicios', 700, 'doc-tire-maintenance/index', 'fa-solid fa-angle-right', 'Y', 3, 'S', $date, $time, 1, null, null, 0],
            [704, 'Baja', 'Desecho fisico de llantas', 700, 'doc-tire-disposal/index', 'fa-solid fa-angle-right', 'Y', 4, 'S', $date, $time, 1, null, null, 0],
            [800, 'Recursos Humanos', 'Modulo de Personal', 0, '#', 'fa-solid fa-users', 'Y', 800, 'M', $date, $time, 1, null, null, 0],
            [801, 'Gestion de Empleados', 'Control de empleados', 800, 'employee/index', 'fa-solid fa-angle-right', 'Y', 1, 'S', $date, $time, 1, null, null, 0],
            [9000, 'Configuracion', 'Parametros del Sistema', 0, '', 'fa-solid fa-gear', 'Y', 9000, 'M', $date, $time, 1, null, null, 0],
            [9001, 'Usuarios', 'Administracion de accesos', 9000, 'config/users', 'fa-solid fa-users', 'Y', 1, 'S', $date, $time, 1, null, null, 0],
            [9002, 'Sistema', 'Variables globales', 9000, 'config/system', 'fa-solid fa-sliders', 'Y', 2, 'S', $date, $time, 1, null, null, 0],
            [9100, 'Definiciones de Catalogos', 'Agrupador de catalogos internos', 9000, '', 'fa-solid fa-layer-group', 'Y', 3, 'M', $date, $time, 1, null, null, 0],
            [9101, 'Finanzas', 'Catalogo finanzas', 9100, 'config/finance', 'fa-solid fa-angle-right', 'Y', 1, 'S', $date, $time, 1, null, null, 0],
            [9102, 'Ventas', 'Catalogo ventas', 9100, 'config/sales', 'fa-solid fa-angle-right', 'Y', 2, 'S', $date, $time, 1, null, null, 0],
            [9103, 'Compras', 'Catalogo compras', 9100, 'config/purchase', 'fa-solid fa-angle-right', 'Y', 3, 'S', $date, $time, 1, null, null, 0],
            [9104, 'Inventario', 'Catalogo inventario', 9100, 'config/inventory', 'fa-solid fa-angle-right', 'Y', 4, 'S', $date, $time, 1, null, null, 0],
            [9105, 'Vehiculos', 'Catalogo vehiculos', 9100, 'config/vehicule', 'fa-solid fa-angle-right', 'Y', 5, 'S', $date, $time, 1, null, null, 0],
            [9106, 'Mantenimiento', 'Catalogo mantenimiento', 9100, 'config/mantto', 'fa-solid fa-angle-right', 'Y', 6, 'S', $date, $time, 1, null, null, 0],
            [9107, 'General', 'Catalogo general', 9100, 'config/gral', 'fa-solid fa-angle-right', 'Y', 7, 'S', $date, $time, 1, null, null, 0],
            [9108, 'Recursos Humanos', 'Catalogo RRHH', 9100, 'config/rrhh', 'fa-solid fa-angle-right', 'Y', 8, 'S', $date, $time, 1, null, null, 0],
        ];

        $existingIds = array_map(
            'intval',
            (new Query())
                ->select('id')
                ->from('{{%modules}}')
                ->column($this->db)
        );

        $rowsToInsert = [];
        foreach ($modulesData as $row) {
            if (!in_array((int) $row[0], $existingIds, true)) {
                $rowsToInsert[] = $row;
            }
        }

        if (!empty($rowsToInsert)) {
            $this->batchInsert(
                '{{%modules}}',
                ['id', 'name', 'description', 'idparent', 'page', 'icon', 'active', 'order', 'type', 'createdate', 'createtime', 'createuser', 'updatedate', 'updatetime', 'updateuser'],
                $rowsToInsert
            );
        }
    }

    private function seedPermissions(string $date, string $time): void
    {
        $moduleIds = (new Query())
            ->select(['id'])
            ->from('{{%modules}}')
            ->column($this->db);

        $defaultUsers = [1];
        $defaultGroups = [1];

        $existing = (new Query())
            ->select(["CONCAT(COALESCE(iduser, 'NULL'), '|', COALESCE(idgroup, 'NULL'), '|', idmodulo) AS pkey"])
            ->from('{{%permissions}}')
            ->column($this->db);

        $existingMap = array_fill_keys($existing, true);
        $permissionsRows = [];

        foreach ($defaultUsers as $userId) {
            foreach ($moduleIds as $moduleId) {
                $compound = $userId . '|NULL|' . $moduleId;
                if (!isset($existingMap[$compound])) {
                    $permissionsRows[] = [
                        $userId,
                        null,
                        $moduleId,
                        'Y',
                        $date,
                        $time,
                        1,
                        null,
                        null,
                        0,
                    ];
                }
            }
        }

        foreach ($defaultGroups as $groupId) {
            foreach ($moduleIds as $moduleId) {
                $compound = 'NULL|' . $groupId . '|' . $moduleId;
                if (!isset($existingMap[$compound])) {
                    $permissionsRows[] = [
                        null,
                        $groupId,
                        $moduleId,
                        'Y',
                        $date,
                        $time,
                        1,
                        null,
                        null,
                        0,
                    ];
                }
            }
        }

        if (!empty($permissionsRows)) {
            $this->batchInsert(
                '{{%permissions}}',
                ['iduser', 'idgroup', 'idmodulo', 'admit', 'createdate', 'createtime', 'createuser', 'updatedate', 'updatetime', 'updateuser'],
                $permissionsRows
            );
        }
    }

    private function createIndexIfNotExists(string $name, string $table, string|array $columns, bool $unique = false): void
    {
        $rawTable = $this->db->schema->getRawTableName($table);
        $schemaName = $this->db->createCommand('SELECT DATABASE()')->queryScalar();

        $tableSchema = $this->db->schema->getTableSchema($rawTable, true);
        if ($tableSchema === null) {
            return;
        }

        $exists = (new Query())
            ->from('information_schema.STATISTICS')
            ->where([
                'TABLE_SCHEMA' => $schemaName,
                'TABLE_NAME' => $rawTable,
                'INDEX_NAME' => $name,
            ])
            ->exists($this->db);

        if ($exists) {
            return;
        }

        $this->createIndex($name, $table, $columns, $unique);
    }

    private function addForeignKeyIfNotExists(
        string $name,
        string $table,
        string|array $columns,
        string $refTable,
        string|array $refColumns,
        ?string $delete = null,
        ?string $update = null
    ): void {
        $rawTable = $this->db->schema->getRawTableName($table);
        $exists = (new Query())
            ->from('information_schema.REFERENTIAL_CONSTRAINTS')
            ->where([
                'CONSTRAINT_SCHEMA' => $this->db->createCommand('SELECT DATABASE()')->queryScalar(),
                'CONSTRAINT_NAME' => $name,
                'TABLE_NAME' => $rawTable,
            ])
            ->exists($this->db);

        if ($exists) {
            return;
        }

        $this->addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }
}
