<?php

namespace Modules\Employee\Admin\Forms;

class DepartmentPlanAdd extends \Classes\Base\Form
{
    public function rules()
    {
        return [
            'plan_id' => [
                'NotEmpty' => 'Выберите показатель',
            ],
        ];
    }

    public function save()
    {
        $data = $this->getData(['department_id', 'plan_id']);
        $id = $this->model('DepartmentPlan')->insert($data);
        $plan = $this->model('DepartmentPlan')->getById($id);
        return $plan;
    }
}