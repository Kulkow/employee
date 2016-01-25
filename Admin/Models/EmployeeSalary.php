<?
namespace Modules\Employee\Admin\Models;
use Modules\Employee\Admin\Models\EmployeePlan;

/**
* Ставки работников период
**/
class EmployeeSalary extends \Classes\Base\Model
{
    const ALLOW_GRANT = 'PRIVATE_OFFICE'; // права доступа в админку
    
    const OKLAD_ID = 10;
    
    protected $table = 'employee_salary';
    
    protected function init_sql(){
      /** ставки время обучения */
      "CREATE TABLE IF NOT EXISTS `employee_salary` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `base` int(11) NOT NULL default '0',
        `start` datetime,
        `status` int(5), 
        `end` datetime default NULL,
        `creater` int(11),
        `updater` int(11),
        PRIMARY KEY (`id`),
        KEY user_id (`user_id`)
      ) ENGINE=Aria DEFAULT CHARSET=utf8;";
    }
    
    
    public function getByList(array $filter)
    {
        $criteria = ["oid" => " u.owner_id=:oid:"];
        $params = ["oid" => OWNER_ID];
        if(! empty($filter['start']) AND ! empty($filter['end'])){
            $criteria['start'] = "(
                (es.start >=  :start: AND es.end < :end:)
                OR
                (es.start <= :start: AND es.end >= :start:)
                OR
                (es.start <= :start: AND es.end IS NULL)
                OR
                (es.start >= :start: AND es.start <= :end: AND es.end IS NULL)
                )";
            $params['start'] = $filter['start'];
            $params['end'] = $filter['end'];
        }
        else{
            $criteria["end"] = "es.end IS NULL";
        }
        
        if(! empty($filter['name'])){
            $criteria['q'] = "(u.lastname LIKE :q: OR u.firstname LIKE :q: OR u.secondname LIKE :q:)";
            $params['q'] = '%'.$filter['name'].'%';
        }
        if(! empty($filter['users'])){
            $criteria['user_id'] = "u.id IN (:user_ids:)";
            $params['user_ids'] = $filter['users'];
        }
        if(! empty($filter['user_id'])){
            $criteria['user_id'] = "u.id = (:user_id:)";
            $params['user_id'] = $filter['user_id'];
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ed.status,
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_salary es ON es.user_id = u.id
            LEFT JOIN employee e ON e.user_id = u.id
            LEFT JOIN employee_data ed ON ed.user_id = u.id
            {$where}
            ORDER BY ed.status, u.lastname, u.firstname, u.secondname
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
    public function getByPrev($user_id = NULL, $start = NULL)
    {
        $criteria = ["oid" => " u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($start)){
            $criteria['start'] = "es.end >= :start:";
            $params['start'] = $start;
        }
        if(! empty($user_id)){
            $criteria['user_id'] = "u.id = (:user_id:)";
            $params['user_id'] = $user_id;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_salary es ON es.user_id = u.id
            {$where}
            ORDER BY es.start
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    
    /**
     * подтянуть вперед
    */
    public function getByNext($user_id = NULL, $start = NULL)
    {
        $criteria = ["oid" => " u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($start)){
            $date = new \DateTime($start);
            $date->sub(new \DateInterval('P1D'));
            $end = $date->format('Y-m-d');
            $criteria['end'] = "es.end = :end:";
            $params['end'] = $end;
        }
        if(! empty($user_id)){
            $criteria['user_id'] = "u.id = (:user_id:)";
            $params['user_id'] = $user_id;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_salary es ON es.user_id = u.id
            {$where}
            ORDER BY es.start
        ");
        $query->bind($params);
        return $query->getFirstRecord();
    }
    
    public function getByNextStart($user_id = NULL, $start = NULL)
    {
        $criteria = ["oid" => " u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($start)){
            $criteria['start'] = "es.end > :start:";
            $params['start'] = $start;
        }
        if(! empty($user_id)){
            $criteria['user_id'] = "u.id = (:user_id:)";
            $params['user_id'] = $user_id;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_salary es ON es.user_id = u.id
            {$where}
            ORDER BY es.start asc
        ");
        $query->bind($params);
        return $query->getFirstRecord();
    }
    
    public function getByPrevStart($user_id = NULL, $start = NULL)
    {
        $criteria = ["oid" => " u.owner_id=:oid:",
                     ];
        $params = ["oid" => OWNER_ID,
                    ];
        if(! empty($start)){
            $criteria['start'] = "es.end < :start:";
            $params['start'] = $start;
        }
        if(! empty($user_id)){
            $criteria['user_id'] = "u.id = (:user_id:)";
            $params['user_id'] = $user_id;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_salary es ON es.user_id = u.id
            {$where}
            ORDER BY es.start desc
        ");
        $query->bind($params);
        return $query->getFirstRecord();
    }
    
    public function pull($user_id = NULL, $start = NULL, $start_new = NULL)
    {
        if(! $user_id AND ! $start AND ! $start_new){
            return !1;
        }
        $_stamp = strtotime($start_new);
        $_estamp = strtotime($start);
        if($_estamp <= $_stamp){
            //подтянуть в старту
            $next = $this->getByNext($user_id, $start);
            if($next){
                $up = ['id' => $next['id'],
                       'end' => $this->getPrevDay($start_new),
                       ];
                $this->upsert($up);
            }
        }else{
            //подвинуть назад все
            $prevs = $this->getByPrev($user_id, $start_new);
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
    
    //текущая ставка сотрудника
    public function getByUserId($id, $date = NULL)
    {
        $criteria = ["id" => "es.user_id = :id:",
                     "oid" => "u.owner_id=:oid:"
                     ];
        $params = ["id" => $id,
                   "oid" => OWNER_ID
                   ];
        if(! $date){
            $criteria["end"] = "es.end IS NULL";
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_salary es
            LEFT OUTER JOIN user u ON u.id = es.user_id
            {$where}
            LIMIT 1
        ");
        $query->bind($params);
        return $query->getFirstRecord();
    }
    
    public function getById($id = NULL)
    {
        $criteria = ["id" => "es.id = :id:"];
        $params = ["id" => $id];
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_salary es
            LEFT OUTER JOIN user u ON u.id = es.user_id
            {$where}
            LIMIT 1
        ");
        $query->bind($params);
        return $query->getFirstRecord();
    }
    
    //текущий оклад сотрудника
    public function getByOkladUserId($id, $filter = NULL)
    {
        $return = $oklad = $base = 0;
        $is_plan = $is_job = $is_oklad = false;
        if(! $filter){
            $filter = [];
        }
        $filter['user_id'] = $id;
        $esalary = $this->getByList($filter);
        if(null != $esalary){
            $esalary = array_pop($esalary);
            $base = \Arr::get($esalary, 'base', 0);
        }
        
        $ePlan = new EmployeePlan($this->db, $this->user);
        $plans = $ePlan->getByList($filter);
        foreach($plans as $_plan){
            if(self::OKLAD_ID == $_plan['plan_id']){
                $oklad = $_plan['value'];
                $is_oklad = 1;
            }else{
                if(1 == $_plan['is_plan_based']){
                    $is_plan = 1;
                }else{
                    if(0 < $_plan['is_discrete']){
                        $is_job = 1;
                    }
                }
            }
        }
        if($is_oklad){
            if($is_plan){
                $return = $oklad+$base;
            }else{
                $return = $oklad;
            }
        }else{
            if($is_plan){
                $return = $base;
            }else{
                $return = $base;
            }
        }
        return $return;
    }
    
    
    // оклад сотрудников
    public function getByOkladUsers($date = NULL)
    {
        $criteria = ["oid" => "u.owner_id=:oid:"];
        $params = ["oid" => OWNER_ID];
        if(! $date){
            $criteria["end"] = "es.end IS NULL";
        }else{
            if(is_array($date)){
                list($start, $end) = $date;
            }else{
                $start = $date; // первый день месяца
                $stamp = strtotime($start);
                $_start = date('Y',$stamp).'-'.date('m',$stamp).'-01'; 
                $_date = new \DateTime($_start);
                $_date->sub(new \DateInterval('P1D'));
                $end = $_date->format('Y-m-d');
            }
            $criteria["end"] = "(es.start <= :start: AND es.end <= :end:) OR (es.start <= :start: AND es.end IS NULL)";
            $params["start"] = $start;
            $params["end"] = $end;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_salary es
            LEFT OUTER JOIN user u ON u.id = es.user_id
            {$where}
        ");
        $query->bind($params);
        $users = [];
        foreach($query->getAllRecords() as $_base){
            if(! isset($users)){
                $users[$_base['user_id']] = [];
            }
            $users[$_base['user_id']]['base'] = $_base['base'];
        }
        
        $query = $this->db->newStatement("
            SELECT
            ep.user_id,
            ep.plan_id,
            p.is_plan_based,
            p.is_discrete,
            ep.value,
            TRIM( CONCAT_WS( ' ', u.lastname, u.firstname, u.secondname ) ) name
            FROM employee_plan ep
            LEFT OUTER JOIN plan p ON p.id = ep.plan_id
            LEFT OUTER JOIN user u ON u.id = ep.user_id
            WHERE ep.end IS NULL
            ORDER BY ep.user_id, p.is_plan_based
        ");
        $query->bind($params);
        $plans = [];
        foreach($query->getAllRecords() as $_plan){
            if(! isset($users)){
                $users[$_plan['user_id']] = [];
            }
            if(10 == $_plan['plan_id']){
                $users[$_plan['user_id']]['oklad'] = $_plan['value'];
            }else{
                if(1 == $_plan['is_plan_based']){
                    $users[$_plan['user_id']]['isplans'] = 1;
                }else{
                    if(0 < $_plan['is_discrete']){
                        $users[$_plan['user_id']]['sdelka'] = 1;
                    }
                }
            }
        }
        $oklads = [];
        foreach($users as $user_id => $user){
            if($oklad = \Arr::get($user, 'oklad', 0)){
                if($isplan = \Arr::get($user, 'isplans', 0)){
                    $oklads[$user_id] = $oklad + \Arr::get($user, 'base', 0);
                }else{
                    $oklads[$user_id] = $oklad;
                }
            }else{
                if($isplan = \Arr::get($user, 'isplans', 0)){
                    $oklads[$user_id] = \Arr::get($user, 'base', 0);
                }else{
                    //$oklads[$user_id] = $oklad;
                }
            }
        }
        return $oklads;
    }
    
    
    // данные по окладам сотрудников
    public function getInfoUsers($date = NULL)
    {
        $criteria = ["oid" => "u.owner_id=:oid:"];
        $params = ["oid" => OWNER_ID];
        if(! $date){
            $criteria["end"] = "es.end IS NULL";
        }else{
            if(is_array($date)){
                list($start, $end) = $date;
            }else{
                $start = $date; // первый день месяца
                $stamp = strtotime($start);
                $_start = date('Y',$stamp).'-'.date('m',$stamp).'-01'; 
                $_date = new \DateTime($_start);
                $_date->sub(new \DateInterval('P1D'));
                $end = $_date->format('Y-m-d');
            }
            $criteria["end"] = "(es.start <= :start: AND es.end <= :end:) OR (es.start <= :start: AND es.end IS NULL)";
            $params["start"] = $start;
            $params["end"] = $end;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_salary es
            LEFT OUTER JOIN user u ON u.id = es.user_id
            {$where}
        ");
        $query->bind($params);
        $users = [];
        foreach($query->getAllRecords() as $_base){
            if(! isset($users)){
                $users[$_base['user_id']] = [];
            }
            $users[$_base['user_id']]['base'] = $_base['base'];
        }
        
        $query = $this->db->newStatement("
            SELECT
            ep.user_id,
            ep.plan_id,
            p.is_plan_based,
            p.is_discrete,
            ep.value,
            TRIM( CONCAT_WS( ' ', u.lastname, u.firstname, u.secondname ) ) name
            FROM employee_plan ep
            LEFT OUTER JOIN plan p ON p.id = ep.plan_id
            LEFT OUTER JOIN user u ON u.id = ep.user_id
            WHERE ep.end IS NULL
            ORDER BY ep.user_id, p.is_plan_based
        ");
        $query->bind($params);
        $plans = [];
        foreach($query->getAllRecords() as $_plan){
            if(! isset($users)){
                $users[$_plan['user_id']] = [];
            }
            if(10 == $_plan['plan_id']){
                $users[$_plan['user_id']]['oklad'] = $_plan['value'];
            }else{
                if(1 == $_plan['is_plan_based']){
                    $users[$_plan['user_id']]['isplans'] = 1;
                }else{
                    if(0 < $_plan['is_discrete']){
                        $users[$_plan['user_id']]['sdelka'] = 1;
                    }
                }
            }
        }
        
        foreach($users as $user_id => $user){
            if($oklad = \Arr::get($user, 'oklad', 0)){
                if($isplan = \Arr::get($user, 'isplans', 0)){
                    $users[$user_id]['total'] = $oklad + \Arr::get($user, 'base', 0);
                }else{
                    $users[$user_id]['total'] = $oklad;
                }
            }else{
                if($isplan = \Arr::get($user, 'isplans', 0)){
                    $users[$user_id]['total'] = \Arr::get($user, 'base', 0);
                }else{
                    //$oklads[$user_id] = $oklad;
                }
            }
        }
        return $users;
    }
    
    public function rate_per_hour(array $ids){
        $criteria = ["id" => "es.id IN :ids:"];
        $params = ["ids" => $ids];
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                es.base,
                ep.plan_id,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_salary es
            LEFT employee_plan ep ON ep.user_id=es.user_id
            LEFT OUTER JOIN user u ON u.id = es.user_id
            {$where}
            LIMIT 1
        ");
        $query->bind($params);
        return ;
    }
    
    /*
    * Запонение из employee data
    **/
    public function mirgate()
    {
        return 1;
        $query = $this->db->newStatement("
            SELECT
                u.id,
                ed.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_data ed ON ed.user_id = u.id
            LEFT OUTER JOIN codex c1 ON c1.role_id=u.role_id AND c1.rule_id=(SELECT id FROM rule WHERE code = :allow:)
            LEFT OUTER JOIN codex c2 ON c2.user_id=u.id AND c2.rule_id=(SELECT id FROM rule WHERE code = :allow:)
            ORDER BY ed.status, u.lastname, u.firstname, u.secondname
        ");
        $query->setvarChar('allow', self::ALLOW_GRANT);
        $employees = $query->getAllRecords();
        $i = 0;
        foreach($employees as $employee){
            $data = ['user_id' => $employee['id'],
                     'base' => $employee['basic_rates'],
                     'creater' => $this->user->id,
                     'updater' => $this->user->id,
                     'start' => date('Y-m-d H:i:s'),
                     //'updated' => date('Y-m-d H:i:s'),
                     ];
            $this->insert($data);
            $i++;
        }
    }
    
}