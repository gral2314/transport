<?php

use yii\db\Migration;
use app\components\BaseMigration;

class m260516_000001_create_fleet_tables extends BaseMigration
{
    public function safeUp(): void
    {
        // ============================================================
        // CATÁLOGOS
        // ============================================================

         // ============================================================
        // 0. country — Países
        // ============================================================
        $this->createTableWithLog('{{%country}}', [
            'code' => $this->string(10)->notNull()->comment('Código país (ISO 3166-1 alpha-2)'),
            'name' => $this->string(100)->notNull()->comment('Nombre del país'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha creación'),
            'createtime' => $this->time()->null()->comment('Hora creación'),
            'createuser' => $this->integer()->null()->comment('Usuario creación'),
            'updatedate' => $this->date()->null()->comment('Fecha actualización'),
            'updatetime' => $this->time()->null()->comment('Hora actualización'),
            'updateuser' => $this->integer()->null()->comment('Usuario actualización'),
        ], pkColumns: ['code']);

        // ============================================================
        // 1. axle_type
        // Tipos de eje
        // ============================================================


        $this->createTableWithLog('{{%axle_type}}', 
        [
            'code' => $this->string(50)->notNull()->comment('Código de eje'),
            'name' => $this->string(100)->notNull()->comment('Nombre del eje'),
            'tire_qty' => $this->integer()->notNull()->comment('Cantidad de llantas'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 2. axle_type_config
        // Posiciones válidas por tipo de eje
        // ============================================================

        $this->createTableWithLog('{{%axle_type_config}}', [
            'code' => $this->string(50)->notNull()->comment('Código eje '),
            'line_num' => $this->string(50)->notNull()->comment('Número de línea'),
            'name' => $this->string(100)->notNull()->comment('Nombre de la posicion'),
            'pos_code' => "ENUM('LI','LO','RI','RO','LS','RS','LI1','LO1','RI1','RO1','LI2','LO2','RI2','RO2','LI3','LO3','RI3','RO3') NOT NULL COMMENT 'Posicion code (LI=Left Inner, LO=Left Outer, RI=Right Inner, RO=Right Outer, LS=Left Single, RS=Right Single, LI1-RO1=Tandem Axle 1, LI2-RO2=Tandem Axle 2, LI3-RO3=Tandem Axle 3)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code', 'line_num']);
        $this->addForeignKey('fk_axle_type_config_code', '{{%axle_type_config}}', 'code', '{{%axle_type}}', 'code', 'CASCADE');
        
        // ============================================================
        // 1. vehicle_type — Tipos de unidad
        // ============================================================
        $this->createTableWithLog('{{%vehicle_type}}', [
            'code' => $this->string(50)->notNull()->comment('Código tipo unidad'),
            'name' => $this->string(100)->notNull()->comment('Nombre del tipo de unidad'),
            'type_unidad' => "ENUM('Uti','Uni','Rem','Dol') NULL COMMENT 'Tipo de unidad (Uti=Utilitario, Uni=Unidad, Rem=Remolque, Dol=Dolly)'",
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);


        
        
        // ============================================================
        // 4. vehicle_type_axle
        // Configuración de ejes por tipo de unidad
        // ============================================================

        $this->createTableWithLog('{{%vehicle_type_axle}}', [
            'code' => $this->string(50)->notNull()->comment('Código tipo unidad'),
            'line_num' => $this->integer()->notNull()->comment('Número de línea'),
            'axle_type_code' => $this->string(50)->notNull()->comment('Tipo de eje'),

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code', 'line_num']);        
        $this->addForeignKey('fk_vehicle_type_axle_code', '{{%vehicle_type_axle}}', 'axle_type_code', '{{%axle_type}}', 'code');


        // ============================================================
        // 2. vehicle_brand — Marcas de unidad
        // ============================================================
        $this->createTableWithLog('{{%vehicle_brand}}', [
            'code' => $this->string(50)->notNull()->comment('Código de marca'),
            'name' => $this->string(100)->notNull()->comment('Nombre de la marca'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 3. fuel_type — Tipos de combustible
        // ============================================================
        $this->createTableWithLog('{{%fuel_type}}', [
            'code' => $this->string(50)->notNull()->comment('Código tipo combustible'),
            'name' => $this->string(100)->notNull()->comment('Nombre del combustible'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 4. center_cost - Centro de costos
        // ============================================================
        $this->createTableWithLog('{{%center_cost}}', [
            'code' => $this->string(50)->notNull()->comment('Código costo'),
            'name' => $this->string(100)->notNull()->comment('Nombre del Costo'),
            'dimesion' => $this->integer()->notNull()->comment('Dimesion del costto'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 5. service_type — Tipos de servicio
        // ============================================================
        $this->createTableWithLog('{{%service_type}}', [
            'code' => $this->string(50)->notNull()->comment('Código tipo servicio'),
            'name' => $this->string(100)->notNull()->comment('Nombre del servicio'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 6. cargo_type — Tipos de carga
        // ============================================================
        $this->createTableWithLog('{{%cargo_type}}', [
            'code' => $this->string(50)->notNull()->comment('Código tipo carga'),
            'name' => $this->string(100)->notNull()->comment('Nombre del tipo carga'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 7. sat_vehicle_config - Configuracion vehicular SAT
        // ============================================================
        $this->createTableWithLog('{{%sat_vehicle_config}}', [
            'code' => $this->string(50)->notNull()->comment('Código del SAT'),
            'name' => $this->string(200)->notNull()->comment('Descripcion del SAT'),
            'max_ejes' => $this->integer()->notNull()->comment('Maximo de ejes'),
            'max_tires' => $this->integer()->notNull()->comment('Maximo de llantas'),
            'max_remolque' => $this->integer()->notNull()->comment('Maximo de remolques'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 8. nom012 - Nom-012 SSTC
        // ============================================================
        $this->createTableWithLog('{{%nom012}}', [
            'code' => $this->string(50)->notNull()->comment('Código del SAT'),
            'name' => $this->string(200)->notNull()->comment('Descripcion del SAT'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 9. doc_type_vehicule - Tipo de documento vehiculo
        // ============================================================
        $this->createTableWithLog('{{%doc_type_vehicule}}', [
            'code' => $this->string(50)->notNull()->comment('Código de documento'),
            'name' => $this->string(200)->notNull()->comment('Nombre del vehiculo'),
            'alert_time' => $this->integer()->null()->comment('Dias para emitir alerta'),
            'alert_repit' => $this->integer()->null()->comment('Dias para repeticion'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);


        // ============================================================
        // 8. tire_brand — Marcas de llanta
        // ============================================================
        $this->createTableWithLog('{{%tire_brand}}', [
            'code' => $this->string(50)->notNull()->comment('Código marca llanta'),
            'name' => $this->string(100)->notNull()->comment('Nombre marca llanta'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 9. tire_model — Modelos de llanta
        // ============================================================
        $this->createTableWithLog('{{%tire_model}}', [
            'code' => $this->string(50)->notNull()->comment('Código modelo llanta'),
            'name' => $this->string(100)->notNull()->comment('Nombre modelo llanta'),
            'brand_code' => $this->string(50)->notNull()->comment('Marca de llanta (FK tire_brand.code)'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        $this->addForeignKey(
            'fk_tire_model_brand',
            '{{%tire_model}}',
            'brand_code',
            '{{%tire_brand}}',
            'code'
        );

        // ============================================================
        // 10. tire_size — Medidas de llanta
        // ============================================================
        $this->createTableWithLog('{{%tire_size}}', [
            'code' => $this->string(50)->notNull()->comment('Código medida llanta'),
            'name' => $this->string(100)->notNull()->comment('Nombre medida llanta'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 11. tire_type — tipos de llanta
        // ============================================================
        $this->createTableWithLog('{{%tire_type}}', [
            'code' => $this->string(50)->notNull()->comment('Código tipo de llanta'),
            'name' => $this->string(100)->notNull()->comment('Nombre de tipo llanta'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['code']);

        // ============================================================
        // 12. tire_usage_type — Tipo de uso de llanta
        // ============================================================
        $this->createTableWithLog('{{%tire_usage_type}}', [
            'code' => $this->string(20)->notNull()->comment('Código tipo de uso'),
            'name' => $this->string(100)->notNull()->comment('Nombre tipo de uso'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha creación'),
            'createtime' => $this->time()->null()->comment('Hora creación'),
            'createuser' => $this->integer()->null()->comment('Usuario creación'),
            'updatedate' => $this->date()->null()->comment('Fecha actualización'),
            'updatetime' => $this->time()->null()->comment('Hora actualización'),
            'updateuser' => $this->integer()->null()->comment('Usuario actualización'),
        ], pkColumns: ['code']);

        // ============================================================
        // 13. tire_tread_design — Diseño de rodada
        // ============================================================
        $this->createTableWithLog('{{%tire_tread_design}}', [
            'code' => $this->string(20)->notNull()->comment('Código diseño rodada'),
            'name' => $this->string(100)->notNull()->comment('Nombre diseño rodada'),
            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'createdate' => $this->date()->null()->comment('Fecha creación'),
            'createtime' => $this->time()->null()->comment('Hora creación'),
            'createuser' => $this->integer()->null()->comment('Usuario creación'),
            'updatedate' => $this->date()->null()->comment('Fecha actualización'),
            'updatetime' => $this->time()->null()->comment('Hora actualización'),
            'updateuser' => $this->integer()->null()->comment('Usuario actualización'),
        ], pkColumns: ['code']);

        // ============================================================
        // 14. tire — Llantas
        // ============================================================
        $this->createTableWithLog('{{%tire}}', [
            'tire_code' => $this->string(50)->notNull()->comment('Código llanta'),
            'tire_name' => $this->string(250)->notNull()->comment('Nombre llanta'),
            'object' => $this->string(50)->notNull()->comment('Tipo objeto'),

            'brand_code' => $this->string(50)->null()->comment('Marca llanta (FK tire_brand.code)'),
            'model_code' => $this->string(50)->null()->comment('Modelo llanta (FK tire_model.code)'),
            'size_code' => $this->string(50)->null()->comment('Medida llanta (FK tire_size.code)'),
            'type_code' => $this->string(50)->null()->comment('Medida llanta (FK type_size.code)'),

            'serial_no' => $this->string(50)->null()->comment('Número serie'),
            'dot_code' => $this->string(50)->null()->comment('Código DOT'),

            'manufacture_date' => $this->date()->null()->comment('Fecha fabricación'),
            'purchase_date' => $this->date()->null()->comment('Fecha compra'),
            'purchase_price' => $this->float()->null()->comment('Precio compra'),

            'current_km' => $this->float()->null()->comment('Kilometraje actual'),
            'max_km' => $this->float()->null()->comment('Kilometraje máximo'),

            'retread_qty' => $this->integer()->null()->comment('Cantidad renovados'),

            'operational_status' => "ENUM('AV','US','MT','DS') NOT NULL DEFAULT 'AV' COMMENT 'Estado operacional de la llanta (AV=Disponible, US=En uso, MT=Mantenimiento, DS=Desechada)'",
            'physical_condition' => "ENUM('NW','RT','GD','LW','IW','SD','PU','UN' ) NOT NULL DEFAULT 'NW' COMMENT 'Condición física de la llanta (NW=Nueva, RT=Renovada, GD=Buena, LW=Desgaste leve, IW=Desgaste irregular, SD=Daño lateral, PU=Ponchadura, UN=Inutilizable)'",
            'location_status' => "ENUM('WH','VH','WS','SC','SP' ) NOT NULL DEFAULT 'WH' COMMENT 'Ubicación actual de la llanta (WH=Almacen, VH=Vehículo, WS=Taller, SC=Desecho, SP=Proveedor)'",

            'is_final' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",

            'notes' => $this->text()->null()->comment('Observaciones'),

            'tread_design_code' => $this->string(20)->null()->comment('Diseño rodada (FK tire_tread_design.code)'),
            'tire_width' => $this->decimal(10,2)->null()->comment('Ancho llanta (mm)'),
            'aspect_ratio' => $this->decimal(10,2)->null()->comment('Relación aspecto (%)'),
            'structure_type' => "ENUM('R','B','D') NULL COMMENT 'Tipo estructura (R=Radial, B=Bias, D=Diagonal)'",
            'rim_size' => $this->decimal(10,2)->null()->comment('Tamaño rin (pulgadas)'),
            'load_idx' => $this->string(10)->null()->comment('Índice carga'),
            'max_load' => $this->decimal(10,2)->null()->comment('Carga máxima (kg)'),
            
            'max_press' => $this->decimal(10,2)->null()->comment('Presión máxima (PSI)'),
            'traction_rate' => "ENUM('AA','A','B','C') NULL COMMENT 'Clasificación tracción (AA,A,B,C)'",
            'temp_rate' => "ENUM('A','B','C') NULL COMMENT 'Clasificación temperatura (A,B,C)'",
            'country_code' => $this->string(10)->null()->comment('País fabricación (FK country.code)'),
            'orig_tread_depth' => $this->decimal(10,2)->null()->comment('Profundidad original (mm)'),
            'init_tread_depth' => $this->decimal(10,2)->null()->comment('Profundidad inicial (mm)'),
            'curr_tread_depth' => $this->decimal(10,2)->null()->comment('Profundidad actual (mm)'),
            'tread_wear_factor' => $this->decimal(10,4)->null()->comment('Factor desgaste profundidad'),

            'usage_type_code' => $this->string(20)->null()->comment('Tipo uso (FK tire_usage_type.code)'),
            'init_km' => $this->decimal(12,2)->null()->comment('Kilometraje inicial'),
            'repair_qty' => $this->integer()->notNull()->defaultValue(0)->comment('Cantidad reparaciones'),
            'assigned_unit_code' => $this->string(50)->null()->comment('Unidad asignada (FK vehicle.vehicle_code)'),

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['tire_code']);


        $this->createIndex('idx_tire_usage_type_code', '{{%tire}}', 'usage_type_code');
        $this->createIndex('idx_tire_tread_design_code', '{{%tire}}', 'tread_design_code');
        $this->createIndex('idx_tire_country_code', '{{%tire}}', 'country_code');
        $this->createIndex('idx_tire_dot_code', '{{%tire}}', 'dot_code');
        $this->createIndex('idx_tire_serial_no', '{{%tire}}', 'serial_no');
        $this->createIndex('idx_tire_assigned_unit_code', '{{%tire}}', 'assigned_unit_code');

        
        $this->addForeignKey('fk_tire_brand', '{{%tire}}', 'brand_code', '{{%tire_brand}}', 'code');
        $this->addForeignKey('fk_tire_model', '{{%tire}}', 'model_code', '{{%tire_model}}', 'code');
        $this->addForeignKey('fk_tire_size', '{{%tire}}', 'size_code', '{{%tire_size}}', 'code');
        $this->addForeignKey('fk_tire_type', '{{%tire}}', 'type_code', '{{%tire_type}}', 'code');
        // ============================================================
        // FOREIGN KEYS (solo en tabla principal, no en log__)
        // ============================================================

        $this->addForeignKey('fk_tire_tread_design','{{%tire}}','tread_design_code','{{%tire_tread_design}}','code','SET NULL','CASCADE');
        $this->addForeignKey('fk_tire_usage_type','{{%tire}}','usage_type_code','{{%tire_usage_type}}','code','SET NULL','CASCADE');
        
        $this->addForeignKey('fk_tire_country','{{%tire}}','country_code','{{%country}}','code','SET NULL','CASCADE');




        // ============================================================
        // DATOS MAESTROS
        // ============================================================

        // ============================================================
        // 13. vehicle — Unidades
        // ============================================================
        $this->createTableWithLog('{{%vehicle}}', [
            'vehicle_code' => $this->string(50)->notNull()->comment('Código unidad'),
            'vehicle_name' => $this->string(250)->notNull()->comment('Nombre unidad'),
            'object' => $this->string(50)->notNull()->comment('Tipo de objeto'),
            'vehicle_type_code' => $this->string(50)->notNull()->comment('Tipo unidad (FK vehicle_type.code)'),
            'brand_code' => $this->string(50)->null()->comment('Marca unidad (FK vehicle_brand.code)'),
            'model' => $this->string(100)->null()->comment('Modelo'),
            'unit_year' => $this->integer()->null()->comment('Año unidad'),
            'plate_no' => $this->string(20)->null()->comment('Número de placa'),
            'economic_no' => $this->string(20)->null()->comment('Número económico'),
            'vin' => $this->string(50)->null()->comment('VIN'),
            'engine_no' => $this->string(50)->null()->comment('Número motor'),
            'serial_no' => $this->string(50)->null()->comment('Número serie'),
            //'axle_qty' => $this->integer()->null()->comment('Cantidad de ejes'),
            //'tire_qty' => $this->integer()->null()->comment('Cantidad de llantas'),

            'fuel_type_code' => $this->string(50)->null()->comment('Tipo combustible (FK fuel_type.code)'),
            'fuel_capacity' => $this->float()->null()->comment('Capacidad combustible'),
            'current_fuel' => $this->float()->null()->comment('Combustible actual'),
            'fuel_performance' => $this->float()->null()->comment('Rendimiento combustible'),

            'current_km' => $this->float()->null()->comment('Kilometraje actual'),
            'initial_km' => $this->float()->null()->comment('Kilometraje inicial'),

            'weight_capacity' => $this->float()->null()->comment('Capacidad peso'),
            'volume_capacity' => $this->float()->null()->comment('Capacidad volumen'),

            'cargo_length' => $this->float()->null()->comment('Largo carga'),
            'cargo_width' => $this->float()->null()->comment('Ancho carga'),
            'cargo_height' => $this->float()->null()->comment('Alto carga'),

            'unit_length' => $this->float()->null()->comment('Largo unidad'),
            'unit_width' => $this->float()->null()->comment('Ancho unidad'),
            'unit_height' => $this->float()->null()->comment('Alto unidad'),

            'gps_id' => $this->string(50)->null()->comment('GPS ID'),
            'gps_model' => $this->string(50)->null()->comment('Modelo GPS'),
            'gps_provider' => $this->string(50)->null()->comment('Proveedor GPS'),

            'iave' => $this->string(50)->null()->comment('IAVE'),
            
            'fixed_asset_code' => $this->string(50)->null()->comment('Activo fijo'),
            'gl_account' => $this->string(50)->null()->comment('Cuenta contable'),
            'cost_center_code' => $this->string(50)->null()->comment('FK center_cost.code Centro costos'),

            'acquisition' => "ENUM('P','R') NOT NULL DEFAULT 'P' COMMENT 'Tipo de Adqusicion (P=Comprada, R=Rentada)'",
            'purchase_date' => $this->date()->null()->comment('Fecha compra'),
            'purchase_price' => $this->float()->null()->comment('Precio compra'),

            'sat_vehicle_config_code' => $this->string(50)->null()->comment('FK sat_vehicle_config.code'),
            'nom012_code' => $this->string(50)->null()->comment('FK nom012.code Tipo NOM012'),

            //'suspension_drive' => $this->string(50)->null()->comment('Suspensión motriz'),
            //'suspension_directional' => $this->string(50)->null()->comment('Suspensión direccional'),
            //'suspension_trailer' => $this->string(50)->null()->comment('Suspensión arrastre'),

            'service_type_code' => $this->string(50)->null()->comment('Tipo servicio (FK service_type.code)'),
            'cargo_type_code' => $this->string(50)->null()->comment('Tipo carga (FK cargo_type.code)'),

            'active' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Activo (Y=Sí, N=No)'",
            'status' => "ENUM('A','I','M','O') NOT NULL DEFAULT 'A' COMMENT 'Status (A=Activo, I=Inactivo, M=Mantenimiento, O=Fuera de servicio)'",

            'available' => "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Disponible (Y=Sí, N=No)'",

            'default_driver_code' => $this->string(50)->null()->comment('Operador principal'),
            'default_driver2_code' => $this->string(50)->null()->comment('Operador secundario'),

            'default_trailer1_code' => $this->string(50)->null()->comment('Remolque 1'),
            'default_trailer2_code' => $this->string(50)->null()->comment('Remolque 2'),
            'default_dolly_code' => $this->string(50)->null()->comment('Dolly'),

            'notes' => $this->text()->null()->comment('Observaciones'),

            'last_service_date' => $this->date()->null()->comment('Último servicio'),

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['vehicle_code']);

        $this->addForeignKey('fk_vehicle_type', '{{%vehicle}}', 'vehicle_type_code', '{{%vehicle_type}}', 'code');
        $this->addForeignKey('fk_vehicle_brand', '{{%vehicle}}', 'brand_code', '{{%vehicle_brand}}', 'code');
        $this->addForeignKey('fk_vehicle_fuel', '{{%vehicle}}', 'fuel_type_code', '{{%fuel_type}}', 'code');
        $this->addForeignKey('fk_vehicle_cargo', '{{%vehicle}}', 'cargo_type_code', '{{%cargo_type}}', 'code');
        $this->addForeignKey('fk_vehicle_cost', '{{%vehicle}}', 'cost_center_code', '{{%center_cost}}', 'code');
        $this->addForeignKey('fk_vehicle_sat_code', '{{%vehicle}}', 'sat_vehicle_config_code', '{{%sat_vehicle_config}}', 'code');
        $this->addForeignKey('fk_vehicle_nom012', '{{%vehicle}}', 'nom012_code', '{{%nom012}}', 'code');

        


        // ============================================================
        // TABLAS HIJAS
        // ============================================================

        // ============================================================
        // 15. vehicle_document — Documentos unidad
        // ============================================================
        $this->createTableWithLog('{{%vehicle_document}}', [
            'vehicle_code' => $this->string(50)->notNull()->comment('Código unidad (FK vehicle.vehicle_code)'),
            'line_num' => $this->integer()->notNull()->comment('Número de línea'),
            'object' => $this->string(50)->notNull()->comment('Tipo objeto'),

            'doc_type_code' => $this->string(50)->null()->comment('fk doc_type_vehicule.code Tipo documento'),
            'document_no' => $this->string(50)->null()->comment('Número documento'),

            'issue_date' => $this->date()->null()->comment('Fecha emisión'),
            'exp_date' => $this->date()->null()->comment('Fecha vencimiento'),
            'next_alert' => $this->date()->null()->comment('Proxima alerta'),

            'attach' => $this->text()->null()->comment('Adjunto'),
            'notes' => $this->text()->null()->comment('Observaciones'),

            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['vehicle_code', 'line_num']);

        $this->addForeignKey('fk_vehicle_document_vehicle','{{%vehicle_document}}','vehicle_code','{{%vehicle}}','vehicle_code','CASCADE');
        $this->addForeignKey('fk_doc_type_vehicule_code', '{{%vehicle_document}}', 'doc_type_code', '{{%doc_type_vehicule}}', 'code');

        // ============================================================
        // 16. vehicle_tire — Relación llanta unidad
        // ============================================================
        $this->createTableWithLog('{{%vehicle_tire}}', [
            'vehicle_code' => $this->string(50)->notNull()->comment('Código unidad (FK vehicle.vehicle_code)'),
            'line_num' => $this->integer()->notNull()->comment('Número línea'),
            'object' => $this->string(50)->notNull()->comment('Tipo objeto'),

            'tire_code' => $this->string(50)->null()->comment('Código de llanta (NULL si no está asignada)'),
            'axle_line_num' => $this->integer()->notNull()->comment('Número de eje del tipo de vehículo (FK vehicle_type_axle.line_num)'),
            'axle_type_code' => $this->string(50)->notNull()->comment('Código de eje usado en tipo de vehículo (FK vehicle_type_axle.axle_type_code - desnormalizado para queries)'),   

            'eje_code' => $this->string(50)->notNull()->comment('Eje usado llanta (FK tire_position.code)'),
            
            'position_code' => $this->string(50)->notNull()->comment('Posición llanta en el eje (LI, LO, RI, RO, LS, RS, LI1-RO3)'),
            'side_code' => $this->string(50)->notNull()->comment('Posición llanta (FK tire_position.code)'),


            'install_date' => $this->date()->null()->comment('Fecha instalación'),
            'install_km' => $this->float()->null()->comment('KM instalación'),
            
            'record_km' => $this->float()->null()->comment('KM recorrido'),
            
            'createdate' => $this->date()->null()->comment('Fecha de creación'),
            'createtime' => $this->time()->null()->comment('Hora de creación'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario que crea'),
            'updatedate' => $this->date()->null()->comment('Fecha de actualización'),
            'updatetime' => $this->time()->null()->comment('Hora de actualización'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario que actualiza'),
        ], pkColumns: ['vehicle_code', 'line_num']);

        $this->addForeignKey('fk_vehicle_tire_vehicle', '{{%vehicle_tire}}', 'vehicle_code', '{{%vehicle}}', 'vehicle_code', 'CASCADE');
        $this->addForeignKey('fk_vehicle_tire_tire','{{%vehicle_tire}}','tire_code','{{%tire}}','tire_code','SET NULL','CASCADE'  );

        $this->addForeignKey('fk_vehicle_tire_axle_type','{{%vehicle_tire}}','axle_type_code','{{%axle_type}}','code');


        // NOTA: Se agrega FK a vehicle para facilitar queries, aunque la relación principal es a través de vehicle_tire
        $this->addForeignKey('fk_tire_assigned_unit','{{%tire}}','assigned_unit_code','{{%vehicle}}','vehicle_code','SET NULL','CASCADE');

    }

    public function safeDown(): void
    {
        
        $this->dropTableWithLog('{{%vehicle_tire}}');
        $this->dropTableWithLog('{{%vehicle_document}}');
        $this->dropTableWithLog('{{%vehicle}}');
        $this->dropTableWithLog('{{%tire}}');

        $this->dropTableWithLog('{{%vehicle_type_axle}}');
        $this->dropTableWithLog('{{%axle_type_config}}');

        $this->dropTableWithLog('{{%tire_size}}');
        $this->dropTableWithLog('{{%tire_usage_type}}');
        $this->dropTableWithLog('{{%tire_tread_design}}');
        $this->dropTableWithLog('{{%tire_model}}');
        $this->dropTableWithLog('{{%tire_brand}}');
        $this->dropTableWithLog('{{%tire_type}}');
        $this->dropTableWithLog('{{%doc_type_vehicule}}');
        $this->dropTableWithLog('{{%nom012}}');
        $this->dropTableWithLog('{{%sat_vehicle_config}}');
        $this->dropTableWithLog('{{%cargo_type}}');
        $this->dropTableWithLog('{{%service_type}}');
        $this->dropTableWithLog('{{%center_cost}}');
        $this->dropTableWithLog('{{%fuel_type}}');
        $this->dropTableWithLog('{{%vehicle_brand}}');
        
        $this->dropTableWithLog('{{%axle_type}}');
        $this->dropTableWithLog('{{%vehicle_type}}');
        $this->dropTableWithLog('{{%country}}');
        
    }
}