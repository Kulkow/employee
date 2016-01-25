<?php

namespace Modules\Employee\Admin\Forms;

class PlanEdit extends \Classes\Base\Form
{
        
    public function defaults()
    {
        return [
                'is_negative' => 0,
                'is_plan_based' => 0,
                'is_discrete' => 0,
                'is_common' => 0,
                'pid' => 0,
                ];
    }
    public function filters()
    {
        return ['user_id' => 'intval',
                'plan_id' => 'intval',
                'pid' => 'intval',
        ];
    }
    
    public function rules()
    {
        return [
            'name' => [
                'NotEmpty' => 'Введите имя',
            ],
            'alias' => [
                'NotEmpty' => 'Введите код показателя (соответсвует классу обработчика)',
            ]
        ];
    }
    

    public function save()
    {
        $data = $this->getSafeData();
        if($id = $this->getData('id')){
            $data['id'] = $id;    
        }
        /*if(! \Arr::get($data,'is_common', 0)){
            $data['is_common'] = 0;
        }
        if(! \Arr::get($data,'is_plan_based', 0)){
            $data['is_plan_based'] = 0;
        }
        if(! \Arr::get($data,'is_plan_based', 0)){
            $data['is_plan_based'] = 0;
        }*/
        if($id){
            $this->model('Plan')->upsert($data);
        }else{
            $id = $this->model('Plan')->upsert($data);    
        }
        $plan = $this->model('Plan')->getById($id);
        return $plan;
    }
}