<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\exam\models\ExamQuestion */
/* @var $lists array */

$this->title = 'Update Question #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Online Exams', 'url' => ['/exam/exam/index']];
$this->params['breadcrumbs'][] = ['label' => 'Question Bank', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="exam-question-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-pen text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Edit question text, options, marks, or correct answer.</p>
        </div>
        <div>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Bank', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', ['model' => $model, 'lists' => $lists]) ?>
</div>
