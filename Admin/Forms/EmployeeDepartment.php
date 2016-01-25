<?php

namespace Modules\Employee\Admin\Forms;

class EmployeeDepartment extends \Classes\Base\Form
{
    
    public function rules()
    {
        $department_id = $this->getData('department_id', 0);
        $employees = $this->model('Employee')->getByDepartment($department_id);
        return [
            'name' => function() use ($employees){
                $user_id = $this->getData('user_id');
                if(! $user_id){
                    return 'Выберите сотрудника';
                }
                if(\Arr::get($employees, $user_id, null)){
                    return 'Сотрудник уже в подразделении';
                }
            },
            'start' => function($value) use ($employees){
                if(date('d', strtotime($value)) != '1'){
                    return 'Сотрудник присоединяется к подразделению с первого числа месяца';
                }
            }
        ];
    }
    
    public function adapters()
    {
        return [
            'start' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'end' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
        ];
    }
    

    public function save()
    {
        $data = $this->getData(['department_id', 'user_id', 'start']);
        $id = $this->model('Employee')->insert($data);
        $employee = $this->model('Employee')->getById($id);
        return $employee;
    }
}