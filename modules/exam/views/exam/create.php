<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\exam\models\Exam */
/* @var $lists array */

$this->title = 'Create Exam';
$this->params['breadcrumbs'][] = ['label' => 'Online Exams', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="exam-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-plus-circle text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Configure exam details, optional schedule, and student options.</p>
        </div>
        <div>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Exams', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', ['model' => $model, 'lists' => $lists]) ?>
</div>
