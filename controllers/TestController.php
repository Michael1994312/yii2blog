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

    public function actionTesta()
    {
        $return = OrderModel::createOrder(1, 9);
//        echo $return;
    }

    public function actionTestb()
    {
        $return = OrderModel::createOrder(1, 2);
//        echo $return;
    }
}