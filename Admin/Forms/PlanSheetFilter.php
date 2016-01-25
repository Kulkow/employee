<?php

namespace Modules\Employee\Admin\Forms;

class PlanSheetFilter extends \Classes\Base\Form
{
    public function defaults()
    {
        $allyear = $this->getData('year', date('Y')) - 1;
        return [
            'month' => date('m'),
            'year' => date('Y'),
            'allyear' => $allyear,
            ];
    }
    
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
    
    public function init()
    {
        $data = $this->getSafeData();
        $start = \Arr::get($data, 'start');
        $end = \Arr::get($data, 'end');
        if(! $end){
            $end = $start;
        }
        $mount = \Arr::get($data, 'month', date('m'));
        $year = \Arr::get($data, 'year', date('Y'));
        if((! $start AND ! $end)){
            $end = $year.'-'.$mount.'-01';
            $c = new \DateTime($end); // для рассчета год назад
            $c->sub(new \DateInterval('P3M'));// по умолчанию за пол года
            $start = $c->format('Y-m-d');
            $this->setData('start', $start);
            $this->setData('end', $end);
        }else{
            $month = date('m', strtotime($start));
            $year = date('Y', strtotime($start));
            $this->setData('month', $month);
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