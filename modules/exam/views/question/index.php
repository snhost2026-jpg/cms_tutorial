<?php

use app\modules\exam\models\ExamQuestion;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $lists array */
/* @var $filters array */

$this->title = 'Question Bank';
$this->params['breadcrumbs'][] = ['label' => 'Online Exams', 'url' => ['/exam/exam/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="exam-question-index">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-circle-question text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h3>
            <p class="text-muted small mb-0">Create reusable MCQ questions and attach them to any exam.</p>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-file-signature me-1"></i> Manage Exams', ['/exam/exam/index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Add Question', ['create'], ['class' => 'btn btn-primary']) ?>
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
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="text" name="q" value="<?= Html::encode($filters['q'] ?? '') ?>" class="form-control form-control-sm" placeholder="Search question text...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Course</label>
                    <?= Html::dropDownList('course_id', $filters['course_id'], $lists['course'], ['prompt' => 'All Courses', 'class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Subject</label>
                    <?= Html::dropDownList('subject_id', $filters['subject_id'], $lists['subject'], ['prompt' => 'All Subjects', 'class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Difficulty</label>
                    <?= Html::dropDownList('difficulty', $filters['difficulty'], ExamQuestion::difficultyOptions(), ['prompt' => 'All Levels', 'class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Questions Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'headerRowOptions' => ['class' => 'table-light text-uppercase small text-muted border-bottom'],
                'layout' => "{items}\n<div class='p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2'>{summary}{pager}</div>",
                'emptyText' => '<div class="text-center py-5 text-muted"><i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>No questions found. Click <strong>Add Question</strong> to create your first MCQ.</div>',
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width: 55px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center; color: #6c757d;'],
                    ],
                    [
                        'attribute' => 'question_text',
                        'label' => 'Question',
                        'format' => 'raw',
                        'value' => function (ExamQuestion $m) {
                            $text = Html::encode(mb_strimwidth($m->question_text, 0, 120, '...'));
                            $meta = [];
                            if ($m->course) {
                                $meta[] = '<span class="badge bg-light text-dark border">' . Html::encode($m->course->name) . '</span>';
                            }
                            if ($m->subject) {
                                $meta[] = '<span class="badge bg-primary-subtle text-primary">' . Html::encode($m->subject->name) . '</span>';
                            }
                            return '<div class="fw-semibold text-dark">' . $text . '</div>'
                                . ($meta ? '<div class="mt-1 d-flex gap-1 flex-wrap">' . implode('', $meta) . '</div>' : '');
                        },
                    ],
                    [
                        'attribute' => 'correct_option',
                        'label' => 'Answer',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 90px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'value' => function (ExamQuestion $m) {
                            return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">' . Html::encode($m->correct_option) . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'marks',
                        'headerOptions' => ['style' => 'width: 85px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'value' => function (ExamQuestion $m) {
                            return rtrim(rtrim(number_format((float) $m->marks, 2), '0'), '.');
                        },
                    ],
                    [
                        'attribute' => 'difficulty',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 110px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'value' => function (ExamQuestion $m) {
                            $cls = $m->difficulty === 'hard'
                                ? 'bg-danger-subtle text-danger'
                                : ($m->difficulty === 'easy' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning');
                            return '<span class="badge ' . $cls . '">' . ucfirst(Html::encode($m->difficulty)) . '</span>';
                        },
                    ],
                    [
                        'label' => 'Actions',
                        'format' => 'raw',
                        'headerOptions' => ['class' => 'text-end px-3', 'style' => 'width: 120px;'],
                        'contentOptions' => ['class' => 'text-end px-3'],
                        'value' => function (ExamQuestion $m) {
                            return Html::a('<i class="fa-solid fa-pen"></i>', ['update', 'id' => $m->id], [
                                'class' => 'btn btn-sm btn-outline-primary me-1',
                                'title' => 'Edit',
                            ]) . Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $m->id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'title' => 'Delete',
                                'data' => ['method' => 'post', 'confirm' => 'Delete this question? It will also be removed from any exams using it.'],
                            ]);
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
