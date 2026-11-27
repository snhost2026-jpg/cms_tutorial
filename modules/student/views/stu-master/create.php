<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $master app\modules\student\models\StuMaster */
/* @var $info app\modules\student\models\StuInfo */
/* @var $lists array */

$this->title = 'Add Student';
$this->params['breadcrumbs'][] = ['label' => 'Students', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="student-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-user-plus text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Every field is optional. Leave the registration number empty to have one generated automatically.</p>
        </div>
        <div>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to Students', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <?= $this->render('_form', ['master' => $master, 'info' => $info, 'lists' => $lists]) ?>
</div>
