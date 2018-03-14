<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "blog_article".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property integer $user_id
 * @property integer $is_delete
 * @property integer $created_at
 * @property integer $updated_at
 */
class ArticleModel extends \yii\db\ActiveRecord
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
        return '{{%article}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'user_id'], 'required', 'message' => '{attribute}不能为空'],
            [['user_id', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            ['title', 'string', 'max' => 40],
            [['contents'], 'string'],
            [['created_at', 'updated_at'], 'default', 'value' => time()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'user_id' => 'User ID',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function publish($articlePost)
    {
        $articlePost = [
            'title' => '测试标题',
            'content' => '测试内容',
        ];
        list($title, $content) = $articlePost;
        //1.检查是否登陆 2.入库
        if (!empty($title) && !empty($content)) {
            $userId = Yii::$app->session->get('userId');
            if (!empty($userId)) {
                $articlePost['id'] = $userId;
                static::$model->setAttributes($articlePost, false);
                if (static::$model->save()) {
                    return true;
                } else {
                    return '发布文章失败';
                }
            } else {
                return '发布文章之前请先登陆';
            }
        } else {
            return '发布文章信息不能为空';
        }
    }


}
