<?php

use app\modules\student\models\StuInfo;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $master app\modules\student\models\StuMaster */
/* @var $lists array */

$info = $master->info ? $master->info : new StuInfo();
$this->title = $info->fullName ?: 'Student #' . $master->stu_master_id;
$this->params['breadcrumbs'][] = ['label' => 'Student Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$label = function ($key, $id) use ($lists) {
    if ($id === null || $id === '') {
        return '-';
    }
    return isset($lists[$key][$id]) ? $lists[$key][$id] : $id;
};

$sections = [
    'Student Personal Information' => [
        'icon' => 'fa-solid fa-user-graduate text-primary',
        'attributes' => [
            'stu_unique_id', 'application_no', 'stu_admission_date', 'stu_title', 'stu_first_name',
            'stu_middle_name', 'stu_last_name', 'stu_gender', 'stu_dob', 'stu_birthplace',
            'stu_religion', 'stu_aadhaar', 'stu_mobile_no', 'stu_whatsapp_no', 'stu_email_id',
        ],
    ],
    'Father\'s Details' => [
        'icon' => 'fa-solid fa-person text-info',
        'attributes' => ['father_name', 'father_occupation', 'father_mobile', 'father_whatsapp', 'father_email'],
    ],
    'Mother\'s Details' => [
        'icon' => 'fa-solid fa-person-dress text-info',
        'attributes' => ['mother_name', 'mother_occupation', 'mother_mobile', 'mother_whatsapp', 'mother_email'],
    ],
    'Legal Guardian' => [
        'icon' => 'fa-solid fa-user-shield text-warning',
        'attributes' => ['guardian_name', 'guardian_relation', 'guardian_mobile'],
    ],
    'Address Information' => [
        'icon' => 'fa-solid fa-location-dot text-success',
        'attributes' => ['address_line', 'city', 'state', 'pincode'],
    ],
    'Previous School' => [
        'icon' => 'fa-solid fa-school text-secondary',
        'attributes' => ['prev_school_name', 'prev_class'],
    ],
    'Emergency Contact' => [
        'icon' => 'fa-solid fa-phone-volume text-danger',
        'attributes' => ['emergency_contact_name', 'emergency_contact_phone'],
    ],
];
?>
<div class="student-view">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><?= Html::encode($this->title) ?></h3>
            <p class="text-muted small mb-0">
                Reg No: <span class="badge bg-light text-dark border font-monospace"><?= Html::encode($info->stu_unique_id ?: 'Not assigned') ?></span>
                &bull; Status: <span class="badge bg-<?= $master->is_status == 1 ? 'success' : 'secondary' ?>-subtle text-<?= $master->is_status == 1 ? 'success' : 'secondary' ?>"><?= Html::encode($master->statusLabel) ?></span>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Back to List', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i> Edit', ['update', 'id' => $master->stu_master_id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-trash me-1"></i> Delete', ['delete', 'id' => $master->stu_master_id], [
                'class' => 'btn btn-outline-danger',
                'data' => ['confirm' => 'Move this student to the trash?', 'method' => 'post'],
            ]) ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <!-- Class Placement & System Record Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-graduation-cap text-success me-2"></i>Class Placement &amp; Academic Details
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <?= DetailView::widget([
                    'model' => $master,
                    'options' => ['class' => 'table table-hover align-middle mb-0'],
                    'attributes' => [
                        ['label' => 'Course / Class', 'value' => $label('course', $master->stu_master_course_id)],
                        ['label' => 'Batch / Year', 'value' => $label('batch', $master->stu_master_batch_id)],
                        ['label' => 'Section', 'value' => $label('section', $master->stu_master_section_id)],
                        ['label' => 'Branch / Wing', 'value' => $label('branch', $master->stu_master_branch_id)],
                        ['label' => 'Status', 'value' => $master->statusLabel],
                        'stu_master_user_id',
                        'created_at:datetime',
                        'created_by',
                        'updated_at:datetime',
                        'updated_by',
                    ],
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Student and Family Details Cards -->
    <div class="row g-4">
        <?php foreach ($sections as $title => $sec): ?>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="<?= $sec['icon'] ?> me-2"></i><?= Html::encode($title) ?>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <?= DetailView::widget([
                                'model' => $info,
                                'options' => ['class' => 'table table-hover align-middle mb-0'],
                                'attributes' => $sec['attributes'],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
