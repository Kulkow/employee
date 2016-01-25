<?php

namespace Modules\Employee\Admin\Forms\Calculator;
use Modules\Employee\Admin\Extensions\Calendar;

class Base extends \Classes\Base\Form
{
        
    public function defaults()
    {
        return [];
    }
    public function filters()
    {
        return ['base1' => 'intval',
                'base2' => 'intval',
        ];
    }
    
    public function rules()
    {
        return [
            'base1' => [
                'NotEmpty' => 'Введите оклад на период испытательного срока',
            ],
            'base2' => [
                'NotEmpty' => 'Введите оклад после испытательного срока',
            ],
            'end' => [
                'NotEmpty' => 'Не указана дата окончания испытального срока',
            ],
            'start' => [
                'NotEmpty' => 'Не указана дата начала работы',
                function($value){
                    
                }
            ],
        ];
    }
    
    public function adapters()
    {
        return ['base1' => [
                            'price',[],
                            ],
                'base2' => [
                            'price',[],
                            ],
                'start' => [
                            'date.formater',
                            ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
                        ],
                'end' => [
                            'date.formater',
                            ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
                        ],
                'start' => [
                            'date.formater',
                            ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
                        ],
                ];
    }
    

    public function save()
    {
        $data = $this->getData(['base1', 'base2','start', 'end', 'date', 'user_id']);
        $base1 = \Arr::get($data, 'base1');
        $base2 = \Arr::get($data, 'base2');
        $base1 = intval($base1/100);
        $base2 = intval($base2/100);
        
        $date = \Arr::get($data, 'date');
        $start = \Arr::get($data, 'start');
        $end = \Arr::get($data, 'end');
        
        $periodmonth = Calendar::getPeriodMonth($date);
        
        $count = date('t', strtotime($date));
        $start = (strtotime($start) > strtotime($periodmonth['start']) ? $start : $periodmonth['start']);
        $end = (strtotime($end) > strtotime($periodmonth['end']) ? $periodmonth['end'] : $end);
        
        $s = new \DateTime($start);
        $e = new \DateTime($end);
        $interval1 = $s->diff($e);
        
        $em = new \DateTime($periodmonth['end']);
        $interval2 = $e->diff($em);
        
        $coun1 =  $interval1->days; 
        $coun2 = $interval2->days;
        $percent1 = $coun1/$count;
        $percent2 = $coun2/$count;
        
        $html = $base1.'*'.$coun1.'/'.$count.' + '.$base2.'*'.$coun2.'/'.$count;
        $result = $base1*$percent1+$base2*$percent2;
        return ['result' => round($result),
                'html' => $html
                ];
    }
}