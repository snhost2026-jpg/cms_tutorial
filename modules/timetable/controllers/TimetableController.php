<?php

namespace app\modules\timetable\controllers;

use Yii;
use app\components\AccessHelper;
use app\modules\timetable\models\CoachingTimetable;
use app\modules\timetable\models\CoachingTimetableSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Controller for creating, updating, and viewing coaching weekly timetables.
 *
 * @property-read \app\modules\timetable\Module $module
 */
class TimetableController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'toggle-status' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!Yii::$app->user->isGuest) {
            $isReadAction = in_array($action->id, ['index', 'view'], true);
            $allowed = $this->canManageTimetable()
                || AccessHelper::checkRoute('/' . $action->getUniqueId())
                || ($isReadAction && (
                    Yii::$app->user->can('manageStudent')
                    || Yii::$app->user->can('manageEmployee')
                    || Yii::$app->user->can('viewTimetable')
                ));

            if (!$allowed) {
                throw new ForbiddenHttpException('You are not allowed to access timetable management.');
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * Visual Weekly Timetable Viewpoint (Time-Slot × Day-of-Week Matrix + Day Schedule).
     */
    public function actionIndex()
    {
        $searchModel = new CoachingTimetableSearch();
        $weeklyData = $searchModel->searchWeeklyMatrix(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'weeklyData' => $weeklyData,
            'lists' => $this->module->resolveLists(),
            'placementMeta' => $this->module->resolvePlacementMeta(),
            'canManage' => $this->canManageTimetable(),
        ]);
    }

    /**
     * Interactive Weekly Timetable Builder for creating and updating an entire week's
     * class schedule for a selected session, course, batch, and optional section.
     */
    public function actionBuilder()
    {
        $request = Yii::$app->request;
        $lists = $this->module->resolveLists();
        $placementMeta = $this->module->resolvePlacementMeta();

        $defaultSessionId = !empty($lists['academic_session'])
            ? (int) array_key_first($lists['academic_session'])
            : (int) date('Y');

        $source = $request->isPost ? (array) $request->post('context', []) : $request->queryParams;

        $context = [
            'academic_session_id' => $this->cleanInt($source['academic_session_id'] ?? $defaultSessionId),
            'course_id' => $this->cleanInt($source['course_id'] ?? null),
            'batch_id' => $this->cleanInt($source['batch_id'] ?? null),
            'section_id' => $this->cleanInt($source['section_id'] ?? null),
            'effective_from' => !empty($source['effective_from']) && is_string($source['effective_from'])
                ? trim($source['effective_from'])
                : date('Y-m-d'),
            'effective_to' => !empty($source['effective_to']) && is_string($source['effective_to'])
                ? trim($source['effective_to'])
                : '',
        ];

        $isReady = $context['academic_session_id'] !== null
            && $context['course_id'] !== null
            && $context['batch_id'] !== null;

        $rows = [];
        $rowErrors = [];

        if ($isReady && $request->isPost) {
            $submittedRows = (array) $request->post('slots', []);
            $deletedRaw = $request->post('deleted_ids', '');
            $deletedIds = is_array($deletedRaw)
                ? array_filter(array_map('intval', $deletedRaw))
                : array_filter(array_map('intval', explode(',', (string) $deletedRaw)));

            $tx = Yii::$app->db->beginTransaction();
            try {
                if (!empty($deletedIds)) {
                    CoachingTimetable::deleteAll([
                        'and',
                        ['id' => $deletedIds],
                        [
                            'academic_session_id' => $context['academic_session_id'],
                            'course_id' => $context['course_id'],
                            'batch_id' => $context['batch_id'],
                        ],
                    ]);
                }

                // Collect existing IDs being updated so we can temporarily deactivate them
                // during validation to allow clean in-place swaps without false self-conflicts
                $updatingIds = [];
                foreach ($submittedRows as $slotData) {
                    if (!empty($slotData['id']) && ctype_digit((string) $slotData['id'])) {
                        $updatingIds[] = (int) $slotData['id'];
                    }
                }
                if (!empty($updatingIds)) {
                    CoachingTimetable::updateAll(
                        ['status' => CoachingTimetable::STATUS_INACTIVE],
                        [
                            'and',
                            ['id' => $updatingIds],
                            [
                                'academic_session_id' => $context['academic_session_id'],
                                'course_id' => $context['course_id'],
                                'batch_id' => $context['batch_id'],
                            ],
                        ]
                    );
                }

                $savedCount = 0;
                $hasValidationErrors = false;

                foreach ($submittedRows as $idx => $slotData) {
                    if (!is_array($slotData)) {
                        continue;
                    }

                    // Skip completely blank rows
                    if (
                        empty($slotData['subject_id'])
                        && empty($slotData['employee_id'])
                        && empty($slotData['start_time'])
                        && empty($slotData['end_time'])
                    ) {
                        continue;
                    }

                    $model = null;
                    if (!empty($slotData['id']) && ctype_digit((string) $slotData['id'])) {
                        $model = CoachingTimetable::findOne([
                            'id' => (int) $slotData['id'],
                            'academic_session_id' => $context['academic_session_id'],
                            'course_id' => $context['course_id'],
                            'batch_id' => $context['batch_id'],
                        ]);
                    }
                    if ($model === null) {
                        $model = new CoachingTimetable();
                    }

                    $model->academic_session_id = $context['academic_session_id'];
                    $model->course_id = $context['course_id'];
                    $model->batch_id = $context['batch_id'];
                    $model->section_id = $context['section_id'];
                    $model->day_of_week = $slotData['day_of_week'] ?? null;
                    $model->start_time = $slotData['start_time'] ?? '';
                    $model->end_time = $slotData['end_time'] ?? '';
                    $model->subject_id = $slotData['subject_id'] ?? null;
                    $model->employee_id = $slotData['employee_id'] ?? null;
                    $model->room_id = ($slotData['room_id'] ?? '') !== '' ? $slotData['room_id'] : null;
                    $model->class_type = $slotData['class_type'] ?? CoachingTimetable::TYPE_THEORY;
                    $model->effective_from = !empty($slotData['effective_from'])
                        ? $slotData['effective_from']
                        : $context['effective_from'];
                    $model->effective_to = ($slotData['effective_to'] ?? '') !== ''
                        ? $slotData['effective_to']
                        : ($context['effective_to'] !== '' ? $context['effective_to'] : null);
                    $model->status = isset($slotData['status']) && $slotData['status'] !== ''
                        ? (int) $slotData['status']
                        : CoachingTimetable::STATUS_ACTIVE;

                    $rows[$idx] = $model;

                    if (!$model->save()) {
                        $hasValidationErrors = true;
                        $flatErrors = [];
                        foreach ($model->getFirstErrors() as $err) {
                            $flatErrors[] = $err;
                        }
                        $rowErrors[$idx] = implode(' ', $flatErrors);
                    } else {
                        $savedCount++;
                    }
                }

                if ($hasValidationErrors) {
                    $tx->rollBack();
                    Yii::$app->session->setFlash(
                        'error',
                        'Please fix the highlighted schedule validation or conflict errors below.'
                    );
                } else {
                    $tx->commit();
                    $deletedCount = count($deletedIds);
                    $msgParts = [];
                    if ($savedCount > 0) {
                        $msgParts[] = "saved {$savedCount} weekly class " . ($savedCount === 1 ? 'slot' : 'slots');
                    }
                    if ($deletedCount > 0) {
                        $msgParts[] = "removed {$deletedCount} " . ($deletedCount === 1 ? 'slot' : 'slots');
                    }
                    Yii::$app->session->setFlash(
                        'success',
                        'Weekly timetable updated: ' . ($msgParts ? implode(' and ', $msgParts) : 'no changes submitted') . '.'
                    );

                    return $this->redirect([
                        'index',
                        'academic_session_id' => $context['academic_session_id'],
                        'course_id' => $context['course_id'],
                        'batch_id' => $context['batch_id'],
                        'section_id' => $context['section_id'],
                    ]);
                }
            } catch (\Throwable $e) {
                if ($tx->isActive) {
                    $tx->rollBack();
                }
                Yii::error($e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Could not save weekly timetable: ' . $e->getMessage());
            }
        } elseif ($isReady) {
            $query = CoachingTimetable::find()
                ->where([
                    'academic_session_id' => $context['academic_session_id'],
                    'course_id' => $context['course_id'],
                    'batch_id' => $context['batch_id'],
                ])
                ->orderBy([
                    'day_of_week' => SORT_ASC,
                    'start_time' => SORT_ASC,
                    'id' => SORT_ASC,
                ]);

            if ($context['section_id'] !== null) {
                $query->andWhere(['section_id' => $context['section_id']]);
            } else {
                $query->andWhere(['section_id' => null]);
            }

            $rows = $query->all();

            if (!empty($rows) && !$request->get('effective_from')) {
                $context['effective_from'] = $rows[0]->effective_from ?: $context['effective_from'];
                $context['effective_to'] = $rows[0]->effective_to ?: '';
            }
        }

        return $this->render('builder', [
            'context' => $context,
            'isReady' => $isReady,
            'rows' => $rows,
            'rowErrors' => $rowErrors,
            'lists' => $lists,
            'placementMeta' => $placementMeta,
        ]);
    }

    /**
     * Create a single timetable slot or schedule across multiple days of the week at once.
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $lists = $this->module->resolveLists();
        $placementMeta = $this->module->resolvePlacementMeta();

        $model = new CoachingTimetable([
            'academic_session_id' => !empty($lists['academic_session'])
                ? (int) array_key_first($lists['academic_session'])
                : (int) date('Y'),
            'day_of_week' => CoachingTimetable::DAY_MONDAY,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'class_type' => CoachingTimetable::TYPE_THEORY,
            'effective_from' => date('Y-m-d'),
            'status' => CoachingTimetable::STATUS_ACTIVE,
        ]);

        // Pre-populate from query string (e.g., clicking "+" on a weekly grid cell)
        $preFillAttrs = [
            'academic_session_id',
            'course_id',
            'batch_id',
            'section_id',
            'subject_id',
            'employee_id',
            'day_of_week',
            'start_time',
            'end_time',
            'room_id',
            'class_type',
        ];
        foreach ($preFillAttrs as $attr) {
            $val = $request->get($attr);
            if ($val !== null && $val !== '') {
                $model->$attr = $val;
            }
        }
        $model->days_of_week = [(int) $model->day_of_week];

        if ($model->load($request->post())) {
            $selectedDays = array_values(array_unique(array_filter(
                array_map('intval', (array) $model->days_of_week),
                static function (int $d): bool {
                    return $d >= CoachingTimetable::DAY_MONDAY && $d <= CoachingTimetable::DAY_SUNDAY;
                }
            )));

            if (empty($selectedDays) && $model->day_of_week) {
                $selectedDays = [(int) $model->day_of_week];
            }

            if (empty($selectedDays)) {
                $model->addError('days_of_week', 'Select at least one day of the week.');
            } else {
                sort($selectedDays);
                $tx = Yii::$app->db->beginTransaction();
                try {
                    $createdDays = [];
                    $allValid = true;

                    foreach ($selectedDays as $dayNum) {
                        $slot = new CoachingTimetable();
                        $slot->attributes = $model->attributes;
                        $slot->day_of_week = $dayNum;

                        if (!$slot->save()) {
                            $allValid = false;
                            foreach ($slot->getErrors() as $attr => $errs) {
                                foreach ($errs as $err) {
                                    $model->addError($attr, $err);
                                }
                            }
                            break;
                        }
                        $createdDays[] = CoachingTimetable::dayOptions()[$dayNum];
                    }

                    if ($allValid) {
                        $tx->commit();
                        $model->days_of_week = $selectedDays;
                        Yii::$app->session->setFlash(
                            'success',
                            sprintf(
                                'Timetable scheduled for %s (%s).',
                                implode(', ', $createdDays),
                                CoachingTimetable::formatTimeLabel($model->start_time) . ' – ' . CoachingTimetable::formatTimeLabel($model->end_time)
                            )
                        );

                        return $this->redirect([
                            'index',
                            'academic_session_id' => $model->academic_session_id,
                            'course_id' => $model->course_id,
                            'batch_id' => $model->batch_id,
                            'section_id' => $model->section_id,
                        ]);
                    }

                    $tx->rollBack();
                } catch (\Throwable $e) {
                    if ($tx->isActive) {
                        $tx->rollBack();
                    }
                    Yii::error($e->getMessage(), __METHOD__);
                    Yii::$app->session->setFlash('error', 'Could not create timetable slot: ' . $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'lists' => $lists,
            'placementMeta' => $placementMeta,
        ]);
    }

    /**
     * Update an existing coaching timetable slot.
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $lists = $this->module->resolveLists();
        $placementMeta = $this->module->resolvePlacementMeta();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Timetable slot updated successfully.');
            return $this->redirect([
                'index',
                'academic_session_id' => $model->academic_session_id,
                'course_id' => $model->course_id,
                'batch_id' => $model->batch_id,
            ]);
        }

        return $this->render('update', [
            'model' => $model,
            'lists' => $lists,
            'placementMeta' => $placementMeta,
        ]);
    }

    /**
     * View details of a single timetable slot.
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'lists' => $this->module->resolveLists(),
            'canManage' => $this->canManageTimetable(),
        ]);
    }

    /**
     * Tabular list of all timetable slots (Active and Inactive) with search/filtering.
     */
    public function actionList()
    {
        $searchModel = new CoachingTimetableSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $this->module->pageSize);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'lists' => $this->module->resolveLists(),
            'canManage' => $this->canManageTimetable(),
        ]);
    }

    /**
     * Toggle Active/Inactive status of a timetable slot.
     */
    public function actionToggleStatus($id)
    {
        $model = $this->findModel($id);
        $model->status = ((int) $model->status === CoachingTimetable::STATUS_ACTIVE)
            ? CoachingTimetable::STATUS_INACTIVE
            : CoachingTimetable::STATUS_ACTIVE;

        if ($model->save()) {
            $state = (int) $model->status === CoachingTimetable::STATUS_ACTIVE ? 'activated' : 'deactivated';
            Yii::$app->session->setFlash('success', "Timetable slot {$state} successfully.");
        } else {
            $firstErrors = implode(' ', $model->getFirstErrors());
            Yii::$app->session->setFlash('error', 'Could not change status: ' . $firstErrors);
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['list']);
    }

    /**
     * Delete a timetable slot.
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $sessionId = $model->academic_session_id;
        $courseId = $model->course_id;
        $batchId = $model->batch_id;
        $model->delete();

        Yii::$app->session->setFlash('success', 'Timetable slot deleted successfully.');

        if (Yii::$app->request->referrer && strpos(Yii::$app->request->referrer, '/timetable/view') === false) {
            return $this->redirect(Yii::$app->request->referrer);
        }

        return $this->redirect([
            'index',
            'academic_session_id' => $sessionId,
            'course_id' => $courseId,
            'batch_id' => $batchId,
        ]);
    }

    protected function findModel($id): CoachingTimetable
    {
        $model = CoachingTimetable::find()
            ->with(['course', 'batch', 'section', 'subject', 'employee.info'])
            ->where(['id' => (int) $id])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('The requested timetable slot does not exist.');
        }

        return $model;
    }

    private function canManageTimetable(): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        return Yii::$app->user->can('SuperAdmin')
            || Yii::$app->user->can('admin')
            || Yii::$app->user->can('manageTimetable')
            || Yii::$app->user->can('manageAcademic')
            || Yii::$app->user->can('manageRbac')
            || AccessHelper::checkRoute('/timetable/*')
            || AccessHelper::checkRoute('/timetable/timetable/*')
            || AccessHelper::checkRoute('/academic/*');
    }

    private function cleanInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0) {
            return (int) $value;
        }
        return null;
    }
}
