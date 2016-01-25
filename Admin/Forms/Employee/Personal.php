<?php

namespace Modules\Employee\Admin\Forms\Employee;

class Personal extends \Classes\Base\Form
{
    
    public function rules()
    {
        return [
            'phone' => [
                'NotEmpty' => [
                    'message' => 'Не заполнен телефон.',
                ],
            ],
            'lastname' => [
                'NotEmpty' => [
                    'message' => 'Не заполнена фамилия',
                ],
            ],
            'firstname' => [
                'NotEmpty' => [
                    'message' => 'Не заполнена фамилия',
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