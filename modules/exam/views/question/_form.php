<?php

use app\modules\exam\models\ExamQuestion;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\exam\models\ExamQuestion */
/* @var $lists array */

$form = ActiveForm::begin();
?>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger shadow-sm']) ?>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Question Details
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <?= $form->field($model, 'course_id')->dropDownList($lists['course'], ['prompt' => '— Any Course (Optional) —', 'class' => 'form-select']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'subject_id')->dropDownList($lists['subject'], ['prompt' => '— Any Subject (Optional) —', 'class' => 'form-select']) ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'marks')->input('number', ['step' => '0.25', 'min' => '0.25', 'class' => 'form-control']) ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'difficulty')->dropDownList(ExamQuestion::difficultyOptions(), ['class' => 'form-select']) ?>
            </div>
        </div>

        <div class="mb-3">
            <?= $form->field($model, 'question_text')->textarea(['rows' => 3, 'class' => 'form-control', 'placeholder' => 'Enter the question statement...']) ?>
        </div>

        <h6 class="fw-bold text-muted mt-4 mb-3"><i class="fa-solid fa-list-ul me-1"></i> Answer Options</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <?= $form->field($model, 'option_a')->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'Option A text']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'option_b')->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'Option B text']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'option_c')->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'Option C text (optional)']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'option_d')->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'Option D text (optional)']) ?>
            </div>
        </div>

        <div class="row g-3 mt-2 align-items-center">
            <div class="col-md-4">
                <?= $form->field($model, 'correct_option')->dropDownList(ExamQuestion::optionKeys(), ['prompt' => '— Select Correct Option —', 'class' => 'form-select']) ?>
            </div>
            <div class="col-md-4 pt-3">
                <div class="form-check form-switch">
                    <?= Html::activeCheckbox($model, 'is_active', ['class' => 'form-check-input', 'label' => 'Active (available in Question Bank)', 'labelOptions' => ['class' => 'form-check-label']]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mb-4 flex-wrap">
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa-solid fa-check me-1"></i> Save Question' : '<i class="fa-solid fa-floppy-disk me-1"></i> Save Changes', ['class' => 'btn btn-primary px-4']) ?>
    <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa-solid fa-plus me-1"></i> Save & Add Another', ['class' => 'btn btn-outline-primary px-4', 'name' => 'save_and_add', 'value' => '1']) ?>
    <?php endif; ?>
    <?= Html::a('<i class="fa-solid fa-xmark me-1"></i> Cancel', ['index'], ['class' => 'btn btn-outline-secondary px-4']) ?>
</div>

<?php ActiveForm::end(); ?>
