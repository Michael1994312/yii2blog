<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "blog_comment".
 *
 * @property integer $id
 * @property string $username
 * @property string $nickname
 * @property string $password
 * @property integer $created_at
 * @property integer $updated_at
 */
class CommentModel extends \yii\db\ActiveRecord
{
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
            [['username', 'password'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['username', 'nickname', 'password'], 'string', 'max' => 40],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'nickname' => 'Nickname',
            'password' => 'Password',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
