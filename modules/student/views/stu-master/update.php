<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $master app\modules\student\models\StuMaster */
/* @var $info app\modules\student\models\StuInfo */
/* @var $lists array */

$fullName = $info ? $info->fullName : '';
$this->title = 'Update: ' . ($fullName ?: ('Student #' . $master->stu_master_id));
$this->params['breadcrumbs'][] = ['label' => 'Students', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $fullName ?: ('#' . $master->stu_master_id), 'url' => ['view', 'id' => $master->stu_master_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="student-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-user-pen text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Update profile, class placement, family details and emergency contact.</p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-eye me-1"></i> View Profile', ['view', 'id' => $master->stu_master_id], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <?= $this->render('_form', ['master' => $master, 'info' => $info, 'lists' => $lists]) ?>
</div>
