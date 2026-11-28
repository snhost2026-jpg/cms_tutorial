<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array<string, array<string, mixed>> $entities */

$this->title = 'Course Management';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
        <p class="text-muted mb-0">Manage courses, subjects, batches, and sections.</p>
    </div>
</div>

<div class="row g-3">
    <?php foreach ($entities as $key => $definition): ?>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><?= Html::encode($definition['label']) ?></h5>
                    <p class="text-muted">Browse and manage <?= Html::encode(strtolower($definition['label'])) ?>.</p>
                    <?= Html::a('Open ' . Html::encode($definition['label']), ['records', 'entity' => $key], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('Add', ['create', 'entity' => $key], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
