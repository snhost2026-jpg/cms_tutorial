<?php

use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $entity */
/** @var array<string, mixed> $definition */
/** @var \yii\data\ActiveDataProvider $dataProvider */

$this->title = $definition['label'];
$this->params['breadcrumbs'][] = ['label' => 'Academic Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0"><?= Html::encode($this->title) ?></h2>
    <div class="d-flex gap-2">
        <?= Html::a('Academic Home', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('Add ' . Html::encode($definition['singular']), ['create', 'entity' => $entity], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover align-middle'],
            'columns' => array_merge(
                [['attribute' => 'id', 'contentOptions' => ['class' => 'text-muted']]],
                array_map(function ($attribute, $field) {
                    if (isset($field['relation'])) {
                        $relation = $field['relation'];
                        return [
                            'label' => $field['label'] ?? ucfirst(str_replace('_id', '', $attribute)),
                            'value' => function ($model) use ($relation) {
                                return $model->{$relation}->name ?? '-';
                            },
                        ];
                    }
                    return ['attribute' => $attribute];
                }, array_keys($definition['fields']), $definition['fields']),
                [[
                    'label' => 'Actions',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'value' => function ($model) use ($entity) {
                        return Html::a('View', ['view', 'entity' => $entity, 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary me-1'])
                            . Html::a('Edit', ['update', 'entity' => $entity, 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary me-1'])
                            . Html::a('Delete', ['delete', 'entity' => $entity, 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data' => ['method' => 'post', 'confirm' => 'Delete this record?'],
                            ]);
                    },
                ]]
            ),
        ]) ?>
    </div>
</div>
