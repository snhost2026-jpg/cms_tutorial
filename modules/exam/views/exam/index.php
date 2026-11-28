<?php

use app\modules\exam\models\Exam;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $lists array */
/* @var $filters array */

$this->title = 'Online Exams';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="exam-index">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-file-signature text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Create exams, assign questions from the Question Bank, publish, and track student results.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-circle-question me-1"></i> Question Bank', ['/exam/question/index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('<i class="fa-solid fa-laptop-file me-1"></i> My Exams', ['/exam/take/index'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Create Exam', ['create'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= Html::encode($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="get" action="<?= Url::to(['index']) ?>" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Search Title</label>
                    <input type="text" name="q" value="<?= Html::encode($filters['q'] ?? '') ?>" class="form-control form-control-sm" placeholder="Exam title...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Course</label>
                    <?= Html::dropDownList('course_id', $filters['course_id'], $lists['course'], ['prompt' => 'All Courses', 'class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Batch</label>
                    <?= Html::dropDownList('batch_id', $filters['batch_id'], $lists['batch'], ['prompt' => 'All Batches', 'class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <?= Html::dropDownList('status', $filters['status'], Exam::statusOptions(), ['prompt' => 'All Statuses', 'class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Exams Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'headerRowOptions' => ['class' => 'table-light text-uppercase small text-muted border-bottom'],
                'layout' => "{items}\n<div class='p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2'>{summary}{pager}</div>",
                'emptyText' => '<div class="text-center py-5 text-muted"><i class="fa-solid fa-file-circle-plus fa-2x mb-2 d-block"></i>No exams created yet. Click <strong>Create Exam</strong> to get started.</div>',
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width: 55px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; color: #6c757d;'],
                    ],
                    [
                        'attribute' => 'title',
                        'format' => 'raw',
                        'value' => function (Exam $m) {
                            $parts = [];
                            if ($m->course) $parts[] = $m->course->name;
                            if ($m->batch) $parts[] = $m->batch->name;
                            if ($m->section) $parts[] = 'Sec ' . $m->section->name;
                            if ($m->subject) $parts[] = $m->subject->name;
                            $sub = $parts ? implode(' • ', array_map([Html::class, 'encode'], $parts)) : 'General (All Students)';
                            return Html::a(Html::encode($m->title), ['view', 'id' => $m->id], ['class' => 'fw-semibold text-dark text-decoration-none d-block'])
                                . '<small class="text-muted">' . $sub . '</small>';
                        },
                    ],
                    [
                        'label' => 'Questions',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 115px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'value' => function (Exam $m) {
                            $cnt = $m->getQuestionCount();
                            $marks = rtrim(rtrim(number_format($m->getTotalMarks(), 2), '0'), '.');
                            return '<span class="fw-semibold">' . $cnt . '</span> <small class="text-muted">(' . $marks . 'm)</small>';
                        },
                    ],
                    [
                        'label' => 'Schedule & Time',
                        'format' => 'raw',
                        'value' => function (Exam $m) {
                            $dur = $m->duration_minutes ? ($m->duration_minutes . ' min') : 'Untimed';
                            $win = '';
                            if ($m->start_time || $m->end_time) {
                                $s = $m->start_time ? Yii::$app->formatter->asDatetime($m->start_time, 'php:M d, h:i A') : 'Anytime';
                                $e = $m->end_time ? Yii::$app->formatter->asDatetime($m->end_time, 'php:M d, h:i A') : 'No End';
                                $win = '<div class="small text-muted">' . $s . ' → ' . $e . '</div>';
                            } else {
                                $win = '<div class="small text-muted">Always open when published</div>';
                            }
                            return '<span class="badge bg-light text-dark border"><i class="fa-regular fa-clock me-1"></i>' . $dur . '</span>' . $win;
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 110px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'value' => function (Exam $m) {
                            $label = Exam::statusOptions()[$m->status] ?? ucfirst($m->status);
                            return '<span class="badge ' . $m->getStatusBadgeClass() . ' px-2 py-1">' . Html::encode($label) . '</span>';
                        },
                    ],
                    [
                        'label' => 'Actions',
                        'format' => 'raw',
                        'headerOptions' => ['class' => 'text-end px-3', 'style' => 'width: 215px;'],
                        'contentOptions' => ['class' => 'text-end px-3'],
                        'value' => function (Exam $m) {
                            return Html::a('<i class="fa-solid fa-eye"></i>', ['view', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-secondary me-1', 'title' => 'Overview'])
                                . Html::a('<i class="fa-solid fa-list-check"></i>', ['questions', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-primary me-1', 'title' => 'Manage Questions'])
                                . Html::a('<i class="fa-solid fa-chart-column"></i>', ['results', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-success me-1', 'title' => 'Results'])
                                . Html::a('<i class="fa-solid fa-pen"></i>', ['update', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-dark me-1', 'title' => 'Edit'])
                                . Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $m->id], [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => 'Delete',
                                    'data' => ['method' => 'post', 'confirm' => 'Delete this exam and all its attempts?'],
                                ]);
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
