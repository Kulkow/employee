<?php

namespace Modules\Employee\Admin\Forms;

class EmployeePlanEditUser extends \Classes\Base\Form
{
    public $plans = []; //list plans db
    
    protected $plan_users = []; // current plans user
    
    protected $max_summa = 100; // общая сумма процентов плановых показателей
    
    public function filters()
    {
        return [
                'user_id' => 'intval',
        ];
    }
    
    public function rules()
    {
        $this->_preset_data();
        return [
            'values' => function(){
                $total_percent = 0;
                $is_plan = FALSE;
                foreach($this->plan_users as $id => $plan){
                    if($plan['is_plan_based'] == 1){
                        $is_plan = TRUE;
                        $total_percent += $plan['value'];
                    }
                }
                if($is_plan){
                    if($total_percent > $this->max_summa){
                        return 'Сумма плановых показателей больше '.$this->max_summa.' %';
                    }elseif($total_percent < $this->max_summa){
                        return 'Сумма плановых показателей меньше '.$this->max_summa.' %';
                    }
                }
            },
            'user_id' => [
                'NotEmpty' => 'Выберите пользователя',
            ]
        ];
    }
    
    public function init(array $plans){
       $this->plans = $plans;
       return $this;
    }
    protected function _preset_data(){
        $data = $this->getSafeData();
        $values = \Arr::get($data,'value',[]);
        $plan_ids = \Arr::get($data,'plan_id',[]);
        $plans = [];
        foreach($plan_ids as $id => $plan_id){
            $is_plan_based = 0;
            if($plan = \Arr::get($this->plans, $plan_id, NULL)){
                $is_plan_based = \Arr::get($plan, 'is_plan_based', 0);    
            }
            $plans[$id] = [
                'id' => $id,
                'plan_id' => $plan_id,
                'value' => \Arr::get($values, $id,0),
                'is_plan_based' => $is_plan_based
            ];
        }
        // plan add
        $add = ['value' => $this->getData('nvalue'),
                'plan_id' => $this->getData('nplan_id'),
               ];
        foreach(\Arr::get($add, 'plan_id', []) as $index => $plan_id){
            $is_plan_based = 0;
            if($plan = \Arr::get($this->plans, $plan_id, NULL)){
                $is_plan_based = \Arr::get($plan, 'is_plan_based', 0);    
            }
            $plans[] = [
                'plan_id' => $plan_id,
                'value' => \Arr::path($add, 'value.'.$index,0),
                'is_plan_based' => $is_plan_based
            ];
        }
        $this->plan_users = $plans;
    }
    

    public function save()
    {
        $errors = [];
        foreach($this->plan_users as $_plan){
            $plan = \Arr::get($this->plans, $_plan['plan_id'], []);
            $data = $_plan;
            if($data['is_plan_based'] == 0){
                $data['value'] = 100 * $data['value'];
            }
            unset($data['is_plan_based']);
            if(empty($data['id'])){
                $data['creater'] = $this->getData('updater');
                $data['updater'] = $this->getData('updater');
                $data['created'] = $this->getData('updated');
                $data['updated'] = $this->getData('updated');
                $data['user_id'] = $this->getData('user_id');
            }else{
                $data['updater'] = $this->getData('updater');
                $data['updated'] = $this->getData('updated');
            }
            if(! $this->model('EmployeePlan')->upsert($data)){
                $errors[] = "Ошибка при добавен или обновлении показателя ".\Arr::get($plan, 'name', '')." пользователя";
            }
        }
        if(! empty($errors)){
            return [
                    'errors' => $errors
                    ];
        }else{
            return [];
        }
        /*$user_id = $this->getData('user_id');
        $data = $this->getSafeData('');
        if($id = $this->getData('id')){
            $data['id'] = $id;    
        }
        $id = $this->model('EmployeePlan')->upsert($data);
        $plans = $this->model('EmployeePlan')->getByUserId($user_id);
        return $plans;*/
    }
}