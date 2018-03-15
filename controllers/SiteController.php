<?php

namespace app\controllers;

use app\models\ArticleModel;
use app\models\CommentModel;
use app\models\PublishForm;
use app\models\UserModel;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;

class SiteController extends Controller
{
    public static $request;

    public static $cookie;

    public $pageSize;

    public function init()
    {
        parent::init();
        $this->pageSize = 5;
        static::$request = Yii::$app->request;
        static::$cookie  = Yii::$app->request->cookies;
        UserModel::checkRememberMe();
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

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($this->enableCsrfValidation) {
                Yii::$app->getRequest()->getCsrfToken(true);
            }
            return true;
        }

        return false;
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
                    $this->goBack();
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
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionExit()
    {
        UserModel::logout();
        $this->redirect(['index']);
    }

    /**
     * @info 注册入口
     * @return string
     */
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

    /**
     * @info 发布文章入口
     * @return string|Response
     */
    public function actionPublish()
    {
        if (!UserModel::checkLogin()) {
            Yii::$app->getSession()->setFlash('error', '您还未登录，请先登陆');
            return $this->redirect(['login']);
        }

        $articlePost = static::$request->post();

        if (!empty($articlePost['PublishForm'])) {
            $artReturn = ArticleModel::publish($articlePost['PublishForm']);
            if (is_bool($artReturn) && $artReturn === true) {
                $this->redirect(['articlelist']);
            } else {
                Yii::$app->getSession()->setFlash('error', $artReturn);
            }
        }

        $model = new PublishForm();
        return $this->render('publish', [
            'model' => $model,
        ]);
    }

    /**
     * @info 文章列表入口
     * @return string
     */
    public function actionArticlelist()
    {
        $model = new ArticleModel();
        $query = $model::find();
        $count = $query->count();
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $this->pageSize]);
        $articles = $query->offset($pager->offset)
            ->limit($pager->pageSize)
            ->asArray()
            ->all();
        $articles = ArticleModel::acticleListFormat($articles);

        return $this->render('articlelist', [
            'articles' => $articles,
            'pager' => $pager,
        ]);
    }

    /**
     * @info 文章详情入口
     * @return string
     */
    public function actionArticledetail()
    {
        $params = static::$request->get();
        $articleId = intval($params['article_id']);
        if (!empty($articleId)) {
            $model = new ArticleModel();
            $commentModel = new CommentModel();
            $artDetail = $model::find()->where(['id' => $articleId])->asArray()->one();
            $artDetail = $model::articleFormat($artDetail);

            $query     = $commentModel::find();
            $count     = $query->where(['article_id' => $articleId])->count();
            $pager     = new Pagination(['totalCount' => $count, 'pageSize' => $this->pageSize]);
            $comments  = $query->offset($pager->offset)
                ->limit($pager->pageSize)
                ->asArray()
                ->all();
            $comments  = $commentModel::commentFormat($comments);

            return $this->render('articledetail', [
                'articles' => $artDetail,
                'comments' => $comments,
                'pager'    => $pager,
                'model'    => $commentModel,
            ]);
        } else {
            Yii::$app->getSession()->setFlash('error', '文章参数错误');
        }
    }

    /**
     * @info 评论入口
     * @return bool|Response
     */
    public function actionComment()
    {
        if (!UserModel::checkLogin()) {
            Yii::$app->getSession()->setFlash('error', '您还未登录，请先登陆');
            return $this->redirect(['login']);
        }

        $commentPost = static::$request->post();

        if (!empty($commentPost['CommentModel'])) {
            $commentReturn = CommentModel::replyComment($commentPost['CommentModel']);
            if (is_bool($commentReturn) && $commentReturn === true) {
                $lastPage = CommentModel::getLastPage($this->pageSize);
                $this->redirect(['articledetail', 'article_id' => $commentPost['CommentModel']['id'], 'page' => $lastPage]);
                return true;
            } else {
                Yii::$app->getSession()->setFlash('error', $commentReturn);
                return false;
            }
        }
    }
}
