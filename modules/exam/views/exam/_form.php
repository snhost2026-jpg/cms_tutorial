<?php

use app\modules\exam\models\Exam;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\exam\models\Exam */
/* @var $lists array */

$form = ActiveForm::begin();
?>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger shadow-sm']) ?>

<!-- Basic Details & Target Audience -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-file-signature text-primary me-2"></i>Exam Details &amp; Audience
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-8">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'e.g. Midterm Physics Quiz - Unit 1']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'status')->dropDownList(Exam::statusOptions(), ['class' => 'form-select']) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'description')->textarea(['rows' => 2, 'class' => 'form-control', 'placeholder' => 'Optional instructions shown to students before starting...']) ?>
            </div>
        </div>

        <hr class="my-4">
        <h6 class="fw-bold text-muted mb-3"><i class="fa-solid fa-graduation-cap me-1"></i> Target Class &amp; Subject (Leave blank for all)</h6>
        <div class="row g-3">
            <div class="col-md-3">
                <?= $form->field($model, 'course_id')->dropDownList($lists['course'], ['prompt' => '— All Courses —', 'class' => 'form-select']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'batch_id')->dropDownList($lists['batch'], ['prompt' => '— All Batches —', 'class' => 'form-select']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'section_id')->dropDownList($lists['section'], ['prompt' => '— All Sections (Optional) —', 'class' => 'form-select']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'subject_id')->dropDownList($lists['subject'], ['prompt' => '— Any Subject (Optional) —', 'class' => 'form-select']) ?>
            </div>
        </div>
    </div>
</div>

<!-- Timing, Grading & Options (All Optional) -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-sliders text-success me-2"></i>Schedule, Timing &amp; Options
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-3">
                <?= $form->field($model, 'start_time')->input('datetime-local', [
                    'class' => 'form-control',
                    'value' => $model->formatForDateTimeInput($model->start_time),
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'end_time')->input('datetime-local', [
                    'class' => 'form-control',
                    'value' => $model->formatForDateTimeInput($model->end_time),
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'duration_minutes')->input('number', [
                    'min' => 1,
                    'max' => 600,
                    'class' => 'form-control',
                    'placeholder' => 'e.g. 30 (blank = untimed)',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'pass_percentage')->input('number', [
                    'step' => '0.5',
                    'min' => 0,
                    'max' => 100,
                    'class' => 'form-control',
                    'placeholder' => 'e.g. 40 (optional)',
                ]) ?>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="form-check form-switch mb-0">
                        <?= Html::activeCheckbox($model, 'shuffle_questions', [
                            'class' => 'form-check-input',
                            'label' => '<strong>Shuffle Questions</strong> <div class="small text-muted">Randomize question order for each student.</div>',
                            'labelOptions' => ['class' => 'form-check-label'],
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="form-check form-switch mb-0">
                        <?= Html::activeCheckbox($model, 'show_result', [
                            'class' => 'form-check-input',
                            'label' => '<strong>Show Result Immediately</strong> <div class="small text-muted">Let students view their score &amp; answer review right after submitting.</div>',
                            'labelOptions' => ['class' => 'form-check-label'],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mb-4">
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa-solid fa-arrow-right me-1"></i> Create & Select Questions' : '<i class="fa-solid fa-floppy-disk me-1"></i> Save Changes', ['class' => 'btn btn-primary px-4']) ?>
    <?= Html::a('<i class="fa-solid fa-xmark me-1"></i> Cancel', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary px-4']) ?>
</div>

<?php ActiveForm::end(); ?>
