<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\users\models\AuthAssignment */
/* @var $itemsList array */
/* @var $usersList array */

$this->title = 'Create Auth Assignment';
$this->params['breadcrumbs'][] = ['label' => 'Auth Assignments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-assignment-create">

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0 text-dark"><?= Html::encode($this->title) ?></h4>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to List', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
        </div>
        <div class="card-body p-4">
            <?= $this->render('_form', [
                'model' => $model,
                'itemsList' => $itemsList,
                'usersList' => $usersList,
            ]) ?>
        </div>
    </div>

</div>

