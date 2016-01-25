<?php

namespace Modules\Employee\Admin\Forms;

class BonusFilter extends \Classes\Base\Form
{
    public function filters()
    {
        return [
            'month' => 'intval',
            'year' => 'intval',
            ];
    }
    
    public function rules()
    {
        return [
            
        ];
    }

}