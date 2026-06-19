<?php

declare(strict_types=1);

use yii\db\Migration;
use app\components\BaseMigration;

/**
 * Migración: Catalodos varios para sistema
 * 
 * Crea las tablas para catalogos como estados, vendedores, grupos etc...
 * Inserta menús y permisos RBAC necesarios.
 * 
 * IDs menu_items:
 * - 19: Catálogos RRHH (dentro de Configuración)
 * - 400-410: Menú principal RRHH y submenús
 * 
 * sort_order:
 * - RRHH: 600 (entre Flotilla=500 y Configuración=990)
 */
final class m260522_000003_create_catalogs extends BaseMigration
{
   
    public function safeUp(): void
    {
        // ============================================================
        // CATÁLOGOS
        // ============================================================

        // ============================================================
        // 1. group_sn — Grupo de Socios de Negocio
        // ============================================================
        $this->createTableWithLog('{{%group_sn}}', [
            'code' => $this->integer()->notNull()->comment('Código único del grupo'),
            'name' => $this->string(200)->notNull()->comment('Nombre del grupo'),
            'cardtype' => "ENUM('C','S') NOT NULL DEFAULT 'C' COMMENT 'Tipo de socio de negocios (C=Cliente, S=Proveedor)'",
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);
        // ============================================================
        // 2. group_items — Grupo de Articulos
        // ============================================================
        $this->createTableWithLog('{{%group_items}}', [
            'code' => $this->integer()->notNull()->comment('Código único del item'),
            'name' => $this->string(200)->notNull()->comment('Nombre del item'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 3. vendors — Vendedores
        // ============================================================
        $this->createTableWithLog('{{%vendors}}', [
            'code' => $this->integer()->notNull()->comment('Código único del vendedor'),
            'name' => $this->string(200)->notNull()->comment('Nombre del vendedor'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);
        // ============================================================
        // 4. states — Estados
        // ============================================================
        $this->createTableWithLog('{{%states}}', [
            'code' => $this->string(50)->notNull()->comment('Código único del estado'),
            'name' => $this->string(200)->notNull()->comment('Nombre del estado'),
            'country' => $this->string(10)->notNull()->comment('FK Código del país'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code','country']);
        $this->addForeignKey('fk_states_country', '{{%states}}', 'country', '{{%country}}', 'code', 'RESTRICT', 'CASCADE');


        // ============================================================
        // 5. cfdi_use_sn — Uso de CFDI para Socios de Negocio
        // ============================================================
        $this->createTableWithLog('{{%cfdi_use_sn}}', [
            'code' => $this->string(50)->notNull()->comment('Código único del uso de CFDI'),
            'name' => $this->string(250)->notNull()->comment('Nombre del uso de CFDI'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);
       
        // ============================================================
        // 7. cfdi_regimen_fiscal — Régimen fiscal para Socios de Negocio
        // ============================================================
        $this->createTableWithLog('{{%cfdi_regimen_fiscal}}', [
            'code' => $this->string(50)->notNull()->comment('Código único del régimen fiscal'),
            'name' => $this->string(250)->notNull()->comment('Nombre del régimen fiscal'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 8. monedas — Monedas
        // ============================================================
        $this->createTableWithLog('{{%currency}}', [
            'code' => $this->string(3)->notNull()->comment('Código único de la moneda'),
            'name' => $this->string(250)->notNull()->comment('Nombre de la moneda'),
            'symbol' => $this->string(10)->notNull()->comment('Símbolo de la moneda'),
            'decimals' => $this->integer()->notNull()->comment('Decimales de la moneda'),
            'txt_singular' => $this->string(250)->notNull()->comment('Texto en singular de la moneda'),
            'txt_plural' => $this->string(250)->notNull()->comment('Texto en plural de la moneda'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);


        
        // ============================================================
        // 8. Condiciones de pago — Condiciones de pago
        // ============================================================
        $this->createTableWithLog('{{%payment_conditions}}', [
            'code' => $this->integer()->notNull()->comment('Código único de la condición de pago'),
            'name' => $this->string(250)->notNull()->comment('Nombre de la condición de pago'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);


        // ============================================================
        // 8. Metodos de pago — Metodos de pago
        // ============================================================
        $this->createTableWithLog('{{%payment_methods}}', [
            'code' => $this->string(15)->notNull()->comment('Código único del método de pago'),
            'name' => $this->string(250)->notNull()->comment('Nombre del método de pago'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);
        // ============================================================
        // 9. Almacenes — Almacenes
        // ============================================================
        $this->createTableWithLog('{{%warehouse}}', [
            'code' => $this->string(50)->notNull()->comment('Código único del almacén'),
            'name' => $this->string(250)->notNull()->comment('Nombre del almacén'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

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
        // 8. BP — Dato maestro de socios de negocio
        // ============================================================
        $this->createTableWithLog('{{%bp}}', [
            'cardcode' => $this->string(15)->notNull()->comment('Código único del socio de negocios'),
            'cardname' => $this->string(50)->notNull()->comment('Nombre del socio de negocios'),
            'cardtype' => "ENUM('C','S','L') NOT NULL DEFAULT 'C' COMMENT 'Tipo de socio de negocios (C=Cliente, S=Proveedor, L=Lead)'",
            'lictradnum' => $this->string(50)->null()->comment('Número de licencia o identificación fiscal'),
            'card_group' => $this->integer()->notNull()->comment('FK al Grupo al que pertenece el socio de negocios'),
            'currency' => $this->string(3)->notNull()->comment('FK a la moneda del socio de negocios'),
            'tel' => $this->string(20)->notNull()->comment('Teléfono del socio de negocios'),
            'email' => $this->string(250)->notNull()->comment('Correo electrónico del socio de negocios'),
            'payment_cond' => $this->integer()->null()->comment('Fk Condiciones de pago del socio de negocios'),
            'payment_method' => $this->string(15)->null()->comment('Fk Método de pago del socio de negocios'),
            'cfdi_use_code' => $this->string(50)->null()->comment('Fk Código de uso de CFDI del socio de negocios'),
            'cfdi_regimen_code' => $this->string(50)->null()->comment('Fk Código de régimen de CFDI del socio de negocios'),
            'vendor_code' => $this->integer()->null()->comment('Fk Código de vendedor del socio de negocios'),

            'comments' => $this->string(300)->null()->comment('Comentarios adicionales'),

            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Registro activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['cardcode']);

        // FKs de bp
        $this->addForeignKey('fk_bp_card_group', '{{%bp}}', 'card_group', '{{%group_sn}}', 'code', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_bp_currency', '{{%bp}}', 'currency', '{{%currency}}', 'code', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_bp_payment_conditions', '{{%bp}}', 'payment_cond', '{{%payment_conditions}}', 'code', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_bp_payment_methods', '{{%bp}}', 'payment_method', '{{%payment_methods}}', 'code', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_bp_cfdi_use_code', '{{%bp}}', 'cfdi_use_code', '{{%cfdi_use_sn}}', 'code', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_bp_cfdi_regimen_code', '{{%bp}}', 'cfdi_regimen_code', '{{%cfdi_regimen_fiscal}}', 'code', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_bp_vendor_code', '{{%bp}}', 'vendor_code', '{{%vendors}}', 'code', 'RESTRICT', 'CASCADE');

        // Índices para búsquedas frecuentes
        $this->createIndex('idx_bp_active', '{{%bp}}', 'active');
        $this->createIndex('idx_bp_email', '{{%bp}}', 'email');
        $this->createIndex('idx_bp_card_group', '{{%bp}}', 'card_group');
        $this->createIndex('idx_bp_cardtype', '{{%bp}}', 'cardtype');
        $this->createIndex('idx_bp_currency', '{{%bp}}', 'currency');
        $this->createIndex('idx_bp_payment_cond', '{{%bp}}', 'payment_cond');
        $this->createIndex('idx_bp_payment_method', '{{%bp}}', 'payment_method');
        $this->createIndex('idx_bp_vendor_code', '{{%bp}}', 'vendor_code');

        // ============================================================
        // 8.1 BP_contacts — Contactos del Dato maestro de socios de negocio
        // ============================================================
        $this->createTableWithLog('{{%bp_contacts}}', [
            'cardcode' => $this->string(15)->notNull()->comment('Código único del socio de negocios'),
            'contact_code' => $this->string(15)->notNull()->comment('Código único del contacto dentro del socio de negocios'),
            'name' => $this->string(100)->notNull()->comment('Nombre del contacto'),
            'last_name' => $this->string(100)->null()->comment('Apellido del contacto'),
            'depto' => $this->string(50)->null()->comment('Departamento del contacto'),
            'tel' => $this->string(20)->notNull()->comment('Teléfono del contacto'),
            'email' => $this->string(250)->notNull()->comment('Correo electrónico del contacto'),
            'comments' => $this->string(300)->null()->comment('Comentarios adicionales'),

            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Registro activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['cardcode', 'contact_code']);

        // FKs de bp_contacts
        $this->addForeignKey('fk_bp_contacts_cardcode', '{{%bp_contacts}}', 'cardcode', '{{%bp}}', 'cardcode', 'CASCADE', 'CASCADE');

        // ============================================================
        // 8.2 BP_address — Direcciones del Dato maestro de socios de negocio
        // ============================================================
        $this->createTableWithLog('{{%bp_address}}', [
            'cardcode' => $this->string(15)->notNull()->comment('Código único del socio de negocios'),
            'address_type' => "ENUM('B','S') NOT NULL DEFAULT 'B' COMMENT 'Tipo de dirección (B=Billing, S=Shipping)'",
            'address_code' => $this->string(15)->notNull()->comment('Código único de la dirección dentro del socio de negocios'),
            'street' => $this->string(150)->notNull()->comment('Calle de la dirección'),
            'city' => $this->string(100)->notNull()->comment('Ciudad de la dirección'),
            'state_code' => $this->string(50)->null()->comment('FK Código del estado de la dirección'),
            'zip' => $this->string(10)->null()->comment('Código postal de la dirección'),
            'country_code' => $this->string(10)->notNull()->comment('FK Código del país de la dirección'),

            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Registro activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['cardcode', 'address_code']);

        // FKs de bp_address
        $this->addForeignKey('fk_bp_address_cardcode', '{{%bp_address}}', 'cardcode', '{{%bp}}', 'cardcode', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_bp_address_state_country', '{{%bp_address}}', ['state_code','country_code'], '{{%states}}', ['code','country'], 'RESTRICT', 'CASCADE');
        
        // Índices para búsquedas frecuentes
        $this->createIndex('idx_bp_address_address_type', '{{%bp_address}}', 'address_type');
        $this->createIndex('idx_bp_address_active', '{{%bp_address}}', 'active');
        $this->createIndex('idx_bp_address_state_code', '{{%bp_address}}', 'state_code');
        $this->createIndex('idx_bp_address_zip', '{{%bp_address}}', 'zip');

        // ============================================================
        // 9. Items — Dato maestro de artículos
        // ============================================================
        $this->createTableWithLog('{{%items}}', [
            'itemcode' => $this->string(100)->notNull()->comment('FK del empleado'),
            'itemname' => $this->string(200)->notNull()->comment('FK al catálogo de tipos de documento'),
            'item_group' => $this->integer()->notNull()->comment('FK Código del país de la dirección'),
            'is_inventory' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Articulo de inventario (Y=Sí, N=No)'",
            'is_purchase' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Articulo de compra (Y=Sí, N=No)'",
            'is_sales' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Articulo de venta (Y=Sí, N=No)'",
            'tire_code' => $this->string(50)->notNull()->comment('FK Código de llanta plantilla'),
            'uom_purchase' => $this->string(50)->notNull()->comment('Unidad de medida para compras'),
            'uom_sales' => $this->string(50)->notNull()->comment('Unidad de medida para ventas'),
            'uom_inventory' => $this->string(50)->notNull()->comment('Unidad de medida para inventario'),
            
            
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Registro activo (Y=Sí, N=No)'",
            'notes' => $this->string(300)->null()->comment('Observaciones'),

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['itemcode']);

        // FKs de Items
        $this->addForeignKey('fk_items_group', '{{%items}}', 'item_group', '{{%group_items}}', 'code', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_items_tire', '{{%items}}', 'tire_code', '{{%tire}}', 'tire_code', 'RESTRICT', 'CASCADE');

        // Índices para búsquedas frecuentes
        $this->createIndex('idx_items_item_group', '{{%items}}', 'item_group');
        $this->createIndex('idx_items_is_inventory', '{{%items}}', 'is_inventory');
        $this->createIndex('idx_items_is_purchase', '{{%items}}', 'is_purchase');
        $this->createIndex('idx_items_is_sales', '{{%items}}', 'is_sales');
        $this->createIndex('idx_items_tire_code', '{{%items}}', 'tire_code');


        // ============================================================
        // 9.1 Items_warehouse — Stock por almacén del Dato maestro de artículos
        // ============================================================

        $this->createTableWithLog('{{%item_warehouse}}', [
            'itemcode' => $this->string(100)->notNull()->comment('FK artículo'),
            'warehouse_code' => $this->string(50)->notNull()->comment('FK almacén'),
            // INVENTARIO
            'onhand' => $this->decimal(19,6)->notNull()->defaultValue(0)->comment('Existencia actual'),
            'committed' => $this->decimal(19,6)->notNull()->defaultValue(0)->comment('Comprometido en ventas'),
            'ordered' => $this->decimal(19,6)->notNull()->defaultValue(0)->comment('Pedido a proveedor'),
            
            // AUDITORÍA
            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),

        ], pkColumns: ['itemcode', 'warehouse_code']);

        $this->addForeignKey('fk_item_wh_item','{{%item_warehouse}}','itemcode','{{%items}}','itemcode','CASCADE','CASCADE');
        $this->addForeignKey('fk_item_wh_warehouse','{{%item_warehouse}}','warehouse_code','{{%warehouse}}','code','RESTRICT','CASCADE');

     
    }

    public function safeDown(): void
    {
       
        // ============================================================
        // DROP FOREIGN KEYS
        // ============================================================

        // item_warehouse
        $this->dropForeignKey('fk_item_wh_item', '{{%item_warehouse}}');
        $this->dropForeignKey('fk_item_wh_warehouse', '{{%item_warehouse}}');

        // items
        $this->dropForeignKey('fk_items_group', '{{%items}}');
        $this->dropForeignKey('fk_items_tire', '{{%items}}');

        // bp_address
        $this->dropForeignKey('fk_bp_address_cardcode', '{{%bp_address}}');
        $this->dropForeignKey('fk_bp_address_state_country', '{{%bp_address}}');

        // bp_contacts
        $this->dropForeignKey('fk_bp_contacts_cardcode', '{{%bp_contacts}}');

        // bp
        $this->dropForeignKey('fk_bp_card_group', '{{%bp}}');
        $this->dropForeignKey('fk_bp_currency', '{{%bp}}');
        $this->dropForeignKey('fk_bp_payment_conditions', '{{%bp}}');
        $this->dropForeignKey('fk_bp_payment_methods', '{{%bp}}');
        $this->dropForeignKey('fk_bp_cfdi_use_code', '{{%bp}}');
        $this->dropForeignKey('fk_bp_cfdi_regimen_code', '{{%bp}}');
        $this->dropForeignKey('fk_bp_vendor_code', '{{%bp}}');

        // states
        $this->dropForeignKey('fk_states_country', '{{%states}}');

        // ============================================================
        // DROP TABLES (children first)
        // ============================================================

        $this->dropTableWithLog('{{%item_warehouse}}');

        $this->dropTableWithLog('{{%bp_address}}');
        $this->dropTableWithLog('{{%bp_contacts}}');

        $this->dropTableWithLog('{{%items}}');

        $this->dropTableWithLog('{{%bp}}');

        // catalogs
        $this->dropTableWithLog('{{%warehouse}}');

        $this->dropTableWithLog('{{%payment_methods}}');

        $this->dropTableWithLog('{{%payment_conditions}}');

        $this->dropTableWithLog('{{%currency}}');

        $this->dropTableWithLog('{{%cfdi_regimen_fiscal}}');

        $this->dropTableWithLog('{{%cfdi_use_sn}}');

        $this->dropTableWithLog('{{%states}}');

        $this->dropTableWithLog('{{%vendors}}');

        $this->dropTableWithLog('{{%group_items}}');

        $this->dropTableWithLog('{{%group_sn}}');

        
    }
}
