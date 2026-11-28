<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\exam\models\ExamQuestion */
/* @var $lists array */

$this->title = 'Add Question';
$this->params['breadcrumbs'][] = ['label' => 'Online Exams', 'url' => ['/exam/exam/index']];
$this->params['breadcrumbs'][] = ['label' => 'Question Bank', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="exam-question-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-plus-circle text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Add a multiple-choice question to the reusable Question Bank.</p>
        </div>
        <div>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Bank', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <?= $this->render('_form', ['model' => $model, 'lists' => $lists]) ?>
</div>
