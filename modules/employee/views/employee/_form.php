<?php

use app\modules\employee\models\Employee;
use app\modules\employee\models\EmployeeInfo;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Employee $model */
/** @var EmployeeInfo $info */
?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <?php $form = ActiveForm::begin(); ?>

        <?php if ($model->isNewRecord): ?>
            <p class="text-muted">Employee ID will be generated automatically when this record is saved.</p>
        <?php else: ?>
            <p class="form-control-plaintext mb-3">
                <strong>Employee ID:</strong> <?= Html::encode($model->emp_master_id) ?>
            </p>
        <?php endif; ?>

        <h5 class="fw-bold mb-3">Employment Details</h5>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($info, 'employee_code')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($info, 'emp_application_no')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($info, 'employee_type')->dropDownList(EmployeeInfo::getTypeOptions(), ['prompt' => 'Select employee type']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'designation')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'department')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($info, 'emp_joining_date')->input('date') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'is_status')->dropDownList(Employee::getStatusOptions()) ?>
            </div>
        </div>

        <hr>
        <h5 class="fw-bold mb-3">Personal Information</h5>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($info, 'emp_title')->dropDownList(EmployeeInfo::titleOptions(), ['prompt' => 'Select title']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($info, 'emp_first_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($info, 'emp_middle_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($info, 'emp_last_name')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($info, 'emp_gender')->dropDownList(EmployeeInfo::genderOptions(), ['prompt' => 'Select gender']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($info, 'emp_dob')->input('date') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($info, 'emp_birthplace')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($info, 'emp_religion')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($info, 'emp_aadhaar')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <hr>
        <h5 class="fw-bold mb-3">Contact and Address</h5>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($info, 'emp_mobile_no')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'emp_whatsapp_no')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'emp_email_id')->input('email', ['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <?= $form->field($info, 'address_line')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'city')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'state')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'pincode')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <hr>
        <h5 class="fw-bold mb-3">Family Details</h5>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($info, 'emp_father_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'emp_father_mobile')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'emp_father_whatsapp')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($info, 'emp_father_email')->input('email', ['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($info, 'emp_father_occupation')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($info, 'emp_mother_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'emp_mother_mobile')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'emp_mother_whatsapp')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($info, 'emp_mother_email')->input('email', ['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($info, 'emp_mother_occupation')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($info, 'emp_guardian_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'emp_guardian_relation')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($info, 'emp_guardian_mobile')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <hr>
        <h5 class="fw-bold mb-3">Previous Employment and Emergency Contact</h5>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($info, 'previous_employer_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($info, 'previous_designation')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($info, 'emergency_contact_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($info, 'emergency_contact_phone')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <?= Html::submitButton($model->isNewRecord ? 'Create Employee' : 'Save Changes', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
