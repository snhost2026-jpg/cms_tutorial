<?php

namespace app\modules\student\models;

use yii\data\ActiveDataProvider;

/**
 * Filtering for the student list. Joins stu_info (alias "i").
 */
class StuMasterSearch extends StuMaster
{
    public $name;
    public $stu_unique_id;
    public $gender;
    public $mobile;

    public function rules()
    {
        return [
            [['name', 'stu_unique_id', 'gender', 'mobile'], 'safe'],
            [['stu_master_course_id', 'stu_master_batch_id', 'stu_master_section_id',
              'stu_master_branch_id', 'is_status'], 'integer'],
        ];
    }

    public function scenarios()
    {
        return \yii\base\Model::scenarios();
    }

    /**
     * @param array $params request parameters
     * @param bool  $trash  list soft-deleted students instead of live ones
     * @param int   $pageSize
     */
    public function search($params, $trash = false, $pageSize = 20)
    {
        $query = $trash
            ? StuMaster::findWithDeleted()->andWhere(['stu_master.is_status' => StuMaster::STATUS_DELETED])
            : StuMaster::find();
        $query->joinWith(['info i']);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $pageSize],
            'sort' => [
                'defaultOrder' => ['stu_master_id' => SORT_DESC],
                'attributes' => [
                    'stu_master_id' => [
                        'asc' => ['stu_master.stu_master_id' => SORT_ASC],
                        'desc' => ['stu_master.stu_master_id' => SORT_DESC],
                    ],
                    'stu_unique_id' => [
                        'asc' => ['i.stu_unique_id' => SORT_ASC],
                        'desc' => ['i.stu_unique_id' => SORT_DESC],
                    ],
                    'name' => [
                        'asc' => ['i.stu_first_name' => SORT_ASC, 'i.stu_last_name' => SORT_ASC],
                        'desc' => ['i.stu_first_name' => SORT_DESC, 'i.stu_last_name' => SORT_DESC],
                    ],
                    'stu_master_course_id' => [
                        'asc' => ['stu_master.stu_master_course_id' => SORT_ASC],
                        'desc' => ['stu_master.stu_master_course_id' => SORT_DESC],
                    ],
                    'is_status' => [
                        'asc' => ['stu_master.is_status' => SORT_ASC],
                        'desc' => ['stu_master.is_status' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $provider;
        }

        $query->andFilterWhere([
            'stu_master.stu_master_course_id' => $this->stu_master_course_id,
            'stu_master.stu_master_batch_id' => $this->stu_master_batch_id,
            'stu_master.stu_master_section_id' => $this->stu_master_section_id,
            'stu_master.stu_master_branch_id' => $this->stu_master_branch_id,
        ]);
        if (!$trash) {
            $query->andFilterWhere(['stu_master.is_status' => $this->is_status]);
        }
        $query->andFilterWhere(['like', 'i.stu_unique_id', $this->stu_unique_id])
            ->andFilterWhere(['i.stu_gender' => $this->gender])
            ->andFilterWhere(['like', 'i.stu_mobile_no', $this->mobile])
            ->andFilterWhere(['like',
                "CONCAT_WS(' ', i.stu_first_name, i.stu_middle_name, i.stu_last_name)",
                $this->name]);

        return $provider;
    }
}
