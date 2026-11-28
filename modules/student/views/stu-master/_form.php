<?php

use app\modules\student\models\StuInfo;
use app\modules\student\models\StuMaster;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $master app\modules\student\models\StuMaster */
/* @var $info app\modules\student\models\StuInfo */
/* @var $lists array */

$form = ActiveForm::begin(['options' => ['class' => 'student-form', 'enctype' => 'multipart/form-data']]);

// Helpers to keep markup concise and consistent
$col = function ($model, $attr, $size = 4, $type = 'text') use ($form) {
    return '<div class="col-md-' . $size . '">'
        . $form->field($model, $attr)->input($type, ['maxlength' => true, 'class' => 'form-control'])
        . '</div>';
};
$select = function ($model, $attr, $items, $size = 4) use ($form) {
    return '<div class="col-md-' . $size . '">'
        . $form->field($model, $attr)->dropDownList($items, ['prompt' => '— Select —', 'class' => 'form-select'])
        . '</div>';
};
$classField = function ($attr, $key, $size = 4) use ($form, $master, $lists) {
    $field = $form->field($master, $attr);
    $html = !empty($lists[$key])
        ? $field->dropDownList($lists[$key], ['prompt' => '— Select —', 'class' => 'form-select'])
        : $field->input('number', ['min' => 0, 'class' => 'form-control']);
    return '<div class="col-md-' . $size . '">' . $html . '</div>';
};
?>

<?= $form->errorSummary([$master, $info], ['class' => 'alert alert-danger shadow-sm']) ?>

<!-- Student Personal Details -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-user-graduate text-primary me-2"></i>Student Information
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <?= $col($info, 'stu_unique_id', 3) ?>
            <?= $col($info, 'application_no', 3) ?>
            <?= $col($info, 'stu_admission_date', 3, 'date') ?>
            <?= $select($master, 'is_status', StuMaster::statusOptions(), 3) ?>
        </div>
        <div class="row g-3 mt-1 align-items-end">
            <div class="col-md-6">
                <?= $form->field($info, 'profileImageFile')->fileInput(['accept' => 'image/png,image/jpeg,image/webp','class' => 'form-control']) ?>
            </div>
            <?php if (!empty($info->Profile_image)): ?>
                <div class="col-md-6">
                    <div class="text-muted small mb-1">Current profile image</div>
                    <?= Html::img($info->Profile_image, ['alt' => 'Student profile', 'style' => 'width:96px;height:96px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6']) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="row g-3 mt-1">
            <?= $select($info, 'stu_title', StuInfo::titleOptions(), 2) ?>
            <?= $col($info, 'stu_first_name', 4) ?>
            <?= $col($info, 'stu_middle_name', 3) ?>
            <?= $col($info, 'stu_last_name', 3) ?>
        </div>
        <div class="row g-3 mt-1">
            <?= $select($info, 'stu_gender', StuInfo::genderOptions(), 3) ?>
            <?= $col($info, 'stu_dob', 3, 'date') ?>
            <?= $col($info, 'stu_birthplace', 3) ?>
            <?= $col($info, 'stu_religion', 3) ?>
        </div>
        <div class="row g-3 mt-1">
            <?= $col($info, 'stu_aadhaar', 3) ?>
            <?= $col($info, 'stu_mobile_no', 3, 'tel') ?>
            <?= $col($info, 'stu_whatsapp_no', 3, 'tel') ?>
            <?= $col($info, 'stu_email_id', 3, 'email') ?>
        </div>
    </div>
</div>

<!-- Class Placement -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-graduation-cap text-success me-2"></i>Class Placement
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <?= $classField('stu_master_course_id', 'course', 4) ?>
            <?= $classField('stu_master_batch_id', 'batch', 4) ?>
            <?= $classField('stu_master_section_id', 'section', 4) ?>
        </div>
    </div>
</div>

<!-- Parent & Guardian Information -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-users text-info me-2"></i>Family &amp; Guardian Details
        </h5>
    </div>
    <div class="card-body p-4">
        <h6 class="fw-bold text-muted mb-3"><i class="fa-solid fa-person me-1"></i> Father's Details</h6>
        <div class="row g-3">
            <?= $col($info, 'father_name', 4) ?>
            <?= $col($info, 'father_occupation', 4) ?>
            <?= $col($info, 'father_email', 4, 'email') ?>
        </div>
        <div class="row g-3 mt-1">
            <?= $col($info, 'father_mobile', 4, 'tel') ?>
            <?= $col($info, 'father_whatsapp', 4, 'tel') ?>
        </div>

        <hr class="my-4 text-muted">

        <h6 class="fw-bold text-muted mb-3"><i class="fa-solid fa-person-dress me-1"></i> Mother's Details</h6>
        <div class="row g-3">
            <?= $col($info, 'mother_name', 4) ?>
            <?= $col($info, 'mother_occupation', 4) ?>
            <?= $col($info, 'mother_email', 4, 'email') ?>
        </div>
        <div class="row g-3 mt-1">
            <?= $col($info, 'mother_mobile', 4, 'tel') ?>
            <?= $col($info, 'mother_whatsapp', 4, 'tel') ?>
        </div>

        <hr class="my-4 text-muted">

        <h6 class="fw-bold text-muted mb-3"><i class="fa-solid fa-user-shield me-1"></i> Legal Guardian (if different from parents)</h6>
        <div class="row g-3">
            <?= $col($info, 'guardian_name', 4) ?>
            <?= $col($info, 'guardian_relation', 4) ?>
            <?= $col($info, 'guardian_mobile', 4, 'tel') ?>
        </div>
    </div>
</div>

<!-- Address & Previous School -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-location-dot text-warning me-2"></i>Address &amp; Previous Education
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <?= $col($info, 'address_line', 12) ?>
        </div>
        <div class="row g-3 mt-1">
            <?= $col($info, 'city', 4) ?>
            <?= $col($info, 'state', 4) ?>
            <?= $col($info, 'pincode', 4) ?>
        </div>

        <hr class="my-4 text-muted">

        <h6 class="fw-bold text-muted mb-3"><i class="fa-solid fa-school me-1"></i> Previous School Details</h6>
        <div class="row g-3">
            <?= $col($info, 'prev_school_name', 8) ?>
            <?= $col($info, 'prev_class', 4) ?>
        </div>
    </div>
</div>

<!-- Emergency Contact -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-phone-volume text-danger me-2"></i>Emergency Contact
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <?= $col($info, 'emergency_contact_name', 6) ?>
            <?= $col($info, 'emergency_contact_phone', 6, 'tel') ?>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mb-4">
    <?= Html::submitButton($master->isNewRecord ? '<i class="fa-solid fa-check me-1"></i> Create Student' : '<i class="fa-solid fa-floppy-disk me-1"></i> Save Changes', ['class' => 'btn btn-primary px-4']) ?>
    <?= Html::a('<i class="fa-solid fa-xmark me-1"></i> Cancel', $master->isNewRecord ? ['index'] : ['view', 'id' => $master->stu_master_id], ['class' => 'btn btn-outline-secondary px-4']) ?>
</div>

<?php ActiveForm::end(); ?>
