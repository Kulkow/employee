<?php

namespace Modules\Employee\Admin\Forms;

class PlanOrgFilter extends \Classes\Base\Form
{
    public function defaults()
    {
        return [
            'owner_id' => OWNER_ID,
        ];
    }
    
    public function adapters()
    {
        return [
            'date' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
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

}