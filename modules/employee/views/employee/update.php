<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\employee\models\Employee $model */
/** @var app\modules\employee\models\EmployeeInfo $info */

$this->title = 'Update Employee: ' . ($info->fullName ?: 'Employee');
$this->params['breadcrumbs'][] = ['label' => 'Employee Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $info->fullName ?: 'Employee', 'url' => ['view', 'id' => $model->emp_master_id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="mb-3">
    <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
</div>

<?= $this->render('_form', ['model' => $model, 'info' => $info]) ?>
