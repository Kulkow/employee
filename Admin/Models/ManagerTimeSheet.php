<?php

namespace Modules\Employee\Admin\Models;

use Modules\Employee\Admin\Models\Employee;
use Modules\Employee\Admin\Extensions\Calendar;

class ManagerTimeSheet extends \Classes\Base\Model
{
    const MIN_HOUR_WEEK = 45; // минимальное кол-во часов в неделю
    
    protected $table = 'manager_timesheet';

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `manager_timesheet` (
                `id` smallint(6) NOT NULL AUTO_INCREMENT,
                `user_id` mediumint(9) NOT NULL DEFAULT '0',
                `s1` time DEFAULT '09:00:00',
                `e1` time DEFAULT '18:00:00',
                `s2` time DEFAULT '09:00:00',
                `e2` time DEFAULT '18:00:00',
                `s3` time DEFAULT '09:00:00',
                `e3` time DEFAULT '18:00:00',
                `s4` time DEFAULT '09:00:00',
                `e4` time DEFAULT '18:00:00',
                `s5` time DEFAULT '09:00:00',
                `e5` time DEFAULT '18:00:00',
                `s6` time DEFAULT NULL,
                `e6` time DEFAULT NULL,
                `s0` time DEFAULT NULL,
                `e0` time DEFAULT NULL,
                `since` date DEFAULT NULL,
                `till` date DEFAULT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
          ";
    }
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                mt.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM manager_timesheet mt
            LEFT OUTER JOIN user u ON mt.user_id=u.id
            WHERE mt.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function getByUserId($id, $start = NULL, $end = NULL)
    {
        $t = $this->getByList(['user_id' => $id,
                                'start' => $start,
                                'end' => $end
                                ]);
        if($t){
            $t = array_pop($t);
        }
        return $t;
    }
    
    
    
    public function getByList(array $filter)
    {
        $criteria = $params = [];
        if(! empty($filter['start']) && ! empty($filter['end'])){
            $criteria['start'] = "(
                (mt.since >=  :start: AND mt.till <= :end:)
                OR
                (mt.since <= :start: AND mt.till >= :start:)
                OR
                (mt.since <= :start: AND mt.till IS NULL)
                OR
                (mt.since >= :start: AND mt.since <= :end: AND mt.till IS NULL)
                )";
            $params['start'] = $filter['start'];
            $params['end'] = $filter['end'];
        }else{
            $criteria['end'] = "mt.till IS NULL";
        }
        if(isset($filter['user_id'])){
            if(is_array($filter['user_id'])){
                $criteria['user_id'] = "mt.user_id IN (:user_id:)";
                $params['user_id'] = $filter['user_id'];    
            }else{
                $criteria['user_id'] = "mt.user_id = :user_id:";
                $params['user_id'] = $filter['user_id'];
            }
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                mt.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
                FROM manager_timesheet as mt
                LEFT OUTER JOIN user u ON mt.user_id=u.id
            {$where}
            ORDER BY mt.user_id
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function getByListArray(array $filter)
    {
        $timer = [];
        foreach($this->getByList($filter) as $row){
            $timer[strtotime($row['since'])] = $row;
        }
        return $timer;
    }
    
    
    public static function prepare(array $sheet){
        $sheet['lang'] = [];
        foreach(range(1,6) as $day){
            $sheet['lang'][$day] = [
                                    'day'=> $day,
                                    'lang' => Calendar::weekdays($day),
                                    'start'=> \Arr::get($sheet, 's'.$day, NULL),
                                    'end'=> \Arr::get($sheet, 'e'.$day, NULL),
                             ];    
        }
        $sheet['lang']['0'] = [
                                    'day'=> 0,
                                    'lang' => Calendar::weekdays(0),
                                    'start'=> \Arr::get($sheet, 's0', NULL),
                                    'end'=> \Arr::get($sheet, 'e0', NULL),
                             ];
        
        return $sheet;
    }
    
    public function progress($manager = null) 
    {
        $min = $this::MIN_HOUR_WEEK;
        $count = 0;
        if(null !== $manager){
            foreach(range(0,6) as $day){
                $s = \Arr::get($manager, "s".$day,null);
                $e = \Arr::get($manager, "e".$day,null);
                if($s AND $e){
                    $count += (strtotime($e) - strtotime($s))/3600;
                }
            }
        }
        $percent = 100*$count/$min;
        $percent = 100 <= $percent ? 100 : 0;
        return ['count' => $count, 'all' => $min, 'percent' => $percent];
    }
    
}