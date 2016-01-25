<?php

namespace Modules\Employee\Admin\Forms;

class EmployeeAdd extends \Classes\Base\Form
{
    public function rules()
    {
        $data = $this->getData();
        $department_id = \Arr::get($data, 'department_id', 0);
        $start = \Arr::get($data, 'start', 0);
        $end = \Arr::get($data, 'end', null);
        if($start AND ! $end){
            $end = date('Y-m-d', strtotime('last day of '.$start));
        }
        $employees = $this->model('Employee')->getByDepartment($department_id, ['start' => $start, 'end' => $end]);
        return [
            'start' => [
                'NotEmpty' => 'Введите дату начала работы',
            ],
            'department_id' => function() use ($employees){
                $user_id = $this->getData('user_id');
                if(! $user_id){
                    return 'Выберите сотрудника';
                }
                if($u = \Arr::get($employees, $user_id, null)){
                    return 'Сотрудник уже в подразделении '.$u['name'];
                }
            },
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