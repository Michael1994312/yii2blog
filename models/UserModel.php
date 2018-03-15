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

    public function __construct()
    {
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

    /**
     * @info 登陆
     * @param $loginPost
     * @return bool|string
     */
    public static function login($loginPost)
    {
        //1. 查找用户名是否存在 2.匹配密码 3.登录成功，记住我
        $username   = trim($loginPost['username']);
        $password   = trim($loginPost['password']);
        if (!empty($username) && !empty($password)) {
            $model = new UserModel();
            $cookies = $cookie = \Yii::$app->response->cookies;
            $where = ['username' => $username];
            $userInfo = $model->find()->select(['id', 'username', 'password'])->where($where)->asArray()->one();
            if (!empty($userInfo)) {
                if (sha1(sha1($password)) === $userInfo['password']) {
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
            return '用户账号或者密码不能为空';
        }
    }

    /**
     * @info 注册
     * @param $regPost
     * @return bool|string
     */
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
                        Yii::$app->session->set('userId', $model->attributes['id']);
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

    /**
     * @info 退出
     */
    public static function logout()
    {
        $cookie = \Yii::$app->response->cookies;
        $session = \Yii::$app->session;
        $cookie->remove('username');
        $cookie->remove('password');
        $session->remove('userId');
    }

    /**
     * @info 检测是否登陆
     * @return bool
     */
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

    /**
     * @info 检测是否 记住我
     */
    public static function checkRememberMe()
    {
        $model = new UserModel();
        if (!$model::checkLogin()) {
            $username = static::$cookie->getValue('username');
            $password = static::$cookie->getValue('password');
            $where    = ['username' => $username, 'password' => $password];
            $userId   = $model::find()->select('id')->where($where)->scalar();
            if ($userId) {
                $model::$session->set('userId', $userId);
            }
        }
    }

    /**
     * @info 获取用户名
     * @param null $userId
     * @return false|null|string
     */
    public static function getUserName($userId = null)
    {
        $userId = $userId ? $userId : \Yii::$app->session->get('userId');
        $userName = static::find()->select('username')->where(['id' => $userId])->scalar();
        return $userName;
    }
}
