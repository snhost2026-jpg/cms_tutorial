<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\employee\models\Employee $model */
/** @var app\modules\employee\models\EmployeeInfo $info */

$this->title = 'Add Employee';
$this->params['breadcrumbs'][] = ['label' => 'Employee Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="mb-3">
    <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
    <p class="text-muted mb-0">Create an employee profile and select the employee type.</p>
</div>

<?= $this->render('_form', ['model' => $model, 'info' => $info]) ?>
