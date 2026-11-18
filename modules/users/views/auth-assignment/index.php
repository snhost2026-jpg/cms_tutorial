<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\users\models\AuthAssignmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Auth Assignments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-assignment-index">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0 text-dark"><?= Html::encode($this->title) ?></h3>
            <div class="text-muted small">Manage role, permission, and page-wise route assignments in <code>auth_assignment</code> table.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::beginForm(['sync-routes'], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton('<i class="fa-solid fa-arrows-rotate me-1"></i> Sync Page Routes', [
                'class' => 'btn btn-outline-primary btn-sm',
                'title' => 'Populate standard CRM routes into auth_item table',
                'data-bs-toggle' => 'tooltip',
            ]) ?>
            <?= Html::endForm() ?>

            <?= Html::a('<i class="fa-solid fa-shield-halved me-1"></i> Role Page Access', ['roles'], ['class' => 'btn btn-outline-dark btn-sm']) ?>
            <?= Html::a('<i class="fa-solid fa-users me-1"></i> User Management', ['/users/user/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Add Assignment', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link active" href="<?= \yii\helpers\Url::to(['index']) ?>">
                <i class="fa-solid fa-users me-1"></i> User Assignments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= \yii\helpers\Url::to(['roles']) ?>">
                <i class="fa-solid fa-shield-halved me-1"></i> Role Access & Permissions
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'headerRowOptions' => ['class' => 'table-light small text-uppercase text-muted'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'item_name',
                        'label' => 'Item Name / Route',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $itemName = $model->item_name;
                            $isRoute = ($itemName !== '' && $itemName[0] === '/');
                            $isRole = !$isRoute && Yii::$app->authManager->getRole($itemName) !== null;

                            if ($isRole) {
                                return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill"><i class="fa-solid fa-shield-halved me-1"></i>' . Html::encode($itemName) . ' <span class="badge bg-primary text-white ms-1">Role</span></span>';
                            } elseif ($isRoute) {
                                return '<span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2.5 py-1.5 rounded-pill font-monospace"><i class="fa-solid fa-route me-1 text-secondary"></i>' . Html::encode($itemName) . '</span>';
                            } else {
                                return '<span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1.5 rounded-pill"><i class="fa-solid fa-key me-1"></i>' . Html::encode($itemName) . '</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'user_id',
                        'label' => 'Assigned User',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $user = $model->user;
                            if ($user) {
                                return '<div class="fw-semibold text-dark">' . Html::encode($user->username) . ' <span class="badge bg-light text-muted border">ID: ' . (int)$user->id . '</span></div>' .
                                    ($user->email ? '<div class="text-muted small">' . Html::encode($user->email) . '</div>' : '');
                            }
                            return '<span class="text-muted font-monospace">User #' . Html::encode($model->user_id) . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'label' => 'Assigned Date',
                        'value' => function ($model) {
                            return !empty($model->created_at) ? date('M d, Y h:i A', (int)$model->created_at) : '—';
                        },
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Actions',
                        'headerOptions' => ['style' => 'width: 140px;', 'class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            return \yii\helpers\Url::to([$action, 'item_name' => $model->item_name, 'user_id' => $model->user_id]);
                        },
                        'buttons' => [
                            'user' => function ($url, $model) {
                                return Html::a('<i class="fa-solid fa-sliders"></i>', ['user', 'user_id' => $model->user_id], [
                                    'class' => 'btn btn-sm btn-outline-success py-1 px-2 me-1',
                                    'title' => 'Open Dual-Listbox Assignment Manager for this user',
                                ]);
                            },
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fa-solid fa-eye"></i>', $url, ['class' => 'btn btn-sm btn-outline-secondary py-1 px-2 me-1', 'title' => 'View']);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('<i class="fa-solid fa-trash"></i>', $url, [
                                    'class' => 'btn btn-sm btn-outline-danger py-1 px-2',
                                    'title' => 'Delete',
                                    'data-method' => 'post',
                                    'data-confirm' => 'Are you sure you want to remove this auth assignment?',
                                ]);
                            },
                        ],
                        'template' => '{user} {view} {delete}',
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>
