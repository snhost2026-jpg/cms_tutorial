<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\AuthItem */
/* @var $context mdm\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = 'Update ' . $labels['Item'] . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Users & Access', 'url' => ['/users/user/index']];
$this->params['breadcrumbs'][] = ['label' => $labels['Items'], 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="auth-item-update py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-pen text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Edit details for <?= strtolower($labels['Item']) ?> <?= Html::encode($model->name) ?></p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-sliders me-1"></i> Configure Rights', ['view', 'id' => $model->name], ['class' => 'btn btn-outline-primary rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to ' . $labels['Items'], ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
