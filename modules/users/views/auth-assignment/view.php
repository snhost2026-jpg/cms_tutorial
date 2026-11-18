<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\users\models\AuthAssignment */

$this->title = $model->item_name . ' (' . ($model->user ? $model->user->username : 'User #' . $model->user_id) . ')';
$this->params['breadcrumbs'][] = ['label' => 'Auth Assignments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-assignment-view">

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0 text-dark"><?= Html::encode($this->title) ?></h4>
                <div class="text-muted small">Auth Assignment Details</div>
            </div>
            <div class="d-flex gap-2">
                <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to List', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                <?= Html::a('<i class="fa-solid fa-sliders me-1"></i> Manage Access', ['user', 'user_id' => $model->user_id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('<i class="fa-solid fa-trash me-1"></i> Delete', ['delete', 'item_name' => $model->item_name, 'user_id' => $model->user_id], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this auth assignment?',
                        'method' => 'post',
                    ],
                ]) ?>
            </div>
        </div>
        <div class="card-body p-4">
            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-bordered detail-view'],
                'attributes' => [
                    [
                        'attribute' => 'item_name',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $isRole = Yii::$app->authManager->getRole($model->item_name) !== null;
                            $badgeClass = $isRole ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-info-subtle text-info border border-info-subtle';
                            return '<span class="badge ' . $badgeClass . ' px-2.5 py-1.5 rounded-pill fs-6">' . Html::encode($model->item_name) . '</span>' .
                                ($isRole ? ' <span class="badge bg-light text-muted border">Role</span>' : ' <span class="badge bg-light text-muted border">Permission</span>');
                        },
                    ],
                    [
                        'attribute' => 'user_id',
                        'label' => 'Assigned User',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $user = $model->user;
                            if ($user) {
                                return '<strong>' . Html::encode($user->username) . '</strong> (User ID: ' . (int)$user->id . ')' .
                                    ($user->email ? '<div class="text-muted small">' . Html::encode($user->email) . '</div>' : '');
                            }
                            return 'User ID: ' . Html::encode($model->user_id);
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => !empty($model->created_at) ? date('M d, Y h:i:s A', (int)$model->created_at) : '—',
                    ],
                ],
            ]) ?>
        </div>
    </div>

</div>

