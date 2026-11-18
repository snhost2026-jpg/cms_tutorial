<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Reset Password';
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h3 class="mb-0"><?= Html::encode($this->title) ?> - <?= Html::encode($user->username) ?></h3>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->errorSummary($model) ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'new_password')->passwordInput() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'confirm_password')->passwordInput() ?>
            </div>
        </div>

        <div class="mt-3">
            <?= Html::submitButton('Reset Password', ['class' => 'btn btn-warning']) ?>
            <?= Html::a('Back', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
