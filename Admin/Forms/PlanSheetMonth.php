<?php

namespace Modules\Employee\Admin\Forms;
use Modules\Employee\Admin\Extensions\Calendar;

class PlanSheetMonth extends \Classes\Base\Form
{
    protected $_user = NULL; //Личные 
    
    protected $_common = NULL; // Общие
    
    public $_ids = NULL; 
    
    public function defaults()
    {
        return [
            'month' => date('m'),
            'year' => date('Y'),
        ];
    }
    
    public function rules()
    {
        return [
            'amount' => function(){
            },
        ];
    }
    
    public function adapters()
    {
        $adapter = $this->container->get('data.adapter.price');
        return [
            'start' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'end' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'common' => ['input' => function($value) use ($adapter){
                        if(is_array($value)){
                            foreach($value as &$v){
                                $v = $adapter->input($v);
                            }
                            return ['common' => $value];
                        }
                    },
                    'output' => function($value) use ($adapter){
                        if(is_array($value)){
                            foreach($value as &$v){
                                $v = $adapter->output($v);
                            }
                            return ['common' => $value];
                        }
                    }
            ],
            'user' => ['input' => function($value) use ($adapter){
                        foreach($value as &$v){
                            $v = $adapter->input($v);
                        }
                        return ['user' => $value];
                    },
                    'output' => function($value) use ($adapter){
                        if(is_array($value)){
                            foreach($value as &$v){
                                $v = $adapter->output($v);
                            }
                            return ['user' => $value];
                        }
                    }
            ],
           
        ];
    }
    
    public function init($sheets = NULL){
        $m = $this->getData('month', date('m'));
        $y = $this->getData('year', date('Y'));
        $p = Calendar::getPeriodMonth($y.'-'.$m.'-01');
        $this->setData('start', $p['start']);
        $this->setData('end', $p['end']);
        if($sheets){
            foreach($sheets as $sheet){
                $key = '';
                $_id = \Arr::get($sheet,'id',0);// id sheet
                $plan_id = \Arr::get($sheet,'plan_id',NULL); // Plan_id
                $user_id = \Arr::get($sheet,'manager_id',NULL); // user_id
                $department_id = \Arr::get($sheet,'department_id',NULL); // department_id
                $ammount = \Arr::get($sheet,'plan_amount',NULL); // ammount
                if($user_id){
                    $key = $user_id.'_'.$plan_id; // user_id _ plan_id
                    $this->_user[$key] = 0 < $ammount ? $ammount : '';
                    $this->_ids['user_'.$key] = $_id;
                }else{
                    $key = ($department_id ? $department_id.'_' : '').$plan_id;
                    $this->_common[$key] = 0 < $ammount ? $ammount : '';
                    $this->_ids['common_'.$key] = $_id;
                }
            }
            $this->setData('common', $this->_common);
            $this->setData('user', $this->_user);
            $this->setData('ids', $this->_ids);
        }
        return $this;
    }

    public function save(){
        $errors = [];
        $data = $this->getSafeData();
        $PlanSheet = $this->model('PlanSheet');
        $planTypes = $this->model('Plan')->getByType();
        foreach(\Arr::get($data,'common', []) as $key => $amount){
            $pkey = explode('_',$key);
            if(count($pkey) == 2){
                list($department_id, $plan_id) = $pkey;
            }else{
                $plan_id = $key;
                $department_id = null;
            }
            $sheet = [];
            if($_id = \Arr::get($this->_ids, 'common_'.$key, NULL)){ //update
                $sheet['id'] = $_id;
            }
            $sheet['manager_id'] = 0;
            $sheet['type'] = \Arr::get($planTypes, $key, $plan_id);
            $sheet['plan_id'] = $plan_id;
            $sheet['department_id'] = $department_id;
            $sheet['plan_amount'] = $amount;
            $sheet['date'] = $this->getData('start');
            $sheet['end'] = $this->getData('end');
            $PlanSheet->upsert($sheet);
        }
        foreach(\Arr::get($data,'user', []) as $key => $amount){
            $sheet = [];
            list($user_id, $plan_id) = explode('_',$key);
            if($_id = \Arr::get($this->_ids, 'user_'.$key, NULL)){ //update
                $sheet['id'] = $_id;
            }
            $sheet['manager_id'] = $user_id;
            $sheet['type'] = $plan_id;
            $sheet['plan_id'] = $plan_id;
            $sheet['plan_amount'] = $amount;
            $sheet['date'] = $this->getData('start');
            $sheet['end'] = $this->getData('end');
            $PlanSheet->upsert($sheet);
        }
        return [];
    }
}