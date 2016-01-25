<?php

namespace Modules\Employee\Admin\Forms;

class EmployeeNumber extends \Classes\Base\Form
{
    protected $department_id = null;
    
    public function filters()
    {
        return ['number' => function($value){
                $value = str_replace([',','/'],'.', $value);
                $value = rtrim($value, '.');
                return $value;
            },
        ];
    }
    
    public function rules()
    {
        return [
            'number' => [
                'NotEmpty' => 'Введите номер сотруднику',
                function($value){
                    $path = explode('.',$value);
                    $number = array_pop($path);
                    $number_department = implode('.',$path);
                    $department = $this->model('Department')->getByNumber($number_department);
                    if(null == $department){
                        return 'Нет подразделения с номером '.$number_department;
                    }
                    $this->department_id = $department['id'];
                },
            ]
        ];
    }
    

    public function save()
    {
        $mEmployee = $this->model('EmployeeData');
        $user_id = $this->getData('user_id');
        $employee = $mEmployee->getById($user_id);
        $data = $this->getData(['number', 'user_id']);
        $number = \Arr::get($data, 'number', 0);
        $path = explode('.',$number);
        $number = array_pop($path);
        $number_department = implode('.',$path);
        if(!$this->department_id){
            $department = $this->model('Department')->getByNumber($number_department);
            $this->department_id = $department['id'];
        }
        $data['department_id'] = $this->department_id;
        
        $number = $mEmployee->setNumber($data, $number);
        $data['number'] = $number;
        
        if($employee['department_id'] != $data['department_id']){
            $data['status'] = 3;    
        }
        $_id = $mEmployee->upsert($data);
        $id = \Arr::get($data, 'user_id', $_id);
        $employee = $mEmployee->getById($id);
        return $employee;
    }
}