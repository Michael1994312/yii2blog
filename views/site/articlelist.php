<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/27
 * Time: 15:45
 */
use yii\widgets\LinkPager;
?>

<table>
    <tr>
        <th class="col-lg-7">标题</th>
        <th class="col-lg-3">作者</th>
        <th class="col-lg-3">时间</th>
    </tr>
    <?php
    if (!empty($articles)) {
        foreach ($articles as $row) {
            ?>
            <tr>
            <td><a href="<?= \yii\helpers\Url::to(['site/articledetail', 'article_id' => $row['id']]) ?>"><span><?= $row['title'] ?></span></a></td>
            <td><?= $row['user_name']?></td>
            <td><?= $row['created_at']?></td>
            </tr>
        <?php
        }
    }
    ?>
</table>

<?= LinkPager::widget([
    'pagination' => $pager,
]); ?>