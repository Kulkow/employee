<?php

namespace Modules\Employee\Admin\Forms\Employee;

class Rule extends \Classes\Base\Form
{
    
    public function rules()
    {
        return [
            'user_id' => [
                'NotEmpty' => [
                    'message' => 'Выберите сотрудника',
                ],
            ],
            'rule_id' => [
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
        
        return;
    }
}