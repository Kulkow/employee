<?php

namespace Modules\Employee\Admin\Forms;

class SalaryFilter extends \Classes\Base\Form
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
    
    public function init()
    {
        $data = $this->getSafeData();
        $start = \Arr::get($data, 'start');
        $end = \Arr::get($data, 'end');
        if(! $end){
            $end = $start;
        }
        $mount = \Arr::get($data, 'mount');
        $year = \Arr::get($data, 'year');
        if((! $start AND ! $end) OR ($start == $end)){
            $start = $year.'-'.$mount.'-01';
            $this->setData('start', $start);
        }else{
            $mount = date('m', strtotime($start));
            $year = date('Y', strtotime($start));
            $this->setData('mount', $mount);
            $this->setData('year', $year);
        }
        if($start > $end){
            $this->setData('end', $start);
        }
        return $this;
    }
    
    public function adapters()
    {
        $adapters = [
            'start' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'end' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
        ];
        return $adapters;
    }

}