<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use mdm\admin\components\RouteRule;
use mdm\admin\components\Configs;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel mdm\admin\models\searchs\AuthItem */
/* @var $context mdm\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$isRole = ($labels['Item'] === 'Role');

\app\components\AccessHelper::syncDefaultPermissionHierarchy();

$this->title = $labels['Items'];
$this->params['breadcrumbs'][] = ['label' => 'Users & Access', 'url' => ['/users/user/index']];
$this->params['breadcrumbs'][] = $this->title;

$rules = array_keys(Configs::authManager()->getRules());
$rules = array_combine($rules, $rules);
unset($rules[RouteRule::RULE_NAME]);
?>

<div class="user-rights-item-index py-2">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="fa-solid <?= $isRole ? 'fa-shield-halved' : 'fa-key' ?> text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">
                <?= $isRole 
                    ? 'Define system roles and assign page routes and permissions' 
                    : 'Manage granular system permissions and capabilities' ?>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-user-shield me-1"></i> Assignments', ['/rights/assignment'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?php if (!$isRole): ?>
                <?= Html::a('<i class="fa-solid fa-shield-halved me-1"></i> Roles', ['/rights/role'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?php else: ?>
                <?= Html::a('<i class="fa-solid fa-key me-1"></i> Permissions', ['/rights/permission'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?php endif; ?>
            <?= Html::a('<i class="fa-solid fa-route me-1"></i> Routes', ['/rights/route'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Create ' . $labels['Item'], ['create'], ['class' => 'btn btn-primary rounded-3 shadow-sm']) ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'headerRowOptions' => ['class' => 'table-light'],
            'layout' => "<div class=\"p-3 text-muted small border-bottom d-flex justify-content-between align-items-center\"><div>{summary}</div></div><div class=\"table-responsive\">{items}</div><div class=\"p-3 border-top d-flex justify-content-center\">{pager}</div>",
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'header' => '#',
                    'headerOptions' => ['style' => 'width: 60px;', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center text-muted fw-bold'],
                ],
                [
                    'attribute' => 'name',
                    'label' => $labels['Item'] . ' Name',
                    'format' => 'raw',
                    'value' => function ($model) use ($isRole) {
                        return '<div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle ' . ($isRole ? 'bg-primary-subtle text-primary' : 'bg-info-subtle text-info') . ' d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                                <i class="fa-solid ' . ($isRole ? 'fa-shield-halved' : 'fa-key') . '"></i>
                            </span>
                            <div>
                                ' . Html::a(Html::encode($model->name), ['view', 'id' => $model->name], ['class' => 'fw-bold text-dark text-decoration-none']) . '
                            </div>
                        </div>';
                    },
                ],
                [
                    'attribute' => 'description',
                    'label' => 'Description',
                    'format' => 'ntext',
                    'value' => function ($model) {
                        return $model->description ?: '-';
                    },
                ],
                [
                    'label' => $isRole ? 'Children & Routes' : 'Sub-items',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $auth = Yii::$app->authManager;
                        $children = $auth->getChildren($model->name);
                        $count = count($children);
                        $routesCount = 0;
                        foreach ($children as $c) {
                            if ($c->name !== '' && $c->name[0] === '/') {
                                $routesCount++;
                            }
                        }
                        return '<span class="badge bg-light text-dark border px-2 py-1">
                            <i class="fa-solid fa-layer-group me-1 text-primary"></i>' . $count . ' items
                        </span>' . ($routesCount > 0 ? ' <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-route me-1"></i>' . $routesCount . ' routes</span>' : '');
                    },
                ],
                [
                    'label' => 'Assigned Users',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $userCount = (new \yii\db\Query())
                            ->from('auth_assignment')
                            ->where(['item_name' => $model->name])
                            ->count();
                        return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                            <i class="fa-solid fa-users me-1"></i>' . (int)$userCount . ' users
                        </span>';
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Actions',
                    'headerOptions' => ['style' => 'width: 200px;', 'class' => 'text-end pe-4'],
                    'contentOptions' => ['class' => 'text-end pe-4'],
                    'template' => '{view} {update} {delete}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a('<i class="fa-solid fa-sliders"></i>', ['view', 'id' => $model->name], [
                                'class' => 'btn btn-sm btn-outline-primary rounded-3',
                                'title' => 'Configure Permissions & Page Routes',
                                'data-bs-toggle' => 'tooltip',
                            ]);
                        },
                        'update' => function ($url, $model) {
                            return Html::a('<i class="fa-solid fa-pen"></i>', ['update', 'id' => $model->name], [
                                'class' => 'btn btn-sm btn-outline-secondary rounded-3 ms-1',
                                'title' => 'Edit Details',
                                'data-bs-toggle' => 'tooltip',
                            ]);
                        },
                        'delete' => function ($url, $model) {
                            return Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $model->name], [
                                'class' => 'btn btn-sm btn-outline-danger rounded-3 ms-1',
                                'title' => 'Delete Item',
                                'data-bs-toggle' => 'tooltip',
                                'data-confirm' => 'Are you sure you want to delete ' . Html::encode($model->name) . '?',
                                'data-method' => 'post',
                            ]);
                        },
                    ],
                ],
            ],
            'pager' => [
                'options' => ['class' => 'pagination pagination-sm mb-0 gap-1'],
                'linkContainerOptions' => ['class' => 'page-item'],
                'linkOptions' => ['class' => 'page-link rounded-2'],
                'disabledListItemSubTagOptions' => ['class' => 'page-link rounded-2 text-muted'],
            ],
        ]); ?>
    </div>
</div>
