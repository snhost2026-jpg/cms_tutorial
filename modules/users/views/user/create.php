<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Add User';

$roleList = [];
foreach ($roles as $role) {
    $roleList[$role->name] = ucfirst($role->name);
}
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h3 class="mb-0"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->errorSummary($model) ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'username')->textInput() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'email')->textInput() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'password')->passwordInput() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'role')->dropDownList($roleList, ['prompt' => 'Select Role']) ?>
            </div>
        </div>

        <div class="mt-3">
            <?= Html::submitButton('Create User', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Back', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
