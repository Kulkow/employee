<?php

namespace Modules\Employee\Admin\Forms;
use Modules\Employee\Admin\Models\Plan;

class EmployeePlanValue extends \Classes\Base\Form
{
    public function filters()
    {
        return ['value' => 'trim',
                ];
    }
    
    public function rules()
    {
        return [
                'id' => [
                    'NotEmpty' => 'Выберите плановый показатель',
                ],
            ];
    }
   
    public function save()
    {
        $mEplan = $this->model('EmployeePlan');
        $data = $this->getData(['id', 'value']);
        $id = \Arr::get($data, 'id', NULL);
        $value = \Arr::get($data, 'value', NULL);
        $eplan = $mEplan->getById($id);
        
        $adapter = $this->container->get('data.adapter.price');
        $value = Plan::adapterInput($value, $eplan, $adapter);
        //$value = Plan::adapterOut($value, $plan, $adapter);
        $data = ['id' => $id, 'value' => $value];
        $mEplan->upsert($data);
        $eplan = $mEplan->getById($id);
        $eplan['value'] = Plan::adapterOut($eplan['value'], $eplan, $adapter);
        return  $eplan; 
    }
}