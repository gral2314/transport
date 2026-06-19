<?php

use yii\db\Migration;
use app\components\BaseMigration;

class m260516_000002_data_default extends BaseMigration
{
    public function safeUp(): void
    {
       
        
        //-----------------------------
        // CARGA DE CATALOGOS INICIALES
        //-----------------------------
        $date = date('Y-m-d');
        $time = date('H:i:s');
        //----- Configruacion vehicular del SAT
        $this->batchInsert('{{%sat_vehicle_config}}', ['code','name', 'max_ejes', 'max_tires', 'max_remolque', 'createdate', 'createtime', 'createuser','active'], 
            [
                ['VL','Vehículo ligero de carga (2 llantas en el eje delantero y 2 llantas en el eje trasero)',	02, 04	,1,$date,$time,null,'Y'],
                ['C2','Camión Unitario (2 llantas en el eje delantero y 4 llantas en el eje trasero)', 02, 06,0,$date,$time,null,'Y'],
                ['C3','Camión Unitario (2 llantas en el eje delantero y 6 o 8 llantas en los dos ejes traseros)', 03, 10, 0,$date,$time,null,'Y'],
                ['C2R2','Camión-Remolque (6 llantas en el camión y 8 llantas en remolque)', 4, 14,1,$date,$time,null,'Y'],
                ['C3R2','Camión-Remolque (10 llantas en el camión y 8 llantas en remolque)', 5, 18,1,$date,$time,null,'Y'],
                ['C2R3','Camión-Remolque (6 llantas en el camión y 12 llantas en remolque)', 5, 18,1,$date,$time,null,'Y'],
                ['C3R3','Camión-Remolque (10 llantas en el camión y 12 llantas en remolque)', 6, 22,1,$date,$time,null,'Y'],
                ['T2S1','Tractocamión Articulado (6 llantas en el tractocamión, 4 llantas en el semirremolque)', 3, 10,1,$date,$time,null,'Y'],
                ['T2S2','Tractocamión Articulado (6 llantas en el tractocamión, 8 llantas en el semirremolque)', 4, 14,1,$date,$time,null,'Y'],
                ['T2S3','Tractocamión Articulado (6 llantas en el tractocamión, 12 llantas en el semirremolque)', 5, 18,1,$date,$time,null,'Y'],
                ['T3S1','Tractocamión Articulado (10 llantas en el tractocamión, 4 llantas en el semirremolque)', 4, 14,1,$date,$time,null,'Y'],
                ['T3S2','Tractocamión Articulado (10 llantas en el tractocamión, 8 llantas en el semirremolque)', 5, 18,1,$date,$time,null,'Y'],
                ['T3S3','Tractocamión Articulado (10 llantas en el tractocamión, 12 llantas en el semirremolque)', 6, 22,1,$date,$time,null,'Y'],
                ['T2S1R2','Tractocamión Semirremolque-Remolque (6 llantas en el tractocamión, 4 llantas en el semirremolque y 8 llantas en el remolque)', 5, 18, 1, $date, $time, null, 'Y'],
                ['T2S2R2','Tractocamión Semirremolque-Remolque (6 llantas en el tractocamión, 8 llantas en el semirremolque y 8 llantas en el remolque)', 6, 22, 1, $date, $time, null, 'Y'],
                ['T2S1R3','Tractocamión Semirremolque-Remolque (6 llantas en el tractocamión, 4 llantas en el semirremolque y 12 llantas en el remolque)', 6, 22, 1, $date, $time, null, 'Y'],
                ['T3S1R2','Tractocamión Semirremolque-Remolque (10 llantas en el tractocamión, 4 llantas en el semirremolque y 8 llantas en el remolque)', 6, 22, 1, $date, $time, null, 'Y'],
                ['T3S1R3', 'Tractocamión Semirremolque-Remolque (10 llantas en el tractocamión, 4 llantas en el semirremolque y 12 llantas en el remolque)', 7, 26, 1, $date, $time, null, 'Y'],
                ['T3S2R2', 'Tractocamión Semirremolque-Remolque (10 llantas en el tractocamión, 8 llantas en el semirremolque y 8 llantas en el remolque)', 7, 26, 1, $date, $time, null, 'Y'],
                ['T3S2R3', 'Tractocamión Semirremolque-Remolque (10 llantas en el tractocamión, 8 llantas en el semirremolque y 12 llantas en el remolque)', 8, 30, 1, $date, $time, null, 'Y'],
                ['T3S2R4', 'Tractocamión Semirremolque-Remolque (10 llantas en el tractocamión, 8 llantas en el semirremolque y 16 llantas en el remolque)', 9, 34, 1, $date, $time, null, 'Y'],
                ['T2S2S2', 'Tractocamión Semirremolque-Semirremolque (6 llantas en el tractocamión, 8 llantas en el semirremolque delantero y 8 llantas en el semirremolque trasero)', 6, 22,1, $date, $time, null,'Y'],
                ['T3S2S2', 'Tractocamión Semirremolque-Semirremolque (10 llantas en el tractocamión, 8 llantas en el semirremolque delantero y 8 llantas en el semirremolque trasero)', 7, 26,1, $date, $time, null,'Y'],
                ['T3S3S2', 'Tractocamión Semirremolque-Semirremolque (10 llantas en el tractocamión, 12 llantas en el semirremolque delantero y 8 llantas en el semirremolque trasero)', 8, 30,1, $date, $time, null,'Y'],
            ]
        );
        //----- Marcas de Vehiculos
        $this->batchInsert('{{%vehicle_brand}}', ['code','name', 'createdate', 'createtime','active'], 
        [
             ['KW', "Kenworth" ,$date,$time,'Y'],
             ['FRHT', "Freightliner" ,$date,$time,'Y'],
             ['INTL', "International" ,$date,$time,'Y'],
             ['VOLV', "Volvo Trucks" ,$date,$time,'Y'],
             ['MACK', "Mack Trucks" ,$date,$time,'Y'],
             ['PET', "Peterbilt" ,$date,$time,'Y'],
             ['SCAN', "Scania" ,$date,$time,'Y'],
             ['MAN', "MAN Trucks" ,$date,$time,'Y'],
             ['DAF', "DAF Trucks" ,$date,$time,'Y'],
             ['HINO', "Hino Motors" ,$date,$time,'Y'],
             ['ISZ', "Isuzu" ,$date,$time,'Y'],
             ['FUSO', "Mitsubishi Fuso" ,$date,$time,'Y'],
             ['UD', "UD Trucks" ,$date,$time,'Y'],
             ['RAM', "RAM" ,$date,$time,'Y'],
             ['FORD', "Ford Trucks" ,$date,$time,'Y'],
             ['CHEV', "Chevrolet" ,$date,$time,'Y'],
             ['DINA', "DINA" ,$date,$time,'Y'],
             ['MERC', "Mercedes-Benz Trucks" ,$date,$time,'Y'],
             ['IVEC', "IVECO" ,$date,$time,'Y'],
             ['TREM', "Tremcar" ,$date,$time,'Y'],
             ['UTIL', "Utility Trailer" ,$date,$time,'Y'],
             ['WABA', "Wabash National" ,$date,$time,'Y'],
             ['GRTD', "Great Dane" ,$date,$time,'Y'],
             ['HYND', "Hyundai Translead" ,$date,$time,'Y'],
             ['FONT', "Fontaine Trailer" ,$date,$time,'Y'],
             ['TRAV', "Trail King" ,$date,$time,'Y'],
             ['COTT', "Cottingham" ,$date,$time,'Y'],
             ['HEIL', "Heil Trailer" ,$date,$time,'Y'],
             ['LBTY', "Liberty Trailers" ,$date,$time,'Y'],
             ['MAC', "MAC Trailer" ,$date,$time,'Y'],
             ['DOLY', "Dolly Generic" ,$date,$time,'Y'],
             ['JOST', "JOST" ,$date,$time,'Y'],
             ['HOLL', "Holland" ,$date,$time,'Y'],
             ['SAF', "SAF-Holland" ,$date,$time,'Y'],
             ['BPW', "BPW" ,$date,$time,'Y'],
             ['CSEC', "Caja Seca Genérica" ,$date,$time,'Y'],
             ['REFR', "Refrigerada Genérica" ,$date,$time,'Y'],
             ['PLAT', "Plataforma Genérica" ,$date,$time,'Y'],
             ['TANK', "Tanque Genérico" ,$date,$time,'Y'],
             ['TOLV', "Tolva Genérica",$date,$time,'Y']
        ]);
        //----- COMBUSTIBLES
        $this->batchInsert('{{%fuel_type}}', ['code','name', 'createdate', 'createtime','active'], 
        [
             ['DISEL', "DIESEL" ,$date,$time,'Y'],
             ['GASLP', "GAS LP" ,$date,$time,'Y'],
             ['GASVER', "GASOLINA NORMAL" ,$date,$time,'Y'],
             ['GASPRE', "GASOLINA PREMIUM" ,$date,$time,'Y'],
        ]);

        //----- TIPOS DE DOCUMENTO UNIDAD
        $this->batchInsert('{{%doc_type_vehicule}}', ['code','name', 'createdate', 'createtime','active'], 
        [
            ['TCIRC', "TARJETA DE CIRCULACION" ,$date,$time,'Y'],
            ['TENEN', "TENENCIA" ,$date,$time,'Y'],
            ['VERIF', "VERIFICACION" ,$date,$time,'Y'],
            ['POLIZ', "POLIZA DE SEGURO" ,$date,$time,'Y'],
            ['PERM', "PERMISO SCT" ,$date,$time,'Y'],
            ['AMBI', "PERMISO AMBIENTAL" ,$date,$time,'Y']
        ]);

        //----- MARCAS DE LLANTA
        $this->batchInsert('{{%tire_brand}}', ['code','name', 'createdate', 'createtime','active'], 
        [
            ['MICHE', 'MICHELIN', $date, $time, 'Y'],
            ['GOODY', 'GOODYEAR', $date, $time, 'Y'],
            ['BRIDG', 'BRIDGESTONE', $date, $time, 'Y'],
            ['CONTI', 'CONTINENTAL', $date, $time, 'Y'],
            ['PIREL', 'PIRELLI', $date, $time, 'Y'],
            ['YOKOH', 'YOKOHAMA', $date, $time, 'Y'],
            ['HANKO', 'HANKOOK', $date, $time, 'Y'],
            ['TOYO', 'TOYO', $date, $time, 'Y'],
            ['FIRE', 'FIRESTONE', $date, $time, 'Y'],
            ['DOUBLE', 'DOUBLE COIN', $date, $time, 'Y']
        ]);

        //----- TAMAÑOS DE LLANTAS
        $this->batchInsert('{{%tire_size}}', ['code','name', 'createdate', 'createtime','active'], 
        [
            ['11R225', '11R22.5', $date, $time, 'Y'],
            ['295752', '295/75R22.5', $date, $time, 'Y'],
            ['285752', '285/75R24.5', $date, $time, 'Y'],
            ['LP2225', 'LP22.5', $date, $time, 'Y'],
            ['255702', '255/70R22.5', $date, $time, 'Y'],
            ['275802', '275/80R22.5', $date, $time, 'Y'],
            ['315802', '315/80R22.5', $date, $time, 'Y'],
            ['385652', '385/65R22.5', $date, $time, 'Y'],
            ['425652', '425/65R22.5', $date, $time, 'Y'],
            ['445652', '445/65R22.5', $date, $time, 'Y'],
            ['1200R2', '12.00R24', $date, $time, 'Y'],
            ['1100R2', '11.00R20', $date, $time, 'Y']
        ]);

        //----- MODELOS DE LLANTA
        $this->batchInsert('{{%tire_model}}',['code', 'name', 'brand_code', 'createdate', 'createtime', 'active'],
            [
                // MICHELIN
                ['XMULT', 'X MULTI', 'MICHE', $date, $time, 'Y'],
                ['XLINE', 'X LINE ENERGY', 'MICHE', $date, $time, 'Y'],
                ['XDY3', 'XDY 3', 'MICHE', $date, $time, 'Y'],

                // GOODYEAR
                ['FUELM', 'FUEL MAX', 'GOODY', $date, $time, 'Y'],
                ['ENDUR', 'ENDURANCE', 'GOODY', $date, $time, 'Y'],
                ['KMAX', 'KMAX', 'GOODY', $date, $time, 'Y'],

                // BRIDGESTONE
                ['M760', 'M760', 'BRIDG', $date, $time, 'Y'],
                ['R283A', 'R283A ECOPIA', 'BRIDG', $date, $time, 'Y'],
                ['M870', 'M870', 'BRIDG', $date, $time, 'Y'],

                // CONTINENTAL
                ['HDR2', 'HDR2', 'CONTI', $date, $time, 'Y'],
                ['HSR2', 'HSR2', 'CONTI', $date, $time, 'Y'],
                ['HTR2', 'HTR2', 'CONTI', $date, $time, 'Y'],

                // PIRELLI
                ['FG01', 'FG01', 'PIREL', $date, $time, 'Y'],
                ['TR01', 'TR01', 'PIREL', $date, $time, 'Y'],
                ['FR85', 'FR85', 'PIREL', $date, $time, 'Y'],

                // YOKOHAMA
                ['104ZR', '104ZR', 'YOKOH', $date, $time, 'Y'],
                ['RY617', 'RY617', 'YOKOH', $date, $time, 'Y'],

                // HANKOOK
                ['SMART', 'SMART FLEX', 'HANKO', $date, $time, 'Y'],
                ['AH37', 'AH37', 'HANKO', $date, $time, 'Y'],

                // TOYO
                ['M144', 'M144', 'TOYO', $date, $time, 'Y'],
                ['M657', 'M657', 'TOYO', $date, $time, 'Y'],

                // FIRESTONE
                ['FS591', 'FS591', 'FIRE', $date, $time, 'Y'],
                ['FD663', 'FD663', 'FIRE', $date, $time, 'Y'],

                // DOUBLE COIN
                ['RLB1', 'RLB1', 'DOUBLE', $date, $time, 'Y'],
                ['RR202', 'RR202', 'DOUBLE', $date, $time, 'Y']
            ]
        );

        //----- TIPOS DE LLANTAS
        $this->batchInsert('{{%tire_type}}', ['code','name', 'createdate', 'createtime','active'], 
        [
            ['DIR', 'DIRECCIONAL', $date, $time, 'Y'],
            ['TRACC', 'TRACCION', $date, $time, 'Y'],
            ['REMOL', 'REMOLQUE', $date, $time, 'Y'],
            ['ALLPOS', 'ALL POSITION', $date, $time, 'Y'],
            ['RADIAL', 'RADIAL', $date, $time, 'Y'],
            ['TUBEL', 'TUBELESS', $date, $time, 'Y'],
            ['MIXSER', 'MIXTO SERVICIO', $date, $time, 'Y'],
            ['LARGDI', 'LARGA DISTANCIA', $date, $time, 'Y'],
            ['REGION', 'REGIONAL', $date, $time, 'Y'],
            ['URBANO', 'URBANO', $date, $time, 'Y'],
            ['ONOFF', 'ON/OFF ROAD', $date, $time, 'Y'],
            ['ECONOM', 'AHORRO COMBUSTIBLE', $date, $time, 'Y']
        ]);

        //----- NOM -012
        $this->batchInsert('{{%nom012}}',['code', 'name', 'createdate', 'createtime', 'active'],
            [
                ['C2', 'CAMION UNITARIO 2 EJES', $date, $time, 'Y'],
                ['C3', 'CAMION UNITARIO 3 EJES', $date, $time, 'Y'],

                ['T2S1', 'TRACTOCAMION 2 EJES + SEMIRREMOLQUE 1 EJE', $date, $time, 'Y'],
                ['T2S2', 'TRACTOCAMION 2 EJES + SEMIRREMOLQUE 2 EJES', $date, $time, 'Y'],
                ['T2S3', 'TRACTOCAMION 2 EJES + SEMIRREMOLQUE 3 EJES', $date, $time, 'Y'],

                ['T3S1', 'TRACTOCAMION 3 EJES + SEMIRREMOLQUE 1 EJE', $date, $time, 'Y'],
                ['T3S2', 'TRACTOCAMION 3 EJES + SEMIRREMOLQUE 2 EJES', $date, $time, 'Y'],
                ['T3S3', 'TRACTOCAMION 3 EJES + SEMIRREMOLQUE 3 EJES', $date, $time, 'Y'],

                ['T3S2R2', 'TRACTOCAMION + SEMIRREMOLQUE + REMOLQUE', $date, $time, 'Y'],
                ['T3S2R3', 'TRACTOCAMION DOBLEMENTE ARTICULADO', $date, $time, 'Y'],

                ['C2R2', 'CAMION UNITARIO + REMOLQUE', $date, $time, 'Y'],
                ['C3R2', 'CAMION 3 EJES + REMOLQUE', $date, $time, 'Y'],

                ['FULL', 'FULL DOBLE SEMIRREMOLQUE', $date, $time, 'Y'],
                ['GRUA', 'GRUA INDUSTRIAL', $date, $time, 'Y'],
                ['EXCES', 'CARGA EXCESO DIMENSIONES', $date, $time, 'Y']
            ]
        );


        //---- TIPO DE EJE
        $this->batchInsert('{{%axle_type}}',['code','name','tire_qty','createdate','createtime','active'],
            [
                // =========================
                // EJE SENCILLO
                // =========================
                ['S1', 'EJE SENCILLO DIRECCIONAL', 2, $date, $time, 'Y'],
                ['S2', 'EJE SENCILLO TRACCION', 2, $date, $time, 'Y'],
                ['S3', 'EJE SENCILLO REMOLQUE', 2, $date, $time, 'Y'],

                // =========================
                // DOBLE LLANTA POR EJE (MUY COMÚN EN TRACTO)
                // =========================
                ['S2D', 'EJE SENCILLO DOBLE LLANTA (DIRECCIONAL)', 4, $date, $time, 'Y'],
                ['S2T', 'EJE SENCILLO DOBLE LLANTA (TRACCION)', 4, $date, $time, 'Y'],

                // =========================
                // TANDEM
                // =========================
                ['T2', 'TANDEM 2 EJES (TRACCION)', 8, $date, $time, 'Y'],
                ['T3', 'TANDEM 3 EJES (REMOLQUE PESADO)', 12, $date, $time, 'Y'],

                // =========================
                // LIFT / AUXILIAR
                // =========================
                ['LFT', 'EJE LIFTABLE (AUXILIAR)', 2, $date, $time, 'Y'],

                // =========================
                // DIRECCIONAL DOBLE
                // =========================
                ['D2', 'DOBLE DIRECCIONAL', 4, $date, $time, 'Y']
            ]
        );

        //---- CONFIGURACION DE EJES
        $this->batchInsert('{{%axle_type_config}}',['code','line_num','name','pos_code','createdate','createtime'],
            [
                // =========================
                // EJE SIMPLE (2 llantas)
                // =========================
                ['S1', 1, 'LLANTA IZQUIERDA', 'LS', $date, $time],
                ['S1', 2, 'LLANTA DERECHA', 'RS', $date, $time],
                ['S2', 1, 'LLANTA IZQUIERDA', 'LS', $date, $time],
                ['S2', 2, 'LLANTA DERECHA', 'RS', $date, $time],
                ['S3', 1, 'LLANTA IZQUIERDA', 'LS', $date, $time],
                ['S3', 2, 'LLANTA DERECHA', 'RS', $date, $time],

                // =========================
                // DOBLE LLANTA POR LADO (4 llantas)
                // =========================
                ['S2D', 1, 'IZQUIERDA INTERIOR', 'LI', $date, $time],
                ['S2D', 2, 'IZQUIERDA EXTERIOR', 'LO', $date, $time],
                ['S2D', 3, 'DERECHA INTERIOR', 'RI', $date, $time],
                ['S2D', 4, 'DERECHA EXTERIOR', 'RO', $date, $time],
                ['S2T', 1, 'IZQUIERDA INTERIOR', 'LI', $date, $time],
                ['S2T', 2, 'IZQUIERDA EXTERIOR', 'LO', $date, $time],
                ['S2T', 3, 'DERECHA INTERIOR', 'RI', $date, $time],
                ['S2T', 4, 'DERECHA EXTERIOR', 'RO', $date, $time],

                // =========================
                // TANDEM (se repite por eje lógico)
                // =========================
                ['T2', 1, 'EJE 1 IZQ INTERIOR', 'LI', $date, $time],
                ['T2', 2, 'EJE 1 IZQ EXTERIOR', 'LO', $date, $time],
                ['T2', 3, 'EJE 1 DER INTERIOR', 'RI', $date, $time],
                ['T2', 4, 'EJE 1 DER EXTERIOR', 'RO', $date, $time],
                ['T3', 1, 'EJE 1 IZQ INTERIOR', 'LI', $date, $time],
                ['T3', 2, 'EJE 1 IZQ EXTERIOR', 'LO', $date, $time],
                ['T3', 3, 'EJE 1 DER INTERIOR', 'RI', $date, $time],
                ['T3', 4, 'EJE 1 DER EXTERIOR', 'RO', $date, $time],
                // =========================
                // LIFT / AUXILIAR
                // =========================
                ['LFT', 1, 'LLANTA IZQUIERDA', 'LS', $date, $time],
                ['LFT', 2, 'LLANTA DERECHA', 'RS', $date, $time],
                // =========================
                // DIRECCIONAL DOBLE
                // =========================
                ['D2', 1, 'EJE 2 IZQ INTERIOR', 'LI', $date, $time],
                ['D2', 2, 'EJE 2 IZQ EXTERIOR', 'LO', $date, $time],
                ['D2', 3, 'EJE 2 DER INTERIOR', 'RI', $date, $time],
                ['D2', 4, 'EJE 2 DER EXTERIOR', 'RO', $date, $time]
            ]
        );

        //------ TIPO DE VEHICULOS
        $this->batchInsert('{{%vehicle_type}}',['code','name','createdate','createtime','active'],
            [
                ['C2', 'CAMION 2 EJES', $date, $time, 'Y'],
                ['C3', 'CAMION 3 EJES', $date, $time, 'Y'],
                ['T2S1', 'TRACTOCAMION 2 EJES + SEMIRREMOLQUE 1 EJE', $date, $time, 'Y'],
                ['T2S2', 'TRACTOCAMION 2 EJES + SEMIRREMOLQUE 2 EJES', $date, $time, 'Y'],
                ['T3S2', 'TRACTOCAMION 3 EJES + SEMIRREMOLQUE 2 EJES', $date, $time, 'Y'],
                ['FULL', 'DOBLE REMOLQUE (FULL)', $date, $time, 'Y']
            ]
        );

        //----- CONFIGURACION DE EJES POR VEHICULO
        $this->batchInsert('{{%vehicle_type_axle}}',['code','line_num','axle_type_code','createdate','createtime'],
            [

                // =========================
                // C2 (camión 2 ejes)
                // =========================
                ['C2', 1, 'S1', $date, $time],
                ['C2', 2, 'T2', $date, $time],

                // =========================
                // C3
                // =========================
                ['C3', 1, 'S1', $date, $time],
                ['C3', 2, 'T2', $date, $time],
                ['C3', 3, 'S3', $date, $time],

                // =========================
                // TRACTO 2 EJES + SEMI 2 EJES
                // =========================
                ['T2S2', 1, 'S2D', $date, $time],
                ['T2S2', 2, 'T2', $date, $time],

                // =========================
                // FULL
                // =========================
                ['FULL', 1, 'S2D', $date, $time],
                ['FULL', 2, 'T2', $date, $time],
                ['FULL', 3, 'T2', $date, $time]
            ]
        );
        
        // country - Países principales
        $this->batchInsert('{{%country}}', ['code', 'name', 'active'], [
            ['MX', 'México', 'Y'],
            ['US', 'Estados Unidos', 'Y'],
            ['CN', 'China', 'Y'],
            ['JP', 'Japón', 'Y'],
            ['KR', 'Corea del Sur', 'Y'],
            ['DE', 'Alemania', 'Y'],
            ['FR', 'Francia', 'Y'],
            ['IT', 'Italia', 'Y'],
            ['ES', 'España', 'Y'],
            ['BR', 'Brasil', 'Y'],
            ['CA', 'Canadá', 'Y'],
        ]);
        
        // tire_usage_type
        $this->batchInsert('{{%tire_usage_type}}', ['code', 'name', 'active'], [
            ['STR', 'Dirección', 'Y'],
            ['DRV', 'Tracción', 'Y'],
            ['TRL', 'Remolque', 'Y'],
            ['SPR', 'Refacción', 'Y'],
            ['MIX', 'Mixto', 'Y'],
        ]);
    }

    public function safeDown(): void
    {
         // Hijas antes que padres para respetar FKs.
         $this->delete('{{%vehicle_type_axle}}');
         $this->delete('{{%axle_type_config}}');

         $this->delete('{{%tire_model}}');

         $this->delete('{{%sat_vehicle_config}}');
         $this->delete('{{%vehicle_brand}}');
         $this->delete('{{%fuel_type}}');
         $this->delete('{{%doc_type_vehicule}}');
         $this->delete('{{%tire_brand}}');
         $this->delete('{{%tire_size}}');
         $this->delete('{{%tire_type}}');
         $this->delete('{{%nom012}}');
         $this->delete('{{%axle_type}}');
         $this->delete('{{%vehicle_type}}');

         // Seeds adicionales cargados en safeUp.
         $this->delete('{{%tire_usage_type}}', ['code' => ['STR', 'DRV', 'TRL', 'SPR', 'MIX']]);
         $this->delete('{{%country}}', ['code' => ['MX', 'US', 'CN', 'JP', 'KR', 'DE', 'FR', 'IT', 'ES', 'BR', 'CA']]);


    }
}