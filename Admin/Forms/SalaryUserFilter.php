<?php

namespace Modules\Employee\Admin\Forms;

class SalaryUserFilter extends \Classes\Base\Form
{
    public function defaults()
    {
        return [
            'mount' => date('m'),
            'year' => date('Y'),
            ];
    }
    
    public function filters()
    {
        return [
            'mount' => 'intval',
            'year' => 'intval',
            ];
    }
    
    public function rules()
    {
        return [
            
        ];
    }
    
}