<?php

use yii\db\Migration;

/**
 * Class m180314_060109_create_tables
 */
class m180314_060109_create_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $userTable = '{{%user}}';
        $this->createTable($userTable, [
            'id'         => $this->primaryKey(),
            'username'   => $this->string(40)->notNull()->comment('用户登陆账号'),
            'nickname'   => $this->string(40)->comment('用户昵称'),
            'password'   => $this->string(40)->notNull()->comment('用户登陆密码'),
            'created_at' => $this->integer(10)->comment('用户创建时间'),
            'updated_at' => $this->integer(10)->comment('用户修改时间'),
        ], $tableOptions);
        $this->addCommentOnTable($userTable, '用户表');

        $articleTable = '{{%article}}';
        $this->createTable($articleTable, [
            'id'         => $this->primaryKey(),
            'title'      => $this->string(40)->notNull()->comment('文章标题'),
            'content'    => $this->text()->notNull()->comment('文章内容'),
            'user_id'    => $this->integer(10)->notNull()->comment('文章发布者id'),
            'is_delete'  => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('0表示未删除 1表示删除'),
            'created_at' => $this->integer(10)->comment('文章创建时间'),
            'updated_at' => $this->integer(10)->comment('文章修改时间'),
        ], $tableOptions);
        $this->addCommentOnTable($articleTable, '文章表');

        $commentTable = '{{%comment}}';
        $this->createTable($commentTable, [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer(10)->notNull()->comment('用户id'),
            'article_id' => $this->integer(10)->notNull()->comment('文章id'),
            'parent_id'  => $this->integer(10)->notNull()->defaultValue(0)->comment('父级评论id'),
            'prev_id'    => $this->integer(10)->notNull()->defaultValue(0)->comment('上级回复id'),
            'contents'   => $this->text()->comment('评论内容'),
            'created_at' => $this->integer(10)->comment('评论时间'),
        ], $tableOptions);
        $this->addCommentOnTable($commentTable, '评论回复表');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180314_060109_create_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180314_060109_create_tables cannot be reverted.\n";

        return false;
    }
    */
}
