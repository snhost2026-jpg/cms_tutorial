<?php

use app\modules\timetable\models\CoachingTimetable;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var CoachingTimetable $model */
/** @var array $lists */
/** @var array $placementMeta */

$isNew = $model->isNewRecord;
$selectedDays = array_map('intval', (array) ($model->days_of_week ?: [$model->day_of_week]));
?>

<?php $form = ActiveForm::begin(['id' => 'coaching-timetable-form']); ?>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger rounded-3 shadow-sm']) ?>

<!-- SECTION 1: ACADEMIC & CLASS PLACEMENT -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-graduation-cap text-primary me-2"></i>Academic &amp; Batch Placement
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <?php if (!empty($lists['academic_session'])): ?>
                    <?= $form->field($model, 'academic_session_id')->dropDownList($lists['academic_session'], [
                        'prompt' => 'Select Academic Session',
                        'class' => 'form-select',
                    ]) ?>
                <?php else: ?>
                    <?= $form->field($model, 'academic_session_id')->input('number', [
                        'min' => 1,
                        'placeholder' => 'e.g. ' . date('Y'),
                        'class' => 'form-control',
                    ]) ?>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <?php if (!empty($lists['course'])): ?>
                    <?= $form->field($model, 'course_id')->dropDownList($lists['course'], [
                        'prompt' => 'Select Course',
                        'id' => 'tt-course-id',
                        'class' => 'form-select',
                    ]) ?>
                <?php else: ?>
                    <?= $form->field($model, 'course_id')->input('number', ['min' => 1, 'id' => 'tt-course-id']) ?>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <?php if (!empty($lists['batch'])): ?>
                    <?= $form->field($model, 'batch_id')->dropDownList($lists['batch'], [
                        'prompt' => 'Select Batch',
                        'id' => 'tt-batch-id',
                        'class' => 'form-select',
                    ]) ?>
                <?php else: ?>
                    <?= $form->field($model, 'batch_id')->input('number', ['min' => 1, 'id' => 'tt-batch-id']) ?>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <?php if (!empty($lists['section'])): ?>
                    <?= $form->field($model, 'section_id')->dropDownList($lists['section'], [
                        'prompt' => 'Whole Batch (All Sections)',
                        'id' => 'tt-section-id',
                        'class' => 'form-select',
                    ]) ?>
                <?php else: ?>
                    <?= $form->field($model, 'section_id')->input('number', [
                        'min' => 1,
                        'id' => 'tt-section-id',
                        'placeholder' => 'Optional Section ID',
                    ]) ?>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <?php if (!empty($lists['subject'])): ?>
                    <?= $form->field($model, 'subject_id')->dropDownList($lists['subject'], [
                        'prompt' => 'Select Subject',
                        'id' => 'tt-subject-id',
                        'class' => 'form-select',
                    ]) ?>
                <?php else: ?>
                    <?= $form->field($model, 'subject_id')->input('number', ['min' => 1, 'id' => 'tt-subject-id']) ?>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <?php if (!empty($lists['employee'])): ?>
                    <?= $form->field($model, 'employee_id')->dropDownList($lists['employee'], [
                        'prompt' => 'Select Teacher / Faculty',
                        'id' => 'tt-employee-id',
                        'class' => 'form-select',
                    ]) ?>
                <?php else: ?>
                    <?= $form->field($model, 'employee_id')->input('number', ['min' => 1, 'id' => 'tt-employee-id']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 2: WEEKLY SCHEDULE & CLASS ROOM -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-regular fa-clock text-success me-2"></i>Weekly Timing &amp; Class Type
        </h5>
        <?php if ($isNew): ?>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 tt-day-preset" data-days="1,2,3,4,5">Mon–Fri</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 tt-day-preset" data-days="1,2,3,4,5,6">Mon–Sat</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 tt-day-preset" data-days="1,3,5">MWF</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 tt-day-preset" data-days="2,4,6">TTS</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body p-4">
        <?php if ($isNew): ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Days of Week <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (CoachingTimetable::dayOptions() as $dayNum => $dayName): ?>
                        <?php $checked = in_array($dayNum, $selectedDays, true); ?>
                        <input type="checkbox"
                               class="btn-check tt-day-checkbox"
                               name="CoachingTimetable[days_of_week][]"
                               id="tt-day-<?= $dayNum ?>"
                               value="<?= $dayNum ?>"
                               <?= $checked ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary rounded-pill px-3" for="tt-day-<?= $dayNum ?>">
                            <?= Html::encode($dayName) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="form-text">Select one or multiple weekdays to schedule recurring weekly classes in one step.</div>
            </div>
            <?= Html::activeHiddenInput($model, 'day_of_week', ['id' => 'tt-primary-day']) ?>
        <?php else: ?>
            <div class="row g-3 mb-2">
                <div class="col-md-4">
                    <?= $form->field($model, 'day_of_week')->dropDownList(CoachingTimetable::dayOptions(), [
                        'class' => 'form-select',
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-3">
                <?= $form->field($model, 'start_time')->input('time', [
                    'value' => $model->getInputTime('start_time'),
                    'class' => 'form-control',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'end_time')->input('time', [
                    'value' => $model->getInputTime('end_time'),
                    'class' => 'form-control',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'class_type')->dropDownList(CoachingTimetable::classTypeOptions(), [
                    'class' => 'form-select',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?php if (!empty($lists['room'])): ?>
                    <?= $form->field($model, 'room_id')->dropDownList($lists['room'], [
                        'prompt' => 'Optional Room',
                        'class' => 'form-select',
                    ]) ?>
                <?php else: ?>
                    <?= $form->field($model, 'room_id')->input('number', [
                        'min' => 1,
                        'placeholder' => 'Optional Room #',
                        'class' => 'form-control',
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 3: EFFECTIVE PERIOD & STATUS -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-regular fa-calendar-check text-info me-2"></i>Effective Dates &amp; Status
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <?= $form->field($model, 'effective_from')->input('date', ['class' => 'form-control']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'effective_to')->input('date', ['class' => 'form-control']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'status')->dropDownList(CoachingTimetable::statusOptions(), [
                    'class' => 'form-select',
                ]) ?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mb-4">
    <?= Html::a('Cancel', ['index', 'course_id' => $model->course_id, 'batch_id' => $model->batch_id], [
        'class' => 'btn btn-outline-secondary px-4',
    ]) ?>
    <?= Html::submitButton(
        $isNew ? '<i class="fa-solid fa-check me-1"></i> Create Timetable Slot' : '<i class="fa-solid fa-floppy-disk me-1"></i> Update Timetable Slot',
        ['class' => 'btn btn-primary px-4']
    ) ?>
</div>

<?php ActiveForm::end(); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const meta = <?= json_encode($placementMeta) ?>;
    const courseEl = document.getElementById('tt-course-id');
    const batchEl = document.getElementById('tt-batch-id');
    const sectionEl = document.getElementById('tt-section-id');
    const subjectEl = document.getElementById('tt-subject-id');
    const employeeEl = document.getElementById('tt-employee-id');

    function filterDependentFields() {
        const cId = courseEl && courseEl.value ? parseInt(courseEl.value, 10) : null;

        if (batchEl && batchEl.tagName === 'SELECT' && Object.keys(meta.batchByCourse || {}).length > 0) {
            Array.from(batchEl.options).forEach(function (opt) {
                if (!opt.value) return;
                opt.hidden = Boolean(cId && meta.batchByCourse[opt.value] !== cId);
            });
            if (batchEl.value && cId && meta.batchByCourse[batchEl.value] !== cId) {
                batchEl.value = '';
            }
        }

        const bId = batchEl && batchEl.value ? parseInt(batchEl.value, 10) : null;

        if (sectionEl && sectionEl.tagName === 'SELECT' && Object.keys(meta.sectionByCourseAndBatch || {}).length > 0) {
            Array.from(sectionEl.options).forEach(function (opt) {
                if (!opt.value) return;
                const pair = meta.sectionByCourseAndBatch[opt.value];
                opt.hidden = Boolean((cId && (!pair || pair[0] !== cId)) || (bId && (!pair || pair[1] !== bId)));
            });
            if (sectionEl.value) {
                const pair = meta.sectionByCourseAndBatch[sectionEl.value];
                if ((cId && (!pair || pair[0] !== cId)) || (bId && (!pair || pair[1] !== bId))) {
                    sectionEl.value = '';
                }
            }
        }

        if (subjectEl && subjectEl.tagName === 'SELECT' && Object.keys(meta.subjectByCourse || {}).length > 0) {
            Array.from(subjectEl.options).forEach(function (opt) {
                if (!opt.value) return;
                const subCourse = meta.subjectByCourse[opt.value];
                opt.hidden = Boolean(cId && subCourse && subCourse !== cId);
            });
            if (subjectEl.value && cId && meta.subjectByCourse[subjectEl.value] && meta.subjectByCourse[subjectEl.value] !== cId) {
                subjectEl.value = '';
            }
        }
    }

    function autoSuggestTeacher() {
        if (!employeeEl || employeeEl.value) return;
        const cId = courseEl ? courseEl.value : '';
        const bId = batchEl ? batchEl.value : '';
        const secId = sectionEl && sectionEl.value ? sectionEl.value : '0';
        const subId = subjectEl ? subjectEl.value : '';
        if (!cId || !bId || !subId) return;

        const map = meta.assignedTeachers || {};
        const suggested = map[cId + ':' + bId + ':' + secId + ':' + subId] || map[cId + ':' + bId + ':0:' + subId];
        if (suggested) {
            employeeEl.value = String(suggested);
        }
    }

    [courseEl, batchEl].forEach(function (el) {
        if (el) el.addEventListener('change', filterDependentFields);
    });
    [courseEl, batchEl, sectionEl, subjectEl].forEach(function (el) {
        if (el) el.addEventListener('change', autoSuggestTeacher);
    });
    filterDependentFields();

    // Sync day preset buttons & hidden primary day_of_week on Create form
    const dayCheckboxes = document.querySelectorAll('.tt-day-checkbox');
    const primaryDayInput = document.getElementById('tt-primary-day');

    function syncPrimaryDay() {
        if (!primaryDayInput) return;
        const checked = Array.from(dayCheckboxes).filter(function (cb) { return cb.checked; });
        if (checked.length > 0) {
            primaryDayInput.value = checked[0].value;
        }
    }

    dayCheckboxes.forEach(function (cb) {
        cb.addEventListener('change', syncPrimaryDay);
    });

    document.querySelectorAll('.tt-day-preset').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targets = btn.getAttribute('data-days').split(',');
            dayCheckboxes.forEach(function (cb) {
                cb.checked = targets.includes(cb.value);
            });
            syncPrimaryDay();
        });
    });
});
</script>
