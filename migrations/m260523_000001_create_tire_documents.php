<?php

declare(strict_types=1);

use app\components\BaseMigration;

final class m260523_000001_create_tire_documents extends BaseMigration
{
    
    public function safeUp(): void
    {
        // ============================================================
        // doc_tire_movement (HEADER)
        // id interno autoincremental + docnum visible para usuario
        // ============================================================

        $this->createTableWithLog('{{%doc_tire_movement}}', [
            'docentry' => $this->bigPrimaryKey(),
            'docnum' => $this->string(30)->notNull()->comment('Número visible del documento'),
            'doc_date' => $this->date()->notNull()->comment('Fecha del documento'),
            'doc_duedate' => $this->date()->notNull()->comment('Fecha del vencimiento o fecha de ejecución planificada'),
            'doc_status' => "ENUM('O','C') NOT NULL DEFAULT 'O' COMMENT 'Estado del documento (O=Abierto, C=Cerrado)'",
            'status' => "ENUM('PLAN','EXEC','VAL','CLOSE') NOT NULL DEFAULT 'PLAN' COMMENT 'Estado operativo (PLAN=Planeado, EXEC=Ejecutado, VAL=Validado, CLOSE=Cerrado)'",
            'canceled' => "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Documento cancelado (Y=Si, N=No)'",
            'comments' => $this->string(300)->null()->comment('Comentarios'),
            'technician_user_id' => $this->bigInteger()->null()->comment('Tecnico asignado (FK users.id)'),
            'priority' => "ENUM('LOW','MEDIUM','HIGH','URGENT') NOT NULL DEFAULT 'LOW' COMMENT 'Prioridad (LOW=Baja, MEDIUM=Media, HIGH=Alta, URGENT=Urgente)'",
            'origin_type' => "ENUM('MANUAL','MAINTENANCE','INSPECTION','REPAIR','WAREHOUSE') NOT NULL DEFAULT 'MANUAL' COMMENT 'Origen (MANUAL=Manual, MAINTENANCE=Mantenimiento, INSPECTION=Inspeccion, REPAIR=Reparacion, WAREHOUSE=Almacen)'",
            'base_type' => $this->string(50)->null()->comment('Tipo de documento base relacionado'),
            'base_entry' => $this->bigInteger()->null()->comment('DocEntry del documento base relacionado'),

            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario creador (FK users.id)'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario actualizador (FK users.id)'),

        ], pkColumns: ['docentry']);


        // ============================================================
        // INDEXES
        // ============================================================

        $this->createIndex('ux_doc_tire_movement_docnum','{{%doc_tire_movement}}','docnum',true);
        $this->createIndex('idx_doc_tire_movement_status','{{%doc_tire_movement}}','status');
        $this->createIndex('idx_doc_tire_movement_doc_status','{{%doc_tire_movement}}','doc_status');
        $this->createIndex('idx_doc_tire_movement_canceled','{{%doc_tire_movement}}','canceled');
        $this->createIndex('idx_doc_tire_movement_technician_user_id','{{%doc_tire_movement}}','technician_user_id');


        // ============================================================
        // FK
        // ============================================================
        $this->addForeignKey('fk_doc_tire_movement_createuser','{{%doc_tire_movement}}','createuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_updateuser','{{%doc_tire_movement}}','updateuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_technician_user_id','{{%doc_tire_movement}}','technician_user_id','{{%users}}','id','RESTRICT','CASCADE');



        // ============================================================
        // doc_tire_movement_vehicle
        // vehículos involucrados
        // ============================================================

        $this->createTableWithLog('{{%doc_tire_movement_vehicle}}', [

            'id' => $this->bigPrimaryKey(),
            'docentry' => $this->bigInteger()->notNull()->comment('Documento movimiento (FK doc_tire_movement.docentry)'),
            'linenum' => $this->integer()->notNull()->comment('Número de línea'),
            'vehicle_code' => $this->string(50)->null()->comment('Unidad involucrada (FK vehicle.vehicle_code)'),
            'vehicle_km' => $this->decimal(19,2)->null()->comment('Odómetro al momento del movimiento'),
            'comments' => $this->string(300)->null()->comment('Comentarios'),

            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario creador (FK users.id)'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario actualizador (FK users.id)'),

        ], pkColumns: ['id']);


        // ============================================================
        // INDEXES
        // ============================================================
        $this->createIndex('ux_doc_tire_movement_vehicle_docentry_linenum','{{%doc_tire_movement_vehicle}}',['docentry', 'linenum'],true);
        $this->createIndex('idx_doc_tire_movement_vehicle_vehicle_code','{{%doc_tire_movement_vehicle}}','vehicle_code');
        $this->createIndex('idx_doc_tire_movement_vehicle_vehicle_km','{{%doc_tire_movement_vehicle}}','vehicle_km');


        // ============================================================
        // FK
        // ============================================================
        $this->addForeignKey('fk_doc_tire_movement_vehicle_docentry','{{%doc_tire_movement_vehicle}}','docentry','{{%doc_tire_movement}}','docentry','CASCADE','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_vehicle_vehicle','{{%doc_tire_movement_vehicle}}','vehicle_code','{{%vehicle}}','vehicle_code','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_vehicle_createuser','{{%doc_tire_movement_vehicle}}','createuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_vehicle_updateuser','{{%doc_tire_movement_vehicle}}','updateuser','{{%users}}','id','RESTRICT','CASCADE');


        // ============================================================
        // doc_tire_movement_detail
        // ledger de movimientos
        // ============================================================

        $this->createTableWithLog('{{%doc_tire_movement_detail}}', [
            'id' => $this->bigPrimaryKey(),
            'docentry' => $this->bigInteger()->notNull()->comment('Documento movimiento (FK doc_tire_movement.docentry)'),
            'linenum' => $this->integer()->notNull()->comment('Número de línea'),

            'movement_type' => "ENUM('ASSIGN','ROTATE','REMOVE','TRANSFER','REPAIR_SEND','REPAIR_RETURN','SCRAP') NOT NULL COMMENT 'Tipo (ASSIGN=Asignacion, ROTATE=Rotacion, REMOVE=Retiro, TRANSFER=Traslado, REPAIR_SEND=Envio reparacion, REPAIR_RETURN=Retorno reparacion, SCRAP=Baja)'",
            'tire_code' => $this->string(50)->null()->comment('Llanta principal (FK tire.tire_code)'),
            'tire_km' => $this->decimal(19,2)->null()->comment('KM acumulado de llanta'),
            'related_tire_code' => $this->string(50)->null()->comment('Segunda llanta para rotacion (FK tire.tire_code)'),

            'related_tire_km' => $this->decimal(19,2)->null()->comment('KM acumulado segunda llanta'),
            'vehicle_code_from' => $this->string(50)->null()->comment('Unidad origen (FK vehicle.vehicle_code)'),
            'vehicle_code_to' => $this->string(50)->null()->comment('Unidad destino (FK vehicle.vehicle_code)'),
            'position_from' => $this->string(50)->null()->comment('Posicion origen de la llanta'),
            'position_to' => $this->string(50)->null()->comment('Posicion destino de la llanta'),
            'whs_code_from' => $this->string(50)->null()->comment('Almacen origen (FK warehouse.code)'),
            'whs_code_to' => $this->string(50)->null()->comment('Almacen destino (FK warehouse.code)'),
            
            'line_status' => "ENUM('PENDING','EXECUTED','CANCELED') NOT NULL DEFAULT 'PENDING' COMMENT 'Estado de linea (PENDING=Pendiente, EXECUTED=Ejecutada, CANCELED=Cancelada)'",
            'execution_date' => $this->date()->null()->comment('Fecha ejecución'),

            'execution_time' => $this->time()->null()->comment('Hora ejecución'),

            'comments' => $this->string(300)->null()->comment('Comentarios'),
            'tire_condition' => "ENUM('GOOD','USED','DAMAGED','SCRAP','REPAIR') NULL COMMENT 'Condicion operativa (GOOD=Buena, USED=Usada, DAMAGED=Daniada, SCRAP=Baja, REPAIR=Reparacion)'",
            'physical_condition' => "ENUM('NW','RT','GD','LW','IW','SD','PU','UN') NOT NULL DEFAULT 'NW' COMMENT 'Condicion fisica (NW=Nueva, RT=Renovada, GD=Buena, LW=Desgaste leve, IW=Desgaste irregular, SD=Danio lateral, PU=Ponchadura, UN=Inutilizable)'",
            'tread_depth' => $this->decimal(10,2)->null()->comment('Profundidad de dibujo registrada (mm)'),

            'target_type' => $this->string(50)->null()->comment('Tipo de documento objetivo relacionado'),
            'target_entry' => $this->bigInteger()->null()->comment('DocEntry del documento objetivo relacionado'),

            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario creador (FK users.id)'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario actualizador (FK users.id)'),

        ], pkColumns: ['id']);


        // ============================================================
        // INDEXES
        // ============================================================

        $this->createIndex('ux_doc_tire_movement_detail_docentry_linenum','{{%doc_tire_movement_detail}}',['docentry', 'linenum'],true);
        $this->createIndex('idx_doc_tire_movement_detail_document','{{%doc_tire_movement_detail}}','docentry');
        $this->createIndex('idx_doc_tire_movement_detail_movement_type','{{%doc_tire_movement_detail}}','movement_type');
        $this->createIndex('idx_doc_tire_movement_detail_tire_code','{{%doc_tire_movement_detail}}','tire_code');
        $this->createIndex('idx_doc_tire_movement_detail_related_tire_code','{{%doc_tire_movement_detail}}','related_tire_code');
        $this->createIndex('idx_doc_tire_movement_detail_vehicle_code_from','{{%doc_tire_movement_detail}}','vehicle_code_from');
        $this->createIndex('idx_doc_tire_movement_detail_vehicle_code_to','{{%doc_tire_movement_detail}}','vehicle_code_to');
        $this->createIndex('idx_doc_tire_movement_detail_line_status','{{%doc_tire_movement_detail}}','line_status');
        $this->createIndex('idx_doc_tire_movement_detail_execution_date','{{%doc_tire_movement_detail}}','execution_date');

        // ============================================================
        // FK
        // ============================================================
        $this->addForeignKey('fk_doc_tire_movement_detail_docentry','{{%doc_tire_movement_detail}}','docentry','{{%doc_tire_movement}}','docentry','CASCADE','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_detail_tire','{{%doc_tire_movement_detail}}','tire_code','{{%tire}}','tire_code','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_detail_related_tire','{{%doc_tire_movement_detail}}','related_tire_code','{{%tire}}','tire_code','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_detail_vehicle_from','{{%doc_tire_movement_detail}}','vehicle_code_from','{{%vehicle}}','vehicle_code','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_detail_vehicle_to','{{%doc_tire_movement_detail}}','vehicle_code_to','{{%vehicle}}','vehicle_code','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_detail_whs_from','{{%doc_tire_movement_detail}}','whs_code_from','{{%warehouse}}','code','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_detail_whs_to','{{%doc_tire_movement_detail}}','whs_code_to','{{%warehouse}}','code','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_detail_createuser','{{%doc_tire_movement_detail}}','createuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_detail_updateuser','{{%doc_tire_movement_detail}}','updateuser','{{%users}}','id','RESTRICT','CASCADE');



        // ============================================================
        // doc_tire_movement_attach
        // evidencias
        // ============================================================
        $this->createTableWithLog('{{%doc_tire_movement_attach}}', [
            'id' => $this->bigPrimaryKey(),
            'docentry' => $this->bigInteger()->notNull()->comment('Documento movimiento (FK doc_tire_movement.docentry)'),
            'linenum' => $this->integer()->notNull()->comment('Número de línea'),
            'filename' => $this->string(255)->notNull()->comment('Nombre archivo'),
            'filepath' => $this->string(500)->notNull()->comment('Ruta archivo'),
            'notes' => $this->text()->null()->comment('Observaciones'),

            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario creador (FK users.id)'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario actualizador (FK users.id)'),

        ], pkColumns: ['id']);

        // ============================================================
        // INDEXES
        // ============================================================
        $this->createIndex('ux_doc_tire_movement_attach_docentry_linenum','{{%doc_tire_movement_attach}}',['docentry', 'linenum'],true);

        // ============================================================
        // FK
        // ============================================================
        $this->addForeignKey('fk_doc_tire_movement_attach_document','{{%doc_tire_movement_attach}}','docentry','{{%doc_tire_movement}}','docentry','CASCADE','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_attach_createuser','{{%doc_tire_movement_attach}}','createuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_movement_attach_updateuser','{{%doc_tire_movement_attach}}','updateuser','{{%users}}','id','RESTRICT','CASCADE');
        
        // ============================================================
        // doc_tire_repair (HEADER)
        // ============================================================

        $this->createTableWithLog('{{%doc_tire_repair}}', [
            'docentry' => $this->bigPrimaryKey(),
            'docnum' => $this->string(30)->notNull()->comment('Número visible del documento'),
            'doc_date' => $this->date()->notNull()->comment('Fecha del documento'),
            'doc_status' => "ENUM('O','C') NOT NULL DEFAULT 'O' COMMENT 'Estado del documento (O=Abierto, C=Cerrado)'",
            'status' => "ENUM('PLAN','EXEC','VAL','CLOSE') NOT NULL DEFAULT 'PLAN' COMMENT 'Estado operativo (PLAN=Planeado, EXEC=Ejecutado, VAL=Validado, CLOSE=Cerrado)'",
            'canceled' => "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Documento cancelado (Y=Si, N=No)'",
            'provider_code' => $this->string(50)->null()->comment('Proveedor/Taller (FK bp.cardcode)'),
            'repair_date' => $this->date()->notNull()->comment('Fecha de reparación'),
            'return_date' => $this->date()->null()->comment('Fecha de retorno a operación'),
            'comments' => $this->string(300)->null()->comment('Comentarios generales'),

            // Auditoría
            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario creador (FK users.id)'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario actualizador (FK users.id)'),

        ], pkColumns: ['docentry']);

        // ============================================================
        // INDEXES
        // ============================================================
        $this->createIndex('ux_doc_tire_repair_docnum','{{%doc_tire_repair}}','docnum',true);
        $this->createIndex('idx_doc_tire_repair_provider_code','{{%doc_tire_repair}}','provider_code');
        $this->createIndex('idx_doc_tire_repair_status','{{%doc_tire_repair}}','status');
        $this->createIndex('idx_doc_tire_repair_doc_status','{{%doc_tire_repair}}','doc_status');
        $this->createIndex('idx_doc_tire_repair_repair_date','{{%doc_tire_repair}}','repair_date');


        // ============================================================
        // FK
        // ============================================================
        $this->addForeignKey('fk_doc_tire_repair_vendor','{{%doc_tire_repair}}','provider_code','{{%bp}}','cardcode','SET NULL','CASCADE');
        $this->addForeignKey('fk_doc_tire_repair_createuser','{{%doc_tire_repair}}','createuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_repair_updateuser','{{%doc_tire_repair}}','updateuser','{{%users}}','id','RESTRICT','CASCADE');


        // ============================================================
        // doc_tire_repair_detail (DETAIL)
        // ============================================================

        $this->createTableWithLog('{{%doc_tire_repair_detail}}', [
            'id' => $this->bigPrimaryKey(),
            'docentry' => $this->bigInteger()->notNull()->comment('Documento reparacion (FK doc_tire_repair.docentry)'),
            'linenum' => $this->integer()->notNull()->comment('Número de línea'),
            'tire_code' => $this->string(50)->notNull()->comment('Llanta afectada (FK tire.tire_code)'),
            'repair_type' => "ENUM('PUNCTURE','PATCH','RETREAD','BALANCE','ALIGNMENT','ROTATION','OTHER') NOT NULL DEFAULT 'OTHER' COMMENT 'Tipo (PUNCTURE=Ponchadura, PATCH=Parche, RETREAD=Renovado, BALANCE=Balanceo, ALIGNMENT=Alineacion, ROTATION=Rotacion, OTHER=Otro)'",
            'cost' => $this->decimal(10,2)->null()->comment('Costo reparación'),
            'comments' => $this->string(300)->null()->comment('Comentarios línea'),

            // Auditoría
            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario creador (FK users.id)'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario actualizador (FK users.id)'),

        ], pkColumns: ['id']);


        // ============================================================
        // INDEXES
        // ============================================================
        $this->createIndex('ux_doc_tire_repair_detail_docentry_linenum','{{%doc_tire_repair_detail}}',['docentry', 'linenum'],true);
        $this->createIndex('idx_doc_tire_repair_detail_tire_code','{{%doc_tire_repair_detail}}','tire_code');
        $this->createIndex('idx_doc_tire_repair_detail_repair_type','{{%doc_tire_repair_detail}}','repair_type');


        // ============================================================
        // FK
        // ============================================================
        $this->addForeignKey('fk_doc_tire_repair_detail_document','{{%doc_tire_repair_detail}}','docentry','{{%doc_tire_repair}}','docentry','CASCADE','CASCADE');
        $this->addForeignKey('fk_doc_tire_repair_detail_tire','{{%doc_tire_repair_detail}}','tire_code','{{%tire}}','tire_code','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_repair_detail_createuser','{{%doc_tire_repair_detail}}','createuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_repair_detail_updateuser','{{%doc_tire_repair_detail}}','updateuser','{{%users}}','id','RESTRICT','CASCADE');


        // ============================================================
        // doc_tire_repair_attach (ATTACHMENTS)
        // ============================================================

        $this->createTableWithLog('{{%doc_tire_repair_attach}}', [
            'id' => $this->bigPrimaryKey(),
            'docentry' => $this->bigInteger()->notNull()->comment('Documento reparacion (FK doc_tire_repair.docentry)'),
            'linenum' => $this->integer()->notNull()->comment('Número de línea'),
            'filename' => $this->string(255)->notNull()->comment('Nombre archivo'),
            'filepath' => $this->string(500)->notNull()->comment('Ruta archivo'),
            'notes' => $this->text()->null()->comment('Observaciones'),

            // Auditoría
            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario creador (FK users.id)'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario actualizador (FK users.id)'),

        ], pkColumns: ['id']);


        // ============================================================
        // INDEXES
        // ============================================================
        $this->createIndex('ux_doc_tire_repair_attach_docentry_linenum','{{%doc_tire_repair_attach}}',['docentry', 'linenum'],true);
        $this->createIndex('idx_doc_tire_repair_attach_docentry','{{%doc_tire_repair_attach}}','docentry');


        // ============================================================
        // FK
        // ============================================================
        $this->addForeignKey('fk_doc_tire_repair_attach_document','{{%doc_tire_repair_attach}}','docentry','{{%doc_tire_repair}}','docentry','CASCADE','CASCADE');
        $this->addForeignKey('fk_doc_tire_repair_attach_createuser','{{%doc_tire_repair_attach}}','createuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_repair_attach_updateuser','{{%doc_tire_repair_attach}}','updateuser','{{%users}}','id','RESTRICT','CASCADE');

        // ============================================================
        // doc_tire_disposal (HEADER)
        // ============================================================

        $this->createTableWithLog('{{%doc_tire_disposal}}', [
            'docentry' => $this->bigPrimaryKey(),
            'docnum' => $this->string(30)->notNull()->comment('Número visible del documento'),
            'doc_date' => $this->date()->notNull()->comment('Fecha del documento'),
            'doc_status' => "ENUM('O','C') NOT NULL DEFAULT 'O' COMMENT 'Estado del documento (O=Abierto, C=Cerrado)'",
            'status' => "ENUM('PLAN','EXEC','VAL','CLOSE') NOT NULL DEFAULT 'PLAN' COMMENT 'Estado operativo (PLAN=Planeado, EXEC=Ejecutado, VAL=Validado, CLOSE=Cerrado)'",
            'canceled' => "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Documento cancelado (Y=Si, N=No)'",
            'disposal_date' => $this->date()->notNull()->comment('Fecha de baja'),
            'comments' => $this->string(300)->null()->comment('Comentarios generales'),

            // Auditoría
            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario creador (FK users.id)'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario actualizador (FK users.id)'),

        ], pkColumns: ['docentry']);


        // ============================================================
        // INDEXES
        // ============================================================
        $this->createIndex('ux_doc_tire_disposal_docnum','{{%doc_tire_disposal}}','docnum',true);
        $this->createIndex('idx_doc_tire_disposal_status','{{%doc_tire_disposal}}','status');
        $this->createIndex('idx_doc_tire_disposal_doc_status','{{%doc_tire_disposal}}','doc_status');
        $this->createIndex('idx_doc_tire_disposal_disposal_date','{{%doc_tire_disposal}}','disposal_date');


        // ============================================================
        // FK
        // ============================================================
        $this->addForeignKey('fk_doc_tire_disposal_createuser','{{%doc_tire_disposal}}','createuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_disposal_updateuser','{{%doc_tire_disposal}}','updateuser','{{%users}}','id','RESTRICT','CASCADE');


        // ============================================================
        // doc_tire_disposal_detail (DETAIL)
        // ============================================================

        $this->createTableWithLog('{{%doc_tire_disposal_detail}}', [
            'id' => $this->bigPrimaryKey(),
            'docentry' => $this->bigInteger()->notNull()->comment('Documento baja (FK doc_tire_disposal.docentry)'),
            'linenum' => $this->integer()->notNull()->comment('Número de línea'),
            'tire_code' => $this->string(50)->notNull()->comment('Llanta a dar de baja (FK tire.tire_code)'),
            'disposal_reason' => "ENUM('WEAR','DAMAGE','ACCIDENT','THEFT','RETREAD_LIMIT','SIDEWALL_DAMAGE','OTHER') NOT NULL DEFAULT 'OTHER' COMMENT 'Motivo (WEAR=Desgaste, DAMAGE=Danio, ACCIDENT=Accidente, THEFT=Robo, RETREAD_LIMIT=Limite renovados, SIDEWALL_DAMAGE=Danio lateral, OTHER=Otro)'",
            'scrap_value' => $this->decimal(10,2)->null()->comment('Valor recuperación'),
            'comments' => $this->string(300)->null()->comment('Comentarios línea'),

            // Auditoría
            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario creador (FK users.id)'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario actualizador (FK users.id)'),

        ], pkColumns: ['id']);

        // ============================================================
        // INDEXES
        // ============================================================
        $this->createIndex('ux_doc_tire_disposal_detail_docentry_linenum','{{%doc_tire_disposal_detail}}',['docentry', 'linenum'],true);
        $this->createIndex('idx_doc_tire_disposal_detail_tire_code','{{%doc_tire_disposal_detail}}','tire_code');
        $this->createIndex('idx_doc_tire_disposal_detail_reason','{{%doc_tire_disposal_detail}}','disposal_reason');

        // ============================================================
        // FK
        // ============================================================
        $this->addForeignKey('fk_doc_tire_disposal_detail_document','{{%doc_tire_disposal_detail}}','docentry','{{%doc_tire_disposal}}','docentry','CASCADE','CASCADE');
        $this->addForeignKey('fk_doc_tire_disposal_detail_tire','{{%doc_tire_disposal_detail}}','tire_code','{{%tire}}','tire_code','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_disposal_detail_createuser','{{%doc_tire_disposal_detail}}','createuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_disposal_detail_updateuser','{{%doc_tire_disposal_detail}}','updateuser','{{%users}}','id','RESTRICT','CASCADE');


        // ============================================================
        // doc_tire_disposal_attach (ATTACHMENTS)
        // ============================================================

        $this->createTableWithLog('{{%doc_tire_disposal_attach}}', [
            'id' => $this->bigPrimaryKey(),
            'docentry' => $this->bigInteger()->notNull()->comment('Documento baja (FK doc_tire_disposal.docentry)'),
            'linenum' => $this->integer()->notNull()->comment('Número de línea'),
            'filename' => $this->string(255)->notNull()->comment('Nombre archivo'),
            'filepath' => $this->string(500)->notNull()->comment('Ruta archivo'),
            'notes' => $this->text()->null()->comment('Observaciones'),

            // Auditoría
            'createdate' => $this->date()->null()->comment('Fecha de creacion'),
            'createtime' => $this->time()->null()->comment('Hora de creacion'),
            'createuser' => $this->bigInteger()->null()->comment('Usuario creador (FK users.id)'),

            'updatedate' => $this->date()->null()->comment('Fecha de actualizacion'),
            'updatetime' => $this->time()->null()->comment('Hora de actualizacion'),
            'updateuser' => $this->bigInteger()->null()->comment('Usuario actualizador (FK users.id)'),

        ], pkColumns: ['id']);


        // ============================================================
        // INDEXES
        // ============================================================
        $this->createIndex('ux_doc_tire_disposal_attach_docentry_linenum','{{%doc_tire_disposal_attach}}',['docentry', 'linenum'],true);
        $this->createIndex('idx_doc_tire_disposal_attach_docentry','{{%doc_tire_disposal_attach}}','docentry');


        // ============================================================
        // FK
        // ============================================================
        $this->addForeignKey('fk_doc_tire_disposal_attach_document','{{%doc_tire_disposal_attach}}','docentry','{{%doc_tire_disposal}}','docentry','CASCADE','CASCADE');
        $this->addForeignKey('fk_doc_tire_disposal_attach_createuser','{{%doc_tire_disposal_attach}}','createuser','{{%users}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_doc_tire_disposal_attach_updateuser','{{%doc_tire_disposal_attach}}','updateuser','{{%users}}','id','RESTRICT','CASCADE');

       
    }

    public function safeDown(): void
    {
        // Disposal attach
        $this->dropForeignKey('fk_doc_tire_disposal_attach_updateuser', '{{%doc_tire_disposal_attach}}');
        $this->dropForeignKey('fk_doc_tire_disposal_attach_createuser', '{{%doc_tire_disposal_attach}}');
        $this->dropForeignKey('fk_doc_tire_disposal_attach_document', '{{%doc_tire_disposal_attach}}');
        $this->dropTableWithLog('{{%doc_tire_disposal_attach}}');

        // Disposal detail
        $this->dropForeignKey('fk_doc_tire_disposal_detail_updateuser', '{{%doc_tire_disposal_detail}}');
        $this->dropForeignKey('fk_doc_tire_disposal_detail_createuser', '{{%doc_tire_disposal_detail}}');
        $this->dropForeignKey('fk_doc_tire_disposal_detail_tire', '{{%doc_tire_disposal_detail}}');
        $this->dropForeignKey('fk_doc_tire_disposal_detail_document', '{{%doc_tire_disposal_detail}}');
        $this->dropTableWithLog('{{%doc_tire_disposal_detail}}');

        // Disposal header
        $this->dropForeignKey('fk_doc_tire_disposal_updateuser', '{{%doc_tire_disposal}}');
        $this->dropForeignKey('fk_doc_tire_disposal_createuser', '{{%doc_tire_disposal}}');
        $this->dropTableWithLog('{{%doc_tire_disposal}}');

        // Repair attach
        $this->dropForeignKey('fk_doc_tire_repair_attach_updateuser', '{{%doc_tire_repair_attach}}');
        $this->dropForeignKey('fk_doc_tire_repair_attach_createuser', '{{%doc_tire_repair_attach}}');
        $this->dropForeignKey('fk_doc_tire_repair_attach_document', '{{%doc_tire_repair_attach}}');
        $this->dropTableWithLog('{{%doc_tire_repair_attach}}');

        // Repair detail
        $this->dropForeignKey('fk_doc_tire_repair_detail_updateuser', '{{%doc_tire_repair_detail}}');
        $this->dropForeignKey('fk_doc_tire_repair_detail_createuser', '{{%doc_tire_repair_detail}}');
        $this->dropForeignKey('fk_doc_tire_repair_detail_tire', '{{%doc_tire_repair_detail}}');
        $this->dropForeignKey('fk_doc_tire_repair_detail_document', '{{%doc_tire_repair_detail}}');
        $this->dropTableWithLog('{{%doc_tire_repair_detail}}');

        // Repair header
        $this->dropForeignKey('fk_doc_tire_repair_updateuser', '{{%doc_tire_repair}}');
        $this->dropForeignKey('fk_doc_tire_repair_createuser', '{{%doc_tire_repair}}');
        $this->dropForeignKey('fk_doc_tire_repair_vendor', '{{%doc_tire_repair}}');
        $this->dropTableWithLog('{{%doc_tire_repair}}');

        // Movement attach
        $this->dropForeignKey('fk_doc_tire_movement_attach_updateuser', '{{%doc_tire_movement_attach}}');
        $this->dropForeignKey('fk_doc_tire_movement_attach_createuser', '{{%doc_tire_movement_attach}}');
        $this->dropForeignKey('fk_doc_tire_movement_attach_document', '{{%doc_tire_movement_attach}}');
        $this->dropTableWithLog('{{%doc_tire_movement_attach}}');

        // Movement detail
        $this->dropForeignKey('fk_doc_tire_movement_detail_updateuser', '{{%doc_tire_movement_detail}}');
        $this->dropForeignKey('fk_doc_tire_movement_detail_createuser', '{{%doc_tire_movement_detail}}');
        $this->dropForeignKey('fk_doc_tire_movement_detail_whs_to', '{{%doc_tire_movement_detail}}');
        $this->dropForeignKey('fk_doc_tire_movement_detail_whs_from', '{{%doc_tire_movement_detail}}');
        $this->dropForeignKey('fk_doc_tire_movement_detail_vehicle_to', '{{%doc_tire_movement_detail}}');
        $this->dropForeignKey('fk_doc_tire_movement_detail_vehicle_from', '{{%doc_tire_movement_detail}}');
        $this->dropForeignKey('fk_doc_tire_movement_detail_related_tire', '{{%doc_tire_movement_detail}}');
        $this->dropForeignKey('fk_doc_tire_movement_detail_tire', '{{%doc_tire_movement_detail}}');
        $this->dropForeignKey('fk_doc_tire_movement_detail_docentry', '{{%doc_tire_movement_detail}}');
        $this->dropTableWithLog('{{%doc_tire_movement_detail}}');

        // Movement vehicles
        $this->dropForeignKey('fk_doc_tire_movement_vehicle_updateuser', '{{%doc_tire_movement_vehicle}}');
        $this->dropForeignKey('fk_doc_tire_movement_vehicle_createuser', '{{%doc_tire_movement_vehicle}}');
        $this->dropForeignKey('fk_doc_tire_movement_vehicle_vehicle', '{{%doc_tire_movement_vehicle}}');
        $this->dropForeignKey('fk_doc_tire_movement_vehicle_docentry', '{{%doc_tire_movement_vehicle}}');
        $this->dropTableWithLog('{{%doc_tire_movement_vehicle}}');

        // Movement header
        $this->dropForeignKey('fk_doc_tire_movement_technician_user_id', '{{%doc_tire_movement}}');
        $this->dropForeignKey('fk_doc_tire_movement_updateuser', '{{%doc_tire_movement}}');
        $this->dropForeignKey('fk_doc_tire_movement_createuser', '{{%doc_tire_movement}}');
        $this->dropTableWithLog('{{%doc_tire_movement}}');
    }
}
