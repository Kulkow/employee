<?php

namespace Modules\Employee\Admin\Forms;

class BonusAmount extends \Classes\Base\Form
{
        
    public function filters()
    {
        return ['amount' => 'intval',
        ];
    }
    
    public function rules()
    {
        return [
            'amount' => [
                'NotEmpty' => 'Введите сумму с учетом знака',
                function($value){
                    if($value == 0){
                        return 'Введите сумму с учетом знака';
                    }
                },
            ],
        ];
    }
    
    public function adapters()
    {
        return ['amount' => [
                            'price',[],
                            ],
                ];
    }
    

    public function save()
    {
        $data = $this->getData(['amount']);
        if(! empty($data['salary_id'])){
            unset($data['salary_id']);
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