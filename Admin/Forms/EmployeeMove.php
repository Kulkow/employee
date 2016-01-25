<?php

namespace Modules\Employee\Admin\Forms;

class EmployeeMove extends \Classes\Base\Form
{
    
    protected $removes = [];
    
    public function rules()
    {
        return [
            'user_id' => [
                'NotEmpty' => 'Выберите пользователя',
            ],
            'move_id' => [
                'NotEmpty' => 'Выберите подразделение',
            ],
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
        $data = $this->getSafeData();
        $user_id = \Arr::get($data, 'user_id');
        $move_id = \Arr::get($data, 'move_id');
        $d_id = \Arr::get($data, 'department_id');
        $start = \Arr::get($data, 'start');
        $departments = $this->model('Employee')->getByList(['user_id' => $user_id]);
        if(! $start){
            $data['start'] = "Y-m-d"; //по умолчанию действует с сегоднешнего дня
        }
        $date = new \DateTime($start);
        $date->sub(new \DateInterval('P1D'));
        $end = $date->format('Y-m-d');// Предыдущим днем
        if($d_id){
            //move
            $move_from_id = 0;
            foreach($departments as $department){
                if($d_id == $department['department_id']){
                    $move_from_id = $department['rowid'];
                }
            }
            if($move_from_id){
                $close = ['id' => $move_from_id,'end' => $end];
                $this->model('Employee')->upsert($close);
            }
            if($move_id){
                $new_department = ['department_id' => $move_id, 'start' => $start,'end' => NULL,'user_id' => $user_id];
                $this->model('Employee')->upsert($new_department);
            }
            
        }else{
            //add
            $new_department = ['department_id' => $move_id, 'start' => $start,'end' => NULL,'user_id' => $user_id];
            $this->model('Employee')->upsert($new_department);
        }
        return $this->model('Department')->getById($move_id);;
    }
}