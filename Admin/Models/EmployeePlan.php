<?php

namespace Modules\Employee\Admin\Models;

use Modules\Employee\Admin\Models\Employee;

/*
* Текущие планы пользователей
*
**/
class EmployeePlan extends \Classes\Base\Model
{
    
    protected $table = 'employee_plan';

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `employee_plan` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `plan_id` int(11) NOT NULL,
              `value` decimal(11,2) NOT NULL,
              `start` datetime DEFAULT NULL,
              `end` datetime DEFAULT NULL,
              `creater` int(11) NOT NULL,
              `updater` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `plan_id` (`plan_id`),
              KEY `user_id` (`user_id`)
            ) ENGINE=Aria  DEFAULT CHARSET=utf8 COLLATE=utf8_bin PAGE_CHECKSUM=1";
          return " ";
    }
    
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                ep.*,
                p.id as plan_id,
                p.name as plan,
                p.name as name,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common
            FROM employee_plan ep
            LEFT OUTER JOIN plan p ON p.id = ep.plan_id
            WHERE ep.id = :id:
            ORDER BY p.name
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    /**
    * Список плановых
    **/
    public function getByUserId($id, $filter = [])
    {
        $_filter = ['user_id' => $id]+ $filter;
        $plans = [];
        return $this->getByList($_filter);
        foreach($this->getByList($_filter) as $plan){
            $plans[$plan["plan_id"]] = $plan; //?
        }
        return $plans;
    }

    public function getByList(array $filter)
    {
        $criteria = ["oid" => "u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($filter['user_id'])){
            $criteria['user_id'] = "ep.user_id = (:user_id:)";
            $params['user_id'] = $filter['user_id'];
        }
        if(! empty($filter['users']) && count($filter['users'])){
            $criteria['user_id'] = "ep.user_id IN (:user_ids:)";
            $params['user_ids'] = $filter['users'];
        }
        if(isset($filter['is_plan_based'])){
            $criteria['is_plan_based'] = "p.is_plan_based = (:is_plan_based:)";
            $params['is_plan_based'] = $filter['is_plan_based'];
        }
        if(! empty($filter['start']) AND ! empty($filter['end'])){
            $criteria['start'] = "(
                (ep.start >=  :start: AND ep.end <= :end:)
                OR
                (ep.start <= :start: AND ep.end >= :start:)
                OR
                (ep.start <= :start: AND ep.end IS NULL)
                OR
                (ep.start >= :start: AND ep.start <= :end: AND ep.end IS NULL)
                )";
            $params['start'] = $filter['start'];
            $params['end'] = $filter['end'];
        }else{
            $criteria["end"] = "ep.end IS NULL";
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                u.id,
                ep.*,
                p.name,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) u_name
            FROM employee_plan ep
            LEFT OUTER JOIN user u ON u.id = ep.user_id
            LEFT OUTER JOIN plan p ON p.id = ep.plan_id
            {$where}
            ORDER BY u.id, p.is_plan_based desc
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function getUnique(array $filter)
    {
        if(! empty($filter['plan_id']) AND ! empty($filter['user_id']) AND ! empty($filter['start'])){
            $criteria = $params = [];
            $criteria['start'] = "ep.user_id =  :user_id: AND ep.plan_id =  :plan_id: AND ep.start =  :start:";
            $params['start'] = $filter['start'];
            $params['plan_id'] = $filter['plan_id'];
            $params['user_id'] = $filter['user_id'];
            
            if(! empty($filter['department_id'])){
                $criteria['department_id'] = "ep.department_id =  :department_id:";
                $params['department_id'] = $filter['department_id'];
            }
            if(! empty($filter['id'])){
                $criteria['id'] = "ep.id !=  :id:";
                $params['id'] = $filter['id'];
            }
            $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
            $query = $this->db->newStatement("
                SELECT
                    ep.id
                FROM employee_plan ep
                {$where}
                ORDER BY ep.id
            ");
            $query->bind($params);
            return $query->getFirstRecord();
        }else{
            return false;
        }
    }
    
    public function getDepartments($user_id = null, $filter = []){
        $departments = [];
        $criteria = ['user_id' => 'ep.user_id = :user_id:'];
        $params = ['user_id' => $user_id];
        if(! empty($filter['start']) AND ! empty($filter['end'])){
            $criteria['start'] = "(
                (ep.start >=  :start: AND ep.end <= :end:)
                OR
                (ep.start <= :start: AND ep.end >= :start:)
                OR
                (ep.start <= :start: AND ep.end IS NULL)
                OR
                (ep.start >= :start: AND ep.start <= :end: AND ep.end IS NULL)
                )";
            $params['start'] = $filter['start'];
            $params['end'] = $filter['end'];
        }else{
            $criteria["end"] = "ep.end IS NULL";
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ep.*,
                pm.department_id as d_id
            FROM employee_plan ep
            INNER JOIN pm_employee as pm ON pm.user_id=ep.user_id
            {$where}
            ORDER BY ep.id
        ");
        $query->bind($params);
        foreach($query->getAllRecords() as $d){
            if(! empty($d['department_id'])){
                $departments[$d['department_id']] = $d['department_id'];
            }
            if(! empty($d['d_id'])){
                $departments[$d['d_id']] = $d['d_id'];
            }
        }
        return $departments;
    }
    
    /**
    * Плановые показатели
    **/
    public function getByListUsers(array $filter, $date = NULL)
    {
        $criteria = ["oid" => "u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($filter['users']) && count($filter['users'])){
            $criteria['user_id'] = "ep.user_id IN (:user_ids:)";
            $params['user_ids'] = $filter['users'];
        }
        if(! $date){
            $criteria["end"] = "ep.end IS NULL";
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ep.*,
                p.name,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) u_name
            FROM employee_plan ep
            LEFT OUTER JOIN user u ON u.id = ep.user_id
            LEFT OUTER JOIN pm_employee e ON u.id = e.user_id
            LEFT OUTER JOIN pm_department ed ON e.department_id = ed.id
            LEFT OUTER JOIN plan p ON p.id = ep.plan_id
            {$where}
            ORDER BY ed.lkey, p.is_plan_based desc
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    
    /*
    * Calendar 
    **/
    public function getPrevDay($start){
        $date = new \DateTime($start);
        $date->sub(new \DateInterval('P1D'));
        return $date->format('Y-m-d');// Предыдущим днем
    }
    
    /**
     *Поиск пересечений
    */
    public function getByPrev($user_id = NULL, $department_id = NULL, $plan_id = NULL, $start = NULL)
    {
        $criteria = ["oid" => " u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($start)){
            $criteria['start'] = "ep.end >= :start:";
            $params['start'] = $start;
        }
        if(isset($department_id)){
            $criteria['department_id'] = "ep.department_id >= :department_id:";
            $params['department_id'] = $department_id;
        }
        if(isset($department_id)){
            $criteria['plan_id'] = "ep.plan_id >= :plan_id:";
            $params['plan_id'] = $plan_id;
        }
        if(! empty($user_id)){
            $criteria['user_id'] = "u.id = (:user_id:)";
            $params['user_id'] = $user_id;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ep.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_plan ep ON ep.user_id = u.id
            {$where}
            ORDER BY ep.start
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    
    /**
     * подтянуть вперед
    */
    public function getByNext($user_id = NULL,$department_id = NULL, $plan_id = NULL, $start = NULL)
    {
        $criteria = ["oid" => " u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($start)){
            $criteria['end'] = "ep.end = :end:";
            $params['end'] = $this->getPrevDay($start);
        }
        if(isset($department_id)){
            $criteria['department_id'] = "ep.department_id = :department_id:";
            $params['department_id'] = $department_id;
        }
        if(isset($plan_id)){
            $criteria['plan_id'] = "ep.plan_id = :plan_id:";
            $params['plan_id'] = $plan_id;
        }
        if(! empty($user_id)){
            $criteria['user_id'] = "u.id = (:user_id:)";
            $params['user_id'] = $user_id;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ep.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_plan ep ON ep.user_id = u.id
            {$where}
            ORDER BY ep.start
        ");
        $query->bind($params);
        return $query->getFirstRecord();
    }
    
    public function getByNextStart($user_id = NULL,$department_id = NULL, $plan_id = NULL, $start = NULL)
    {
        $criteria = ["oid" => " u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($start)){
            $criteria['start'] = "ep.start > :start:";
            $params['start'] = $start;
        }
        if(isset($department_id)){
            $criteria['department_id'] = "ep.department_id = :department_id:";
            $params['department_id'] = $department_id;
        }
        if(isset($plan_id)){
            $criteria['plan_id'] = "ep.plan_id = :plan_id:";
            $params['plan_id'] = $plan_id;
        }
        if(! empty($user_id)){
            $criteria['user_id'] = "u.id = (:user_id:)";
            $params['user_id'] = $user_id;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ep.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_plan ep ON ep.user_id = u.id
            {$where}
            ORDER BY ep.start asc
        ");
        $query->bind($params);
        return $query->getFirstRecord();
    }
    
    public function getByPrevStart($user_id = NULL,$department_id = NULL, $plan_id = NULL, $start = NULL)
    {
        $criteria = ["oid" => " u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($start)){
            $criteria['start'] = "ep.start < :start:";
            $params['start'] = $start;
        }
        if(isset($department_id)){
            $criteria['department_id'] = "ep.department_id = :department_id:";
            $params['department_id'] = $department_id;
        }
        if(isset($plan_id)){
            $criteria['plan_id'] = "ep.plan_id = :plan_id:";
            $params['plan_id'] = $plan_id;
        }
        if(! empty($user_id)){
            $criteria['user_id'] = "u.id = (:user_id:)";
            $params['user_id'] = $user_id;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ep.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_plan ep ON ep.user_id = u.id
            {$where}
            ORDER BY ep.start desc
        ");
        $query->bind($params);
        return $query->getFirstRecord();
    }
    
    public function pull($user_id = NULL, $department_id, $plan_id, $start = NULL, $start_new = NULL)
    {
        if(! $user_id AND ! $start AND ! $start_new){
            return !1;
        }
        $_stamp = strtotime($start_new);
        $_estamp = strtotime($start);
        if($_estamp <= $_stamp){
            //подтянуть в старту
            $next = $this->getByNext($user_id, $department_id, $plan_id, $start);
            if($next){
                $up = ['id' => $next['id'],
                       'end' => $this->getPrevDay($start_new),
                       ];
                $this->upsert($up);
            }
        }else{
            //подвинуть назад все
            $prevs = $this->getByPrev($user_id, $department_id, $plan_id, $start_new);
            foreach($prevs as $prev){
                $stamp = strtotime($prev['start']);
                if($stamp >= $_stamp){
                    $this->delete($prev['id']);
                }else{
                    $up = ['id' => $prev['id'],
                       'end' => $this->getPrevDay($start_new),
                       ];
                    $this->upsert($up);    
                }
            }
        }
    }
    
    //есть ли текущий план 
    public function getActive($user_id = NULL, $department_id = null, $plan_id = NULL)
    {
        if(! $plan_id AND ! $user_id){
            return !1;
        }
        $criteria = ["oid" => " u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        $criteria['plan_id'] = "ep.plan_id = :plan_id: AND ep.end IS NULL";
        $params['plan_id'] = $plan_id;
        $criteria['user_id'] = "u.id = (:user_id:)";
        $params['user_id'] = $user_id;
        if(isset($department_id)){
            $criteria['department_id'] = "ep.department_id = :department_id:";
            $params['department_id'] = $department_id;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ep.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_plan ep ON ep.user_id = u.id
            {$where}
            ORDER BY ep.start
        ");
        $query->bind($params);
        return $query->getFirstRecord();
    }
    
    
    /**/
    public function migrate()
    {
        $criteria = ["oid" => " u.owner_id=:oid:",
                     /*"status" => " ed.status > :status:",
                     "fire_date" => " ed.fire_date IS :fire_date:",
                     "is_allowed" => " (c1.is_allowed = :is_allowed: OR c2.is_allowed = :is_allowed:)"*/
                     ];
        $params = ["oid" => OWNER_ID,
                    /*"status" => 0,
                    "fire_date" => NULL,
                    "is_allowed" => 1*/
                    ];
        $where = ! empty($criteria) ? "WHERE ".implode(' AND', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                u.id,
                ed.plans,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_data ed ON ed.user_id = u.id
            {$where}
            ORDER BY u.id
        ");
        $params['allow'] = 'PRIVATE_OFFICE';
        $query->bind($params);
        $plans = $query->getAllRecords();
        $auth_id = \Model::factory('User')->get('id');
        foreach($plans as $_plan){
            $user_id = $_plan['id'];
            $_s = explode(',',$_plan['plans']);
            $cplans = [];
            foreach($_s as $_str){
                $aPlan = explode(':',$_str);
                if(count($aPlan) == 2){
                    list($plan_id, $value) = $aPlan;
                    $data = ['user_id' => $user_id, 'plan_id' => $plan_id, 'value' => $value];
                    $data['creater'] = $auth_id;
                    $data['updater'] = $auth_id;
                    $data['start'] = '2012-01-01';
                    $data['end'] = NULL;
                    //$data['active'] = 1;
                    $this->insert($data);
                }
            }
        }
    }
}