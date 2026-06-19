<?php

declare(strict_types=1);

use yii\db\Migration;
use app\components\BaseMigration;

/**
 * Migración: Módulo de Recursos Humanos
 * 
 * Crea las tablas para gestión de empleados, documentos, roles y catálogos relacionados.
 * Inserta menús y permisos RBAC necesarios.
 * 
 * IDs menu_items:
 * - 19: Catálogos RRHH (dentro de Configuración)
 * - 400-410: Menú principal RRHH y submenús
 * 
 * sort_order:
 * - RRHH: 600 (entre Flotilla=500 y Configuración=990)
 */
final class m260522_000001_create_hr_module extends BaseMigration
{
    

    public function safeUp(): void
    {
        // ============================================================
        // CATÁLOGOS
        // ============================================================

        // ============================================================
        // 1. position_catalog — Catálogo de puestos
        // ============================================================
        $this->createTableWithLog('{{%position_catalog}}', [
            'code' => $this->string(50)->notNull()->comment('Código único del puesto'),
            'name' => $this->string(100)->notNull()->comment('Nombre del puesto'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",
            'is_system' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Sistema (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 2. area_catalog — Catálogo de áreas
        // ============================================================
        $this->createTableWithLog('{{%area_catalog}}', [
            'code' => $this->string(50)->notNull()->comment('Código único del área'),
            'name' => $this->string(100)->notNull()->comment('Nombre del área'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 3. branch_catalog — Catálogo de sucursales
        // ============================================================
        $this->createTableWithLog('{{%branch_catalog}}', [
            'code' => $this->string(50)->notNull()->comment('Código único de sucursal'),
            'name' => $this->string(100)->notNull()->comment('Nombre de la sucursal'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 4. employee_type_catalog — Catálogo de tipos de empleado
        // ============================================================
        $this->createTableWithLog('{{%employee_type_catalog}}', [
            'code' => $this->string(50)->notNull()->comment('Código único del tipo de empleado'),
            'name' => $this->string(100)->notNull()->comment('Nombre del tipo de empleado'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 5. document_type_catalog — Catálogo de tipos de documento
        // ============================================================
        $this->createTableWithLog('{{%document_type_catalog}}', [
            'code' => $this->string(50)->notNull()->comment('Código único del documento'),
            'name' => $this->string(100)->notNull()->comment('Nombre del documento'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 6. role_catalog — Catálogo de roles
        // ============================================================
        $this->createTableWithLog('{{%role_catalog}}', [
            'code' => $this->string(50)->notNull()->comment('Código único del rol'),
            'name' => $this->string(100)->notNull()->comment('Nombre del rol'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",
            'is_system' => "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Valor del Sistema (Y=Sí, N=No)'",
            
            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // TABLAS PRINCIPALES
        // ============================================================

        // ============================================================
        // 7. employee — Información principal del empleado
        // ============================================================
        $this->createTableWithLog('{{%employee}}', [
            'employee_code' => $this->string(50)->notNull()->comment('Código único del empleado'),
            'first_name' => $this->string(100)->notNull()->comment('Nombre del empleado'),
            'last_name' => $this->string(100)->notNull()->comment('Apellido paterno'),
            'second_last_name' => $this->string(100)->null()->comment('Apellido materno'),
            'curp' => $this->string(18)->null()->comment('CURP del empleado'),
            'phone_number' => $this->string(20)->null()->comment('Número telefónico'),
            'email' => $this->string(150)->null()->comment('Correo electrónico'),
            'address' => $this->string(300)->null()->comment('Dirección'),
            'birth_date' => $this->date()->null()->comment('Fecha de nacimiento'),
            'gender' => "ENUM('M','F','O') NULL COMMENT 'Género (M=Masculino, F=Femenino, O=Otro)'",
            'hire_date' => $this->date()->notNull()->comment('Fecha de ingreso'),
            'employee_status' => "ENUM('ACTIVE','INACTIVE','SUSPENDED','VACATION') NOT NULL DEFAULT 'ACTIVE' COMMENT 'Estatus del empleado'",
            'shift_type' => "ENUM('MORNING','EVENING','NIGHT','MIXED') NULL COMMENT 'Tipo de turno'",
            'position_code' => $this->string(50)->notNull()->comment('FK al catálogo de puestos'),
            'area_code' => $this->string(50)->notNull()->comment('FK al catálogo de áreas'),
            'branch_code' => $this->string(50)->null()->comment('FK al catálogo de sucursales'),
            'employee_type_code' => $this->string(50)->null()->comment('FK al catálogo de tipos de empleado'),
            'direct_manager_code' => $this->string(50)->null()->comment('FK al empleado jefe directo'),
            'documentation_complete' => "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Expediente completo (Y=Sí, N=No)'",
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Registro activo (Y=Sí, N=No)'",
            'notes' => $this->string(500)->null()->comment('Observaciones'),

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['employee_code']);

        // FKs de employee
        $this->addForeignKey('fk_employee_position', '{{%employee}}', 'position_code', '{{%position_catalog}}', 'code', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_employee_area', '{{%employee}}', 'area_code', '{{%area_catalog}}', 'code', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_employee_branch', '{{%employee}}', 'branch_code', '{{%branch_catalog}}', 'code', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_employee_type', '{{%employee}}', 'employee_type_code', '{{%employee_type_catalog}}', 'code', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_employee_manager', '{{%employee}}', 'direct_manager_code', '{{%employee}}', 'employee_code', 'SET NULL', 'CASCADE');

        // Índices para búsquedas frecuentes
        $this->createIndex('idx_employee_curp', '{{%employee}}', 'curp');
        $this->createIndex('idx_employee_email', '{{%employee}}', 'email');
        $this->createIndex('idx_employee_status', '{{%employee}}', 'employee_status');
        $this->createIndex('idx_employee_position', '{{%employee}}', 'position_code');
        $this->createIndex('idx_employee_area', '{{%employee}}', 'area_code');

        // ============================================================
        // 8. employee_document — Control documental del empleado
        // ============================================================
        $this->createTableWithLog('{{%employee_document}}', [
            'employee_code' => $this->string(50)->notNull()->comment('FK del empleado'),
            'document_type_code' => $this->string(50)->notNull()->comment('FK al catálogo de tipos de documento'),
            'delivered' => "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Documento entregado (Y=Sí, N=No)'",
            'expiration_date' => $this->date()->null()->comment('Fecha de vencimiento del documento'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Registro activo (Y=Sí, N=No)'",
            'notes' => $this->string(300)->null()->comment('Observaciones'),

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['employee_code', 'document_type_code']);

        // FKs de employee_document
        $this->addForeignKey('fk_employee_document_employee', '{{%employee_document}}', 'employee_code', '{{%employee}}', 'employee_code', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_employee_document_type', '{{%employee_document}}', 'document_type_code', '{{%document_type_catalog}}', 'code', 'RESTRICT', 'CASCADE');

        // ============================================================
        // 9. employee_role — Relación N roles por empleado
        // ============================================================
        $this->createTableWithLog('{{%employee_role}}', [
            'employee_code' => $this->string(50)->notNull()->comment('FK del empleado'),
            'role_code' => $this->string(50)->notNull()->comment('FK del catálogo de roles'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Registro activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['employee_code', 'role_code']);

        // FKs de employee_role
        $this->addForeignKey('fk_employee_role_employee', '{{%employee_role}}', 'employee_code', '{{%employee}}', 'employee_code', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_employee_role_catalog', '{{%employee_role}}', 'role_code', '{{%role_catalog}}', 'code', 'RESTRICT', 'CASCADE');

        // ============================================================
        // SEED INICIAL - Catálogos básicos
        // ============================================================

        // Puestos básicos
        $this->batchInsert('{{%position_catalog}}', ['code', 'name', 'active','is_system'], [
            ['DRIVER', 'Conductor', 'Y','Y'],
            ['MECHANIC', 'Mecánico', 'Y','Y'],
            ['SUPERVISOR', 'Supervisor', 'Y','Y'],
            ['MANAGER', 'Gerente', 'Y','Y'],
            ['ADMIN', 'Administrativo', 'Y','Y'],
        ]);

        // Áreas básicas
        $this->batchInsert('{{%area_catalog}}', ['code', 'name', 'active'], [
            ['OPERATIONS', 'Operaciones', 'Y'],
            ['MAINTENANCE', 'Mantenimiento', 'Y'],
            ['ADMIN', 'Administración', 'Y'],
            ['HR', 'Recursos Humanos', 'Y'],
        ]);

        // Tipos de empleado básicos
        $this->batchInsert('{{%employee_type_catalog}}', ['code', 'name', 'active'], [
            ['PERMANENT', 'Permanente', 'Y'],
            ['TEMPORARY', 'Temporal', 'Y'],
            ['CONTRACT', 'Por Contrato', 'Y'],
        ]);

        // Tipos de documento básicos
        $this->batchInsert('{{%document_type_catalog}}', ['code', 'name', 'active'], [
            ['INE', 'INE/IFE', 'Y'],
            ['CURP', 'CURP', 'Y'],
            ['RFC', 'RFC', 'Y'],
            ['BIRTH_CERT', 'Acta de Nacimiento', 'Y'],
            ['PROOF_ADDR', 'Comprobante de Domicilio', 'Y'],
            ['LICENSE', 'Licencia de Conducir', 'Y'],
            ['NSS', 'Número de Seguro Social', 'Y'],
            ['SCHOOL_CERT', 'Certificado Escolar', 'Y'],
        ]);

        // Roles básicos
        $this->batchInsert('{{%role_catalog}}', ['code', 'name', 'active','is_system'], [
            ['DRIVER', 'Conductor', 'Y','Y'],
            ['MECHANIC', 'Mecánico', 'Y','Y'],
            ['SUPERVISOR', 'Supervisor de Operaciones', 'Y','Y'],
        ]);

       
    }

    public function safeDown(): void
    {
       
        // Eliminar tablas (en orden inverso por FKs)
        $this->dropTableWithLog('{{%employee_role}}');
        
        $this->dropTableWithLog('{{%employee_document}}');
        
        $this->dropTableWithLog('{{%employee}}');
        
        $this->dropTableWithLog('{{%role_catalog}}');
        
        $this->dropTableWithLog('{{%document_type_catalog}}');
        
        $this->dropTableWithLog('{{%employee_type_catalog}}');
        
        $this->dropTableWithLog('{{%branch_catalog}}');
        
        $this->dropTableWithLog('{{%area_catalog}}');
        
        $this->dropTableWithLog('{{%position_catalog}}');
    }
}
