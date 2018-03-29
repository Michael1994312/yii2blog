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

    /**
     * @info 入队列
     * @param $product_id
     * @param $product_num
     */
    public static function orderPush($product_id, $product_num)
    {
        $product_info = json_encode([$product_id, $product_num]);
        Yii::$app->redis->rpush('test_list', $product_info);
        //TODO：怎么判断入队列成功 自增？
    }

    /**
     * @info 死循环 出队列 消费
     * @warning 注意这里有可能发生死循环 消耗cpu内存
     */
    public static function orderPop()
    {
        do {
            $product_info = Yii::$app->redis->lpop('test_list');
            list($product_id, $product_num) = json_decode($product_info);

            //todo：判断是否消费成功 若消费失败 重新入队列 进行消费
            if (!static::createOrder($product_id, $product_num)) {
                //todo：消表费失败，判断是否库存不足 库存不足的话就不要再进入队列了，可以记录到错误日志或者mysql中
                if (static::checkStock($product_id, $product_num)) {
                    //todo：判断入队列是否成功
                    $old_len = Yii::$app->redis->llen('test_list');
                    static::orderPush($product_id, $product_num);
                    $new_len = Yii::$app->redis->llen('test_list');
                    if ($new_len != $old_len +1) {
                        Yii::error([$product_id, $product_num], '压入队列失败');
                    }
                } else {
                    Yii::error([$product_id, $product_num], '该商品库存不足');
                }
            }
            sleep(1);

        } while (Yii::$app->redis->llen('test_list')>0);
    }

    /**
     * @info 检测某一商品库存是否足够
     * @param $product_id
     * @param $product_num
     * @return bool
     */
    public static function checkStock($product_id, $product_num)
    {
        if (!empty($product_id) && !empty($product_num)) {
            $sql   = "SELECT stock FROM blog_product WHERE id=".$product_id;
            $stock = Yii::$app->db->createCommand($sql)->queryScalar();

            return ($stock >= $product_num) ? true : false;
        }
    }

    /**
     * @info 生成订单
     * @param $product_id
     * @param $product_num
     * @return string
     * @throws \yii\db\Exception
     */
    public static function createOrder($product_id, $product_num)
    {
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
                    return true;
                }
            }
        }

        $tr->rollBack();
        return false;
    }

    /**
     * @info 并发curl请求
     * @param $urls
     * @param string $delay
     * @return array
     */
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
