<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel mdm\admin\models\searchs\Assignment */
/* @var $usernameField string */
/* @var $extraColumns string[] */

$this->title = 'User Rights Assignments';
$this->params['breadcrumbs'][] = ['label' => 'Users & Access', 'url' => ['/users/user/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-rights-assignment-index py-2">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-user-shield text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Assign system roles, permissions, and specific page routes directly to users</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-users me-1"></i> All Users', ['/users/user/index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-shield-halved me-1"></i> Roles', ['/rights/role'], ['class' => 'btn btn-outline-dark rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-key me-1"></i> Permissions', ['/rights/permission'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-route me-1"></i> Routes', ['/rights/route'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?= Html::a('<i class="fa-solid fa-user-plus me-1"></i> Add User', ['/users/user/create'], ['class' => 'btn btn-primary rounded-3 shadow-sm']) ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <?php Pjax::begin(['id' => 'pjax-assignment-list', 'timeout' => 5000]); ?>
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
                    'attribute' => $usernameField,
                    'label' => 'Username',
                    'format' => 'raw',
                    'value' => function ($model) use ($usernameField) {
                        $username = Html::encode($model->{$usernameField});
                        return '<div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-primary-subtle text-primary fw-bold d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;font-size:14px;">' . strtoupper(substr((string)$username, 0, 1) ?: 'U') . '</span>
                            <div>
                                <span class="fw-semibold text-dark d-block">' . $username . '</span>
                                <span class="text-muted small">User ID #' . $model->id . '</span>
                            </div>
                        </div>';
                    },
                ],
                [
                    'label' => 'Assigned Roles',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $roles = Yii::$app->authManager->getRolesByUser($model->id);
                        if (empty($roles)) {
                            return '<span class="badge bg-light text-muted border">No Roles</span>';
                        }
                        $html = [];
                        foreach ($roles as $role) {
                            $html[] = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-shield-halved me-1"></i>' . Html::encode($role->name) . '</span>';
                        }
                        return '<div class="d-flex gap-1 flex-wrap">' . implode(' ', $html) . '</div>';
                    },
                ],
                [
                    'label' => 'Direct Route / Permission Overrides',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $directPermissions = Yii::$app->authManager->getPermissionsByUser($model->id);
                        $directAssignments = (new \yii\db\Query())
                            ->select(['item_name'])
                            ->from('auth_assignment')
                            ->where(['user_id' => (string)$model->id])
                            ->column();

                        $routesCount = 0;
                        $permsCount = 0;
                        foreach ($directAssignments as $item) {
                            if ($item !== '' && $item[0] === '/') {
                                $routesCount++;
                            } elseif (!Yii::$app->authManager->getRole($item)) {
                                $permsCount++;
                            }
                        }

                        $badges = [];
                        if ($routesCount > 0) {
                            $badges[] = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-route me-1"></i>' . $routesCount . ' Specific Routes</span>';
                        }
                        if ($permsCount > 0) {
                            $badges[] = '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-key me-1"></i>' . $permsCount . ' Direct Perms</span>';
                        }
                        return !empty($badges) ? '<div class="d-flex gap-1 flex-wrap">' . implode(' ', $badges) . '</div>' : '<span class="text-muted small">-</span>';
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Actions',
                    'headerOptions' => ['style' => 'width: 180px;', 'class' => 'text-end pe-4'],
                    'contentOptions' => ['class' => 'text-end pe-4'],
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a('<i class="fa-solid fa-sliders me-1"></i> Manage Rights', ['view', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-primary rounded-3 fw-semibold',
                                'title' => 'Assign Roles, Permissions & Specific Routes to this user',
                                'data-pjax' => '0',
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
        <?php Pjax::end(); ?>
    </div>
</div>
