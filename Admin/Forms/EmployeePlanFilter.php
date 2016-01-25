<?php

namespace Modules\Employee\Admin\Forms;

class EmployeePlanFilter extends \Classes\Base\Form
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