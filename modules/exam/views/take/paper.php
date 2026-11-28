<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $attempt app\modules\exam\models\ExamAttempt */
/* @var $exam app\modules\exam\models\Exam */
/* @var $questions app\modules\exam\models\ExamQuestion[] */
/* @var $savedAnswers app\modules\exam\models\ExamAttemptAnswer[] */
/* @var $remainingSeconds int|null */

$this->title = $exam->title;
$this->params['breadcrumbs'] = [];

$saveUrl = Url::to(['save-answer']);
$submitUrl = Url::to(['submit', 'attempt_id' => $attempt->id]);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
?>
<style>
.exam-option-card {
    cursor: pointer;
    transition: all 0.15s ease;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 14px 16px;
    background: #fff;
}
.exam-option-card:hover {
    border-color: #93c5fd;
    background: #f8fbff;
}
.exam-option-card.selected {
    border-color: #0d6efd;
    background: #eff6ff;
}
.palette-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    border: 2px solid #e5e7eb;
    background: #fff;
    color: #374151;
}
.palette-btn.answered {
    background: #198754;
    border-color: #198754;
    color: #fff;
}
.palette-btn.review {
    background: #ffc107;
    border-color: #f59e0b;
    color: #111827;
}
.palette-btn.current {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.35);
    border-color: #0d6efd;
}
</style>

<div class="exam-paper-screen">
    <!-- Top Sticky Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold text-dark mb-0"><?= Html::encode($exam->title) ?></h5>
                <div class="small text-muted">
                    <span id="saveStatus" class="text-success"><i class="fa-solid fa-cloud-check me-1"></i>All changes saved</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <?php if ($remainingSeconds !== null): ?>
                    <div class="px-3 py-2 rounded-3 bg-light border d-flex align-items-center gap-2">
                        <i class="fa-regular fa-clock text-danger"></i>
                        <span class="fw-bold fs-5 font-monospace text-dark" id="examTimer">--:--</span>
                    </div>
                <?php else: ?>
                    <span class="badge bg-light text-dark border px-3 py-2"><i class="fa-regular fa-clock me-1"></i> Untimed</span>
                <?php endif; ?>

                <button type="button" class="btn btn-success fw-semibold px-4" data-bs-toggle="modal" data-bs-target="#submitExamModal">
                    <i class="fa-solid fa-paper-plane me-1"></i> Submit Exam
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Question Area -->
        <div class="col-lg-8">
            <?php foreach ($questions as $index => $q): ?>
                <?php
                $ans = $savedAnswers[$q->id] ?? null;
                $selected = $ans ? $ans->selected_option : null;
                $isReview = $ans ? (bool) $ans->is_marked_review : false;
                ?>
                <div class="card border-0 shadow-sm rounded-4 question-slide <?= $index === 0 ? '' : 'd-none' ?>"
                     data-index="<?= $index ?>"
                     data-qid="<?= $q->id ?>">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary">Question <?= $index + 1 ?> of <?= count($questions) ?></span>
                        <span class="badge bg-primary-subtle text-primary"><?= rtrim(rtrim(number_format((float) $q->marks, 2), '0'), '.') ?> Mark(s)</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="fs-5 fw-semibold text-dark mb-4" style="white-space: pre-line;"><?= Html::encode($q->question_text) ?></div>

                        <div class="d-flex flex-column gap-3 options-list">
                            <?php foreach ($q->getAvailableOptions() as $optKey => $optText): ?>
                                <label class="exam-option-card d-flex align-items-center gap-3 mb-0 <?= $selected === $optKey ? 'selected' : '' ?>">
                                    <input type="radio"
                                           class="form-check-input mt-0 option-radio"
                                           name="q_<?= $q->id ?>"
                                           value="<?= $optKey ?>"
                                           data-qid="<?= $q->id ?>"
                                           data-index="<?= $index ?>"
                                           <?= $selected === $optKey ? 'checked' : '' ?>>
                                    <span class="badge bg-light text-dark border"><?= $optKey ?></span>
                                    <span class="text-dark"><?= Html::encode($optText) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-warning btn-toggle-review <?= $isReview ? 'active' : '' ?>"
                                    data-qid="<?= $q->id ?>" data-index="<?= $index ?>" data-review="<?= $isReview ? '1' : '0' ?>">
                                <i class="fa-solid fa-flag me-1"></i> <span><?= $isReview ? 'Marked for Review' : 'Mark for Review' ?></span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-answer"
                                    data-qid="<?= $q->id ?>" data-index="<?= $index ?>">
                                <i class="fa-solid fa-eraser me-1"></i> Clear
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-prev" <?= $index === 0 ? 'disabled' : '' ?>>
                                <i class="fa-solid fa-arrow-left me-1"></i> Previous
                            </button>
                            <?php if ($index < count($questions) - 1): ?>
                                <button type="button" class="btn btn-primary btn-next">
                                    Next <i class="fa-solid fa-arrow-right ms-1"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#submitExamModal">
                                    Finish &amp; Submit <i class="fa-solid fa-check ms-1"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Question Palette -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-grip text-primary me-2"></i>Question Palette</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-2 mb-4" id="questionPalette">
                        <?php foreach ($questions as $index => $q): ?>
                            <?php
                            $ans = $savedAnswers[$q->id] ?? null;
                            $cls = '';
                            if ($ans && $ans->is_marked_review) {
                                $cls = 'review';
                            } elseif ($ans && $ans->selected_option) {
                                $cls = 'answered';
                            }
                            if ($index === 0) {
                                $cls .= ' current';
                            }
                            ?>
                            <button type="button" class="palette-btn <?= trim($cls) ?>" data-index="<?= $index ?>">
                                <?= $index + 1 ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="small text-muted d-flex flex-column gap-2 border-top pt-3">
                        <div class="d-flex justify-content-between">
                            <span><span class="badge bg-success me-1">&nbsp;</span> Answered</span>
                            <strong id="countAnswered">0</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><span class="badge bg-warning me-1">&nbsp;</span> Marked for Review</span>
                            <strong id="countReview">0</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><span class="badge bg-light border me-1">&nbsp;</span> Unanswered</span>
                            <strong id="countUnanswered">0</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div class="modal fade" id="submitExamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Submit Exam?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Are you sure you want to submit <strong><?= Html::encode($exam->title) ?></strong>?</p>
                <p class="small text-muted mb-0" id="submitSummaryText">Once submitted, you cannot change your answers.</p>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <?= Html::beginForm($submitUrl, 'post', ['id' => 'submitExamForm', 'class' => 'd-inline']) ?>
                    <button type="submit" class="btn btn-success px-4 fw-semibold">Yes, Submit Now</button>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>

<?php
$attemptId = (int) $attempt->id;
$remSecJs = $remainingSeconds === null ? 'null' : (int) $remainingSeconds;
$totalQ = count($questions);

$js = <<<JS
(function() {
    var attemptId = {$attemptId};
    var saveUrl = "{$saveUrl}";
    var csrfParam = "{$csrfParam}";
    var csrfToken = "{$csrfToken}";
    var remaining = {$remSecJs};
    var totalQuestions = {$totalQ};
    var currentIndex = 0;

    var slides = document.querySelectorAll('.question-slide');
    var paletteBtns = document.querySelectorAll('.palette-btn');
    var saveStatus = document.getElementById('saveStatus');

    function showQuestion(idx) {
        if (idx < 0 || idx >= totalQuestions) return;
        currentIndex = idx;
        slides.forEach(function(s, i) { s.classList.toggle('d-none', i !== idx); });
        paletteBtns.forEach(function(b, i) { b.classList.toggle('current', i === idx); });
    }

    function updateSummary() {
        var answered = 0, review = 0;
        paletteBtns.forEach(function(b) {
            if (b.classList.contains('review')) review++;
            if (b.classList.contains('answered')) answered++;
        });
        var unanswered = totalQuestions - answered;
        document.getElementById('countAnswered').textContent = answered;
        document.getElementById('countReview').textContent = review;
        document.getElementById('countUnanswered').textContent = unanswered;
        var summaryEl = document.getElementById('submitSummaryText');
        if (summaryEl) {
            summaryEl.textContent = 'Answered: ' + answered + ' / ' + totalQuestions + (unanswered > 0 ? ' (' + unanswered + ' unanswered).' : ' (All answered).');
        }
    }

    function syncAnswer(qid, idx, selectedOption, isMarkedReview) {
        if (saveStatus) {
            saveStatus.className = 'text-muted';
            saveStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...';
        }
        var formData = new FormData();
        formData.append(csrfParam, csrfToken);
        formData.append('attempt_id', attemptId);
        formData.append('question_id', qid);
        if (selectedOption !== undefined) formData.append('selected_option', selectedOption);
        if (isMarkedReview !== undefined) formData.append('is_marked_review', isMarkedReview ? '1' : '0');

        fetch(saveUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.expired) {
                    document.getElementById('submitExamForm').submit();
                    return;
                }
                if (data.ok) {
                    var btn = paletteBtns[idx];
                    if (btn) {
                        btn.classList.toggle('answered', !!data.selected_option);
                        btn.classList.toggle('review', !!data.is_marked_review);
                    }
                    updateSummary();
                    if (saveStatus) {
                        saveStatus.className = 'text-success';
                        saveStatus.innerHTML = '<i class="fa-solid fa-cloud-check me-1"></i>Saved';
                    }
                }
            })
            .catch(function() {
                if (saveStatus) {
                    saveStatus.className = 'text-danger';
                    saveStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i>Save failed, check connection';
                }
            });
    }

    // Palette click
    paletteBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            showQuestion(parseInt(btn.getAttribute('data-index'), 10));
        });
    });

    // Prev / Next
    document.querySelectorAll('.btn-prev').forEach(function(btn) {
        btn.addEventListener('click', function() { showQuestion(currentIndex - 1); });
    });
    document.querySelectorAll('.btn-next').forEach(function(btn) {
        btn.addEventListener('click', function() { showQuestion(currentIndex + 1); });
    });

    // Radio selection
    document.querySelectorAll('.option-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var qid = radio.getAttribute('data-qid');
            var idx = parseInt(radio.getAttribute('data-index'), 10);
            var slide = slides[idx];
            slide.querySelectorAll('.exam-option-card').forEach(function(c) { c.classList.remove('selected'); });
            radio.closest('.exam-option-card').classList.add('selected');
            syncAnswer(qid, idx, radio.value, undefined);
        });
    });

    // Clear answer
    document.querySelectorAll('.btn-clear-answer').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var qid = btn.getAttribute('data-qid');
            var idx = parseInt(btn.getAttribute('data-index'), 10);
            var slide = slides[idx];
            slide.querySelectorAll('.option-radio').forEach(function(r) { r.checked = false; });
            slide.querySelectorAll('.exam-option-card').forEach(function(c) { c.classList.remove('selected'); });
            syncAnswer(qid, idx, '', undefined);
        });
    });

    // Mark for review
    document.querySelectorAll('.btn-toggle-review').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var qid = btn.getAttribute('data-qid');
            var idx = parseInt(btn.getAttribute('data-index'), 10);
            var nextState = btn.getAttribute('data-review') !== '1';
            btn.setAttribute('data-review', nextState ? '1' : '0');
            btn.classList.toggle('active', nextState);
            btn.querySelector('span').textContent = nextState ? 'Marked for Review' : 'Mark for Review';
            syncAnswer(qid, idx, undefined, nextState);
        });
    });

    // Countdown timer
    if (remaining !== null) {
        var timerEl = document.getElementById('examTimer');
        function renderTimer() {
            var hrs = Math.floor(remaining / 3600);
            var mins = Math.floor((remaining % 3600) / 60);
            var secs = remaining % 60;
            var txt = (hrs > 0 ? String(hrs).padStart(2, '0') + ':' : '') + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            if (timerEl) {
                timerEl.textContent = txt;
                if (remaining <= 60) timerEl.classList.add('text-danger');
            }
        }
        renderTimer();
        var interval = setInterval(function() {
            remaining--;
            if (remaining <= 0) {
                clearInterval(interval);
                renderTimer();
                document.getElementById('submitExamForm').submit();
            } else {
                renderTimer();
            }
        }, 1000);
    }

    updateSummary();
})();
JS;
$this->registerJs($js);
?>
