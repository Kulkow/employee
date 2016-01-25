<?php

namespace Modules\Employee\Admin\Forms;

class EmployeeEdit extends \Classes\Base\Form
{
    public function rules()
    {
        return [
        ];
    }
    
    public function adapters()
    {
        return [
            'start' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'data_rate' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
        ];
    }
    
    protected function preparename($name){
        $lastname = $firstname = $secondname = '';
        $name = preg_replace("/\s{2,}/",' ',$name);
        $arr = explode(' ',$name);
        array_walk($arr, function(&$str){
            $str = trim($str);
        });
        $lastname = \Arr::get($arr, 0, $lastname);
        $firstname = \Arr::get($arr, 1, $firstname);
        $secondname = \Arr::get($arr, 2, $secondname);
        return ['lastname' => $lastname,
                'firstname' => $firstname,
                'secondname' => $secondname,    
        ];
    }

    public function save()
    {
        $mEmployee = $this->model('EmployeeData');
        $data = $this->getData(['number', 'department_id', 'status', 'skype', 'phone', 'is_vax', 'user_id']);
        $number = \Arr::get($data, 'number', 0);
        $number = $mEmployee->setNumber($data, $number);
        $data['number'] = $number;
        $_id = $mEmployee->upsert($data);
        $id = \Arr::get($data, 'user_id', $_id);
        
        if($name = $this->getData('name', null)){
            $user = $this->preparename($name);
            $user['id'] = $id;
            $this->model('User')->upsert($user);
        }
        
        $employee = $this->model('EmployeeData')->getById($id);
        return $employee;
    }
}