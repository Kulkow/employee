<?php

namespace Modules\Employee\Admin\Models;

use Modules\Employee\Admin\Models\Employee;
use Modules\Employee\Admin\Extensions\Calendar;

class TimeSheet extends \Classes\Base\Model
{
    
    const PRICE_LATENESS0 = 10000; //просто опаздал
    const PRICE_LATENESS10 = 5000; //опаздал более чем на 10 минут
    const PRICE_LATENESS = 300; //за каждую минуту
    const PRICE_NOT_CLOSE = 15000; //за незакрытый день
    
    const TYPE_SKIP = 1; //пропуск
    const TYPE_FINE = 2; //штраф
    const TYPE_SKIPDAY = 3; //пропущен целый день
    const TYPE_FINEDAY = 4; //штраф целый день
    const TYPE_BEFORE = 5; //ушел раньше
    const TYPE_BEFORE_FINE = 6; //ушел раньше штраф
    const TYPE_NOTCLOSE = 7; //не закрыл лк
    
    const TYPE_SKIP_NEW = 0; // назначенное вручную
    const TYPE_SKIP_FINE = 1; //штраф
    const TYPE_SKIP_TIME = 2; //неотработанное время
    const TYPE_SKIP_BONUS = 3; //неотработанное время

    const MAXSKIP = 4320; // 3 дня(минут) пропуска откладывают выдачу
    
    protected $table = 'timesheet';

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `timesheet` (
            `id` smallint(6) NOT NULL AUTO_INCREMENT,
            `employee_id` mediumint(9) NOT NULL DEFAULT '0',
            `start` date DEFAULT NULL,
            `finish` date DEFAULT NULL
            PRIMARY KEY (`id`),
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
          ";
    }

    /**
     * @param $id
     * @param null $start
     * @param null $end
     * @return mixed
     */
    public function getByUserId($id, $start = NULL, $end = NULL)
    {
        return $this->getByList(['user_id' => $id,
                                 'start' => $start,
                                 'end' => $end
                                 ]);
    }

    /**
     * @param array $filter
     * @return array
     */
    public function getByListArray(array $filter)
    {
        $timesheet = [];
        foreach ($this->getByList($filter) as $sheet){
            if(! isset($timesheet[$sheet['employee_id']])){
                $timesheet[$sheet['employee_id']] = [];
            }
            $date = date('Y-m-d', strtotime($sheet['start']));
            $timesheet[$sheet['employee_id']][$date] = $sheet;
        }
        return $timesheet;
    }

    /**
     * @param array $filter
     * @return mixed
     */
    public function getByList(array $filter)
    {
        $criteria = $params = [];
        if(isset($filter['start']) && isset($filter['end'])){
            /*$criteria['start'] = "
                (ts.start >=  :start: AND (ts.finish <= :end: OR ts.finish IS NULL))
                ";*/
            $criteria['start'] = "
                ((ts.start >=  :start: AND ts.finish <= :end:) OR (ts.start >=  :start: AND ts.finish IS NULL))
                ";
            $params['start'] = $filter['start'].' 00:00:00';
            $params['end'] = $filter['end'].' 23:59:59';
        }
        if(isset($filter['user_id'])){
            if(is_array($filter['user_id'])){
                $criteria['user_id'] = "ts.employee_id IN (:user_id:)";
                $params['user_id'] = $filter['user_id'];    
            }else{
                $criteria['user_id'] = "ts.employee_id = :user_id:";
                $params['user_id'] = $filter['user_id'];
            }
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ts.*,
                u.id user_id,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM timesheet ts
            LEFT JOIN user u ON ts.employee_id=u.id
            {$where}
            ORDER BY ts.employee_id
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    
    public static function prepare(array $bonus){
        //$type = self::getStatus(\Arr::get($bonus, 'type', 1), \Arr::get($bonus, 'time'));
        $type = self::getStatusSkip(\Arr::get($bonus, 'skip_type', 0), \Arr::get($bonus, 'time'));
        if(! empty($bonus['late'])){
            $type = 'Опоздал к '.$bonus['late'].'. '.$type;
        }
        $_bonus = ['type' => $type,
                   'vtype' => \Arr::get($bonus, 'type', 1),
                  'date' => \Arr::get($bonus, 'date'),
                  'start' => \Arr::get($bonus, 'start'),
                  'end' => \Arr::get($bonus, 'finish'),
                  'amount' => \Arr::get($bonus, 'amount'),
                  'skip_type' => \Arr::get($bonus, 'skip_type'),
                  'skip_time' => \Arr::get($bonus, 'skip_time'),
                  'approved' => 1,
                  'is_approved' => 0,
                  'creator_id' => 0,
                  'name' => '',
                  'id' => 'vid',
                  'comment' => '',
                  'virtual' => 1
                  ];
        return $_bonus;
    }
    
    //Склейка запросов и пропусков
    public static function cluining(array $bonuses, array $skipping){
        //$bonuses = [];
        array_walk($skipping, function(&$_bonus){
            $_bonus = self::prepare($_bonus);
        });
        foreach($skipping as $_skipping){
            $bonuses[] = $_skipping;
        }
        return $bonuses;
    }
    
    protected static function getStatus($status = NULL, $time = NULL){
        $return = '';
        switch ($status){
            case self::TYPE_SKIP:
                $return = 'Не отработано <b>:time:</b>';
            break;
        
            case self::TYPE_BEFORE:
                $return = 'Не отработано <b>:time:</b> ушел раньше';
            break;
        
        
            case self::TYPE_FINE:
                $return = 'Штраф за не отработаное время <b>:time:</b>';
            break;
            
            case self::TYPE_SKIPDAY:
                $return = 'Не отработан целый день';
            break;

            case self::TYPE_NOTCLOSE:
                $return = 'Не вышел из личного кабинета';
            break;
            
            case self::TYPE_FINEDAY:
                $return =  'Штраф за не отработаный день';
            break;
        }
        $minute = ceil($time/60);
        $_time = \Text::declension($minute, ['минута', 'минуты', 'минут']);
        $return = str_replace(':time:',  $minute.' '.$_time, $return);
        return $return;
    }
    
    public static function getStatusSkip($status = NULL, $time = NULL){
        $return = '';
        switch ($status){
            case self::TYPE_SKIP_FINE:
                $return = 'Пропущено минут: :time: (деп.)';
            break;
        
            case self::TYPE_SKIP_TIME:
                $return = 'Не отработано :time: (деп.)';
            break;
        }
        $minute = ceil($time/60);
        $_time = \Text::declension($minute, ['минута', 'минуты', 'минут']);
        $return = str_replace(':time:',  $minute.' '.$_time, $return);
        return $return;
    }
    
    //Склеим график и отработанное время
    public static function cluiningTime($tmanager,  $timesheet = [], $workdays = [], $end_mount = NULL){
        $times = [];
        //weekdays
        foreach($tmanager as $_tmanager){
            $period_start = strtotime($_tmanager['since']);
            $period_end = strtotime(\Arr::get($_tmanager,'till', $end_mount.' 23:59:59'));
            foreach($workdays as $_wodrk_day){
                $notclose = false;
                $recast = 0;
                if(strtotime($_wodrk_day) < time()){
                    $sheet = \Arr::get($timesheet, $_wodrk_day, NULL);
                    if($sheet){
                        $stamp = strtotime($sheet['start']);
                        $stamp_end = strtotime($sheet['finish']);
                        $w = date('w', $stamp);
                        $time = strtotime(date('H:i:s', $stamp));
                        $finish_day = date('Y-m-d', $stamp_end);
                        if($finish_day != $_wodrk_day){
                            $d = strtotime($finish_day) - strtotime($_wodrk_day);//
                            //$time = null;
                            //$finish = null;
                            if($d > 0){
                                $notclose = true;
                            }
                        }else{
                            $finish = strtotime(date('H:i:s', $stamp));
                        }
                        $time_work = $finish - $time;
                        $hour = $time_work/3600;
                        $hour = ceil($hour/0.5)*0.5;
                        
                        $timetable_start = \Arr::get($_tmanager, 's'.$w, 0);
                        $timetable_end = \Arr::get($_tmanager, 'e'.$w, 0);
                        if(! empty($timetable_start) AND ! empty($timetable_end)){
                            if($stamp > $period_start AND $stamp < $period_end){ //входит в отрезок
                                //recast
                                if($time > 0) {
                                    $recast_before = strtotime($timetable_start) - $time;
                                    if($recast_before > 0){
                                        $recast += floor($recast_before/3600);
                                    }
                                }
                                if($finish > 0) {
                                    $recast_after = $finish - strtotime($timetable_end);
                                    if($recast_after > 0){
                                        $recast += floor($recast_after/3600);
                                    }
                                }
                                $times[$_wodrk_day] = [
                                                       'day' => $w,
                                                       'lang' => Calendar::weekdays($w),
                                                        'date' => $_wodrk_day,
                                                        'start' => $sheet['start'],
                                                        'finish' => $sheet['finish'],
                                                        's' => $timetable_start,
                                                        'e' => $timetable_end,
                                                        'hour' => $hour,
                                                        'notclose' => $notclose,
                                                        'recast' => $recast,
                                                       ];
                                unset($timesheet[$_wodrk_day]);
                            }
                        }
                    }else{
                        $stamp = strtotime($_wodrk_day);
                        $w = date('w', $stamp);
                        $timetable_start = \Arr::get($_tmanager, 's'.$w, NULL);
                        $timetable_end = \Arr::get($_tmanager, 'e'.$w, NULL);
                        if($timetable_start){
                            if($stamp > $period_start AND $stamp < $period_end){ //входит в отрезок
                                $times[$_wodrk_day] = [
                                                   'day' => $w,
                                                   'lang' => Calendar::weekdays($w),
                                                    'date' => $_wodrk_day,
                                                    'late' => $timetable_start,
                                                    'start' => 0,
                                                    'finish' => 0,
                                                    's' => $timetable_start,
                                                    'e' => $timetable_end,
                                                   ];
                            }
                        }
                    }
                }else{
                    
                }
            }
        }
        /** обработаем выходные дни */
        if(! empty($timesheet)){
            foreach($tmanager as $_tmanager){
                $period_start = strtotime($_tmanager['since']);
                $period_end = strtotime(\Arr::get($tmanager,'till', $end_mount.' 23:59:59'));
                foreach($timesheet as $sheet){
                    if($sheet){
                        $stamp = strtotime($sheet['start']);
                        $stamp_end = strtotime($sheet['finish']);
                        $w = date('w', $stamp);
                        $time = strtotime(date('H:i:s', $stamp));
                        $finish = strtotime(date('H:i:s', $stamp_end));
                        $timetable_start = \Arr::get($_tmanager, 's'.$w, NULL);
                        $timetable_end = \Arr::get($_tmanager, 'e'.$w, NULL);
                        if($stamp > $period_start AND $stamp < $period_end){ //входит в отрезок
                            $_wodrk_day = date('Y-m-d',$stamp);
                            $time_work = $finish - $time;
                            $hour = $time_work/3600;
                            $hour = ceil($hour/0.5)*0.5;
                            $times[$_wodrk_day] = [
                                               'day' => $w,
                                               'lang' => Calendar::weekdays($w),
                                                'date' => $_wodrk_day,
                                                'start' => $sheet['start'],
                                                'finish' => $sheet['finish'],
                                                's' => $timetable_start,
                                                'e' => $timetable_end,
                                                'hour' => $hour,
                                               ];
                        }
                    }
                }
            }
        }
        ksort($times);
        return $times;
    }
    
    public function latenes(array $sheet,$price_hour = 0){
        $notclose = false;
        if(! empty($sheet['notclose'])){
            $sheet['_start'] = $sheet['start'];
            $sheet['_finish'] = $sheet['finish'];
            $sheet['start'] = 0;
            $sheet['finish'] = 0;
        }
        if(! empty($sheet['start'])){
            $start = strtotime($sheet['start']);
            $start = strtotime(date('H:i:s', $start));
        }else{
            $start = 0;
        }
        if(! empty($sheet['finish'])){
            $finish = strtotime($sheet['finish']);
            $finish = strtotime(date('H:i:s', $finish));
        }else{
            $finish = 0;
        }
        $price_minute = $price_hour/60;
        
        $timetable_start = $sheet['s'];
        $timetable_end = $sheet['e'];
        $skipping = [];
        $is_all_day = false;
        
        if(! empty($finish) AND (strtotime($timetable_start) > $finish)){
            $finish = 0;
            $start = 0;
        }
        if(! empty($start) AND (strtotime($timetable_end) < $start)){
            $finish = 0;
            $start = 0;
        }
        if(empty($timetable_start) and empty($timetable_end)){
            $lateness = 0;
        }else{
            $lateness = $start - strtotime($timetable_start);
        }
        if(empty($finish) and empty($start)){
            if(strtotime($sheet['date'].' '.$timetable_end) < time()){
                $gohome = strtotime($timetable_end) - strtotime($timetable_start); // неотработал полный день
                $is_all_day = true;
            }else{
                $gohome = 0;
            }
        }else{
            if($finish){
                $gohome = strtotime($timetable_end) - $finish; // ушел раньше
            }
        }
        if(! empty($sheet['notclose'])){
            $notclose = true;
        }
        if(! empty($sheet['notclose'])){
            $sheet['start'] = $sheet['_start'];
            $sheet['finish'] = $sheet['_finish'];
        }
        if($lateness > 0){
            $_price = -$this::PRICE_LATENESS0;
            $minute = floor($lateness/60);
            $late = date('H:i', strtotime($sheet['s']));
            if($minute > 10){
                $_price -= $this::PRICE_LATENESS10;
                $late = date('H:i', strtotime($sheet['s']) + 10*60);
            }
            $base_salary = $price_minute * $minute;
            if($base_salary < 100) $base_salary = 100;
            $base_salary = round($base_salary, -2);
            $_price = $_price - $this::PRICE_LATENESS * $minute;
            $skipping[] = ['date' => $sheet['date'],
                           'time' => $lateness,
                           'skip_time' => $minute,
                           'start' => $sheet['start'],
                           'finish' => $sheet['finish'],
                           's' => $sheet['s'],
                           'e' => $sheet['e'],
                           'type' => $this::TYPE_SKIP, // пропушенное время
                           'skip_type' => $this::TYPE_SKIP_TIME, // пропушенное время
                           'notclose' => $notclose,
                           'amount' => -$base_salary,
                           ];
            $skipping[] = ['date' => $sheet['date'],
                           'time' => $lateness,
                           'skip_time' => $minute,
                           'start' => $sheet['start'],
                           'finish' => $sheet['finish'],
                           's' => $sheet['s'],
                           'e' => $sheet['e'],
                           'late' => $late,
                           'type' => $this::TYPE_FINE, // штраф за опоздание
                           'skip_type' => $this::TYPE_SKIP_FINE, // штраф за опоздание
                           'notclose' => $notclose,
                           'amount' => $_price
                           ];
        }
        if($gohome > 0){
            $minute = floor($gohome/60);
            $base_salary = round($price_minute * $minute, -2);
            $_price = $this::PRICE_LATENESS * $minute;
            if($is_all_day){
                $_price = $_price+$this::PRICE_LATENESS0+$this::PRICE_LATENESS10;    
            }
            $skipping[] = ['date' => $sheet['date'],
                           'time' => $gohome,
                           'skip_time' => $minute,
                           'start' => $sheet['start'],
                           'finish' => $sheet['finish'],
                           's' => $sheet['s'],
                           'e' => $sheet['e'],
                           'type' => $this::TYPE_BEFORE, // пропушенное время
                           'skip_type' => $this::TYPE_SKIP_TIME, // пропушенное время
                           'notclose' => $notclose,
                           'amount' => -$base_salary
                           ];
            $skipping[] = ['date' => $sheet['date'],
                           'time' => $gohome,
                           'skip_time' => $minute,
                           'start' => $sheet['start'],
                           'finish' => $sheet['finish'],
                           'late' => ($is_all_day ? date('H:i', strtotime($sheet['s']) + 10*60) : 0),
                           's' => $sheet['s'],
                           'e' => $sheet['e'],
                           'type' => $this::TYPE_BEFORE, // ШТРАФ
                           'skip_type' => $this::TYPE_SKIP_FINE, // штраф
                           'notclose' => $notclose,
                           'amount' => -$_price
                           ];
        }
        array_walk($skipping, function(&$skip){
            $skip = self::prepare($skip);
        });

        $sheet['skipping'] = $skipping;
        return $sheet;
    }
    
    /**
    *
    **/
    public function getByLatenes($tmanager = [], $timesheet = [], $workdays = [], $end_mount = NULL, $price_hour = 0)
    {
        $skipping  = [];
        $price_lateness = $this::PRICE_LATENESS; // за каждую минуту
        $price_lateness_10 = $this::PRICE_LATENESS10; //опоздал на секунду на 10 минут
        $price_lateness_0 = $this::PRICE_LATENESS0; //тупо опоздал на секунду
        foreach($tmanager as $_tmanager){
            $period_start = strtotime($_tmanager['since']);
            $period_end = strtotime(\Arr::get($tmanager,'till', $end_mount.' 23:59:59'));
            foreach($workdays as $_wodrk_day){
                $sheet = \Arr::get($timesheet, $_wodrk_day, NULL);
                if($sheet){
                    $stamp = strtotime($sheet['start']);
                    $stamp_end = strtotime($sheet['finish']);
                    $w = date('w', $stamp);
                    $time = strtotime(date('H:i:s', $stamp));
                    $finish = strtotime(date('H:i:s', $stamp_end));
                    $timetable_start = \Arr::get($_tmanager, 's'.$w, '09:00:00');
                    $timetable_end = \Arr::get($_tmanager, 'e'.$w, '18:00:00');
                    if($stamp > $period_start AND $stamp < $period_end){ //входит в отрезок
                        $lateness = $time - strtotime($timetable_start);
                        $gohome = strtotime($timetable_end) - $finish; // ушел раньше
                        if($lateness > 0){
                            $_price = -$price_lateness_0;
                            $minute = floor($lateness/60);
                            if($minute > 10){
                                $_price -= $price_lateness_10;
                            }
                            $_price = $_price - $price_lateness * $minute;
                            $_date = date('Y-m-d',$stamp);
                            $skipping[] = ['date' => $_date,
                                           'time' => $lateness,
                                           'start' => $sheet['start'],
                                           'finish' => $sheet['finish'],
                                           's' => $timetable_start,
                                           'e' => $timetable_end,
                                           'type' => $this::TYPE_SKIP, // пропушенное время
                                           'amount' => $_price
                                           ];
                            $skipping[] = ['date' => $_date,
                                           'time' => $lateness,
                                           'start' => $sheet['start'],
                                           'finish' => $sheet['finish'],
                                           's' => $timetable_start,
                                           'e' => $timetable_end,
                                           'type' => $this::TYPE_FINE, // штраф за опоздание
                                           'amount' => $_price
                                           ];
                        }
                        if($gohome > 0){
                            $minute = floor($gohome/60);
                            $_price = $price_lateness * $minute;
                            $_date = date('Y-m-d',$stamp);
                            $skipping[] = ['date' => $_date,
                                           'time' => $gohome,
                                           'start' => $sheet['start'],
                                           'finish' => $sheet['finish'],
                                           's' => $timetable_start,
                                           'e' => $timetable_end,
                                           'type' => $this::TYPE_BEFORE, // пропушенное время
                                           'amount' => $_price
                                           ];
                        }
                        unset($timesheet[$_wodrk_day]);
                    }
                }else{
                    $stamp = strtotime($_wodrk_day);
                    $w = date('w', $stamp);
                    $timetable_start = \Arr::get($_tmanager, 's'.$w, NULL);
                    $timetable_end = \Arr::get($_tmanager, 'e'.$w, NULL);
                    if($timetable_start){
                        if($stamp > $period_start AND $stamp < $period_end){ //входит в отрезок
                            $hour = (strtotime($timetable_end) - strtotime($timetable_start))*60; 
                            $time = $hour * 60 * 60; //полный день
                            $_price = round($hour * $price_hour, -2);
                            $skipping[] = ['date' => $_wodrk_day,
                                            'time' => $time,
                                            'start' => $timetable_start,
                                            'finish' => $timetable_end,
                                            's' => $timetable_start,
                                            'e' => $timetable_end,
                                            'type' => $this::TYPE_SKIPDAY, // пропустил день
                                            'amount' => -$_price
                                            ];
                            $skipping[] = ['date' => $_wodrk_day,
                                            'time' => $time,
                                            'start' => $timetable_start,
                                            'finish' => $timetable_end,
                                            's' => $timetable_start,
                                            'e' => $timetable_end,
                                            'type' => $this::TYPE_FINEDAY, // штраф за пропушенный день
                                            'amount' => -$_price
                                            ];
                        }
                    }
                }
            }
        }
        /** обработаем выходные дни */
        if(! empty($timesheet)){
            foreach($tmanager as $_tmanager){
                $period_start = strtotime($_tmanager['since']);
                $period_end = strtotime(\Arr::get($tmanager,'till', $end_mount.' 23:59:59'));
                foreach($timesheet as $sheet){
                    if($sheet){
                        $stamp = strtotime($sheet['start']);
                        $stamp_end = strtotime($sheet['finish']);
                        $w = date('w', $stamp);
                        $time = strtotime(date('H:i:s', $stamp));
                        $finish = strtotime(date('H:i:s', $stamp_end));
                        $timetable_start = \Arr::get($_tmanager, 's'.$w, NULL);
                        $timetable_end = \Arr::get($_tmanager, 'e'.$w, NULL);
                        if($stamp > $period_start AND $stamp < $period_end){ //входит в отрезок
                            $lateness = $time - strtotime($timetable_start);
                            $gohome = strtotime($timetable_end) - $finish; // ушел раньше
                            if($lateness > 0){
                                $_price = -$price_lateness_0;
                                $minute = floor($lateness/60);
                                if($minute > 10){
                                    $_price -= $price_lateness_10;
                                }
                                $_price = $_price - $price_lateness * $minute;
                                $_date = date('Y-m-d',$stamp);
                                $skipping[] = ['date' => $_date,
                                               'time' => $lateness,
                                               'start' => $sheet['start'],
                                               'finish' => $sheet['finish'],
                                               's' => $timetable_start,
                                               'e' => $timetable_end,
                                               'type' => $this::TYPE_SKIP, // пропушенное время
                                               'amount' => $_price
                                               ];
                                $skipping[] = ['date' => $_date,
                                               'time' => $lateness,
                                               'start' => $sheet['start'],
                                               'finish' => $sheet['finish'],
                                               's' => $timetable_start,
                                               'e' => $timetable_end,
                                               'type' => $this::TYPE_FINE, // штраф за опоздание
                                               'amount' => $_price
                                               ];
                            }
                            if($gohome > 0){
                                $minute = floor($gohome/60);
                                $_price = $price_lateness * $minute;
                                $_date = date('Y-m-d',$stamp);
                                $skipping[] = ['date' => $_date,
                                               'time' => $gohome,
                                               'start' => $sheet['start'],
                                               'finish' => $sheet['finish'],
                                               's' => $timetable_start,
                                               'e' => $timetable_end,
                                               'type' => $this::TYPE_SKIP, // пропушенное время
                                               'amount' => $_price
                                               ];
                            }
                            unset($timesheet[$_wodrk_day]);
                        }
                    }
                }
            }
        }
        return $skipping;
    }

}