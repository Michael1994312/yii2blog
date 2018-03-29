<?php

namespace app\controllers;

use app\models\OrderModel;
use Yii;
use yii\base\Controller;

class TestController extends Controller
{
    public function actionIndex()
    {
        $urls = [
            'http://localhost/yii2blog/web/index.php?r=test%2Ftesta',
            'http://localhost/yii2blog/web/index.php?r=test%2Ftestb',
            'http://localhost/yii2blog/web/index.php?r=test%2Ftesta',
            'http://localhost/yii2blog/web/index.php?r=test%2Ftestb',
            'http://localhost/yii2blog/web/index.php?r=test%2Ftesta',
            'http://localhost/yii2blog/web/index.php?r=test%2Ftestb',
        ];
        OrderModel::rollingCurl($urls);
    }

    /**
     * 这个方法以后可以用脚本代替
     */
    public function actionConsume()
    {
//        echo '<pre>';var_dump(Yii::$app->redis->lrange('test_list', 0, -1));exit;
        OrderModel::orderPop();
    }

    public function actionTesta()
    {
        OrderModel::orderPush(1, 9);
    }

    public function actionTestb()
    {
        OrderModel::orderPush(1, 2);
    }
}