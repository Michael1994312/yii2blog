<?php
namespace app\models;

use Yii;

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
            [['user_id', 'parent_id', 'article_id', 'prev_id'], 'required', 'message' => '{attribute}不能为空'],
            [['user_id', 'article_id', 'parent_id', 'prev_id', 'created_at'], 'integer'],
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
            'contents' => 'Contents',
            'created_at' => 'Created At',
        ];
    }

    public static function replyComment($commentPost)
    {
        $commentPost = [
            'article_id' => '1',
            'parent_id' => '1',
            'prev_id' => '1',
            'contents' => '测试回复内容',
        ];
        list($articleId, $parentId, $prevId, $contents) = $commentPost;
        //1.检查是否登陆 2.入库
        if (!empty($articleId) && !empty($contents)) {
            $userId = Yii::$app->session->get('userId');
            if (!empty($userId)) {
                $commentPost['user_id'] = $userId;
                static::$model->setAttributes($commentPost, false);
                if (static::$model->save()) {
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

}
