<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\exam\models\Exam */
/* @var $lists array */

$this->title = 'Edit Exam: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Online Exams', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Edit';
?>
<div class="exam-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-pen text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Update exam schedule, class placement, timing, or options.</p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-eye me-1"></i> Overview', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', ['model' => $model, 'lists' => $lists]) ?>
</div>
