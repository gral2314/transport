<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

use app\models\system\User;
use app\models\system\Users;

use app\models\Modulos;
use app\models\ContactForm;


class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [				
                    [				
                        'actions' => ['logout','login','index','dashboard','error'],				
                        'allow' => true,
                        'roles' => ['?'],
                    ],				
                    [				
                        'actions' => ['logout','login','index','dashboard','error'], // add all actions to take guest to login page				
                        'allow' => true,				
                        'roles' => ['@'],				
                    ],				
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post','get'],
                    'login' => ['post','get'],
                ],
            ],
        ];
    }
   
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (isset(Yii::$app->user->identity->username)){  
            return $this->render('dashboard');
        } else{ 
            return $this->redirect('login');
            return $this->render('login');
            //header('Location: /web/site/login');
        }; 
        
    }
    public function actionDashboard()
    {
        if (isset(Yii::$app->user->identity->username)){  
            return $this->render('dashboard');
        } else{ 
            return $this->goHome(); 
                //header('Location: /Intranet/web/site/login');
        }; 
        
    }

    public function actionMenu()
		{
            $modelModulos = new Modulos();
			$model = $modelModulos->menus();
			return json_encode($model);
		}

    /**
     * Login action.
     *
     * @return Response|string
     */
    public $enableCsrfValidation = false;
    public function actionLogin()
    {
        if(Yii::$app->request->post()){
            $usercode = Yii::$app->request->post('usercode');
            $password = Yii::$app->request->post('password');
            $rememberMe = Yii::$app->request->post('remember');
            $msgError = '';
            //$users = Users::find(['usercode', $usercode])->one();
            //$users = Users::findOne(['usercode' => $usercode]);
            $modelUser = new User();
            $user = $modelUser->findByUserCode($usercode);


            /*echo var_dump($users);
            echo '<br>' . md5($password); die;*/
            if ($user !== null && $user->validatePassword($password)) {
                //actualiza el estado del usuario
                $modelUser->UpdateAfterLogin($usercode);
                Yii::$app->user->login($user);
                return $this->redirect(['site/index']);
            } else {
                $msgError = User::getLastAuthError();
                if ($msgError === '') {
                    $msgError = 'Credenciales incorrectas.';
                }
                return $this->render('login', ['msgError' => $msgError]);
            }
        }
        if (!Yii::$app->user->isGuest) {return $this->goHome();}
        return $this->render('login', ['msgError' => '']);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        $modelUser = new User();
        $modelUser->UpdateBeforeLogout(Yii::$app->user->identity->usercode);
        Yii::$app->user->logout();
        if (!Yii::$app->user->isGuest) {return $this->goHome();}
        return $this->redirect('login');
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    public function actionError()
    {
        return $this->render('error');
    }
}
