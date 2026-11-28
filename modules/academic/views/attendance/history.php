<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $sections array */
/* @var $subjects array */
/* @var $sectionId int|null */
/* @var $subjectId int|null */
/* @var $dateFrom string|null */
/* @var $dateTo string|null */

$this->title = 'Attendance History';
$this->params['breadcrumbs'][] = ['label' => 'Attendance', 'url' => ['/academic/attendance/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="attendance-history">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h2>
            <p class="text-muted mb-0">Review attendance sessions by section, subject, and date.</p>
        </div>
        <?= Html::a('Take Attendance', ['/academic/attendance/index'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <?= Html::beginForm(['history'], 'get', ['class' => 'row g-3 align-items-end']) ?>
                <div class="col-md-3">
                    <?= Html::label('Section', 'section_id', ['class' => 'form-label fw-semibold']) ?>
                    <?= Html::dropDownList('section_id', $sectionId, $sections, [
                        'id' => 'section_id',
                        'class' => 'form-select',
                        'prompt' => 'All sections',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= Html::label('Subject', 'subject_id', ['class' => 'form-label fw-semibold']) ?>
                    <?= Html::dropDownList('subject_id', $subjectId, $subjects, [
                        'id' => 'subject_id',
                        'class' => 'form-select',
                        'prompt' => 'All subjects',
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::label('From', 'date_from', ['class' => 'form-label fw-semibold']) ?>
                    <?= Html::input('date', 'date_from', $dateFrom, ['id' => 'date_from', 'class' => 'form-control']) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::label('To', 'date_to', ['class' => 'form-label fw-semibold']) ?>
                    <?= Html::input('date', 'date_to', $dateTo, ['id' => 'date_to', 'class' => 'form-control']) ?>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <?= Html::submitButton('Filter', ['class' => 'btn btn-outline-primary flex-grow-1']) ?>
                    <?= Html::a('Clear', ['history'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'summaryOptions' => ['class' => 'p-3 text-muted small border-bottom mb-0'],
                'emptyText' => 'No attendance sessions match these filters.',
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['class' => 'ps-3', 'style' => 'width: 60px;'],
                        'contentOptions' => ['class' => 'ps-3 text-muted'],
                    ],
                    [
                        'attribute' => 'section_name',
                        'label' => 'Section',
                    ],
                    [
                        'attribute' => 'subject_name',
                        'label' => 'Subject',
                    ],
                    [
                        'attribute' => 'attendance_date',
                        'label' => 'Date',
                        'value' => function ($session) {
                            return Yii::$app->formatter->asDate($session['attendance_date'], 'php:M j, Y');
                        },
                    ],
                    [
                        'attribute' => 'total_count',
                        'label' => 'Students',
                    ],
                    [
                        'attribute' => 'present_count',
                        'label' => 'Present',
                    ],
                    [
                        'attribute' => 'absent_count',
                        'label' => 'Absent',
                    ],
                    [
                        'attribute' => 'late_count',
                        'label' => 'Late',
                    ],
                    [
                        'attribute' => 'excused_count',
                        'label' => 'Excused',
                    ],
                    [
                        'label' => 'Details',
                        'format' => 'raw',
                        'value' => function ($session) {
                            return Html::a('View students', [
                                'view',
                                'section_id' => $session['section_id'],
                                'subject_id' => $session['subject_id'],
                                'date' => $session['attendance_date'],
                            ], ['class' => 'btn btn-sm btn-outline-primary']);
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
