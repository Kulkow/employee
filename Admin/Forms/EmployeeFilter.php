<?php

namespace Modules\Employee\Admin\Forms;

class EmployeeFilter extends \Classes\Base\Form
{
    public function filters()
    {
        return [
            'name' => 'trim',
            ];
    }
    
    public function rules()
    {
        return [
            
        ];
    }

}