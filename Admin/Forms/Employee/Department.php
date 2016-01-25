<?php

namespace Modules\Employee\Admin\Forms\Employee;

class Department extends \Classes\Base\Form
{
    
    public function rules()
    {
        return [
            'user_id' => [
                'NotEmpty' => [
                    'message' => 'Выберите сотрудника',
                ],
            ],
        ];
    }
    
    public function adapters()
    {
        return [
        ];
    }

    public function save()
    {
        $mData = $this->model('EmployeeData');
        $user_id = $this->getData('user_id');
        $employee = $mData->getById($user_id);
        $data = $this->getData(['user_id', 'department_id']);
        $department_id = \Arr::get($data, 'department_id', 0);
        if($employee['department_id'] != $department_id AND $department_id){
            $maxNumber = $this->model('EmployeeData')->getmaxnumber($department_id);
            $data['number'] = $maxNumber+1;
            $data['status'] = 3;
        }
        $mData->upsert($data);
        
        return $mData->getById($user_id);
    }
}