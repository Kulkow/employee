<?php

namespace Modules\Employee\Admin\Forms;

class BonusDate extends \Classes\Base\Form
{
        
    public function filters()
    {
        return [
                'date' => 'trim',
        ];
    }
    
    public function rules()
    {
        return [
            'date' => [
                'NotEmpty' => 'Введите дату',
            ],
        ];
    }
    
    public function adapters()
    {
        return [
            'date' => [
                        'date.formater',
                        ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
                    ],
        ];
    }
    
    public function save()
    {
        $data = $this->getData(['date']);
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