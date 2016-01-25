<?php

namespace Modules\Employee\Admin\Forms;

class BonusType extends \Classes\Base\Form
{
        
    public function defaults()
    {
        return [];
    }
    public function filters()
    {
        return [
                'type' => 'trim',
        ];
    }
    
    public function rules()
    {
        return [
            'type' => [
                'NotEmpty' => 'Введите причину',
            ],
        ];
    }
    
    public function save()
    {
        $data = $this->getData(['type']);
        if($id = $this->getData('id')){
            $data['id'] = $id;
            $this->model('Bonus')->upsert($data);
        }else{
            $id = $this->model('Bonus')->upsert($data);
        }
        $bonus = $this->model('Bonus')->getById($id);
        if(null != $bonus){
            $bonus['date'] = date('d.m.Y',strtotime($bonus['date'])); 
        }
        return $bonus;
    }
}