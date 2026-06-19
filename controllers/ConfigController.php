<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\objects\Items\ItemsServices;


class ConfigController extends Controller{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    //catalogos    
                    'sales','purchase','inventory','vehicule','tire','mantto','finance','system','rrhh',
                    'users','system' => ['GET'],
                    /*
                    'list'             => ['GET', 'POST'],
                    'get'              => ['GET', 'POST'],
                    'save'             => ['POST'],
                    'delete'           => ['POST'],
                    'get-form-options' => ['GET', 'POST'],*/
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    // ── Views ──────────────────────────────────────────────────────────

    /**
     * Finanzas — Finanzas view
     */
    public function actionFinance(): string
    {
        $this->layout = 'main';

        return $this->render('catalog/finance/index', 
        /*['data' => $data['Data'],]*/
        );
    }
    
    /**
     * sales — sales view
     */
    public function actionSales(): string
    {
        $this->layout = 'main';

        return $this->render('catalog/sales/index', 
        /*['data' => $data['Data'],]*/
        );
    }
    /**
     * purchase — purchase view
     */
    public function actionPurchase(): string
    {
        $this->layout = 'main';

        return $this->render('catalog/purchase/index', 
        /*['data' => $data['Data'],]*/
        );
    }
    /**
     * inventory — inventory view
     */
    public function actionInventory(): string
    {
        $this->layout = 'main';

        return $this->render('catalog/inventory/index', 
        /*['data' => $data['Data'],]*/
        );
    }
    /**
     * vehicule — vehicule view
     */
    public function actionVehicule(): string
    {
        $this->layout = 'main';

        return $this->render('catalog/vehicule/index', 
        /*['data' => $data['Data'],]*/
        );
    }

    /**
     * rrhh — rrhh view
     */
    public function actionRrhh(): string
    {
        $this->layout = 'main';

        return $this->render('catalog/rrhh/index');
    }
    /**
     * tire — tire view
     */
    public function actionTire(): string
    {
        $this->layout = 'main';

        return $this->render('catalog/tire/index', 
        /*['data' => $data['Data'],]*/
        );
    }
    /**
     * mantto — mantto view
     */
    public function actionMantto(): string
    {
        $this->layout = 'main';

        return $this->render('catalog/mantto/index', 
        /*['data' => $data['Data'],]*/
        );
    }

    /**
     * system — catalogos generales view
     */
    public function actionGral(): string
    {
        $this->layout = 'main';

        return $this->render('catalog/system/index');
    }


    
    /**
     * Users — users view
     */
    public function actionUsers(): string
    {
        $this->layout = 'main';

        return $this->render('users/index', 
        /*['data' => $data['Data'],]*/
        );
    }
    /**
     * System — system view
     */
    public function actionSystem(): string
    {
        $this->layout = 'main';

        return $this->render('system/index', 
        /*['data' => $data['Data'],]*/
        );
    }
}