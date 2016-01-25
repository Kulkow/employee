<?php

namespace Modules\Employee\Admin\Forms;

class Password extends \Classes\Base\Form
{
    public function filters()
    {
        return ['password' => 'trim'];
    }
    
    public function rules()
    {
        return [
            'password' => [
                'NotEmpty' => [
                    'message' => 'Не заполнен пароль.',
                ],
                'Length' => [
                    'min' => 6,
                    'message' => 'Поле ":field:" должно быть более :min: символов.',
                ],
            ],
        ];
    }
    
    public function save()
    {
        $user_id = $this->getData('user_id');
        $password = $this->getData('password');
        $data = ['user_id' => $user_id,
                 'salary_password' => $password,
                ];
        $this->model('EmployeeData')->upsert($data);
        return $this->model('EmployeeData')->getById($user_id);
    }
}