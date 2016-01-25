<?php

namespace Modules\Employee\Admin\Forms\Employee;

class Role extends \Classes\Base\Form
{
    
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