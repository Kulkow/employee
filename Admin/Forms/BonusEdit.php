<?php

namespace Modules\Employee\Admin\Forms;

class BonusEdit extends \Classes\Base\Form
{
        
    public function defaults()
    {
        return [];
    }
    public function filters()
    {
        return ['manager_id' => 'intval',
                'creator_id' => 'intval',
                'amount' => 'intval',
                'type' => 'trim',
        ];
    }
    
    public function rules()
    {
        return [
            'type' => [
                'NotEmpty' => 'Введите причину',
            ],
            'amount' => [
                'NotEmpty' => 'Введите сумму с учетом знака',
                function($value){
                    if($value == 0){
                        return 'Введите сумму с учетом знака';
                    }
                },
            ],
            'manager_id' => [
                'NotEmpty' => 'Не указан пользователь установки бонуса или депремирования',
            ],
            'creator_id' => [
                'NotEmpty' => 'Не указан кто устанавливает бонус депремирование',
            ]
        ];
    }
    
    public function adapters()
    {
        return ['amount' => [
                            'price',[],
                            ],
                'date' => [
                            'date.formater',
                            ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
                        ],
                ];
    }
    

    public function save()
    {
        $data = $this->getData(['type', 'manager_id','amount', 'creator_id', 'date', 'comment', 'request', 'is_approved']);
        if(! empty($data['salary_id'])){
            unset($data['salary_id']);
        }
        if(empty($data['is_approved']) AND isset($data['is_approved'])){
            unset($data['is_approved']);
        }
        if(empty($data['date'])){
            unset($data['date']);
        }
        if(empty($data['comment'])){
            unset($data['comment']);
        }
        if(empty($data['request'])){
            unset($data['request']);
        }
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