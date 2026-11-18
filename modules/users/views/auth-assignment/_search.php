<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\users\models\AuthAssignmentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="auth-assignment-search mb-3">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row g-2">
        <div class="col-md-5">
            <?= $form->field($model, 'item_name')->textInput(['placeholder' => 'Search by Role/Permission name'])->label(false) ?>
        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'user_id')->textInput(['placeholder' => 'Search by User ID or Username'])->label(false) ?>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary btn-sm flex-grow-1']) ?>
            <?= Html::a('Reset', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

