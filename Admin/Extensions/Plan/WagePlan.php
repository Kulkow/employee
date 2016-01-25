<?php

namespace Modules\Employee\Admin\Extensions\Plan;

use Modules\Employee\Admin\Extensions\Calendar;

class WagePlan extends Indicator {
    
    public function getValues() {
        parent::getValues();
        
        $date = $this->start;
        $results = array();
        
        while ($date <= $this->end) {
            $data = $this->getWorkingDaysData($date);
            foreach ($this->users as $curr) $results[] = array('user_id'=>$curr,'date'=>$date,'cnt'=>($data[0]/($data[0]+$data[1])));
            $date = date('Y-m',mktime(0, 0, 0, date('m', strtotime($date.'-01'))+1, date("01"), date('Y', strtotime($date.'-01'))));
        }
        
        return $this->processResult($results);
    }
    
    public function calculate() {
        if(empty($this->ids)){
            return FALSE;
        }
        $results = [];
        $end = new \DateTime($this->end_day);
        $start = new \DateTime($this->start_day);
        $c = new \DateTime();
        $interval = $start->diff($c);
        $count = ! $interval->invert ? $interval->days : 0;
        $all = $end->format('d');
        $count = $count > $all ? $all : $count;
        foreach ($this->ids as $_id){
            $results[] = ['user_id' => $_id,
                          'date' => $this->start,
                          'cnt'=> round($count/$all,2)
                          ];
        }
        return $this->PreSetCalculate($results);
    }
    
}