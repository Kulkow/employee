<?php

namespace Modules\Employee\Admin\Forms;

class DepartmentNumber extends \Classes\Base\Form
{
    
    public function rules()
    {
        return [
            'number' => [
                'NotEmpty' => 'Введите номер подразделения',
                function($value){
                    $move = $this->model('Department')->getByNumber($value);
                    if(null == $move){
                        $path = explode('.',$value);
                        if(count($path) > 1){
                            array_pop($path);
                            $_parent = implode('.',$path);
                            $move = $this->model('Department')->getByNumber($_parent);
                            if(null == $move){
                                return 'Нет подразделения с номером '.$_parent;
                            }
                        }
                    }
                },
            ]
        ];
    }
    

    public function save()
    {
        $department = $this->getData(['number']);
        $pid = $this->getData('pid');
        if ($id = $this->getData('id')){
            $department['id'] = $id;
            $this->model('Department')->upsert($department);
        }else{
            return false;   
        }
        return $department;
    }
}