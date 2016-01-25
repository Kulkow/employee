<?php

namespace Modules\Employee\Admin\Models;

/*
* Данные пользователей пользователей
*
**/
class EmployeeData extends \Classes\Base\Model
{
    
    protected $table = 'employee_data';
    
    protected $pk = 'user_id';

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `employee_data` (
            `user_id` mediumint(9) NOT NULL,
            `status` tinyint(1) DEFAULT '0',
            `start` datetime DEFAULT NULL,
            `position` tinyint(4) DEFAULT '0',
            `number` varchar(100) NOT NULL,
            `skype` varchar(15) DEFAULT NULL,
            `phone` varchar(15) DEFAULT NULL,
            `comment` text,
            `basic_rates` int(11) DEFAULT '0',
            `plans` varchar(255) DEFAULT '',
            `first_quest_min_rates` int(11) DEFAULT '0',
            `promised_rates` int(11) DEFAULT '0',
            `desired_basic_rates` int(11) DEFAULT '0',
            `last_quest_min_rates` int(11) DEFAULT '0',
            `salary_password` varchar(50) DEFAULT NULL,
            `point_id` smallint(6) DEFAULT '141',
            `room_id` int(11) DEFAULT NULL,
            `source_id` tinyint(4) DEFAULT '0',
            `hire_date` date DEFAULT NULL,
            `fire_date` date DEFAULT NULL,
            `expire_date` date DEFAULT NULL,
            PRIMARY KEY (`user_id`)
          ) ENGINE=Aria  DEFAULT CHARSET=utf8;
          ";
    }
    
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                ed.*,
                ed.user_id as id,
                u.role_id as role_id,
                u.email,
                u.password,
                u.phone as personal_phone,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_data ed
            LEFT JOIN user u ON ed.user_id=u.id
            WHERE ed.user_id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function getByList(array $filter = [], $is_work = true, $order_by = 'ed.user_id')
    {
        $users = [];
        $params = [];
        $criteria = [];
        foreach($filter as $key => $value){
            if(! is_array($value)){
                $criteria[$key] = "ed.".$key." = :".$key.":";
            }else{
                $criteria[$key] = "ed.".$key." IN (:".$key.":)";
            }
            $params[$key] = $value;
        }
        if($is_work === true){
            $criteria['owner_id'] = "(ed.fire_date IS NULL OR ed.expire_date >= NOW()) AND u.owner_id = :owner_id:";
            $params['owner_id'] = OWNER_ID;
        }else{
            if($is_work){ //experies
                $criteria['owner_id'] = "(ed.fire_date IS NULL OR ed.fire_date >= :experies:) AND u.owner_id = :owner_id:";
                $params['owner_id'] = OWNER_ID;
                $params['experies'] = $is_work;
            }
        }
        
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ed.*,
                u.id,
                u.email,
                u.password,
                u.phone as personal_phone,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_data ed
            LEFT JOIN user u ON ed.user_id=u.id
            {$where}
            ORDER BY {$order_by}
        ");
        $query->bind($params);
        foreach($query->getAllRecords() as $user){
            $users[$user['id']] = $user;
        }
        return $users;
    }
    
    public function setNumber(array $user, $number = 1)
    {
        $number = intval($number);
        if(\Arr::get($user,'department_id', 0)){
            $numbers = [];
            $query = $this->db->newStatement("
                SELECT
                    ed.*
                FROM employee_data ed
                WHERE ed.department_id = :department_id:
                ORDER BY ed.number
            ");
            $query->setInteger('department_id',$user['department_id']);
            foreach($query->getAllRecords() as $_user){
                if($n = \Arr::get($_user, 'number', null)){
                    if(\Arr::get($_user, 'user_id', null) != $user['user_id']){
                        $n = intval($n);
                        $numbers[$n] = $_user;
                    }
                }
            }
            if($_u = \Arr::get($numbers, $number, 0)){
                $_n = $number;
                foreach($numbers as $n => $_user){
                    if($n >= $number){
                        $_n++;
                        $_update = ['user_id' => $_user['user_id'], 'number' => $_n];
                        $this->upsert($_update);
                    }
                }
            }else{
                if(!$number){
                    $ns = array_keys($numbers);
                    if(! empty($ns)){
                        $number = max($ns) + 1;
                    }else{
                        $number = 1;    
                    }
                }else{
                    $number = $number ? $number : 1;
                }
            }
        }
        return $number;
    }
    
    
    public function recruit($data = [])
    {
        if(empty($data['point_id'])){
            $data['point_id'] = 141;
        }
        if(empty($data['room_id'])){
            $data['room_id'] = 4;
        }
        if(empty($data['status'])){
            $data['status'] = 5;
        }
        if(empty($data['start'])){
            $data['start'] = date('Y-m-d H:i:s');
        }
        return $this->insert($data);
    }
    
    
    //Проверяет принадлежит ли сотрудник в подразделению с учетом вложенности
    public function hasChildren($department_id = null, $id = null){
        if(! $department_id AND ! $id){
            return false;
        }
        $query = $this->db->newStatement("
        SELECT pmd.id 
            FROM pm_department AS pmd
            INNER JOIN pm_department AS pmd2
            LEFT JOIN employee_data AS ed ON ed.department_id=pmd.id
            WHERE pmd2.id=:department_id: AND pmd.lkey >= pmd2.lkey AND pmd.rkey <= pmd2.rkey AND ed.user_id = :id:
        ");
        $query->setInteger('id', $id);
        $query->setInteger('department_id', $department_id);
        $department = $query->getFirstRecord();
        if(null == $department){
            return false;
        }
        else{
            return true;
        }
    }
    
    public function has($password = NULL, $user_id = FALSE)
    {
        if(! $user_id){
            $user_id = $this->user->id;
        }
        $query = $this->db->newStatement("
            SELECT  user_id,salary_password
            FROM employee_data
            WHERE user_id = :user_id: AND salary_password = :password:
            LIMIT 1");
        $query->setVarChar('password', $password);
        $query->setInteger('user_id', $user_id);
        if(null !== $query->getFirstRecord()){
            return true;
        }
        return false;
    }
    
    public function getmaxnumber($department_id = null){
        if(! $department_id){
            return 0;
        }
        $query = $this->db->newStatement("
        SELECT max(number) as max 
            FROM employee_data AS pmd
            WHERE pmd.department_id=:department_id:
        ");
        $query->setInteger('department_id', $department_id);
        $max = $query->getFirstRecord();
        if(null == $max){
            return 0;
        }
        else{
            return $max['max'];
        }
    }
    
    public function migrate()
    {
        return 1;
        $query = $this->db->newStatement("
            SELECT
                u.id
            FROM user u
            INNER JOIN employee_data ed ON ed.user_id=u.id
            LEFT OUTER JOIN codex c1 ON c1.role_id=u.role_id AND c1.rule_id=(SELECT id FROM rule WHERE code = 'PRIVATE_OFFICE')
            LEFT OUTER JOIN codex c2 ON c2.user_id=u.id AND c2.rule_id=(SELECT id FROM rule WHERE code = 'PRIVATE_OFFICE')
            WHERE (c1.is_allowed OR c2.is_allowed OR ed.expire_date >= NOW()) AND u.owner_id=1
        ");
        $ids = [];
        foreach($query->getAllRecords() as $user){
            $ids[] = $user['id'];
        }
        $query = $this->db->newStatement("
            UPDATE
                employee_data
            SET
                `fire_date` = '2015-01-01',
                `expire_date` = '2015-01-01'
            WHERE user_id NOT IN (:ids:) AND fire_date IS NULL
        ");
        $query->setArray('ids',$ids);
        $query->execute();
    }
}