<?php
namespace Modules\Employee\Admin\Extensions;

class Calendar 
{
    const MINUTE = 60;
    const HOUR = 3600;
    const DAY = 86400;
    const HOUR_MONTH = 198; //22*9;
    
    public static function GetWorkingDay($_start = NULL, $_end = NULL){
        $days = [];
        $start = new \DateTime($_start);
        $end = new \DateTime($_end);
        $end->modify('+1 day');
        $interval = $end->diff($start);
        $count = $interval->days;
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
        $holidays = self::getHolidays($_start, $_end);
        foreach($period as $dt) {
            $curr = $dt->format('D');
            if (in_array($dt->format('Y-m-d'), $holidays) || $curr == 'Sat' || $curr == 'Sun') {
               $count--;
            }else{
                $days[] = $dt->format('Y-m-d');
            }
        }
        return $days;
    }
    
    public static function GetWorkingDayCount($_start = NULL, $_end = NULL, $iscount = true){
        $start = new \DateTime($_start);
        $end = new \DateTime($_end);
        $end->modify('+1 day');
        $interval = $end->diff($start);
        $days = $interval->days;
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
        $holidays = self::getHolidays($_start, $_end);
        foreach($period as $dt) {
            $curr = $dt->format('D');
            if (in_array($dt->format('Y-m-d'), $holidays)) {
               $days--;
            }
            if ($curr == 'Sat' || $curr == 'Sun') {
                $days--;
            }
        }
        return $days;
    }
    
    // Праздничные или выходные дни
    public static function getHolidays($start = NULL,$end = NULL){
        return [];
    }
    
    public static function weekdays($day = FALSE){
        $days = array('Воскресенье','Понедельник','Вторник','Среда','Четверг' , 'Пятница' , 'Суббота');
        if(FALSE === $day){
        return $days;
        }else{
            return \Arr::get($days,$day);  
        }
    }
    
    public static function getPeriod(array $period){
        $min = \Arr::get($period, 'min', NULL);
        $max = \Arr::get($period, 'max', NULL);
        $start = new \DateTime($min);
        $end = new \DateTime($max);
        $interval = $end->diff($start);
        $end->add(new \DateInterval('P1M'));
        $max = $end->format('Y-m-d'); 
        return [
                'mounts' => range(1,12),
                'years' => range(date('Y', strtotime($min)), date('Y', strtotime($max))),
                ];
    }
    
    //fistr and last Day month
    public static function getPeriodMonth($month, $format = 'Y-m-d'){
        $_date = new \DateTime($month);
        $_date->add(new \DateInterval('P1M'));
        $_date->sub(new \DateInterval('P1D'));
        $end = $_date->format($format);
        return ['start' => date($format,strtotime($month)), 'end' => $end];
    }

    public static function getPrevMonth($date, $format = 'Y-m-d'){
        $_date = new \DateTime($date);
        $_date->sub(new \DateInterval('P1M'));
        return $_date->format($format);
    }
    
    /**
     * return ['year' => 2015, 'month' => 12] 
     */
    public static function getMonthYear($date){
        $stamp = strtotime($date);
        return ['month' => date('m',$stamp), 'year' => date('Y',$stamp)];
    }
}