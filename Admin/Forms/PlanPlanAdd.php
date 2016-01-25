<?php

namespace Modules\Employee\Admin\Forms;

class PlanPlanAdd extends \Classes\Base\Form
{
    public function rules()
    {
        return [
            'plan_id' => [
                'NotEmpty' => 'Выберите показатель',
            ],
            'plan_pid' => [
                'NotEmpty' => 'Выберите показатель',
            ],
        ];
    }

    public function save()
    {
        $data = $this->getData(['plan_pid', 'plan_id']);
        $id = $this->model('PlanPlan')->insert($data);
        $plan = $this->model('PlanPlan')->getById($id);
        return $plan;
    }
}