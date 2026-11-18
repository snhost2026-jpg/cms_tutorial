<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Change Password';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-change-password" style="max-width:700px;margin:0 auto;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h3 class="mb-0"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">

            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>

            <?php $form = ActiveForm::begin(); ?>

                <?= $form->errorSummary($model) ?>

                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'current_password')->passwordInput(['maxlength' => true]) ?>
                    </div>

                    <div class="col-md-6">
                        <?= $form->field($model, 'new_password')->passwordInput(['maxlength' => true]) ?>
                    </div>

                    <div class="col-md-6">
                        <?= $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true]) ?>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <?= Html::submitButton('Update Password', ['class' => 'btn btn-primary']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>