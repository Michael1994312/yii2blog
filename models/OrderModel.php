<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property string $id 订单表主键
 * @property int $product_id 商品id
 * @property int $product_num 商品数量
 * @property int $create_time 创建时间
 */
class OrderModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id'], 'required'],
            [['product_id', 'product_num', 'create_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'product_num' => 'Product Num',
            'create_time' => 'Create Time',
        ];
    }

    public static function createOrder($product_id, $product_num)
    {
        //todo:创建订单
        $tr = Yii::$app->db->beginTransaction();
        $time = time();
        $product_id  = intval($product_id);
        $product_num = intval($product_num);
        $sql = "SELECT stock FROM blog_product where id='$product_id' FOR UPDATE";//此时这条记录被锁住,其它事务必须等待此次事务提交后才能执行
        $row = Yii::$app->db->createCommand($sql)->queryOne();

        if ($row['stock'] > 0) {
            $insertSql    = "INSERT INTO blog_order (`product_id`, `product_num`, `create_time`) VALUES ($product_id, $product_num, $time)";
            $insertResult = Yii::$app->db->createCommand($insertSql)->execute();

            $sql = "SELECT stock FROM blog_product where id=".$product_id;
            $row = Yii::$app->db->createCommand($sql)->queryOne();

            if ($row['stock'] >= $product_num) {
                $updateSql    = "UPDATE blog_product SET stock=stock-". $product_num ." WHERE id=". $product_id ." AND stock>=".$product_num ;
                $updateResult = Yii::$app->db->createCommand($updateSql)->execute();

                if ($insertResult && $updateResult) {
                    //这里有个bug：在网上看博客说扣减库存的时候判断库存是否足够就可以了，即后面多加一个where语句，但是这样是有问题的。
                    //虽然添加了一个where条件，不会更新库存表，但是这个sql在折行的时候返回的是true，这样导致订单表已经生成记录，但是没有
                    //库存扣减，逻辑上显然是不正确的。所以我加了一个判断条件，在update之前判断当前库存是否足够
                    $tr->commit();
                    return 'create order success';
                }
            }
        }

        $tr->rollBack();
        return 'create order fail';
    }

    public static function rollingCurl($urls, $delay='')
    {
        $queue = curl_multi_init();
        $map = array();

        foreach ($urls as $url) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_NOSIGNAL, true);

            curl_multi_add_handle($queue, $ch);
            $map[(string)$ch] = $url;
        }

        $responses = array();
        do {
            while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ;

            if ($code != CURLM_OK) {
                break;
            }

            // a request was just completed -- find out which one
//        while ($done = curl_multi_info_read($queue)) {
//
//            // get the info and content returned on the request
//            $info = curl_getinfo($done['handle']);
//            $error = curl_error($done['handle']);
//            $results = callback(curl_multi_getcontent($done['handle']), $delay);
//            $responses[$map[(string) $done['handle']]] = compact('info', 'error', 'results');
//
//            // remove the curl handle that just completed
//            curl_multi_remove_handle($queue, $done['handle']);
//            curl_close($done['handle']);
//        }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($active > 0) {
                curl_multi_select($queue, 0.5);
            }

        } while ($active);

        curl_multi_close($queue);
        return $responses;
    }

}
