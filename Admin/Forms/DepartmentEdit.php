<?php

namespace Modules\Employee\Admin\Forms;

class DepartmentEdit extends \Classes\Base\Form
{
    public function defaults(){
        return [
            'datesalary' => 21
        ];
    }
    
    public function filters(){
        return [
                'datesalary' => 'intval'
                ];
    }
    
    public function rules()
    {
        return [
            'name' => [
                'NotEmpty' => 'Введите наименование поразделения',
            ],
            'number' => [
                'NotEmpty' => 'Введите номер подразделения',
                /*function($value){
                    $id = $this->getData('id', 0);
                    $department = $this->model('Department')->getByNumber($value);
                    if(null != $department AND $id){
                        if($department['id'] != $id){
                            return 'Номер подразделения должен быть уникальным';
                        }
                    }
                },*/
            ]
        ];
    }
    

    public function save()
    {
        $department = $this->getData(['name', 'chief_id', 'number', 'datesalary']);
        $pid = $this->getData('pid');
        if ($id = $this->getData('id')){
            $department['id'] = $id;
            $this->model('Department')->upsert($department);
        }else{
            $this->model('Department')->add($pid,$department);
        }
        return $department;
    }
}