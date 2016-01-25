<?php

namespace Modules\Employee\Admin\Forms;

class PlanOrgEdit extends \Classes\Base\Form
{
    public function defaults()
    {
        return [
            'owner_id' => OWNER_ID,
        ];
    }
    
    public function rules()
    {
        return [
            'owner_id' => [
                'NotEmpty' => 'Выберите сайт',
            ],
            'profit' => function($value){
                if(intval($value) <= 0){
                    return 'Выставьте общий баланс';
                }
            },
            'date' => [
                'NotEmpty' => 'Выставьте дату',
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
            'profit' => [
                'price',
                [],
            ],
        ];
    }

    public function save()
    {
        $data = $this->getSafeData();
        $id = \Arr::get($data, 'id', NULL);
        $model = $this->model('PlanOrg');
        if($id){
            $model->upsert($data);
            return $model->getById($id);
        }else{
            $id = $model->upsert($data);
            return $model->getById($id);
        }
    }
}