<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Login - ' . Yii::$app->name;
?>

<div class="site-login" style="max-width:420px;margin:40px auto;">
    <div class="card shadow-sm border-0">
        <div class="card-body" style="padding:30px;">
            <h3 class="mb-3 text-center"><?= Html::encode(Yii::$app->name) ?> Login</h3>
            <p class="text-muted text-center mb-4">Enter your credentials to continue</p>

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('info')): ?>
                <div class="alert alert-info">
                    <?= Yii::$app->session->getFlash('info') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($showOtpField)): ?>

                <div class="alert alert-info text-center">
                    A 6-digit OTP has been sent to your registered email address. Please check your inbox or spam folder.
                </div>

                <?php $form = ActiveForm::begin(); ?>

                    <?= Html::hiddenInput('login_step', 'otp') ?>

                    <?= $form->field($model, 'otp')->textInput([
                        'maxlength' => 6,
                        'placeholder' => 'Enter 6-digit OTP',
                        'autocomplete' => 'off',
                    ]) ?>
                    <br>
                    <div class="form-group mb-0">
                        <?= Html::submitButton('Verify OTP', ['class' => 'btn btn-success w-100']) ?>
                    </div>

                <?php ActiveForm::end(); ?>

            <?php else: ?>

                <?php $form = ActiveForm::begin(); ?>

                    <?= Html::hiddenInput('login_step', 'credentials') ?>

                    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                    <?= $form->field($model, 'password')->passwordInput() ?>

                    <?= $form->field($model, 'rememberMe')->checkbox() ?>

                    <div class="form-group mb-0">
                        <?= Html::submitButton('Login', ['class' => 'btn btn-primary w-100']) ?>
                    </div>

                <?php ActiveForm::end(); ?>

            <?php endif; ?>
        </div>
    </div>
</div>
