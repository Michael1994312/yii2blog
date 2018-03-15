<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Register';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to register:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <div class="form-group field-usermodel-repassword required">
            <label class="col-lg-1 control-label" for="usermodel-password">Repassword</label>
            <div class="col-lg-3"><input type="password" id="usermodel-repassword" class="form-control" name="UserModel[repassword]" aria-required="true"></div>
            <div class="col-lg-8"><p class="help-block help-block-error "></p></div>
        </div>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('Register', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>

</div>
<script>
    $("form :input").blur(function(){
        if($(this).is("#usermodel-repassword")){
            var passwd   = $('#usermodel-password').val();
            var repasswd = $('#usermodel-repassword').val();

            if (!repasswd) {
                alert('重复密码不能为空');
            } else {
                if (passwd !== repasswd) {
                    alert('前后密码不一致');
                }
            }
        }
    });
</script>
