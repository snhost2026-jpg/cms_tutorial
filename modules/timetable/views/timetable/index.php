<?php

use app\modules\timetable\models\CoachingTimetable;
use app\modules\timetable\models\CoachingTimetableSearch;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var CoachingTimetableSearch $searchModel */
/** @var array $weeklyData */
/** @var array $lists */
/** @var array $placementMeta */
/** @var bool $canManage */

$this->title = 'Weekly Timetable';
$this->params['breadcrumbs'][] = $this->title;

$days = CoachingTimetable::dayOptions();
$todayIsoDay = (int) date('N'); // 1 (Monday) .. 7 (Sunday)
$slots = $weeklyData['slots'];
$grid = $weeklyData['grid'];
$byDay = $weeklyData['byDay'];
$stats = $weeklyData['stats'];

$labelOf = static function (string $key, $id, string $fallbackPrefix) use ($lists): string {
    if ($id === null || $id === '') {
        return '-';
    }
    return $lists[$key][$id] ?? ($fallbackPrefix . ' #' . $id);
};

$contextParams = array_filter([
    'academic_session_id' => $searchModel->academic_session_id,
    'course_id' => $searchModel->course_id,
    'batch_id' => $searchModel->batch_id,
    'section_id' => $searchModel->section_id,
], static function ($v) {
    return $v !== null && $v !== '';
});
?>

<style>
.tt-kpi-card {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    background: #fff;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
}
.tt-kpi-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.tt-matrix-table {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1080px;
}
.tt-matrix-table thead th {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 12px 10px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #334155;
    vertical-align: middle;
}
.tt-matrix-table thead th.tt-today-col {
    background: #eff6ff;
    color: #1d4ed8;
    border-bottom-color: #3b82f6;
}
.tt-matrix-table tbody td {
    border-right: 1px solid #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    padding: 8px;
    vertical-align: top;
    width: 13%;
    background: #ffffff;
}
.tt-matrix-table tbody td.tt-today-col {
    background: #f8fbff;
}
.tt-matrix-table tbody td.tt-time-cell {
    width: 120px;
    min-width: 120px;
    background: #f8fafc;
    font-weight: 600;
    font-size: 12px;
    color: #475569;
    vertical-align: middle;
    text-align: center;
    border-right: 2px solid #e2e8f0;
}
.tt-slot-card {
    border-radius: 10px;
    padding: 9px 10px;
    margin-bottom: 6px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-left-width: 4px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    position: relative;
}
.tt-slot-card:last-child {
    margin-bottom: 0;
}
.tt-slot-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
}
.tt-slot-card.tt-inactive {
    opacity: 0.6;
    filter: grayscale(0.35);
}
.tt-slot-actions {
    opacity: 0;
    transition: opacity 0.15s ease;
}
.tt-slot-card:hover .tt-slot-actions {
    opacity: 1;
}
.tt-empty-add {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 58px;
    border-radius: 8px;
    border: 1px dashed transparent;
    color: transparent;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.15s ease;
}
.tt-matrix-table tbody td:hover .tt-empty-add {
    border-color: #cbd5e1;
    color: #64748b;
}
.tt-empty-add:hover {
    background: #eff6ff;
    border-color: #60a5fa !important;
    color: #2563eb !important;
}
@media print {
    .crm-sidebar, .crm-topbar, .tt-no-print, .tt-slot-actions, .tt-empty-add, .breadcrumb {
        display: none !important;
    }
    .crm-main {
        padding: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #cbd5e1 !important;
    }
    .tt-matrix-table {
        min-width: 100% !important;
        font-size: 11px !important;
    }
}
</style>

<div class="timetable-weekly-page">
    <!-- Top Action Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 tt-no-print">
        <div>
            <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
            <p class="text-muted mb-0">View, filter, create, and update weekly coaching class schedules across batches and faculty.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Print
            </button>
            <?= Html::a('<i class="fa-solid fa-table-list me-1"></i> All Slots', array_merge(['list'], $contextParams), [
                'class' => 'btn btn-outline-secondary',
            ]) ?>
            <?php if ($canManage): ?>
                <?= Html::a('<i class="fa-solid fa-calendar-week me-1"></i> Weekly Builder', array_merge(['builder'], $contextParams), [
                    'class' => 'btn btn-outline-primary',
                ]) ?>
                <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Add Class Slot', array_merge(['create'], $contextParams), [
                    'class' => 'btn btn-primary',
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm tt-no-print" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <!-- KPI Strip -->
    <div class="row g-3 mb-4 tt-no-print">
        <div class="col-6 col-xl-3">
            <div class="tt-kpi-card">
                <div class="tt-kpi-icon bg-primary-subtle text-primary">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div class="text-muted small">Weekly Classes</div>
                    <div class="fs-4 fw-bold lh-1 mt-1"><?= (int) $stats['totalClasses'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="tt-kpi-card">
                <div class="tt-kpi-icon bg-success-subtle text-success">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <div class="text-muted small">Scheduled Hours</div>
                    <div class="fs-4 fw-bold lh-1 mt-1"><?= Html::encode((string) $stats['totalHours']) ?> <span class="fs-6 fw-normal text-muted">hrs/wk</span></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="tt-kpi-card">
                <div class="tt-kpi-icon bg-info-subtle text-info-emphasis">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div>
                    <div class="text-muted small">Active Faculty</div>
                    <div class="fs-4 fw-bold lh-1 mt-1"><?= (int) $stats['uniqueTeachers'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="tt-kpi-card">
                <div class="tt-kpi-icon bg-warning-subtle text-warning-emphasis">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div>
                    <div class="text-muted small">Subjects Covered</div>
                    <div class="fs-4 fw-bold lh-1 mt-1"><?= (int) $stats['uniqueSubjects'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 tt-no-print">
        <div class="card-body p-4">
            <?= Html::beginForm(['index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Session</label>
                    <?php if (!empty($lists['academic_session'])): ?>
                        <?= Html::dropDownList('academic_session_id', $searchModel->academic_session_id, $lists['academic_session'], [
                            'class' => 'form-select form-select-sm',
                            'prompt' => 'All Sessions',
                        ]) ?>
                    <?php else: ?>
                        <?= Html::input('number', 'academic_session_id', $searchModel->academic_session_id, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => 'e.g. ' . date('Y'),
                            'min' => 1,
                        ]) ?>
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Course</label>
                    <?= Html::dropDownList('course_id', $searchModel->course_id, $lists['course'], [
                        'id' => 'tt-filter-course',
                        'class' => 'form-select form-select-sm',
                        'prompt' => 'All Courses',
                    ]) ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Batch</label>
                    <?= Html::dropDownList('batch_id', $searchModel->batch_id, $lists['batch'], [
                        'id' => 'tt-filter-batch',
                        'class' => 'form-select form-select-sm',
                        'prompt' => 'All Batches',
                    ]) ?>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Section</label>
                    <?= Html::dropDownList('section_id', $searchModel->section_id, $lists['section'], [
                        'id' => 'tt-filter-section',
                        'class' => 'form-select form-select-sm',
                        'prompt' => 'All Sections',
                    ]) ?>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Class Type</label>
                    <?= Html::dropDownList('class_type', $searchModel->class_type, CoachingTimetable::classTypeOptions(), [
                        'class' => 'form-select form-select-sm',
                        'prompt' => 'All Types',
                    ]) ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Teacher / Faculty</label>
                    <?= Html::dropDownList('employee_id', $searchModel->employee_id, $lists['employee'], [
                        'class' => 'form-select form-select-sm',
                        'prompt' => 'All Faculty',
                    ]) ?>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Room</label>
                    <?php if (!empty($lists['room'])): ?>
                        <?= Html::dropDownList('room_id', $searchModel->room_id, $lists['room'], [
                            'class' => 'form-select form-select-sm',
                            'prompt' => 'All Rooms',
                        ]) ?>
                    <?php else: ?>
                        <?= Html::input('number', 'room_id', $searchModel->room_id, [
                            'class' => 'form-control form-control-sm',
                            'placeholder' => 'Room #',
                            'min' => 1,
                        ]) ?>
                    <?php endif; ?>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Effective On Date</label>
                    <?= Html::input('date', 'effective_on', $searchModel->effective_on, [
                        'class' => 'form-control form-control-sm',
                    ]) ?>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                    <?= Html::dropDownList('status', (string) $searchModel->status, [
                        '1' => 'Active Only',
                        '0' => 'Inactive Only',
                        'all' => 'All Statuses',
                    ], ['class' => 'form-select form-select-sm']) ?>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <?= Html::submitButton('<i class="fa-solid fa-filter me-1"></i> Apply Filter', [
                        'class' => 'btn btn-primary btn-sm flex-grow-1',
                    ]) ?>
                    <?= Html::a('Reset', ['index'], [
                        'class' => 'btn btn-outline-secondary btn-sm',
                    ]) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <!-- Layout Mode Tabs + Legend -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 tt-no-print">
        <ul class="nav nav-pills gap-1" id="ttViewTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active btn-sm px-3 py-2 rounded-pill" id="matrix-tab" data-bs-toggle="pill" data-bs-target="#tt-matrix-pane" type="button" role="tab">
                    <i class="fa-solid fa-table-cells me-1"></i> Weekly Grid
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm px-3 py-2 rounded-pill" id="daycards-tab" data-bs-toggle="pill" data-bs-target="#tt-daycards-pane" type="button" role="tab">
                    <i class="fa-solid fa-grip-vertical me-1"></i> Day-wise Schedule
                </button>
            </li>
        </ul>

        <div class="d-flex align-items-center gap-2 flex-wrap small">
            <?php foreach (CoachingTimetable::classTypeOptions() as $typeLabel): ?>
                <?php $meta = CoachingTimetable::classTypeMeta($typeLabel); ?>
                <span class="badge border <?= $meta['badge'] ?> fw-medium px-2 py-1">
                    <i class="fa-solid <?= $meta['icon'] ?> me-1"></i><?= Html::encode($typeLabel) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($weeklyData['records'])): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-5 text-center">
                <div class="mb-3 text-primary opacity-75" style="font-size: 44px;">
                    <i class="fa-regular fa-calendar-plus"></i>
                </div>
                <h5 class="fw-bold mb-2">No Weekly Timetable Slots Found</h5>
                <p class="text-muted mb-4">
                    No classes match the selected filters yet. Start by building a batch's weekly timetable or adding a class slot.
                </p>
                <?php if ($canManage): ?>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <?= Html::a('<i class="fa-solid fa-calendar-week me-1"></i> Open Weekly Builder', array_merge(['builder'], $contextParams), [
                            'class' => 'btn btn-primary',
                        ]) ?>
                        <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Add Single/Recurring Slot', array_merge(['create'], $contextParams), [
                            'class' => 'btn btn-outline-primary',
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="tab-content">
            <!-- TAB 1: TIME-SLOT × DAY WEEKLY MATRIX -->
            <div class="tab-pane fade show active" id="tt-matrix-pane" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table tt-matrix-table mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 120px;">
                                        <i class="fa-regular fa-clock me-1"></i> Time
                                    </th>
                                    <?php foreach ($days as $dayNum => $dayName): ?>
                                        <?php $isToday = ($dayNum === $todayIsoDay); ?>
                                        <th class="<?= $isToday ? 'tt-today-col' : '' ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>
                                                    <?= Html::encode($dayName) ?>
                                                    <?php if ($isToday): ?>
                                                        <span class="badge bg-primary text-white ms-1" style="font-size: 10px;">Today</span>
                                                    <?php endif; ?>
                                                </span>
                                                <?php if ($canManage): ?>
                                                    <a href="<?= Url::to(array_merge(['create', 'day_of_week' => $dayNum], $contextParams)) ?>"
                                                       class="btn btn-sm btn-link p-0 text-decoration-none tt-no-print"
                                                       title="Add class on <?= Html::encode($dayName) ?>">
                                                        <i class="fa-solid fa-circle-plus"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($slots as $slotKey => $slotInfo): ?>
                                    <tr>
                                        <td class="tt-time-cell">
                                            <div><?= Html::encode(CoachingTimetable::formatTimeLabel($slotInfo['start'])) ?></div>
                                            <div class="text-muted fw-normal my-1" style="font-size: 10px;">to</div>
                                            <div><?= Html::encode(CoachingTimetable::formatTimeLabel($slotInfo['end'])) ?></div>
                                        </td>
                                        <?php foreach ($days as $dayNum => $dayName): ?>
                                            <?php
                                            $isToday = ($dayNum === $todayIsoDay);
                                            $cellItems = $grid[$slotKey][$dayNum] ?? [];
                                            ?>
                                            <td class="<?= $isToday ? 'tt-today-col' : '' ?>">
                                                <?php if (!empty($cellItems)): ?>
                                                    <?php foreach ($cellItems as $item): ?>
                                                        <?php
                                                        $meta = CoachingTimetable::classTypeMeta($item->class_type);
                                                        $subjectName = $item->subject ? $item->subject->name : $labelOf('subject', $item->subject_id, 'Subject');
                                                        $batchName = $item->batch ? $item->batch->name : $labelOf('batch', $item->batch_id, 'Batch');
                                                        $sectionName = $item->section ? $item->section->name : null;
                                                        $roomText = $item->room_id ? $labelOf('room', $item->room_id, 'Room') : null;
                                                        ?>
                                                        <div class="tt-slot-card <?= (int) $item->status === CoachingTimetable::STATUS_INACTIVE ? 'tt-inactive' : '' ?>"
                                                             style="background: <?= $meta['softBg'] ?>; border-left-color: <?= $meta['accent'] ?>;">
                                                            <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
                                                                <span class="badge border <?= $meta['badge'] ?>" style="font-size: 10px;">
                                                                    <i class="fa-solid <?= $meta['icon'] ?> me-1"></i><?= Html::encode($item->class_type) ?>
                                                                </span>
                                                                <?php if ($canManage): ?>
                                                                    <div class="tt-slot-actions d-flex gap-1">
                                                                        <a href="<?= Url::to(['update', 'id' => $item->id]) ?>"
                                                                           class="text-secondary small" title="Update slot">
                                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                                        </a>
                                                                        <?= Html::a('<i class="fa-solid fa-trash-can"></i>', ['delete', 'id' => $item->id], [
                                                                            'class' => 'text-danger small ms-1',
                                                                            'title' => 'Delete slot',
                                                                            'data-confirm' => 'Delete this timetable slot?',
                                                                            'data-method' => 'post',
                                                                        ]) ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="fw-bold text-dark lh-sm mb-1" style="font-size: 13px;">
                                                                <a href="<?= Url::to(['view', 'id' => $item->id]) ?>" class="text-dark text-decoration-none">
                                                                    <?= Html::encode($subjectName) ?>
                                                                </a>
                                                            </div>

                                                            <div class="text-muted" style="font-size: 11.5px;">
                                                                <div class="text-truncate" title="<?= Html::encode($item->getTeacherDisplayName()) ?>">
                                                                    <i class="fa-solid fa-chalkboard-user me-1 opacity-75"></i><?= Html::encode($item->getTeacherDisplayName()) ?>
                                                                </div>
                                                                <div class="text-truncate" title="<?= Html::encode($batchName . ($sectionName ? ' · ' . $sectionName : '')) ?>">
                                                                    <i class="fa-solid fa-layer-group me-1 opacity-75"></i><?= Html::encode($batchName) ?><?= $sectionName ? ' · ' . Html::encode($sectionName) : '' ?>
                                                                </div>
                                                                <?php if ($roomText): ?>
                                                                    <div>
                                                                        <i class="fa-solid fa-door-open me-1 opacity-75"></i><?= Html::encode($roomText) ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php elseif ($canManage): ?>
                                                    <a class="tt-empty-add"
                                                       href="<?= Url::to(array_merge([
                                                           'create',
                                                           'day_of_week' => $dayNum,
                                                           'start_time' => substr($slotInfo['start'], 0, 5),
                                                           'end_time' => substr($slotInfo['end'], 0, 5),
                                                       ], $contextParams)) ?>"
                                                       title="Schedule class on <?= Html::encode($dayName) ?> (<?= Html::encode($slotInfo['label']) ?>)">
                                                        <i class="fa-solid fa-plus me-1"></i> Add
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: DAY-WISE SCHEDULE CARDS -->
            <div class="tab-pane fade" id="tt-daycards-pane" role="tabpanel">
                <div class="row g-3">
                    <?php foreach ($days as $dayNum => $dayName): ?>
                        <?php
                        $dayItems = $byDay[$dayNum] ?? [];
                        $isToday = ($dayNum === $todayIsoDay);
                        ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100 <?= $isToday ? 'border border-primary' : '' ?>">
                                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark"><?= Html::encode($dayName) ?></span>
                                        <?php if ($isToday): ?>
                                            <span class="badge bg-primary ms-1">Today</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-secondary border"><?= count($dayItems) ?> classes</span>
                                        <?php if ($canManage): ?>
                                            <a href="<?= Url::to(array_merge(['create', 'day_of_week' => $dayNum], $contextParams)) ?>"
                                               class="btn btn-sm btn-outline-primary py-0 px-2" title="Add slot on <?= Html::encode($dayName) ?>">
                                                <i class="fa-solid fa-plus"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <?php if (empty($dayItems)): ?>
                                        <div class="text-muted small text-center py-4">No classes scheduled on <?= Html::encode($dayName) ?>.</div>
                                    <?php else: ?>
                                        <?php foreach ($dayItems as $item): ?>
                                            <?php
                                            $meta = CoachingTimetable::classTypeMeta($item->class_type);
                                            $subjectName = $item->subject ? $item->subject->name : $labelOf('subject', $item->subject_id, 'Subject');
                                            $batchName = $item->batch ? $item->batch->name : $labelOf('batch', $item->batch_id, 'Batch');
                                            $sectionName = $item->section ? $item->section->name : null;
                                            ?>
                                            <div class="tt-slot-card mb-2" style="background: <?= $meta['softBg'] ?>; border-left-color: <?= $meta['accent'] ?>;">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-semibold small text-dark">
                                                        <i class="fa-regular fa-clock me-1"></i><?= Html::encode($item->getFormattedTimeRange()) ?>
                                                    </span>
                                                    <span class="badge border <?= $meta['badge'] ?>" style="font-size: 10px;">
                                                        <?= Html::encode($item->class_type) ?>
                                                    </span>
                                                </div>
                                                <div class="fw-bold text-dark mb-1"><?= Html::encode($subjectName) ?></div>
                                                <div class="small text-muted d-flex justify-content-between align-items-center">
                                                    <span>
                                                        <?= Html::encode($item->getTeacherDisplayName()) ?> · <?= Html::encode($batchName) ?><?= $sectionName ? '/' . Html::encode($sectionName) : '' ?>
                                                    </span>
                                                    <?php if ($canManage): ?>
                                                        <a href="<?= Url::to(['update', 'id' => $item->id]) ?>" class="btn btn-sm btn-link p-0 text-primary">Edit</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const meta = <?= json_encode($placementMeta) ?>;
    const courseEl = document.getElementById('tt-filter-course');
    const batchEl = document.getElementById('tt-filter-batch');
    const sectionEl = document.getElementById('tt-filter-section');

    function syncFilterDropdowns() {
        const cId = courseEl && courseEl.value ? parseInt(courseEl.value, 10) : null;
        const bId = batchEl && batchEl.value ? parseInt(batchEl.value, 10) : null;

        if (batchEl && Object.keys(meta.batchByCourse || {}).length > 0) {
            Array.from(batchEl.options).forEach(function (opt) {
                if (!opt.value) return;
                const match = !cId || meta.batchByCourse[opt.value] === cId;
                opt.hidden = !match;
            });
            if (bId && cId && meta.batchByCourse[bId] !== cId) {
                batchEl.value = '';
            }
        }

        const activeBatchId = batchEl && batchEl.value ? parseInt(batchEl.value, 10) : null;
        if (sectionEl && Object.keys(meta.sectionByCourseAndBatch || {}).length > 0) {
            Array.from(sectionEl.options).forEach(function (opt) {
                if (!opt.value) return;
                const pair = meta.sectionByCourseAndBatch[opt.value];
                const matchCourse = !cId || (pair && pair[0] === cId);
                const matchBatch = !activeBatchId || (pair && pair[1] === activeBatchId);
                opt.hidden = !(matchCourse && matchBatch);
            });
            if (sectionEl.value) {
                const pair = meta.sectionByCourseAndBatch[sectionEl.value];
                if ((cId && (!pair || pair[0] !== cId)) || (activeBatchId && (!pair || pair[1] !== activeBatchId))) {
                    sectionEl.value = '';
                }
            }
        }
    }

    if (courseEl) courseEl.addEventListener('change', syncFilterDropdowns);
    if (batchEl) batchEl.addEventListener('change', syncFilterDropdowns);
    syncFilterDropdowns();
});
</script>
