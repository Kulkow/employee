<?php

namespace Modules\Employee\Admin\Forms;

class PlanSheetEdit extends \Classes\Base\Form
{
    protected $plans;
    
    public function rules()
    {
        return [
            'plan_amount' => [
                'NotEmpty' => 'План не может быть пустым',
            ],
        ];
    }
    
    public function init()
    {
        
    }
    
    public function adapters()
    {
        return [
            'date' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'end' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
        ];
    }

    public function save(){
        $data = $this->getSafeData();
        if($id = $this->getData('id')){
            $data['id'] = $id;    
        }
        $id = $this->model('PlanSheet')->upsert($data);
        $sheet = $this->model('PlanSheet')->getById($id);
        return $plans;
    }
}