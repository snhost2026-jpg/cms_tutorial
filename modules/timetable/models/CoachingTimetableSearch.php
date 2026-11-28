<?php

namespace app\modules\timetable\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class CoachingTimetableSearch extends Model
{
    public $academic_session_id;
    public $course_id;
    public $batch_id;
    public $section_id;
    public $subject_id;
    public $employee_id;
    public $room_id;
    public $day_of_week;
    public $class_type;
    public $status = CoachingTimetable::STATUS_ACTIVE;
    public $effective_on;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [
                [
                    'academic_session_id',
                    'course_id',
                    'batch_id',
                    'section_id',
                    'subject_id',
                    'employee_id',
                    'room_id',
                    'day_of_week',
                    'class_type',
                    'status',
                    'effective_on',
                ],
                'trim',
            ],
            [
                [
                    'academic_session_id',
                    'course_id',
                    'batch_id',
                    'section_id',
                    'subject_id',
                    'employee_id',
                    'room_id',
                    'day_of_week',
                ],
                'integer',
            ],
            [['status'], 'in', 'range' => ['', '0', '1', 'all', 0, 1]],
            [['class_type'], 'in', 'range' => array_keys(CoachingTimetable::classTypeOptions())],
            [['effective_on'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * Builds filtered ActiveQuery with eager-loaded relations.
     */
    public function buildQuery(array $params, bool $defaultActiveOnly = true)
    {
        $this->status = $defaultActiveOnly ? (string) CoachingTimetable::STATUS_ACTIVE : '';
        $this->load($params, '');

        $query = CoachingTimetable::find()
            ->with(['course', 'batch', 'section', 'subject', 'employee.info']);

        if (!$this->validate()) {
            return $query->where('0=1');
        }

        $query->andFilterWhere([
            'academic_session_id' => $this->academic_session_id !== '' ? $this->academic_session_id : null,
            'course_id' => $this->course_id !== '' ? $this->course_id : null,
            'batch_id' => $this->batch_id !== '' ? $this->batch_id : null,
            'subject_id' => $this->subject_id !== '' ? $this->subject_id : null,
            'employee_id' => $this->employee_id !== '' ? $this->employee_id : null,
            'room_id' => $this->room_id !== '' ? $this->room_id : null,
            'day_of_week' => $this->day_of_week !== '' ? $this->day_of_week : null,
            'class_type' => $this->class_type !== '' ? $this->class_type : null,
        ]);

        if ($this->section_id !== null && $this->section_id !== '') {
            // Include whole-batch classes (section_id IS NULL) as well as section-specific classes
            $query->andWhere([
                'or',
                ['section_id' => (int) $this->section_id],
                ['section_id' => null],
            ]);
        }

        if ($this->status !== null && $this->status !== '' && $this->status !== 'all') {
            $query->andWhere(['status' => (int) $this->status]);
        }

        if (!empty($this->effective_on)) {
            $query->andWhere(['<=', 'effective_from', $this->effective_on])
                ->andWhere([
                    'or',
                    ['effective_to' => null],
                    ['>=', 'effective_to', $this->effective_on],
                ]);
        }

        return $query;
    }

    /**
     * DataProvider for tabular management view (`/timetable/list`).
     */
    public function search(array $params, int $pageSize = 25): ActiveDataProvider
    {
        $query = $this->buildQuery($params, false);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $pageSize],
            'sort' => [
                'defaultOrder' => [
                    'day_of_week' => SORT_ASC,
                    'start_time' => SORT_ASC,
                    'batch_id' => SORT_ASC,
                ],
            ],
        ]);
    }

    /**
     * Fetches and structures weekly timetable slots into a Time-Slot × Day-of-Week matrix
     * and summary KPIs for `/timetable` weekly calendar view.
     *
     * @return array{
     *   records: CoachingTimetable[],
     *   slots: array<string, array{start: string, end: string, label: string}>,
     *   grid: array<string, array<int, CoachingTimetable[]>>,
     *   byDay: array<int, CoachingTimetable[]>,
     *   stats: array{totalClasses: int, totalHours: float, uniqueTeachers: int, uniqueSubjects: int, activeDays: int}
     * }
     */
    public function searchWeeklyMatrix(array $params): array
    {
        /** @var CoachingTimetable[] $records */
        $records = $this->buildQuery($params, true)
            ->orderBy([
                'start_time' => SORT_ASC,
                'end_time' => SORT_ASC,
                'day_of_week' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->all();

        $slots = [];
        $grid = [];
        $byDay = [];
        foreach (array_keys(CoachingTimetable::dayOptions()) as $d) {
            $byDay[$d] = [];
        }

        $totalMinutes = 0;
        $teachers = [];
        $subjects = [];
        $daysWithClasses = [];

        foreach ($records as $item) {
            $day = (int) $item->day_of_week;
            $slotKey = $item->start_time . '|' . $item->end_time;

            if (!isset($slots[$slotKey])) {
                $slots[$slotKey] = [
                    'start' => $item->start_time,
                    'end' => $item->end_time,
                    'label' => $item->getFormattedTimeRange(),
                ];
            }

            $grid[$slotKey][$day][] = $item;
            $byDay[$day][] = $item;

            $totalMinutes += $item->getDurationMinutes();
            $teachers[(int) $item->employee_id] = true;
            $subjects[(int) $item->subject_id] = true;
            $daysWithClasses[$day] = true;
        }

        uasort($slots, static function (array $a, array $b): int {
            return [$a['start'], $a['end']] <=> [$b['start'], $b['end']];
        });

        return [
            'records' => $records,
            'slots' => $slots,
            'grid' => $grid,
            'byDay' => $byDay,
            'stats' => [
                'totalClasses' => count($records),
                'totalHours' => round($totalMinutes / 60, 1),
                'uniqueTeachers' => count($teachers),
                'uniqueSubjects' => count($subjects),
                'activeDays' => count($daysWithClasses),
            ],
        ];
    }
}
