<?php

use app\modules\timetable\models\CoachingTimetable;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $context */
/** @var bool $isReady */
/** @var CoachingTimetable[] $rows */
/** @var array<int|string, string> $rowErrors */
/** @var array $lists */
/** @var array $placementMeta */

$this->title = 'Weekly Timetable Builder';
$this->params['breadcrumbs'][] = ['label' => 'Weekly Timetable', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$days = CoachingTimetable::dayOptions();

// Organize loaded/submitted rows by day_of_week preserving original index for error mapping
$rowsByDay = [];
foreach ($days as $dayNum => $dayLabel) {
    $rowsByDay[$dayNum] = [];
}
foreach ($rows as $rowKey => $rowModel) {
    $d = (int) ($rowModel->day_of_week ?: 1);
    if (!isset($rowsByDay[$d])) {
        $d = 1;
    }
    $rowsByDay[$d][$rowKey] = $rowModel;
}

// Filter subject options to the chosen course_id when possible
$courseSubjectOptions = [];
if ($isReady && !empty($lists['subject'])) {
    foreach ($lists['subject'] as $subId => $subLabel) {
        $mappedCourse = $placementMeta['subjectByCourse'][$subId] ?? null;
        if ($mappedCourse === null || (int) $mappedCourse === (int) $context['course_id']) {
            $courseSubjectOptions[$subId] = $subLabel;
        }
    }
}
if (empty($courseSubjectOptions)) {
    $courseSubjectOptions = $lists['subject'] ?? [];
}
?>

<div class="timetable-builder-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
            <p class="text-muted mb-0">Create or update a complete Monday–Sunday schedule for a coaching batch in one view.</p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-calendar-days me-1"></i> View Weekly Grid', array_filter([
                'index',
                'academic_session_id' => $context['academic_session_id'],
                'course_id' => $context['course_id'],
                'batch_id' => $context['batch_id'],
                'section_id' => $context['section_id'],
            ]), ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Single Slot Form', ['create'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <!-- STEP 1: SELECT PLACEMENT CONTEXT -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h6 class="fw-bold mb-0">
                <i class="fa-solid fa-sliders text-primary me-2"></i>1. Select Batch &amp; Effective Period
            </h6>
        </div>
        <div class="card-body p-4">
            <?= Html::beginForm(['builder'], 'get', ['class' => 'row g-3 align-items-end']) ?>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Academic Session <span class="text-danger">*</span></label>
                    <?php if (!empty($lists['academic_session'])): ?>
                        <?= Html::dropDownList('academic_session_id', $context['academic_session_id'], $lists['academic_session'], [
                            'class' => 'form-select',
                            'required' => true,
                        ]) ?>
                    <?php else: ?>
                        <?= Html::input('number', 'academic_session_id', $context['academic_session_id'], [
                            'class' => 'form-control',
                            'min' => 1,
                            'required' => true,
                        ]) ?>
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Course <span class="text-danger">*</span></label>
                    <?= Html::dropDownList('course_id', $context['course_id'], $lists['course'], [
                        'id' => 'builder-course-id',
                        'class' => 'form-select',
                        'prompt' => 'Select Course',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Batch <span class="text-danger">*</span></label>
                    <?= Html::dropDownList('batch_id', $context['batch_id'], $lists['batch'], [
                        'id' => 'builder-batch-id',
                        'class' => 'form-select',
                        'prompt' => 'Select Batch',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Section <span class="text-muted fw-normal">(Optional)</span></label>
                    <?= Html::dropDownList('section_id', $context['section_id'], $lists['section'], [
                        'id' => 'builder-section-id',
                        'class' => 'form-select',
                        'prompt' => 'Whole Batch',
                    ]) ?>
                </div>

                <div class="col-md-2">
                    <?= Html::submitButton('<i class="fa-solid fa-calendar-check me-1"></i> Load Week', [
                        'class' => 'btn btn-primary w-100',
                    ]) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?php if (!$isReady): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-5 text-center text-muted">
                <i class="fa-solid fa-arrow-up-long text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold text-dark">Choose an Academic Session, Course, and Batch above</h6>
                <p class="mb-0 small">Once loaded, you can add, edit, copy, or remove periods across Monday–Sunday and save the entire weekly timetable together.</p>
            </div>
        </div>
    <?php else: ?>
        <!-- STEP 2: MONDAY–SUNDAY WEEKLY SCHEDULE EDITOR -->
        <?= Html::beginForm([
            'builder',
            'academic_session_id' => $context['academic_session_id'],
            'course_id' => $context['course_id'],
            'batch_id' => $context['batch_id'],
            'section_id' => $context['section_id'],
        ], 'post', ['id' => 'weekly-builder-form']) ?>

            <?= Html::hiddenInput('context[academic_session_id]', $context['academic_session_id']) ?>
            <?= Html::hiddenInput('context[course_id]', $context['course_id']) ?>
            <?= Html::hiddenInput('context[batch_id]', $context['batch_id']) ?>
            <?= Html::hiddenInput('context[section_id]', $context['section_id'] ?? '') ?>
            <?= Html::hiddenInput('deleted_ids', '', ['id' => 'builder-deleted-ids']) ?>

            <!-- Default Effective Date Range Bar -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body py-3 px-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="small text-muted">Editing Weekly Timetable For</div>
                            <div class="fw-bold text-dark">
                                <?= Html::encode($lists['course'][$context['course_id']] ?? ('Course #' . $context['course_id'])) ?>
                                <span class="text-muted mx-1">/</span>
                                <?= Html::encode($lists['batch'][$context['batch_id']] ?? ('Batch #' . $context['batch_id'])) ?>
                                <?php if ($context['section_id']): ?>
                                    <span class="badge bg-primary-subtle text-primary ms-1">
                                        <?= Html::encode($lists['section'][$context['section_id']] ?? ('Section #' . $context['section_id'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary ms-1">Whole Batch</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Effective From <span class="text-danger">*</span></label>
                            <?= Html::input('date', 'context[effective_from]', $context['effective_from'], [
                                'class' => 'form-control form-control-sm',
                                'required' => true,
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Effective To <span class="text-muted fw-normal">(Optional)</span></label>
                            <?= Html::input('date', 'context[effective_to]', $context['effective_to'], [
                                'class' => 'form-control form-control-sm',
                            ]) ?>
                        </div>
                        <div class="col-md-2 text-md-end">
                            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> Save Week', [
                                'class' => 'btn btn-success w-100',
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Day Sections (Monday .. Sunday) -->
            <?php foreach ($days as $dayNum => $dayName): ?>
                <?php $dayRows = $rowsByDay[$dayNum] ?? []; ?>
                <div class="card border-0 shadow-sm rounded-4 mb-3 builder-day-card" data-day="<?= $dayNum ?>">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill px-3 py-2"><?= Html::encode($dayName) ?></span>
                            <span class="text-muted small builder-day-count" id="day-count-<?= $dayNum ?>">
                                <?= count($dayRows) ?> <?= count($dayRows) === 1 ? 'slot' : 'slots' ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-regular fa-copy me-1"></i> Copy <?= Html::encode(CoachingTimetable::dayShortOptions()[$dayNum]) ?> to...
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <?php foreach ($days as $targetDayNum => $targetDayName): ?>
                                        <?php if ($targetDayNum === $dayNum) continue; ?>
                                        <li>
                                            <button type="button" class="dropdown-item builder-copy-day-btn"
                                                    data-from-day="<?= $dayNum ?>" data-to-day="<?= $targetDayNum ?>">
                                                Copy to <?= Html::encode($targetDayName) ?>
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary builder-add-slot-btn" data-day="<?= $dayNum ?>">
                                <i class="fa-solid fa-plus me-1"></i> Add Period
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div class="builder-rows-container" id="builder-rows-day-<?= $dayNum ?>">
                            <?php foreach ($dayRows as $rowKey => $slot): ?>
                                <?php $err = $rowErrors[$rowKey] ?? null; ?>
                                <div class="builder-slot-row border rounded-3 p-2 mb-2 <?= $err ? 'border-danger bg-danger-subtle' : 'bg-light-subtle' ?>" data-row-index="<?= Html::encode((string) $rowKey) ?>">
                                    <?= Html::hiddenInput("slots[{$rowKey}][id]", $slot->id ?: '', ['class' => 'slot-id-input']) ?>
                                    <?= Html::hiddenInput("slots[{$rowKey}][day_of_week]", $dayNum, ['class' => 'slot-day-input']) ?>

                                    <div class="row g-2 align-items-center">
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small text-muted mb-0" style="font-size:11px;">Start</label>
                                            <?= Html::input('time', "slots[{$rowKey}][start_time]", $slot->getInputTime('start_time'), [
                                                'class' => 'form-control form-control-sm slot-start',
                                                'required' => true,
                                            ]) ?>
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small text-muted mb-0" style="font-size:11px;">End</label>
                                            <?= Html::input('time', "slots[{$rowKey}][end_time]", $slot->getInputTime('end_time'), [
                                                'class' => 'form-control form-control-sm slot-end',
                                                'required' => true,
                                            ]) ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-0" style="font-size:11px;">Subject</label>
                                            <?php if (!empty($courseSubjectOptions)): ?>
                                                <?= Html::dropDownList("slots[{$rowKey}][subject_id]", $slot->subject_id, $courseSubjectOptions, [
                                                    'class' => 'form-select form-select-sm slot-subject',
                                                    'prompt' => 'Select Subject',
                                                    'required' => true,
                                                ]) ?>
                                            <?php else: ?>
                                                <?= Html::input('number', "slots[{$rowKey}][subject_id]", $slot->subject_id, [
                                                    'class' => 'form-control form-control-sm slot-subject',
                                                    'placeholder' => 'Subject ID',
                                                    'min' => 1,
                                                    'required' => true,
                                                ]) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small text-muted mb-0" style="font-size:11px;">Teacher</label>
                                            <?php if (!empty($lists['employee'])): ?>
                                                <?= Html::dropDownList("slots[{$rowKey}][employee_id]", $slot->employee_id, $lists['employee'], [
                                                    'class' => 'form-select form-select-sm slot-employee',
                                                    'prompt' => 'Select Teacher',
                                                    'required' => true,
                                                ]) ?>
                                            <?php else: ?>
                                                <?= Html::input('number', "slots[{$rowKey}][employee_id]", $slot->employee_id, [
                                                    'class' => 'form-control form-control-sm slot-employee',
                                                    'placeholder' => 'Employee ID',
                                                    'min' => 1,
                                                    'required' => true,
                                                ]) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-6 col-md-1">
                                            <label class="form-label small text-muted mb-0" style="font-size:11px;">Type</label>
                                            <?= Html::dropDownList("slots[{$rowKey}][class_type]", $slot->class_type ?: CoachingTimetable::TYPE_THEORY, CoachingTimetable::classTypeOptions(), [
                                                'class' => 'form-select form-select-sm slot-type',
                                            ]) ?>
                                        </div>
                                        <div class="col-4 col-md-1">
                                            <label class="form-label small text-muted mb-0" style="font-size:11px;">Room</label>
                                            <?php if (!empty($lists['room'])): ?>
                                                <?= Html::dropDownList("slots[{$rowKey}][room_id]", $slot->room_id, $lists['room'], [
                                                    'class' => 'form-select form-select-sm slot-room',
                                                    'prompt' => '-',
                                                ]) ?>
                                            <?php else: ?>
                                                <?= Html::input('number', "slots[{$rowKey}][room_id]", $slot->room_id, [
                                                    'class' => 'form-control form-control-sm slot-room',
                                                    'placeholder' => '#',
                                                    'min' => 1,
                                                ]) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-2 col-md-1 d-flex align-items-end justify-content-end gap-1 pt-3">
                                            <?= Html::hiddenInput("slots[{$rowKey}][status]", $slot->status ?? CoachingTimetable::STATUS_ACTIVE, ['class' => 'slot-status']) ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger builder-remove-row-btn" title="Remove slot">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php if ($err): ?>
                                        <div class="text-danger small fw-semibold mt-1">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i><?= Html::encode($err) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="text-muted small text-center py-2 builder-empty-msg <?= !empty($dayRows) ? 'd-none' : '' ?>" id="builder-empty-<?= $dayNum ?>">
                            No periods scheduled on <?= Html::encode($dayName) ?>. Click <strong>+ Add Period</strong> to add one.
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="d-flex justify-content-end gap-2 mt-4 mb-5">
                <?= Html::a('Cancel', array_filter([
                    'index',
                    'academic_session_id' => $context['academic_session_id'],
                    'course_id' => $context['course_id'],
                    'batch_id' => $context['batch_id'],
                ]), ['class' => 'btn btn-outline-secondary px-4']) ?>
                <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> Save Weekly Timetable', [
                    'class' => 'btn btn-success px-4',
                ]) ?>
            </div>
        <?= Html::endForm() ?>

        <!-- Hidden Row Template for Client-Side Period Addition -->
        <template id="builder-row-template">
            <div class="builder-slot-row border rounded-3 p-2 mb-2 bg-light-subtle">
                <input type="hidden" name="slots[__IDX__][id]" value="" class="slot-id-input">
                <input type="hidden" name="slots[__IDX__][day_of_week]" value="__DAY__" class="slot-day-input">
                <div class="row g-2 align-items-center">
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-0" style="font-size:11px;">Start</label>
                        <input type="time" name="slots[__IDX__][start_time]" value="__START__" class="form-control form-control-sm slot-start" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-0" style="font-size:11px;">End</label>
                        <input type="time" name="slots[__IDX__][end_time]" value="__END__" class="form-control form-control-sm slot-end" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-0" style="font-size:11px;">Subject</label>
                        <?php if (!empty($courseSubjectOptions)): ?>
                            <?= Html::dropDownList('slots[__IDX__][subject_id]', null, $courseSubjectOptions, [
                                'class' => 'form-select form-select-sm slot-subject',
                                'prompt' => 'Select Subject',
                                'required' => true,
                            ]) ?>
                        <?php else: ?>
                            <input type="number" name="slots[__IDX__][subject_id]" class="form-control form-control-sm slot-subject" placeholder="Subject ID" min="1" required>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-0" style="font-size:11px;">Teacher</label>
                        <?php if (!empty($lists['employee'])): ?>
                            <?= Html::dropDownList('slots[__IDX__][employee_id]', null, $lists['employee'], [
                                'class' => 'form-select form-select-sm slot-employee',
                                'prompt' => 'Select Teacher',
                                'required' => true,
                            ]) ?>
                        <?php else: ?>
                            <input type="number" name="slots[__IDX__][employee_id]" class="form-control form-control-sm slot-employee" placeholder="Employee ID" min="1" required>
                        <?php endif; ?>
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label small text-muted mb-0" style="font-size:11px;">Type</label>
                        <?= Html::dropDownList('slots[__IDX__][class_type]', CoachingTimetable::TYPE_THEORY, CoachingTimetable::classTypeOptions(), [
                            'class' => 'form-select form-select-sm slot-type',
                        ]) ?>
                    </div>
                    <div class="col-4 col-md-1">
                        <label class="form-label small text-muted mb-0" style="font-size:11px;">Room</label>
                        <?php if (!empty($lists['room'])): ?>
                            <?= Html::dropDownList('slots[__IDX__][room_id]', null, $lists['room'], [
                                'class' => 'form-select form-select-sm slot-room',
                                'prompt' => '-',
                            ]) ?>
                        <?php else: ?>
                            <input type="number" name="slots[__IDX__][room_id]" class="form-control form-control-sm slot-room" placeholder="#" min="1">
                        <?php endif; ?>
                    </div>
                    <div class="col-2 col-md-1 d-flex align-items-end justify-content-end gap-1 pt-3">
                        <input type="hidden" name="slots[__IDX__][status]" value="1" class="slot-status">
                        <button type="button" class="btn btn-sm btn-outline-danger builder-remove-row-btn" title="Remove slot">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const meta = <?= json_encode($placementMeta) ?>;
    const courseSelect = document.getElementById('builder-course-id');
    const batchSelect = document.getElementById('builder-batch-id');
    const sectionSelect = document.getElementById('builder-section-id');

    function syncContextSelectors() {
        const cId = courseSelect && courseSelect.value ? parseInt(courseSelect.value, 10) : null;
        if (batchSelect && Object.keys(meta.batchByCourse || {}).length > 0) {
            Array.from(batchSelect.options).forEach(function (opt) {
                if (!opt.value) return;
                opt.hidden = Boolean(cId && meta.batchByCourse[opt.value] !== cId);
            });
            if (batchSelect.value && cId && meta.batchByCourse[batchSelect.value] !== cId) {
                batchSelect.value = '';
            }
        }
        const bId = batchSelect && batchSelect.value ? parseInt(batchSelect.value, 10) : null;
        if (sectionSelect && Object.keys(meta.sectionByCourseAndBatch || {}).length > 0) {
            Array.from(sectionSelect.options).forEach(function (opt) {
                if (!opt.value) return;
                const pair = meta.sectionByCourseAndBatch[opt.value];
                opt.hidden = Boolean((cId && (!pair || pair[0] !== cId)) || (bId && (!pair || pair[1] !== bId)));
            });
            if (sectionSelect.value) {
                const pair = meta.sectionByCourseAndBatch[sectionSelect.value];
                if ((cId && (!pair || pair[0] !== cId)) || (bId && (!pair || pair[1] !== bId))) {
                    sectionSelect.value = '';
                }
            }
        }
    }

    if (courseSelect) courseSelect.addEventListener('change', syncContextSelectors);
    if (batchSelect) batchSelect.addEventListener('change', syncContextSelectors);
    syncContextSelectors();

    const rowTemplate = document.getElementById('builder-row-template');
    if (!rowTemplate) return;

    let nextIdx = 1000;
    const deletedIds = [];
    const deletedInput = document.getElementById('builder-deleted-ids');
    const activeCourseId = <?= (int) ($context['course_id'] ?? 0) ?>;
    const activeBatchId = <?= (int) ($context['batch_id'] ?? 0) ?>;
    const activeSectionId = <?= (int) ($context['section_id'] ?? 0) ?>;

    function addHour(hhmm) {
        if (!hhmm || !/^\d{2}:\d{2}/.test(hhmm)) return '10:00';
        const parts = hhmm.split(':');
        const h = Math.min(23, parseInt(parts[0], 10) + 1);
        return String(h).padStart(2, '0') + ':' + parts[1].slice(0, 2);
    }

    function refreshDayCount(day) {
        const container = document.getElementById('builder-rows-day-' + day);
        const badge = document.getElementById('day-count-' + day);
        const emptyMsg = document.getElementById('builder-empty-' + day);
        if (!container) return;
        const count = container.querySelectorAll('.builder-slot-row').length;
        if (badge) badge.textContent = count + (count === 1 ? ' slot' : ' slots');
        if (emptyMsg) emptyMsg.classList.toggle('d-none', count > 0);
    }

    function appendPeriodRow(day, preset) {
        const container = document.getElementById('builder-rows-day-' + day);
        if (!container) return;

        let startTime = '09:00';
        const existingEnds = container.querySelectorAll('.slot-end');
        if (existingEnds.length > 0) {
            const lastEnd = existingEnds[existingEnds.length - 1].value;
            if (lastEnd) startTime = lastEnd.slice(0, 5);
        }
        let endTime = addHour(startTime);

        if (preset) {
            startTime = preset.start || startTime;
            endTime = preset.end || endTime;
        }

        const idx = 'new_' + (++nextIdx);
        const html = rowTemplate.innerHTML
            .replaceAll('__IDX__', idx)
            .replaceAll('__DAY__', String(day))
            .replaceAll('__START__', startTime)
            .replaceAll('__END__', endTime);

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const rowEl = wrapper.firstElementChild;

        if (preset) {
            if (preset.subject) rowEl.querySelector('.slot-subject').value = preset.subject;
            if (preset.employee) rowEl.querySelector('.slot-employee').value = preset.employee;
            if (preset.type) rowEl.querySelector('.slot-type').value = preset.type;
            if (preset.room) rowEl.querySelector('.slot-room').value = preset.room;
        }

        container.appendChild(rowEl);
        refreshDayCount(day);
    }

    document.querySelectorAll('.builder-add-slot-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            appendPeriodRow(btn.getAttribute('data-day'), null);
        });
    });

    document.querySelectorAll('.builder-copy-day-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const fromDay = btn.getAttribute('data-from-day');
            const toDay = btn.getAttribute('data-to-day');
            const fromContainer = document.getElementById('builder-rows-day-' + fromDay);
            if (!fromContainer) return;

            fromContainer.querySelectorAll('.builder-slot-row').forEach(function (srcRow) {
                appendPeriodRow(toDay, {
                    start: srcRow.querySelector('.slot-start').value,
                    end: srcRow.querySelector('.slot-end').value,
                    subject: srcRow.querySelector('.slot-subject').value,
                    employee: srcRow.querySelector('.slot-employee').value,
                    type: srcRow.querySelector('.slot-type').value,
                    room: srcRow.querySelector('.slot-room').value
                });
            });
        });
    });

    document.getElementById('weekly-builder-form').addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.builder-remove-row-btn');
        if (!removeBtn) return;

        const rowEl = removeBtn.closest('.builder-slot-row');
        const dayCard = removeBtn.closest('.builder-day-card');
        const idInput = rowEl.querySelector('.slot-id-input');
        if (idInput && idInput.value) {
            deletedIds.push(idInput.value);
            deletedInput.value = deletedIds.join(',');
        }
        rowEl.remove();
        if (dayCard) refreshDayCount(dayCard.getAttribute('data-day'));
    });

    // Auto-suggest assigned teacher when subject changes
    document.getElementById('weekly-builder-form').addEventListener('change', function (e) {
        if (!e.target.classList.contains('slot-subject')) return;
        const rowEl = e.target.closest('.builder-slot-row');
        const empInput = rowEl ? rowEl.querySelector('.slot-employee') : null;
        if (!empInput || empInput.value) return;

        const subId = e.target.value;
        const assignedMap = meta.assignedTeachers || {};
        const exactKey = activeCourseId + ':' + activeBatchId + ':' + activeSectionId + ':' + subId;
        const batchKey = activeCourseId + ':' + activeBatchId + ':0:' + subId;
        const suggestedEmp = assignedMap[exactKey] || assignedMap[batchKey];
        if (suggestedEmp) {
            empInput.value = String(suggestedEmp);
        }
    });
});
</script>
