<?php

namespace Modules\Employee\Admin\Models;

class Employee extends \Classes\Base\Model
{
    const STATUS_CHIEF = 1;
    const STATUS_VICE = 2;
    const STATUS_BASE_WORKER = 3;
    const STATUS_WORKER = 4;
    const STATUS_LEARNER = 5; 
    
    const ALLOW_GRANT = 'PRIVATE_OFFICE'; // права доступа в админку
    
    protected $table = 'pm_employee';

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `employee` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `department_id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `start` date NOT NULL,
            `end` date NULL,
            PRIMARY KEY (`id`),
            KEY `department_id` (`department_id`),
            KEY `user_id` (`user_id`)
          ) ENGINE=Aria  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
          ALTER TABLE `employee_data` ADD `number` VARCHAR( 100 ) NOT NULL AFTER `position`
          ALTER TABLE `employee` ADD `end` start date default (0) AFTER `user_id`
          ALTER TABLE `employee` ADD `start` start date NOT NULL AFTER `user_id`
          ";
    }
    
    /**
    * Список статусов внутри подразделения
    **/
    public function listStatus()
    {
        $statuses = [];
        $_statuses = [
                self::STATUS_CHIEF => 'Начальник подразделения',
                self::STATUS_VICE => 'Заместитель',
                self::STATUS_BASE_WORKER => 'Сотрудник',
                self::STATUS_WORKER => 'Младший сотрудник',
                self::STATUS_LEARNER => 'Ученик', 
                ];
        foreach($_statuses as $_id => $name){
            $statuses[$_id] = ['id' => $_id,
                               'name' => $name
                               ];
        }
        return $statuses;
    }
    
    public function search($q, $limit)
    {
        $query = $this->db->newStatement("
            SELECT
                u.id,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_data ed ON ed.user_id = u.id
            WHERE u.lastname LIKE :q: OR u.firstname LIKE :q: OR u.secondname LIKE :q:
            ORDER BY u.lastname, u.firstname, u.secondname
            LIMIT :limit:
        ");
        $query->setVarChar('q', '%'.$q.'%');
        $query->setInteger('limit', $limit);

        return $query->getAllRecords();
    }
    

    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                e.id,
                e.department_id,
                e.user_id,
                e.start,
                e.end,
                d.name as department,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM pm_employee e
            LEFT OUTER JOIN user u ON u.id = e.user_id
            LEFT OUTER JOIN pm_department d ON d.id = e.department_id
            WHERE e.id = :id: AND u.owner_id=:oid:
            ORDER BY u.lastname, u.firstname, u.secondname
        ");
        $query->setInteger('id', $id);
        $query->setInteger('oid', OWNER_ID);
        return $query->getFirstRecord();
    }
            
    public function getByDetail($id)
    {
        $query = $this->db->newStatement("
            SELECT
                u.id,
                d.name as department,
                ed.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_data ed
            LEFT OUTER JOIN user u ON u.id = ed.user_id
            LEFT OUTER JOIN pm_employee e ON e.user_id = ed.user_id
            LEFT JOIN pm_department d ON d.id = e.department_id
            WHERE u.id = :id: AND u.owner_id=:oid:
            ORDER BY u.lastname, u.firstname, u.secondname
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        $query->setInteger('oid', OWNER_ID);
        $employee = $query->getFirstRecord();
        $employee['experience'] = time() - strtotime($employee['start']);
        return $employee;
    }

    public function getByDepartment($departmentId, $filter = [])
    {
        $employees = [];
        $filter['department_id'] = $departmentId;
        foreach($this->getByList($filter) as $row){
            $employees[$row['id']] = $row;
        }
        return $employees;
    }
    
    public function getByList(array $filter, $expired_work = false)
    {
        $criteria = ["oid" => " u.owner_id=:oid:"];
        $params = ["oid" => OWNER_ID];
        if(!$expired_work){
            $criteria['fire_date'] = "(ed.fire_date IS NULL OR ed.expire_date >= NOW() )";
        }else{
            $criteria['fire_date'] = "(ed.fire_date IS NULL OR ed.fire_date >= :expired: )";
            $params["expired"] = $expired_work;
        }

        if(! empty($filter['department_id'])){
            if(is_array($filter['department_id'])){
                $criteria['department_id'] = "e.department_id IN (:department_id:)";
                $params['department_id'] = $filter['department_id'];    
            }else{
                $criteria['department_id'] = "e.department_id = (:department_id:)";
                $params['department_id'] = $filter['department_id'];
            }
        }
        if(! empty($filter['user_id'])){
            $criteria['user_id'] = "e.user_id = (:user_id:)";
            $params['user_id'] = $filter['user_id'];
        }
        if(! empty($filter['users']) && count($filter['users'])){
            $criteria['user_ids'] = "e.user_id IN (:user_ids:)";
            $params['user_ids'] = $filter['users'];
        }
        if(empty($filter['start'])){
            $criteria['start'] = "e.end IS NULL";
        }elseif(!empty($filter['start']) AND !empty($filter['end'])){
            $criteria['start'] = "(
                (e.start >=  :start: AND e.end <= :end:)
                OR
                (e.start <= :start: AND e.end >= :start:)
                OR
                (e.start <= :start: AND e.end IS NULL)
                OR
                (e.start >= :start: AND e.start <= :end: AND e.end IS NULL)
                )";
            $params['start'] = $filter['start'];
            $params['end'] = $filter['end'];
        }
        
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                u.id,
                e.id as rowid,
                u.role_id,
                ed.status,
                ed.fire_date,
                e.department_id,
                e.start,
                e.end,
                d.name as department,
                d.lkey as department_lkey,
                d.level as department_level,
                d.number as department_number,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            INNER JOIN employee_data ed ON ed.user_id = u.id
            LEFT JOIN pm_employee e ON e.user_id = u.id
            LEFT JOIN pm_department d ON d.id = e.department_id
            {$where}
            ORDER BY d.lkey, ed.status, u.lastname, u.firstname, u.secondname
        ");
        $params['allow'] = self::ALLOW_GRANT;
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function getByListFull(array $filter)
    {
        $criteria = ["oid" => " u.owner_id=:oid:"];
        $params = ["oid" => OWNER_ID];
        if(! empty($filter['department_id'])){
            $criteria['department_id'] = "pe.department_id = (:department_id:)";
            $params['department_id'] = $filter['department_id'];
        }
        if(! empty($filter['user_id'])){
            if(is_array($filter['user_id'])){
                $criteria['user_id'] = "pe.user_id IN (:user_id:)";
            }else{
                $criteria['user_id'] = "pe.user_id = (:user_id:)";                
            }
            $params['user_id'] = $filter['user_id'];
        }
        if(!empty($filter['start']) AND !empty($filter['end'])){
            $criteria['start'] = "(
                (pe.start >=  :start: AND pe.end <= :end:)
                OR
                (pe.start <= :start: AND pe.end >= :start:)
                OR
                (pe.start <= :start: AND pe.end IS NULL)
                OR
                (pe.start >= :start: AND pe.start <= :end: AND pe.end IS NULL)
                )";
            $params['start'] = $filter['start'];
            $params['end'] = $filter['end'];
        }else{
            $criteria['start'] = "pe.end IS NULL";
        }
        
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                pe.*,
                pe.id as rowid,
                u.role_id,
                ed.status,
                ed.fire_date,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM pm_employee pe
            INNER JOIN employee_data ed ON ed.user_id = pe.user_id
            LEFT JOIN user u ON pe.user_id = u.id
            {$where}
            ORDER BY ed.status, u.lastname, u.firstname, u.secondname
        ");
        $params['allow'] = self::ALLOW_GRANT;
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function getByListLight(array $filter)
    {
        $criteria = ["oid" => " u.owner_id=:oid:"];
        $params = ["oid" => OWNER_ID];
        if(! empty($filter['department_id'])){
            $criteria['department_id'] = "pe.department_id = (:department_id:)";
            $params['department_id'] = $filter['department_id'];
        }
        if(! empty($filter['user_id'])){
            if(is_array($filter['user_id'])){
                $criteria['user_id'] = "pe.user_id IN (:user_id:)";
            }else{
                $criteria['user_id'] = "pe.user_id = (:user_id:)";                
            }
            $params['user_id'] = $filter['user_id'];
        }
        if(!empty($filter['start']) AND !empty($filter['end'])){
            $criteria['start'] = "(
                (pe.start >=  :start: AND pe.end <= :end:)
                OR
                (pe.start <= :start: AND pe.end >= :start:)
                OR
                (pe.start <= :start: AND pe.end IS NULL)
                OR
                (pe.start >= :start: AND pe.start <= :end: AND pe.end IS NULL)
                )";
            $params['start'] = $filter['start'];
            $params['end'] = $filter['end'];
        }else{
            $criteria['start'] = "pe.end IS NULL";
        }
        
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                pe.*,
                pe.id as rowid,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM pm_employee pe
            LEFT JOIN user u ON pe.user_id = u.id
            {$where}
            ORDER BY pe.department_id
        ");
        $params['allow'] = self::ALLOW_GRANT;
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    /**
    * Employee users ids
    * $exs -true return array Exist employee (many Department)
    **/
    public function ExistUsers(array $users, $exs = FALSE)
    {
        $criteria = [];
        $params = [];
        if(isset($users) && count($users)){
            $criteria['user_id'] = "u.id IN (:user_ids:)";
            $params['user_ids'] = $users;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                u.id,
                e.department_id,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            LEFT JOIN pm_employee e ON e.user_id = u.id
            {$where}
            ORDER BY u.id
        ");
        $query->bind($params);
        $_users = $query->getAllRecords();
        $employee = [];
        foreach($_users as $user){
            if($exs){
                if(! empty($user['department_id'])){
                    $employee[] = $user['id'];
                }
            }else{
                if(empty($user['department_id'])){
                    $employee[] = $user['id'];
                }
            }
        }
        return $employee;
    }
    
    /**
    * $users - ids
    */
    public function move(array $users, $department = NULL)
    {
        $params = [];
        $criteria = [];
        $nexists = $this->ExistUsers($users);
        if($department and ! empty($users)){
            if(! empty($nexists)){
                foreach($nexists as $_id){
                    $data = ['user_id' => $_id, 'department_id' => $department, 'start' => date('Y-m-d')];
                    $this->table->insert($data);
                }
            }
            $set = "`department_id` = :department_id:";
            $params['department_id'] = intval($department);
            if(! empty($users)){
                $criteria = 'user_id IN (:ids:)';
                $params['ids'] = $users;
                $query = $this->db->newStatement("
                    UPDATE `pm_employee`
                    SET ".$set."
                    WHERE {$criteria}
                ");
                $query->bind($params);
                $query->execute();
                return $query->getAffectedRowCount();
            }
        }
        return false;
    }
    
    
    // В каких подразделениях пользователь
    public function map($users = NULL, $revers = FALSE)
    {
        $criteria = [];
        $params = [];
        $return = [];
        if(isset($users) && count($users)){
            $criteria['user_id'] = "e.user_id IN (:user_ids:)";
            $params['user_ids'] = $users;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND', $criteria) : '';
        $sort = $revers ? "ed.lkey" : "u.id";
        $query = $this->db->newStatement("
            SELECT
                e.*,
                ed.name department,
                ed.number department_number,
                ed.lkey department_lkey,
                ed.level department_level,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM pm_employee e
            LEFT JOIN user u ON e.user_id = u.id
            LEFT JOIN pm_department ed ON e.department_id = ed.id
            {$where}
            ORDER BY {$sort}
        ");
        $query->bind($params);
        if($revers){
            foreach($query->getAllRecords() as $row){
                if(! isset($return[$row['department_id']])){
                    $return[$row['department_id']] = ['id' => $row['department_id'],
                                                      'name' => $row['department'],
                                                      'lkey' => $row['department_lkey'],
                                                      'level' => $row['department_level'],
                                                      'number' => $row['department_number'],
                                                      'users' => []
                                                      ];
                }
                $return[$row['department_id']]['users'][$row['user_id']] = $row;
            }
        }else{
            foreach($query->getAllRecords() as $row){
                if(! isset($return[$row['user_id']])){
                    $return[$row['user_id']] = ['id' => $row['user_id'],
                                                'name' => $row['name'],
                                                'departments' => []
                                                ];
                }
                $return[$row['user_id']]['departments'][$row['department_id']] = $row;
            }
        }
        return $return;
    }
}