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
            [['content'], 'string'],
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

    /**
     * @info 发布文章
     * @param $articlePost
     * @return bool|string
     */
    public static function publish($articlePost)
    {
        $title = trim($articlePost['title']);
        $content = trim($articlePost['content']);
        //1.检查是否登陆 2.入库
        if (!empty($title) && !empty($content)) {
            $userId = Yii::$app->session->get('userId');
            if (!empty($userId)) {
                $articlePost['user_id'] = $userId;
                $model = new ArticleModel();
                $model->setAttributes($articlePost, false);
                if ($model->save()) {
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

    /**
     * @info 文章详情数据格式化
     * @param $artDetail
     * @return string
     */
    public static function articleFormat($artDetail)
    {
        if (!empty($artDetail)) {
            $artDetail['user_name'] = UserModel::getUserName($artDetail['user_id']);
            $artDetail['created_at'] = date('Y-m-d H:i', $artDetail['created_at']);
            $artDetail['updated_at'] = date('Y-m-d H:i', $artDetail['updated_at']);
            return $artDetail;
        }
    }

    /**
     * @info 文章列表数据格式化
     * @param $articles
     * @return mixed
     */
    public static function acticleListFormat($articles)
    {
        if (!empty($articles)) {
            foreach ($articles as &$row) {
                $row['user_name'] = UserModel::getUserName($row['user_id']);
                $row['created_at'] = date('Y-m-d H:i', $row['created_at']);
                $row['updated_at'] = date('Y-m-d H:i', $row['updated_at']);
            }

            return $articles;
        }
    }
}
