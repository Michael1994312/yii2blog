<?php

namespace app\controllers;

use app\models\UserModel;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    public static $request;

    public function init()
    {
        parent::init();
        static::$request = Yii::$app->request;
    }
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $model = new LoginForm();

        if (!UserModel::checkLogin()) {
            $loginPost = static::$request->post();
            if (!empty($loginPost)) {
                $loginReturn = UserModel::login($loginPost['LoginForm']);
                if (is_bool($loginReturn) && $loginReturn === true) {
                    $this->redirect(['index']);
                } else {
                    Yii::$app->getSession()->setFlash('error', $loginReturn);
                }
            }

            return $this->render('login', [
                'model' => $model,
            ]);
        } else {
            $this->redirect(['index']);
        }

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionExit()
    {
        UserModel::logout();
        Yii::$app->user->logout();

        return $this->goHome();
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

    public function actionRegister()
    {
        $model = new UserModel();
        if (!$model::checkLogin()) {
            $regPost = static::$request->post();
            if (!empty($regPost)) {
                $regReturn = UserModel::register($regPost['UserModel']);
                if (is_bool($regReturn) && $regReturn === true) {
                    $this->redirect(['index']);
                } else {
                    Yii::$app->getSession()->setFlash('error', $regReturn);
                }
            }
            return $this->render('register', [
                'model' => $model,
            ]);
        } else {
            $this->redirect(['index']);
        }
    }

    public function actionPublish()
    {
        $cookie = \Yii::$app->request->cookies;
        $cookie->getValue('usernameCookie');
        $cookie->getValue('passwordCookie');
        echo '<pre>';var_dump($cookie->getValue('usernameCookie'));var_dump($cookie->getValue('passwordCookie'));exit;
    }
}
