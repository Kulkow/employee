<?php

namespace Modules\Employee\Admin\Forms;
use Modules\Employee\Admin\Models\Plan;

class EmployeePlanEdit extends \Classes\Base\Form
{
    public function defaults()
    {
        return ['start' => date('Y-m').'-01',
        ];
    }
    
    public function filters()
    {
        return ['user_id' => 'intval',
                'plan_id' => 'intval',
                'department_id' => 'intval',
        ];
    }
    
    public function rules()
    {
        return [
            'user_id' => [
                'NotEmpty' => 'Выберите пользователя',
            ],
            'value' => function($value){
                $user_id = $this->getData('user_id');
                $id = $this->getData('id');
                $esalary = $this->model('EmployeePlan')->getByUserId($user_id);
                $s = 0;
                foreach($esalary as $_plan){
                    if(0 < $_plan['is_plan_based']){
                        $s += ($id == $_plan['id'] ? $value : $_plan['value']); 
                    }
                }
                if($s > 100){
                    //return 'Суммарный процент должен быть не более 100 < '.$s;
                }
                return $s;
            },
            'start' => [
                'NotEmpty' => 'введите даты действия планового показателя',
                function($start){
                    if(date('d', strtotime($start)) != 1){
                        return 'Дата начала действия показателя не 1 число месяца';
                    }
                },
            ],
            'new_start' => function(){
                //Не может быть раньше предыдущей ставки
                /*$user_id = $this->getData('user_id');
                $value = $this->getData('new_start');
                if(! empty($value)){
                    if($user_id){
                        $esalary = $this->model('EmployeePlan')->getByUserId($user_id);
                        $start = \Arr::get($esalary, 'start');
                        if(strtotime($value) < strtotime($start)){
                            return 'Время начала действия не может быть раньше начала предыдущей';
                        }
                    }
                }*/
            },
            'plan_id' => [
                'NotEmpty' => 'Выберите плановый показатель',
                function($plan_id){
                    $data = $this->getData();
                    $filter = ['plan_id' => \Arr::get($data, 'plan_id', 0),
                               'user_id' => \Arr::get($data, 'user_id'),
                               'department_id' => \Arr::get($data, 'department_id', 0),
                               'start' => \Arr::get($data, 'start', 0),
                               'id' => \Arr::get($data, 'id', 0),
                               ];
                    $exists = $this->model('EmployeePlan')->getUnique($filter);
                    if(null != $exists){
                        return 'Такой показатель существует ';
                    }
                }
            ],
        ];
    }
    
    
    public function adapters()
    {
        $plan_id = $this->getData('plan_id', 0);
        $plan = $this->model('Plan')->getById($plan_id);
        $adapter = $this->container->get('data.adapter.price');
        $adapters = [
            'start' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'end' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'new_start' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'value' => ['input' => function($value) use ($plan, $adapter){
                            if(null != $plan){
                                $value = Plan::adapterInput($value, $plan, $adapter);
                            }
                            return ['value' => $value];
                        },
                        'output' => function($value) use ($plan, $adapter){
                            if(null != $plan){
                                $value = Plan::adapterOut($value, $plan, $adapter);
                            }
                            return ['value' => $value];
                        }
            ],
            
        ];
        return $adapters;
    }

   
    public function save()
    {
        $data = $this->getData();
        $id = \Arr::get($data, 'id', NULL);
        $user_id = \Arr::get($data, 'user_id', NULL);
        $department_id = \Arr::get($data, 'department_id', NULL);
        $plan_id = \Arr::get($data, 'plan_id', NULL);
        $value_new = \Arr::get($data, 'value', NULL);
        $start = \Arr::get($data, 'new_start', NULL);
        $mEplan = $this->model('EmployeePlan');
        if($id){
            $eplan = $mEplan->getById($id);
            $value = \Arr::get($eplan, 'value', NULL);
            if(! empty($start)){
            // Новый показатель начинаю с 
                if($value != $value_new){
                    $date = new \DateTime($start);
                    $date->sub(new \DateInterval('P1D'));
                    $end = $date->format('Y-m-d');// Предыдущим днем
                    //close prev
                    $close = [
                              'id' => $data['id'],
                              'end' => $end
                              ];
                    $data['start'] = $data['new_start'];
                    unset($data['new_start']);
                    unset($data['id']);
                    $mEplan->upsert($close);
                    $mEplan->insert($data);
                }
            }
            else{
                unset($data['new_start']);
                if(isset($data['restore'])){
                    if(! empty($data['restore'])){
                        $data['end'] = NULL;
                    }else{
                        unset($data['end']);
                    }
                    unset($data['restore']);
                }else{
                    unset($data['end']);
                }
                $mEplan->upsert($data);
                $mEplan->pull($user_id, $department_id, $plan_id, $eplan['start'], $data['start']);
            }
            return $mEplan->getById($id);
            
        }else{
            if(empty($data['start'])){
                $data['start'] = \Arr::get($data, 'new_start', date('Y-m-d'));
            }
            unset($data['new_start']);
            $start = \Arr::get($data, 'start', NULL);
            
            $next = $mEplan->getByNextStart($user_id, $department_id, $plan_id, $start);
            $prev = $mEplan->getByPrevStart($user_id, $department_id, $plan_id, $start);
            
            $_id = $mEplan->insert($data);
            if($next){
                $up = ['id' => $_id,
                       'end' => $mEplan->getPrevDay($next['start']),
                       ];
                $mEplan->upsert($up);
            }
            if($prev){
                $up = ['id' => $prev['id'],
                       'end' => $mEplan->getPrevDay($start),
                       ];
                $mEplan->upsert($up);
            }
            
            return $mEplan->getById($_id);
        }
        return ;
    }
}