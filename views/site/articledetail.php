<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/27
 * Time: 15:45
 */
use yii\widgets\LinkPager;
?>

<span>标题：</span><span><?= $articles['title'] ?></span><br>
<span>内容：</span><textarea><?= $articles['content'] ?></textarea><br>

<?php
if (!empty($comments)) {
    foreach ($comments as $row) {
        ?>
        <span><?= $row['user_name'] . '评论于' . $row['created_at'] . ': ' . $row['contents'] ?></span>
        <br>
    <?php
    }
}
?>

<?php $form = \yii\widgets\ActiveForm::begin(['action' => ['site/comment'],'id' => 'articledetail-form']); ?>

<?= \yii\helpers\Html::activeHiddenInput($model, 'id', ['value' => $articles['id']]) ?>

<?= $form->field($model, 'contents')->textarea(['rows' => 6]) ?>

<div class="form-group">
    <?= \yii\helpers\Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'publish-button']) ?>
</div>

<?php \yii\widgets\ActiveForm::end(); ?>

<?= LinkPager::widget([
    'pagination' => $pager,
]); ?>