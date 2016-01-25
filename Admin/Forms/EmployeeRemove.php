<?php

namespace Modules\Employee\Admin\Forms;

class EmployeeRemove extends \Classes\Base\Form
{
    public function rules()
    {
        return [
            'end' => [
                'NotEmpty' => 'Введите дату окончания работы в подразделении',
            ],
        ];
    }
    
    public function adapters()
    {
        return [
            'start' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'end' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
        ];
    }

    public function save()
    {
        $data = $this->getData(['id', 'end']);
        $this->model('Employee')->upsert($data);
        $employee = $this->model('Employee')->getById($data['id']);
        return $employee;
    }
}