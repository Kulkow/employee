<?php

namespace Modules\Employee\Admin\Models;

use Modules\Employee\Admin\Models\Employee;
use Modules\Employee\Admin\Models\PlanOrg;
use Modules\Employee\Admin\Extensions\Calendar;

/*
* Текущие планы пользователей
* manager_id = user_id
* type = plan_id
* date
* plan_amount
* fact_amount
* participants
* 
**/
class PlanSheet extends \Classes\Base\Model
{
    
    protected $table = 'plan_sheet';
    
    protected $format_date = "Y-m-d";

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `plan_sheet` (
            `id` smallint(6) NOT NULL AUTO_INCREMENT,
            `manager_id` mediumint(9) NOT NULL DEFAULT '0',
            `date` date DEFAULT NULL,
            `plan_amount` int(11) DEFAULT '0',
            `type` tinyint(4) DEFAULT '0',
            `fact_amount` int(11) DEFAULT '0',
            `participants` varchar(150) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `manager_id` (`manager_id`,`date`,`type`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
          ALTER TABLE `plan_sheet` ADD `end` date DEFAULT NULL AFTER `date`
          ALTER TABLE `plan_sheet` ADD `is_summary` tinyint(1) DEFAULT '0' NOT NULL AFTER `type` 
          ";
    }
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                ps.*,
            FROM plan_sheet ps
            RIGHT OUTER JOIN plan p ON ps.plan_id=p.id
            WHERE ps.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    /**
    * Список плановых Показателей Пользователя
    **/
    public function getByUserId($ids, $date = NULL)
    {
        $params = [];
        $where = "";
        if(is_array($date) AND count($date) > 0){
            if(count($date) == 1){
                $date = array_pop($date);
            }
        }
        if(is_array($date)){
            list($start, $end) = $date; 
            $start = date('Y-m-d', strtotime($start));
            $end = date('Y-m-d', strtotime($end));
            $where .= " AND ps.date >= :start: AND ps.date <= :end:";
            $params['start'] = $start;
            $params['end'] = $end;
        }else{
            $date = date('Y-m-d', strtotime($date));
            $where .= " AND ps.date = :date:";
            $params['date'] = $date;
        }
        $query = $this->db->newStatement("
            SELECT
                p.id,
                p.name,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common,
                ps.plan_amount plan,
                ps.manager_id user_id,
                ps.date date
            FROM plan_sheet ps
            RIGHT OUTER JOIN plan p ON ps.plan_id=p.id 
            WHERE ps.manager_id IN (:id:) {$where}
            ORDER BY ps.manager_id, p.name
        ");
        $params['id'] = $ids;
        $query->bind($params);
        $plans = [];
        foreach($query->getAllRecords() as $plan){
            if(! isset($plans[$plan['user_id']])){
                $plans[$plan['user_id']] = [];
            }
            $plans[$plan['user_id']][$plan['id']] = $plan;
        }
        return $plans;
    }
    
    public function getByTotal($date = NULL)
    {
        $params = [];
        $where = "";
        if(is_array($date) AND count($date) > 0){
            if(count($date) == 1){
                $date = array_pop($date);
            }
        }
        if(is_array($date)){
            list($start, $end) = $date; 
            $start = date('Y-m-d', strtotime($start));
            $end = date('Y-m-d', strtotime($end));
            $where .= " AND ps.date >= :start: AND ps.date <= :end:";
            $params['start'] = $start;
            $params['end'] = $end;
        }else{
            $date = date('Y-m-d', strtotime($date));
            $where .= " AND ps.date = :date:";
            $params['date'] = $date;
        }
        $query = $this->db->newStatement("
            SELECT
                p.id,
                p.name,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common,
                ps.plan_amount plan,
                ps.date date
                FROM plan_sheet ps
                RIGHT OUTER JOIN plan p ON ps.plan_id=p.id 
            WHERE ps.manager_id IS NULL {$where}
            ORDER BY ps.date,p.name
        ");
        $params['id'] = $ids;
        $query->bind($params);
        $plans = [];
        foreach($query->getAllRecords() as $plan){
            if(! isset($plans[$plan['user_id']])){
                $plans[$plan['user_id']] = [];
            }
            $plans[$plan['user_id']][$plan['id']] = $plan;
        }
        return $plans;
    }
    
    
    public function getByPerion($date = NULL, $users = NULL)
    {
        $params = [];
        $where = [];
        if(is_array($date) AND count($date) > 0){
            if(count($date) == 1){
                $date = array_pop($date);
            }
        }
        if(is_array($date)){
            list($start, $end) = $date; 
            $start = date('Y-m-d', strtotime($start));
            $end = date('Y-m-d', strtotime($end));
            $where[] = "ps.date >= :start:";
            $where[] = "ps.date <= :end:";
            $params['start'] = $start;
            $params['end'] = $end;
        }else{
            $date = date('Y-m-d', strtotime($date));
            $where[] = "ps.date = :date:";
            $params['date'] = $date;
        }
        if(! empty($users)){
            $users[] = 0;
            $where[] ="ps.manager_id IN (:users:)";
            $params['users'] = $users;
        }
        $where = ! empty($where) ? implode (' AND ',$where) : "";
        $query = $this->db->newStatement("
            SELECT
                p.id,
                ps.type,
                ps.department_id,
                ps.id as sheet_id,
                p.name,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common,
                ps.plan_amount plan,
                ps.date date,
                ps.manager_id user_id
                FROM plan_sheet ps
                RIGHT OUTER JOIN plan p ON ps.plan_id=p.id 
            WHERE  {$where}
            ORDER BY ps.date,p.name
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function getByList(array $filter)
    {
        $criteria = ["oid" => "u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($filter['users']) && count($filter['users'])){
            $criteria['user_id'] = "ep.user_id IN (:user_ids:)";
            $params['user_ids'] = $filter['users'];
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ps.*,
                p.name,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common,
            FROM plan_sheet ps
            LEFT OUTER JOIN plan p ON p.id = ep.plan_id
            {$where}
            ORDER BY p.id
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    // значения плановых показателей пользователей
    /*public function getListEplans($month = NULL, $year = NULL, $is_plan = true, $departments = null){
        if(! $month) $month = date('m');
        if(! $year) $year = date('Y');
        $p = Calendar::getPeriodMonth($year.'-'.$month.'-01');*/
    public function getListEplans($start = NULL, $end = NULL, $is_plan = true, $departments = null){
        $p = ['start' => $start, 'end' => $end];
        $where = '';
        if($is_plan){
            $where .= " AND p.is_plan_based = 1";
        }
        if($departments){
            $where .= " AND ed.department_id IN (:departments:)";
        }
        $query = $this->db->newStatement("
        SELECT
            ep.id as id,
            ep.user_id,
            TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) u_name,
            p.is_plan_based,
            p.is_negative,
            p.is_common,
            p.name as plan,
            p.alias,
            p.is_discrete,
            ep.id eplan_id,
            p.id plan_id,
            p.name,
            p.pid,
            p.measurement,
            ep.plan_id,
            ep.user_id,
            ep.department_id,
            ep.value,
            ep.start,
            ep.end,
            pd.name dname,
            ed.status
        FROM employee_plan ep
        LEFT JOIN plan p ON p.id = ep.plan_id
        LEFT JOIN pm_department pd ON pd.id = ep.department_id
        LEFT JOIN user u ON u.id = ep.user_id
        LEFT JOIN employee_data ed ON ed.user_id = ep.user_id
            WHERE (
                (ep.start >=  :start: AND ep.end <= :end:)
                OR
                (ep.start <= :start: AND ep.end >= :end:)
                OR
                (ep.start <= :start: AND ep.end IS NULL)
                OR
                (ep.start >= :start: AND ep.start <= :end: AND ep.end IS NULL)
                )
                {$where}
        ");
       
        $params = ['start' => $p['start'], 'end' => $p['end']];
        if($departments){
            $params['departments'] = $departments;
        }
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function glue(array $eplans, array $tree, $allusers = []){
        $departments = $personals = $commons = $commons_users = $usersplans = $departmentsplans = $noplans = [];
        //add
        $tree[0] =          ['id' => -1,
                             'name' => 'Вcе общие',
                             'number' => '',
                             'virtual' => 1,
                             'level' => 1,
                             'users' => [],
                             'plans' => [],
                             'personals' => [],
                             ];
        $tree['1000_v1'] = ['id' => 1000,
                            'name' => 'Вне подразделений',
                            'number' => '',
                            'virtual' => 1,
                            'level' => 1,
                            'users' => [],
                            'plans' => [],
                            'personals' => [],
                            ];
        $tree['1000_v2'] = ['id' => 1001,
                            'name' => 'Новенькие',
                            'number' => '',
                            'virtual' => 1,
                            'level' => 1,
                            'users' => [],
                            'plans' => [],
                            'personals' => [],
                            ];
        $tree['1000_v3'] = ['id' => 10002,
                            'name' => 'Сдельные',
                            'number' => '',
                            'virtual' => 1,
                            'level' => 1,
                            'users' => [],
                            'plans' => [],
                            'personals' => [],
                            ];
        foreach($eplans as $row){
            $user_id = $row['user_id'];
            $plan_id = $row['plan_id'];
            $departent_id = $row['department_id'];
            if($row['is_common']){
                if(! isset($commons[$plan_id]))   $commons[$plan_id] = [];
                $commons[$plan_id][$departent_id] = $row;
                if(! isset($commons_users[$user_id]))   $commons_users[$user_id] = [];
                if(! isset($commons_users[$user_id][$plan_id]))   $commons_users[$user_id][$plan_id] = [];
                $commons_users[$user_id][$plan_id][$departent_id] = $row;
            }
            if(1 == $row['is_plan_based']){
                $key_plan = ($row['is_common'] == 1 ? 
                                                    "common_".($departent_id ? $departent_id.'_' : '').$row['plan_id']
                                                    : "user_".$user_id."_".$plan_id);
                $row['key'] = $key_plan;
                if($row['is_common']){
                    if(! isset($departmentsplans[$departent_id])) $departmentsplans[$departent_id] = [];
                    if(! isset($departmentsplans[$departent_id][$plan_id])) {
                        $row['_users'] = [];
                        $departmentsplans[$departent_id][$plan_id] = $row;
                    }
                    $departmentsplans[$departent_id][$plan_id]['_users'][$user_id] = $user_id;
                }else{
                    if(! isset($personals[$user_id]))  $personals[$user_id] = [];
                    $personals[$user_id][$plan_id] = $row;
                    if(! isset($usersplans[$plan_id])) $usersplans[$plan_id] = [];
                    $usersplans[$plan_id][$user_id] = $row;
                }
            }else{
                $key_plan = ($row['is_common'] == 1 ?
                                                    "common_".($departent_id ? $departent_id.'_' : '').$row['plan_id']
                                                    : "user_".$user_id."_".$plan_id);
                if($row['is_common'] AND $departent_id){
                    $row['key'] = $key_plan;
                    if(! isset($departmentsplans[$departent_id])) $departmentsplans[$departent_id] = [];
                    if(! isset($departmentsplans[$departent_id][$plan_id])) {
                        $row['_users'] = [];
                        $departmentsplans[$departent_id][$plan_id] = $row;
                    }
                    if(! isset($departmentsplans[$departent_id][$plan_id]["users"])) {
                        $departmentsplans[$departent_id][$plan_id]["users"] = [];
                    }
                    $departmentsplans[$departent_id][$plan_id]["users"][$user_id] = $row;
                    $departmentsplans[$departent_id][$plan_id]['_users'][$user_id] = $user_id;
                }else{
                    $row['key'] = $key_plan;
                    if(! isset($noplans[$user_id])) $noplans[$user_id] = [];
                    $noplans[$user_id][$plan_id] = $row;    
                }
            }
        }
        $_personal_all = $usersplans;
        $new = $allusers; //новенькие
        $fired = [];//
        foreach($allusers as $_u){
            if(! empty($_u['fire_date'])){
                $fired[$_u['user_id']] = $_u['user_id'];
            }
        }
        array_walk($tree, function(&$d) use ($departmentsplans, $usersplans, $personals, $commons_users, &$_personal_all, $allusers, &$new, &$noplans){
            $users = $_personals = [];
            $d['plans'] = [];
            $employees = [];
            $dusers = [];
            foreach($d['users'] as $user){
                $users[$user['id']] = $user['id'];
                $employees[$user['id']] = $user;
                if($p = \Arr::get($personals, $user['id'], 0)){
                    $_personals[] = $p;
                }
                $dusers[$user['id']] = $user;
            }
            $d['users'] = $dusers;
            $u = $users; //all user
            $dp = \Arr::get($departmentsplans, $d['id'], []);
            foreach($dp as $_plan_id => $common){
                $common['users'] = [];
                if($common['pid']){
                    $personal = \Arr::get($usersplans, $common['pid'], []);
                    $personal = array_intersect_key($personal, $users);
                    $u = array_diff($u, array_keys($personal));
                    $common['users'] = $personal;
                    foreach($personal as $u_id => $_p){
                        $employee = \Arr::get($employees, $u_id, null);
                        if($employee){
                            $personal[$u_id]['rowid'] = $employee['rowid']; // department pm_employee
                        }
                        if($pm_start = \Arr::get($employee, 'start', null)){
                            $personal[$u_id]['start'] = $pm_start;
                        }
                        if($pm_end = \Arr::path($employees, $u_id.'.end', null)){ //Уже не принадлежит этому подразделению
                            $personal[$u_id]['end'] = $pm_end;
                        }
                        //if(! \Arr::path($commons_users, $u_id.'.'.$_plan_id.'.'.$d['id'].'.end', null) && ! $employee){ // есть ли общий план в этом подразделении
                            //$personal[$u_id]['end'] = $commons_user_end;
                        //}

                        if(\Arr::path($_personal_all, $common['pid'].'.'.$u_id, null)){
                            unset($_personal_all[$common['pid']][$u_id]);
                        }
                        //если есть один план убираем из сделки
                        if(\Arr::get($noplans, $u_id, null)){
                            unset($noplans[$u_id]);
                        }
                        if(\Arr::get($new, $u_id, null)){
                            unset($new[$u_id]);
                        }
                    }
                    $common['users'] = $personal;
                }else{
                    if($d['id']){
                        // check existst
                        $unique_key = $common['plan_id'].(! empty($common['department_id']) ? '_'.$common['department_id'] : '');
                        $_add = true;
                        foreach($d['plans'] as $_c_p){
                            $_unique_key = $_c_p['plan_id'].(! empty($_c_p['department_id']) ? '_'.$_c_p['department_id'] : '');
                            if($_unique_key == $unique_key){
                                $_add = false;
                            }
                        }
                        if($_add){
                            $d['plans'][] = $common;
                        }
                    }
                }
                $intersect = array_intersect_key($users, $common['_users']);
                if(!empty($intersect) AND ! empty($common['is_plan_based'])){
                    $common['intersect'] = $intersect;
                    // check existst
                    $unique_key = $common['plan_id'].(! empty($common['department_id']) ? '_'.$common['department_id'] : '');
                    $_add = true;
                    foreach($d['plans'] as $_c_p){
                        $_unique_key = $_c_p['plan_id'].(! empty($_c_p['department_id']) ? '_'.$_c_p['department_id'] : '');
                        if($_unique_key == $unique_key){
                            $_add = false;
                        }
                    }
                    if($_add){
                        $d['plans'][] = $common;
                    }
                }elseif($d['id'] == 0){
                    // check existst
                    $unique_key = $common['plan_id'].(! empty($common['department_id']) ? '_'.$common['department_id'] : '');
                    $_add = true;
                    foreach($d['plans'] as $_c_p){
                        $_unique_key = $_c_p['plan_id'].(! empty($_c_p['department_id']) ? '_'.$_c_p['department_id'] : '');
                        if($_unique_key == $unique_key){
                            $_add = false;
                        }
                    }
                    if($_add){
                        $d['plans'][] = $common;
                    }
                }
            }
            foreach($u as $u_id){
                // нет планов в явном виде - запихиваем их во все общие
                foreach($d['plans'] as $key => $_common){
                    if(0 < $_common['pid']){
                        $employee = \Arr::get($employees, $u_id, []);
                        $employee['u_name'] = $employee['name'];
                        $employee['user_id'] = $u_id;
                        $employee['plan_id'] = $_common['pid'];
                        $employee['key'] = 'user_'.$employee['user_id'].'_'.$employee['plan_id'];
                        $employee['is_plan_based'] = 1;
                        $employee['is_negative'] = 0;
                        $_common['users'][$u_id] = $employee;
                        $d['plans'][$key] = $_common;
                        if(\Arr::path($_personal_all, $common['pid'].'.'.$u_id, null)){
                            unset($_personal_all[$common['pid']][$u_id]);
                        }
                        //если есть один план убираем из сделки
                        if(\Arr::get($noplans, $u_id, null)){
                            unset($noplans[$u_id]);
                        }
                        if(\Arr::get($new, $u_id, null)){
                            unset($new[$u_id]);
                        }
                    }
                }
            }
        });
        
        //Личные вне подразделений
        foreach($_personal_all as $v_id => $p){
            foreach($p as $u_id => $_p){
                if(\Arr::get($allusers,$u_id, null)){
                    $tree['1000_v1']['plans'][] = $_p;
                    if(\Arr::get($new, $u_id, null)){
                        unset($new[$u_id]);
                    }
                }
            }
        }
        //Сдельные
        foreach($noplans as $u_id => $p){
            if(\Arr::get($allusers,$u_id, null)){
                $s = false;
                foreach($p as $_p){
                    if(! isset($fired[$_p['user_id']])) {
                        if ($_p['is_common']) {
                            $_p['users'] = [];
                            $_p['_users'] = [];
                        }
                        $tree['1000_v3']['plans'][] = $_p;
                    }
                }
                if(\Arr::get($new, $u_id, null)){
                    unset($new[$u_id]);
                }
            }
        }
        
        foreach($new as $u_id => $_p){
            if(empty($_p['fire_date'])) {
                $_p['plan_id'] = 0;
                $_p['is_common'] = 0;
                $_p['number'] = 0;
                $_p['is_common'] = 0;
                $_p['plan'] = '';
                $_p['u_name'] = $_p['name'];
                $_p['is_plan_based'] = 0;
                $_p['key'] = '';
                $_p['end'] = '';
                $tree['1000_v2']['plans'][] = $_p;
            }
        }
        $tree['1000_v0'] =  $tree[0];
        unset($tree[0]);
        return $tree;
    }
    
    
    public function lastPlansheet($manager_id = NULL){
        if(! $manager_id){
            return [];
        }
        $last = [];
        $query = $this->db->newStatement("
            SELECT max( date ) last, manager_id user_id
                FROM `plan_sheet`
                WHERE manager_id
                IN ( :manager_id: )
                GROUP BY manager_id");
        $query->setArray('manager_id', $manager_id);
        foreach($query->getAllRecords() as $row){
            $last[$row['user_id']]  = $row['last'];
        }
        return $last;
    }
    
    
    /**
    * Список плановых показателей
    **/
    public function getByStatistic(array $filter)
    {
        $criteria = [];
        $params = [];
        if(! empty($filter['users']) && count($filter['users'])){
            $criteria['user_id'] = "u.id IN (:user_ids:)";
            $params['user_ids'] = $filter['users'];
        }
        if(! empty($filter['start']) AND ! empty($filter['end'])){
            $criteria[] = "ps.date >= :start: AND ps.date <= :end:";
            $params['start'] = $filter['start'];
            $params['end'] = $filter['end'];
        }elseif(! empty($filter['start']) OR ! empty($filter['end'])){
            if(! empty($filter['start'])){
                $start = date('Y-m-d', strtotime($filter['start']));
            }elseif(! empty($filter['end'])){
                $start = date('Y-m-d', strtotime($filter['end']));
            }
            $criteria[] = "ps.date = :start:";
            $params['start'] = $start;
        }

        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement(
        "SELECT 
            ps.* ,
            p.id as plan_id,
            p.name as plan,
            u.id as user_id,
            TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) u_name
        FROM `plan_sheet` as ps
        LEFT JOIN plan as p ON p.id =ps.plan_id
        LEFT JOIN user as u ON u.id=ps.manager_id
        {$where}
        ORDER BY ps.date,ps.plan_id");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    
    /**
    * Get Info на заданный месяц (два предыдущих месяца и год назад или два года назад)
    * return ['date' =>
    *             'common_' => [],
    *             'user_' => []
    * ]
    **/
    public function getByInfo($date = NULL, $currentmonth = NULL){ // передавать дату [Y-m-d], intval, intval
        $start = $end = NULL;
        
        if(is_array($date)){
            list($start, $end) = $date;
            $date = $end;
            $date = date($this->format_date);
        }
        /*
        if(! $start){
            $cdate = new \DateTime($date); // на эту дату
            $cdate->sub(new \DateInterval('P'.$count_month.'M'));
            $start = $cdate->format($this->format_date);
            $end = $date; //$cdate->format($this->format_date);
        }*/
        
        $c = new \DateTime($currentmonth); // для рассчета год назад
        $c->sub(new \DateInterval('P1Y'));
        $call2 = $c->format($this->format_date);
        $c->sub(new \DateInterval('P1M'));// для рассчета год назад - месяц
        $call1 = $c->format($this->format_date);
        
        $c = new \DateTime($currentmonth); // для рассчета год назад
        $c->sub(new \DateInterval('P1M'));
        $prev = $c->format($this->format_date); // предыдущий месяц для пропорции
        
        $criteria = $params = $return = [];
        $criteria[] = "(ps.date >= :start: AND ps.date <= :end:) OR ps.date = :prev_year:";
        $where = "(ps.date >= :start: AND ps.date <= :end:)";
        $params['start'] = $start;
        $params['end'] = $end;
        if(strtotime($end) > strtotime($call1)){
            $where .= " OR ps.date = :call1:";
            $params['call1'] = $call1;
        }
        if(strtotime($end) > strtotime($call2)){
            $where .= " OR ps.date = :call2:";
            $params['call2'] = $call2;
        }
        $where = ! empty($where) ? "WHERE ".$where : '';
        $query = $this->db->newStatement(
        "SELECT 
            ps.* ,
            p.id as plan_id,
            p.is_common as is_common,
            p.name as plan,
            u.id as user_id,
            TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) u_name
        FROM `plan_sheet` as ps
        LEFT JOIN plan as p ON p.id =ps.plan_id
        LEFT JOIN user as u ON u.id=ps.manager_id
        {$where}
        ORDER BY ps.date,ps.plan_id");
        $query->bind($params);
        $helpers = []; // подсказки
        foreach($query->getAllRecords() as $sheet){
            $_date = $sheet['date'];
            $c = new \DateTime($_date); 
            $c->sub(new \DateInterval('P1M'));
            $_prev = $c->format('Y-m-d');
            $plan_id = \Arr::get($sheet, 'plan_id');
            $department_id = \Arr::get($sheet, 'department_id');
            if(!isset($return[$_date])){
                $return[$_date] = [];
            }
            if($user_id = \Arr::get($sheet,'user_id', NULL)){
                $key = 'user_'.$user_id.'_'.$plan_id;
                //$return[$_date]['plan_'.$plan_id][$user_id] = $sheet; ????
            }else{
                $key = 'common_'.($department_id ? $department_id.'_' : '').$plan_id;
            }
            if($pv = \Arr::get($return, $_prev, null)){
                if($_sheet = \Arr::get($pv, $key, null)){
                    $sheet['prev'] = ! empty($_sheet['fact_amount']) ? $_sheet['fact_amount'] : $_sheet['plan_amount'];    
                }
            }
            $return[$_date][$key] = $sheet;
            if(! isset($helpers[$key])){
                $helpers[$key] = [];
            }
            if($_date == $call1){
                $helpers[$key]['call1'] = ! empty($sheet['fact_amount']) ? $sheet['fact_amount'] : $sheet['plan_amount'];          
            }
            elseif($_date == $call2){
                $helpers[$key]['call2'] = ! empty($sheet['fact_amount']) ? $sheet['fact_amount'] : $sheet['plan_amount'];          
            }
            elseif($_date == $prev){
                $helpers[$key]['prev'] = ! empty($sheet['fact_amount']) ? $sheet['fact_amount'] : $sheet['plan_amount'];          
            }
        }
        $return['helpers'] = $helpers;
        $return['call_helpers'] = ['call1' => strtotime($call1), 'call2' => strtotime($call2), 'prev' => strtotime($prev)];
        return $return;
    }
    
    public function getDatehelper($start){
        $c = new \DateTime($start); // для рассчета год назад
        $c->sub(new \DateInterval('P1Y'));
        $call2 = $c->format($this->format_date);
        $c->sub(new \DateInterval('P1M'));// для рассчета год назад - месяц
        $call1 = $c->format($this->format_date);
        return ['call1' => $call1, 'call2' => $call2]; 
    }
    
    public function getDatehelperOrg($start, $allyear = null){
        $c = new \DateTime($start); // для рассчета год назад
        if($allyear){
            $year = intval($c->format('Y')) - $allyear;
        }else{
           $year = 1; //год назад 
        }
        $c->sub(new \DateInterval('P'.$year.'Y'));
        $call2 = $c->format($this->format_date);
        $c->sub(new \DateInterval('P1M'));// для рассчета год назад - месяц
        $call1 = $c->format($this->format_date);
        return ['call1' => $call1, 'call2' => $call2]; 
    }
    
    /**
    * сформируем массив для работы с данными в таблице
    **/
    public function getByInfoPrepare($sheets){
        $return = [];
        foreach($sheets as $sheet){
            $date = $sheet['date'];
            $plan_id = \Arr::get($sheet, 'plan_id');
            if(!isset($return[$date])){
                $return[$date] = [];
            }
            if($user_id = \Arr::get($sheet,'user_id', NULL)){
                $key = 'user_'.$user_id.'_'.$plan_id;
                $return[$date]['plan_'.$plan_id][$user_id] = $sheet;
            }else{
                $key = 'common_'.$plan_id;
            }
            $return[$date][$key] = $sheet;
        }
        return $return;
    }
    
    // Подсказка на текущюю дату, заданного показателя
    public function getHelpInfo($helper){
        $call1 = \Arr::get($helper,'call1',0);
        $call2 = \Arr::get($helper,'call2',0);
        $prev = \Arr::get($helper,'prev',0);
        if(! $call1){
            $call1 = $call2;
        }
        if(! $call1 AND ! $call2){
            return round($prev/100);
        }
        return round(($call2*$prev/$call1)/100);
    }
    
    public function getHelpInfoAll($allpercent, $helper){
        $prev = \Arr::get($helper,'prev',0);
        return round($allpercent * $prev/100,-2);
    }
    
    //Helper calculate
    public function getCalculate($allpercents, $stamp, $helper){
        $prev = \Arr::get($helper,'prev',0);
        $allpercent = \Arr::get($allpercents,$stamp,1);
        return round($allpercent * $prev/100,-2);
    }
    
    public function getMaxPeriod(){
        $query = $this->db->newStatement(
        "SELECT
        max( date ) as max , min( date ) as min
        FROM `plan_sheet`");
        return $query->getFirstRecord();
    }
    
    
    // общие цифры
    public function getByAll($date = null, $count_month = 24){
        if (!$date) $date = date($this->format_date);
        $count_month = $count_month ? $count_month : 24;
        $end = $date;
        $cdate = new \DateTime($end);
        $cdate->sub(new \DateInterval('P'.$count_month.'M'));
        $start = $cdate->format($this->format_date);
        $select = "
            DATE_FORMAT(b.datetime,'%Y-%m') month
            ";
        $params = ['since' => $start.' 00:00:00', 'till' => $end.' 23:59:59'];
        $group = "DATE_FORMAT(b.datetime,'%Y-%m-01')";
        $all = \Model::admin('Balance')->buildOrderStatistics(compact("select", "params", "group"));
        foreach($all as $sheet){
            $return[$sheet['month']] = $sheet;
        }
        return $return;
    }
    
    // общие цифры
    public function getByOrg($date = null, $count_month = 49){
        $PlanOrg = new PlanOrg($this->db, $this->user);
        $count_month = $count_month ? $count_month : 49;
        if (!$date) $date = date($this->format_date);
        $end = date('Y',strtotime($date)).'-01-01';
        $cdate = new \DateTime($end);
        $cdate->sub(new \DateInterval('P'.$count_month.'M'));
        $start = $cdate->format($this->format_date);
        $return = [];
        $all = $PlanOrg->getByList(['start' => $start, 'end' => $end],'po.date asc');
        foreach($all as $sheet){
            $return[$sheet['date']] = $sheet;
        }
        return $return;
    }
    
    public function migrate()
    {
        return !1;
        $criteria = ["oid" => " u.owner_id=:oid:",
                     "status" => " ed.status > :status:",
                     "fire_date" => " ed.fire_date IS :fire_date:",
                     "is_allowed" => " (c1.is_allowed = :is_allowed: OR c2.is_allowed = :is_allowed:)"
                     ];
        $params = ["oid" => OWNER_ID,
                    "status" => 0,
                    "fire_date" => NULL,
                    "is_allowed" => 1
                    ];
        $where = ! empty($criteria) ? "WHERE ".implode(' AND', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                u.id,
                ed.plans,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_data ed ON ed.user_id = u.id
            LEFT OUTER JOIN codex c1 ON c1.role_id=u.role_id AND c1.rule_id=(SELECT id FROM rule WHERE code = :allow:)
            LEFT OUTER JOIN codex c2 ON c2.user_id=u.id AND c2.rule_id=(SELECT id FROM rule WHERE code = :allow:)
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
                    $data['created'] = date('Y-m-d H:i:s');
                    $data['updated'] = date('Y-m-d H:i:s');
                    $data['active'] = 1;
                    //$this->insert($data);
                }
            }
        }
    }
}