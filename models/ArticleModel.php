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
            [['title', 'content', 'user_id'], 'required'],
            [['user_id', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['title', 'content'], 'string', 'max' => 40],
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
}
