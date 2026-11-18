<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use mdm\admin\components\RouteRule;
use mdm\admin\AutocompleteAsset;
use yii\helpers\Json;
use mdm\admin\components\Configs;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\AuthItem */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $context mdm\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$rules = Configs::authManager()->getRules();
unset($rules[RouteRule::RULE_NAME]);
$source = Json::htmlEncode(array_keys($rules));

$js = <<<JS
    $('#rule_name').autocomplete({
        source: $source,
    });
JS;
AutocompleteAsset::register($this);
$this->registerJs($js);
?>

<div class="auth-item-form">
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <?php $form = ActiveForm::begin(['id' => 'item-form']); ?>
        <div class="row g-3">
            <div class="col-md-6">
                <?= $form->field($model, 'name')->textInput([
                    'maxlength' => 64,
                    'class' => 'form-control rounded-3',
                    'placeholder' => 'e.g. SalesSpecialist or manageReports',
                ])->hint('Unique identifier for this ' . strtolower($labels['Item'])) ?>

                <?= $form->field($model, 'description')->textarea([
                    'rows' => 4,
                    'class' => 'form-control rounded-3',
                    'placeholder' => 'Brief description of what this ' . strtolower($labels['Item']) . ' is for...',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'ruleName')->textInput([
                    'id' => 'rule_name',
                    'class' => 'form-control rounded-3',
                    'placeholder' => 'Optional business rule class/name',
                ])->hint('Leave blank if no custom business rule applies.') ?>

                <?= $form->field($model, 'data')->textarea([
                    'rows' => 4,
                    'class' => 'form-control rounded-3 font-monospace',
                    'placeholder' => 'Optional serialized extra data',
                ]) ?>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-outline-secondary rounded-3 px-4']) ?>
            <?= Html::submitButton('<i class="fa-solid fa-check me-1"></i> ' . ($model->isNewRecord ? 'Create ' . $labels['Item'] : 'Save Changes'), [
                'class' => 'btn btn-primary rounded-3 px-4 shadow-sm fw-semibold',
                'name' => 'submit-button',
            ]) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
