<?php

namespace app\models;

use Yii;
use yii\web\Cookie;

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
    public static $expireTime;

    public static $session;

    public static $cookie;

    public $test;

    public function __construct()
    {
        $this->test = 111;
        static::$expireTime = time() + 86400 * 7;
        static::$session = \Yii::$app->session;
        static::$cookie  = \Yii::$app->request->cookies;
    }
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
            [['username', 'password'], 'required', 'message' => '{attribute}不能为空'],
            [['created_at', 'updated_at'], 'integer'],
            [['username', 'nickname', 'password'], 'string', 'max' => 40],
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
            'username' => 'Username',
            'nickname' => 'Nickname',
            'password' => 'Password',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function login($loginPost)
    {
        $cookies = \Yii::$app->response->cookies;
        $usernameCookie = $cookies->getValue('username');
        $passwordCookie = $cookies->getValue('password');
        $where = ['username' => $usernameCookie, 'password' => $passwordCookie];
        $userCheck = static::find()->where($where)->asArray()->one();

        if (empty($usernameCookie) || empty($passwordCookie) || empty($userCheck)) {
            //1. 查找用户名是否存在 2.匹配密码 3.登录成功，记住我
            $model = new UserModel();
            $where = ['username' => $loginPost['username']];
            $userInfo = $model->find()->select(['id', 'username', 'password'])->where($where)->asArray()->one();
            if (!empty($userInfo)) {
                if (sha1(sha1($loginPost['password'])) === $userInfo['password']) {
                    static::$session->set('userId', $userInfo['id']);
                    if ($loginPost['rememberMe'] === '1') {
                        $data = [
                            'usernameCookie' => [
                                'name'     => 'username',
                                'value'    => $userInfo['username'],
                                'httpOnly' => true,
                                'expire'   => static::$expireTime,
                            ],
                            'passwordCookie' => [
                                'name'     => 'password',
                                'value'    => $userInfo['password'],
                                'httpOnly' => true,
                                'expire'   => static::$expireTime,
                            ],
                        ];
                        $cookies->add(new Cookie($data['usernameCookie']));
                        $cookies->add(new Cookie($data['passwordCookie']));
                        return true;
                    }
                } else {
                    return '密码错误';
                }
            } else {
                return '账号错误';
            }
        } else {
            return true;
        }
    }

    public static function register($regPost)
    {
        // 1.检查该账号是否已存在 2. 校验密码是否正确
        $username   = trim($regPost['username']);
        $password   = trim($regPost['password']);
        $rePassword = trim($regPost['repassword']);

        if (!empty($username) && !empty($password) && !empty($rePassword)) {
            $userId = static::find()->select('id')->where(['username' => $username])->scalar();
            if (empty($userId)) {
                if ($password === $rePassword && strlen($password) > 5) {
                    //入库并登陆
                    unset($regPost['repassword']);
                    $regPost['password'] = sha1(sha1($regPost['password']));
                    $model = new UserModel();
                    $model->setAttributes($regPost, false);
                    if ($model->save()) {
                        static::$session->set('userId', $model->attributes['id']);
                        return true;
                    } else {
                        return '注册失败';
                    }
                } else {
                    return '俩次输入的密码不一致或者密码长度不能小于6';
                }
            } else {
                return '该账号已存在';
            }
        } else {
            return '用户账号或者密码不能为空';
        }
    }

    public static function logout()
    {
//        echo '<pre>';var_dump($this->test);exit;
        static::$cookie->remove('username');
        static::$cookie->remove('password');
        static::$session->remove('userInfo');
    }

    public static function checkLogin()
    {
        $userId = \Yii::$app->session->get('userId');
        $userExist = static::find()->select('id')->where(['id' => $userId])->scalar();
        if (!empty($userId) && !empty($userExist)) {
            return true;
        } else {
            return false;
        }
    }
}
