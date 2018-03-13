<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "blog_user".
 *
 * @property integer $id
 * @property string $username
 * @property string $nickname
 * @property string $password
 * @property integer $created_at
 * @property integer $updated_at
 */
class UserModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
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
            [['created_at', 'updated_at'], 'default', 'value' => time()],
            ['username', 'checkUsername'],
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

    public static function login()
    {
        $post = [
            'LoginForm' => [
                'username'   => 'admin',
                'password'   => 'admin',
                'rememberMe' => '1',
            ],
        ];

        //1. 查找用户名是否存在 2.匹配密码 3.登录成功，记住我
        $userPassword = static::find()->where(['username' => $post['LoginForm']['username']])->scalar();
        if (!empty($userPassword)) {

        } else {

        }
    }

    public static function register()
    {

    }

    public static function logout()
    {

    }

    public function checkUsername($attribute)
    {
        if (!empty($this->$attribute)) {
            $this->$attribute = trim($this->$attribute);
            $userName = static::find()->where(['username' => $this->$attribute])->asArray()->one();

            if (!empty($userName)) {

            } else {
                $this->addError($attribute, '该用户不存在');

                return false;
            }
        }
    }
}
