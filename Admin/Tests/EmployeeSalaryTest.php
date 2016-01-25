<?php
namespace Modules\Employee\Admin\Tests;

use Classes\DB\Connection;
use Classes\DB\Table;
use Classes\Auth\User;

use Modules\Employee\Admin\Models\EmployeeSalary;

class EmployeeSalaryTest
{
    /**
     * @var Classes\DB\Connection
     */
    protected $db;

    /**
     * @var Classes\Auth\User
     */
    protected $user;

    public function __construct(Connection $db, User $user)
    {
        $this->db = $db;
        $this->user = $user;
    }
    
    public function checkInterSectInterval($user_id){
        $intervals = $starts = $ends = $intersects = $_intervals = [];
        foreach($this->getByInervalsUser($user_id) as $interval){
            $stamp = strtotime($interval['start']);
            $stamp_end = empty($interval['end']) ? time() :  strtotime($interval['end']);
            if(isset($intervals[$stamp])){
                $intersects[] = ['id' => $interval['id'], 'intersect_id' => $intervals[$stamp]['id'], 'row' => $interval];
            }else{
                $intervals[$stamp] = ['start' => $stamp, 'end' => $stamp_end, 'id' => $interval['id'], 'row' => $interval];
                $starts[$stamp] = $stamp;
                $ends[$stamp] = $stamp_end;
            }
            $_intervals[$interval['id']] = $interval;
        }
        foreach($intervals as $interval){
            foreach($starts as $start){
                if($interval['start'] != $start){
                    $end = \Arr::get($ends, $start, null);
                    if($end){
                        $intersect = \Arr::get($intervals, $start, []);
                        $intersect = \Arr::get($_intervals, $intersect['id'], []);
                        if($interval['start'] > $start){
                            if($end >= $interval['start']){
                                $intersects[] = ['id' => $interval['id'], 'intersect_id' => $intersect['id'], 'row' => $intersect];
                            }
                        }else{
                            if($interval['end'] >= $start){
                                $intersects[] = ['id' => $interval['id'], 'intersect_id' => $intersect['id'], 'row' => $intersect];
                            }
                        }
                    }
                }
            }
        }
        $return = [];
        foreach($intersects as $intersect){
            if(! isset($return[$intersect['id']])){
                $_interval = \Arr::get($_intervals, $intersect['id'], []);
                $_interval['intersect'] = [];
                $return[$intersect['id']] = $_interval;
            }
            $return[$intersect['id']]['intersect'][] = $intersect;
        }
        return $return;
    }
    
    public function checkInterSectIntervalUsers(){
        $return = $users = [];
        foreach($this->getByInervalsUsers() as $interval){
            if(! isset($users[$interval['user_id']])) $users[$interval['user_id']] = [];
            $users[$interval['user_id']][] = $interval;
        }
        
        foreach($users as $user_id => $get_list){
            $intervals = $starts = $ends = $intersects = $_intervals = [];
            foreach($get_list as $interval){
                $stamp = strtotime($interval['start']);
                $stamp_end = empty($interval['end']) ? time() :  strtotime($interval['end']);
                if(isset($intervals[$stamp])){
                    $intersects[] = ['id' => $interval['id'], 'intersect_id' => $intervals[$stamp]['id'], 'row' => $interval];
                }else{
                    $intervals[$stamp] = ['start' => $stamp, 'end' => $stamp_end, 'id' => $interval['id'], 'row' => $interval];
                    $starts[$stamp] = $stamp;
                    $ends[$stamp] = $stamp_end;
                }
                $_intervals[$interval['id']] = $interval;
            }
            foreach($intervals as $interval){
                foreach($starts as $start){
                    if($interval['start'] != $start){
                        $end = \Arr::get($ends, $start, null);
                        if($end){
                            $intersect = \Arr::get($intervals, $start, []);
                            $intersect = \Arr::get($_intervals, $intersect['id'], []);
                            if($interval['start'] > $start){
                                if($end >= $interval['start']){
                                    $intersects[] = ['id' => $interval['id'], 'intersect_id' => $intersect['id'], 'row' => $intersect];
                                }
                            }else{
                                if($interval['end'] >= $start){
                                    $intersects[] = ['id' => $interval['id'], 'intersect_id' => $intersect['id'], 'row' => $intersect];
                                }
                            }
                        }
                    }
                }
            }
            foreach($intersects as $intersect){
                if(! isset($return[$intersect['id']])){
                    $_interval = \Arr::get($_intervals, $intersect['id'], []);
                    $_interval['intersect'] = [];
                    $return[$intersect['id']] = $_interval;
                }
                $return[$intersect['id']]['intersect'][] = $intersect;
            }
        }
        return $return;
    }
    
    protected function getByInervalsUser($user_id = null){
        $criteria = ["user_id" => " es.user_id=:user_id:"];
        $params = ["user_id" => $user_id];
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_salary as es
            LEFT JOIN user u ON u.id = es.user_id
            {$where}
            ORDER BY es.start
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    protected function getByInervalsUsers(){
        $criteria = ["owner_id" => " u.owner_id=:owner_id:"];
        $params = ["owner_id" => OWNER_ID];
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_salary as es
            LEFT JOIN user u ON u.id = es.user_id
            {$where}
            ORDER BY es.user_id,es.start
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
}