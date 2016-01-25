<?php

namespace Modules\Employee\Admin\Forms;

class Login extends \Classes\Base\Form
{
    public function rules()
    {
        return [
            'password' => [
                'NotEmpty' => [
                    'message' => 'Не заполнен пароль.',
                ],
                function ($password) {
                    if (! $this->model('EmployeeData')->has($password)) {
                        return 'Неверный пароль.';
                    }
                }
            ],
        ];
    }
}