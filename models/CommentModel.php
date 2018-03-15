<?php
namespace app\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "{{%comment}}".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $article_id 文章id
 * @property int $parent_id 父级评论id
 * @property int $prev_id 上级回复id
 * @property string $contents 评论内容
 * @property int $created_at 评论时间
 */
class CommentModel extends \yii\db\ActiveRecord
{
    public static $model;

    public static $session;

    public function __construct()
    {
        static::$model = static::find();
        static::$session = Yii::$app->session;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%comment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'article_id'], 'required', 'message' => '{attribute}不能为空'],
            [['user_id', 'article_id', 'created_at'], 'integer'],
            [['contents'], 'string'],
            ['created_at', 'default', 'value' => time()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'article_id' => 'Article ID',
            'parent_id' => 'Parent ID',
            'prev_id' => 'Prev ID',
            'contents' => '评论',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @info 文章评论
     * @param $commentPost
     * @return bool|string
     */
    public static function replyComment($commentPost)
    {
        $contents  = trim($commentPost['contents']);
        $articleId = intval($commentPost['id']);
        //1.检查是否登陆 2.入库
        if (!empty($articleId) && !empty($contents)) {
            $userId = Yii::$app->session->get('userId');
            if (!empty($userId)) {
                unset($commentPost['id']);
                $commentPost['article_id'] = $articleId;
                $commentPost['user_id'] = $userId;
                $model = new CommentModel();
                $model->setAttributes($commentPost, false);
                if ($model->save()) {
                    return true;
                } else {
                    return '回复失败';
                }
            } else {
                return '回复之前请先登陆';
            }
        } else {
            return '回复信息不能为空';
        }
    }

    /**
     * @info 评论数据格式化
     * @param $comments
     * @return string
     */
    public static function commentFormat($comments)
    {
        if (!empty($comments)) {
            foreach ($comments as &$row) {
                $row['created_at'] = date('Y-m-d H:i', $row['created_at']);
                $row['user_name']  = UserModel::getUserName($row['user_id']);
            }
            return $comments;
        }
    }

    /**
     * @info 获取评论最后一页
     * @param $pageSize
     * @return int
     */
    public static function getLastPage($pageSize)
    {
        $pageSize  = $pageSize ? intval($pageSize) : 5;
        $query     = CommentModel::find();
        $count     = $query->count();
        $pager     = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        return $pager->getPageCount();
    }
}
