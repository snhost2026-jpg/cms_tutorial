<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var string $entity */
/** @var array<string, mixed> $definition */
/** @var \app\modules\course\models\CourseRecord $model */

$id = $model->getAttribute('id');
$this->title = $definition['singular'] . ' #' . $id;
$this->params['breadcrumbs'][] = ['label' => 'Course Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $definition['label'], 'url' => ['records', 'entity' => $entity]];
$this->params['breadcrumbs'][] = $this->title;

$attributes = [['attribute' => 'id']];
foreach ($definition['fields'] as $attribute => $field) {
    if (isset($field['relation'])) {
        $relation = $field['relation'];
        $attributes[] = [
            'label' => $model->getAttributeLabel($attribute),
            'value' => $model->{$relation}->name ?? '-',
        ];
    } else {
        $attributes[] = $attribute;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0"><?= Html::encode($this->title) ?></h2>
    <div class="d-flex gap-2">
        <?= Html::a('Back to ' . Html::encode($definition['label']), ['records', 'entity' => $entity], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('Edit', ['update', 'entity' => $entity, 'id' => $id], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => $attributes,
            'options' => ['class' => 'table table-striped detail-view'],
        ]) ?>
    </div>
</div>
