<?php

namespace Modules\Employee\Admin\Forms\Employee;

class Codex extends \Classes\Base\Form
{
    public function defaults()
    {
        return [
            'role_id' => 3, //клиент
        ];
    }
    
    public function rules()
    {
        return [
            'user_id' => [
                'NotEmpty' => [
                    'message' => 'Выберите сотрудника',
                ],
            ],
            'role_id' => [
                'NotEmpty' => [
                    'message' => 'Выберите роль',
                ],
                function($rule_id){
                    
                }
            ],
        ];
    }
    
    public function adapters()
    {
        return [
        ];
    }

    public function save()
    {
        $user_id = $this->getData('user_id');
        $rules = [];
        foreach($this->getData('rule', []) as $rule => $value){
             $rule = intval($rule);
             $rules[$rule] = $rule;
        }
        
        $mCodex = $this->model('Codex');
        $mData = $this->model('EmployeeData');
        $mUser = $this->model('User');
        
        $userrule = $mCodex->getByUser($user_id);
        $employee = $mData->getById($user_id);
        $role_id = $this->getData('role_id');
        if($employee['role_id'] != $role_id){
            $data = ['id' => $user_id, 'role_id' => $role_id];
            $mUser->upsert($data);
        }
        $ids = [];
        foreach($userrule as $rule){
            if(! empty($rule['codex_id'])){
                $ids[$rule['id']] = $rule['codex_id'];
            }
        }
        $add = array_diff_key($rules, $ids);
        $remove = array_diff_key($ids, $rules);
        if(! empty($add)){
            foreach($add as $_r){
                $data = ['user_id' => $user_id, 'rule_id' => $_r, 'is_allowed' => 1];
                $mCodex->insert($data);
            }
        }
        if(! empty($remove)){
            $delete = ['id' => $remove];
            $mCodex->delete($delete);
        }
        return $mCodex->getByUser($user_id);
    }
}