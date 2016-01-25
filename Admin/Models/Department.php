<?php

namespace Modules\Employee\Admin\Models;

use Modules\Employee\Admin\Extensions\NestedSetsTree;

class Department extends \Classes\Base\Model
{
    protected $table = 'pm_department';
    
    public $tree = NULL;
    
    public function __construct(\Classes\DB\Connection $db, \Classes\Auth\User $user)
    {
        parent::__construct($db, $user);
        $this->tree = new NestedSetsTree(['table' => 'pm_department'],$db);
    }
    
    protected function init_sql(){
        "CREATE TABLE IF NOT EXISTS `pm_department` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `lkey` int(11) NOT NULL default '0',
          `rkey` int(11) NOT NULL default '0',
          `level` int(11) NOT NULL default '0',
          `name` varchar(100),
          `number` varchar(100),
          `datesalary` intval(5) default 21,
          `chief_id` int(11) NOT NULL,
          INDEX chief_id (`chief_id`),
          PRIMARY KEY (`id`)
        ) ENGINE=Aria DEFAULT CHARSET=utf8;
        INSERT INTO `employee_department` VALUES (1,1,2,0,'Наша команда',0)";
    }

    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                d.id,
                d.name,
                d.number,
                d.level,
                d.lkey,
                d.rkey,
                d.chief_id,
                d.datesalary,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) chief
            FROM pm_department d
            LEFT OUTER JOIN user u ON u.id = d.chief_id
            WHERE d.id = :id:
        ");
        $query->setInteger('id', $id);

        return $query->getFirstRecord();
    }
    
    public function getBySmallId($id){
        $query = $this->db->newStatement("
            SELECT
                d.id,
                d.name,
            FROM employee_department d
            WHERE d.id = :id:
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }

    public function getList($filter = [])
    {
        $plans = [];
        $params = [];
        $criteria = [];
        foreach($filter as $key => $value){
            if(! is_array($value)){
                $criteria[$key] = "d.".$key." = :".$key.":";
            }else{
                $criteria[$key] = "d.".$key." IN (:".$key.":)";
            }
            $params[$key] = $value;
        }
        $where = ! empty($criteria) ? " AND ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                d.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) chief
            FROM pm_department d
            LEFT OUTER JOIN user u ON u.id = d.chief_id
            WHERE d.level > 0 {$where}
            ORDER BY d.lkey ASC
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function getTree()
    {
        $field = "id, A.name, A.lkey, A.rkey, A.level, A.chief_id, A.number,TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) as chief";
        $join = " LEFT OUTER JOIN user u ON u.id = A.chief_id";
        return $this->tree->Full($field, '', $join);
    }
    
    public function getChildren($department_id = null)
    {
        //$field = "id, A.name, A.lkey, A.rkey, A.level, A.chief_id, A.number,TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) as chief";
        //$join = " LEFT OUTER JOIN user u ON u.id = A.chief_id";
        return $this->tree->Branch($department_id);
    }
    
    
    
    public function getChiefDepartments($users = []){
        $departments = [];
        $query = $this->db->newStatement("SELECT 
            pmd.*
        FROM pm_department as pmd
        WHERE pmd.chief_id IN (:user_id:)
        ORDER BY pmd.lkey");
        $params = ['user_id' => $users];
        $query->bind($params);
        foreach($query->getAllRecords() as $row){
            $departments[$row['id']] = ['id' => $row['id'],
                                   'name' => $row['name'],
                                   'number' => $row['number'],
                                  ]; 
        }
        return $departments;
    }
    
    
    /**
    * На основе employee data
    **/
    public function getTreeSalary(){
        $departments = [];
        $query = $this->db->newStatement("SELECT 
            pmd.*,
            ed.user_id,
            ed.department_id as did,
            ed.number as u_number,
            ed.is_vax as is_vax,
            ed.fire_date as fire_date,
            ed.fire_date as expire_date,
            ed.status as status,
            u.lastname u_lastname,
            TRIM(CONCAT_WS(' ', u.firstname, u.secondname)) u_name
        FROM pm_department as pmd
        LEFT JOIN user u ON pmd.chief_id=u.id
        LEFT JOIN employee_data ed ON ed.user_id=pmd.chief_id
        ORDER BY pmd.lkey,ed.number,ed.status");
        foreach($query->getAllRecords() as $row){
            $lkey = \Arr::get($row, 'lkey', 0);
            if(! isset($departments[$lkey])){
                $department = $row;
                $department['users'] = [];
                $departments[$lkey] = $department;
            }
        }
        $query = $this->db->newStatement("SELECT 
            pd.*,
            ed.user_id,
            ed.department_id as did,
            ed.number as u_number,
            ed.is_vax as is_vax,
            ed.fire_date as fire_date,
            ed.expire_date as expire_date,
            ed.status as status,
            u.lastname u_lastname,
            TRIM(CONCAT_WS(' ', u.firstname, u.secondname)) u_name
        FROM employee_data ed
        LEFT JOIN user u ON u.id=ed.user_id
        LEFT JOIN pm_department pd ON pd.id=ed.department_id
        WHERE (ed.fire_date IS NULL OR ed.expire_date >= NOW()) AND u.owner_id = :owner_id:
        ORDER BY pd.lkey,ed.number,ed.status
        ");
        $query->setInteger('owner_id', OWNER_ID);
        $dusers = [];
        $departments[1000001] =
                        ['id' => -2,
                        'name' => 'Уволенные',
                        'number' => 0,
                        'level' => 1,
                        'lkey' => 1000001,
                        'users' => [],
                        ];
        foreach($query->getAllRecords() as $row){
            $lkey = \Arr::get($row, 'lkey', 100000);
            if(100000 == $lkey){
                if(! isset($departments[$lkey])){
                    $departments[$lkey] =
                        ['id' => -1,
                        'name' => 'Вне штата',
                        'number' => 0,
                        'level' => 1,
                        'lkey' => $lkey,
                        'users' => [],
                        ];
                }    
            }
            $user = ['id' => $row['user_id'],
                    'name' => $row['u_lastname'].' '.$row['u_name'],
                    'family' => $row['u_lastname'],
                    'sname' => $row['u_name'],
                    'number' => $row['u_number'],
                    'fire_date' => $row['fire_date'],
                    'expire_date' => $row['expire_date'],
                    'status' => $row['status'],
                    'is_vax' => $row['is_vax'],
                    ];
            if(! empty($user['fire_date'])){
                $departments[1000001]['users'][] = $user;
            }else{
                $departments[$lkey]['users'][] = $user;                
            }
        }
        unset($departments[1]);
        ksort($departments);
        
        $mEmployeeData = new EmployeeData($this->db, $this->user);
        // upsert empty number
        foreach($departments as $lkey => $d){
            $max = 0;
            foreach($d['users'] as $u){
                if(! empty($u['number'])){
                    $max = intval($u['number']);
                }
            }
            foreach($d['users'] as $key => $u){
                if(empty($u['number'])){
                    $max++;
                    $u['number'] = $max;
                    $d['users'][] = $u;
                    unset($d['users'][$key]);
                    $data = ['user_id' => $u['id'], 'number' => $u['number']];
                    $mEmployeeData->upsert($data);
                }
            }
            $departments[$lkey] = $d;
        }
        return $departments;
    }
    
    
    /**
    * На основе employee data
    **/
    public function getTreeDepartmentSalary($id){
        $departments = $dids = [];
        $query = $this->db->newStatement("
        SELECT 
            pmd.*
        FROM pm_department as pmd, pm_department as pmd2
        WHERE pmd2.id = :id: AND pmd.lkey >= pmd2.lkey AND pmd.rkey <= pmd2.rkey
        ORDER BY pmd.lkey");
        
        $query->setInteger('id', $id);
        foreach($query->getAllRecords() as $row){
            $lkey = \Arr::get($row, 'lkey', 0);
            $dids[$row['id']] = $row['id'];
            if(! isset($departments[$lkey])){
                $department = $row;
                $department['users'] = [];
                $departments[$lkey] = $department;
            }
        }
        $query = $this->db->newStatement("SELECT 
            pmd.*,
            ed.user_id,
            ed.department_id as did,
            ed.number as u_number,
            ed.is_vax as is_vax,
            ed.fire_date as fire_date,
            ed.expire_date as expire_date,
            ed.status as status,
            u.lastname u_lastname,
            TRIM(CONCAT_WS(' ', u.firstname, u.secondname)) u_name
        FROM employee_data ed
        LEFT JOIN user u ON u.id=ed.user_id
        LEFT JOIN pm_department pmd ON pmd.id=ed.department_id
        WHERE ed.department_id IN (:dids:)  AND
            (ed.fire_date IS NULL OR ed.expire_date >= NOW()) AND u.owner_id = :owner_id:
        ORDER BY pmd.lkey,ed.number,ed.status
        ");
        $query->setArray('dids', $dids);
        $query->setInteger('owner_id', OWNER_ID);
        $dusers = [];
        /*
        $departments[1000001] =
                        ['id' => -2,
                        'name' => 'Уволенные',
                        'number' => 0,
                        'level' => 1,
                        'lkey' => 1000001,
                        'users' => [],
                        ];*/
        foreach($query->getAllRecords() as $row){
            $lkey = \Arr::get($row, 'lkey', 100000);
            $user = ['id' => $row['user_id'],
                    'name' => $row['u_lastname'].' '.$row['u_name'],
                    'family' => $row['u_lastname'],
                    'sname' => $row['u_name'],
                    'number' => $row['u_number'],
                    'fire_date' => $row['fire_date'],
                    'expire_date' => $row['expire_date'],
                    'status' => $row['status'],
                    'is_vax' => $row['is_vax'],
                    ];
            if(! empty($user['fire_date'])){
                $departments[1000001]['users'][] = $user;
            }else{
                $departments[$lkey]['users'][] = $user;                
            }
        }
        unset($departments[1]);
        ksort($departments);
        return $departments;
    }
    
    /*
    * Tree for emploeys на текущий момент
    **/
    public function getTreeUsers(){
        $chiefs = $allchiefs = $departments = $ldepartments = [];
        $query = $this->db->newStatement("SELECT 
            pmd.*,
            ed.user_id,
            ed.department_id as did,
            ed.number as u_number,
            ed.is_vax as is_vax,
            ed.fire_date as fire_date,
            ed.status as status,
            u.lastname u_lastname,
            TRIM(CONCAT_WS(' ', u.firstname, u.secondname)) u_name
        FROM pm_department as pmd
        LEFT JOIN user u ON pmd.chief_id=u.id
        LEFT JOIN employee_data ed ON ed.user_id=pmd.chief_id
        ORDER BY pmd.lkey,ed.status");
        foreach($query->getAllRecords() as $row){
            $lkey = \Arr::get($row, 'lkey', 0);
            $ldepartments[$row['id']] = $lkey; //lkey => department_id
            if(! isset($departments[$lkey])){
                $department = $row;
                $department['set_chief'] = 0;
                $department['users'] = [];
                $departments[$lkey] = $department;
            }
            if($row['user_id'] > 0){
                $allchiefs[$row['user_id']] = $row['user_id'];
                $chiefs[$row['id']] = ['id' => $row['user_id'],
                                        'name' => $row['u_lastname'].' '.$row['u_name'],
                                        'family' => $row['u_lastname'],
                                        'sname' => $row['u_name'],
                                        'number' => $row['u_number'],
                                        'fire_date' => $row['fire_date'],
                                        //'status' => $row['status'],
                                        'status' => 1,
                                        'is_vax' => $row['is_vax'],
                                        'did' => $row['did'],
                                        ];
            }
        }
        $query = $this->db->newStatement("SELECT 
            pd.*,
            ed.user_id,
            ed.department_id as did,
            ed.number as u_number,
            ed.is_vax as is_vax,
            ed.fire_date as fire_date,
            ed.status as status,
            u.lastname u_lastname,
            TRIM(CONCAT_WS(' ', u.firstname, u.secondname)) u_name
        FROM employee_data ed
        LEFT JOIN user u ON u.id=ed.user_id
        LEFT JOIN pm_employee e ON e.user_id=ed.user_id
        LEFT JOIN pm_department pd ON pd.id=e.department_id
        WHERE (ed.fire_date IS NULL OR ed.expire_date >= NOW()) AND e.end IS NULL AND u.owner_id = :owner_id:
        ORDER BY pd.lkey,ed.status
        ");
        $query->setInteger('owner_id', OWNER_ID);
        
        $dusers = [];
        foreach($query->getAllRecords() as $row){
            $lkey = \Arr::get($row, 'lkey', 100000);
            $chief = \Arr::get($chiefs, $row['id'], NULL);
            $is_chief = \Arr::get($allchiefs, $row['user_id'], false);
            if(100000 == $lkey){
                if(! isset($departments[$lkey])){
                    $departments[$lkey] =
                        ['id' => 0,
                        'name' => 'Вне штата',
                        'number' => 0,
                        'level' => 1,
                        'lkey' => $lkey,
                        'users' => [],
                        ];
                }    
            }
            
            if($row['chief_id'] AND ! $departments[$lkey]['set_chief']){
                $exist = \Arr::get($dusers, $chief['id'], null);
                $did = \Arr::get($chief, 'did', NULL);
                if($chief AND ! $exist){
                    if(! $did){
                        $dusers[$chief['id']] = $lkey;
                        $departments[$lkey]['users'][] = $chief;
                    }else{
                        if($did == $row['id']){
                            $dusers[$chief['id']] = $lkey;
                            $departments[$lkey]['users'][] = $chief;    
                        }else{
                            //add $did
                            $_lkey = \Arr::get($ldepartments, $did);
                            $departments[$_lkey]['users'][] = $chief;
                            $dusers[$chief['id']] = $_lkey;
                        }
                    }
                }
                $departments[$lkey]['set_chief'] = 1;
            }
            $did = \Arr::get($row, 'did', NULL);
            if($is_chief AND (100000 == $lkey)){
                $exist = true; // не писать тех кто в подразделениях
            }else{
                $exist = \Arr::get($dusers, $row['user_id'], null);
            }
            if(! $exist){
                $user = ['id' => $row['user_id'],
                        'name' => $row['u_lastname'].' '.$row['u_name'],
                        'family' => $row['u_lastname'],
                        'sname' => $row['u_name'],
                        'number' => $row['u_number'],
                        'fire_date' => $row['fire_date'],
                        'status' => $row['status'],
                        'is_vax' => $row['is_vax'],
                        ];
                if(! $did){
                    $dusers[$chief['id']] = $lkey;
                    $departments[$lkey]['users'][] = $user;
                }else{
                    if($did == $row['id']){
                        $dusers[$row['user_id']] = $lkey;
                        $departments[$lkey]['users'][] = $user;
                    }else{
                        //add $did
                        $_lkey = \Arr::get($ldepartments, $did);
                        $departments[$_lkey]['users'][] = $user;
                        $dusers[$row['user_id']] = $_lkey;
                    }
                }
            }
        }
        unset($departments[1]);
        ksort($departments);
        return $departments;
    }
    
    
    public function add($pid = NULL, array $data){
        return $this->tree->Insert($pid, $data);
    }
    
    public function parents($pid = NULL){
        return $this->tree->Parents($pid);
    }
    
    public function children($pid = NULL){
        return $this->tree->Branch($pid);
    }
    
    /**
    * Переместить из $id
    * after/before $node - id
    */
    public function move($id = NULL, $node = NULL, $postion = 'after'){
        if($postion == 'before'){
            $postion = 'before';
        }else{
            $postion = 'after'; 
        }
        $this->tree->ChangePositionAll($id, $node, $postion);
    }
    
    public function up($id = NULL){
        $node = $this->tree->GetNodePrev($id);
        if($node){
            $node_id = $this->tree->GetNode($id);
            $this->move($id, $node['id'], 'before');
            return true;
        }
        return false;
    }
    
    
    public function down($id = NULL){
        $node = $this->tree->GetNodeNext($id);
        if($node){
            $this->move($id, $node['id'], 'after');
            return true;
        }
        return false;
    }
    
    public function remove($id = NULL){
        $department = $this->getById($id);
        if(null !== $department){
            $this->tree->Delete($id);
        }else{
            return FALSE;
        }
    }
    
    public function upsert(array &$data)
    {
        $number = \Arr::get($data, 'number', null);
        $id = \Arr::get($data, 'id', null);
        if($number){
            $department = $this->getById($id);
            $move = $this->getByNumber($number);
            if(null != $move AND $id){
                if($move['id'] != $id){
                    //move department
                    if($move['level'] == $department['level']){
                        $position = ($number > $department['number'] ? 'after' : 'before');
                        $this->tree->ChangePositionAll($id, $move['id'], $position);
                    }else{
                        $parent = $this->tree->GetParent($move['id']);
                        $this->tree->MoveAll($id, $parent['id']);
                        $position = ($number > $department['number'] ? 'after' : 'before');
                        $this->tree->ChangePositionAll($id, $move['id'], $position);
                    }
                }
            }else{
                $path = explode('.',$number);
                array_pop($path);
                $parent = $this->getByNumber(implode('.',$path));
                if(null != $parent){
                    $this->tree->MoveAll($id, $parent['id']);   
                }
            }
        }
        $this->table->upsert($data);
        return $this->setAllNumber();
    }
    
    public function getByNumber($number = null){
       if(! $number){
            return null;
       }
       $query = $this->db->newStatement("
        SELECT 
            pd.*
        FROM pm_department pd
        WHERE pd.number = :number:
        ");
        $query->setVarChar('number', $number);
        return $query->getFirstRecord();  
    }
    
    public function setAllNumber(){
        $tree = $this->getTree();
        $prev_level = $parent_path = 0;
        $parent = [];
        
        $current_depth = 0;
        $node_depth = 0;
        $counter = 0;
        $path = 0;
        $pid = 0;
        $upcounter = 0;
        foreach ($tree as $lkey => $node) {
            $node_depth = $node['level'];
            if(! isset($parent[$node_depth])) $parent[$node_depth] = 0;
            $parent[$node_depth] = $parent[$node_depth]+1;
            
            if ($node_depth == $current_depth) {
            } elseif ($node_depth > $current_depth) {
                $current_depth = $current_depth + ($node_depth - $current_depth);
                $parent[$node_depth] = 1;
            } elseif ($node_depth < $current_depth) {
                $current_depth = $current_depth - ($current_depth - $node_depth);
            }
            
            $level = 1;
            $_path = []; 
            while($level <= $node_depth){
                $_path[] = $parent[$level];
                $level++;
            }
            $path = implode('.', $_path);
            if($node['number'] != $path){
                $_data = ['id' => $node['id'], 'number' => $path];
                $this->table->upsert($_data);
                $upcounter++;
            }
            ++$counter;
            
        }
        return $upcounter;
    }
    
    public function migrate(){
        return 1;
        $query = $this->db->newStatement("
            SELECT
                d.*
            FROM pm_department d
            WHERE d.lkey = 1
            LIMIT 1
        ");
        $root = $query->getFirstRecord();
        $query = $this->db->newStatement("
            SELECT
                d.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) chief
            FROM pm_department d
            LEFT OUTER JOIN user u ON u.id = d.chief_id
            WHERE d.id != :id:
            ORDER BY d.id ASC
        ");
        echo $root['id'];
        $query->setInteger('id',$root['id']);
        $departments = [];
        foreach($query->getAllRecords() as $department){
            $departments[] = $department;
        }
        $count = count($departments);
        $left = 1;
        $max_left = $count+1;
        $min_right = $max_left+1;
        $max_right = $min_right+$count;
        $right = $max_right;
        
        foreach($departments as $department){
            $right--;
            $left++;
            $data = ['id' => $department['id'],
                     'name' => $department['name'],
                     'lkey' => $left,
                     'rkey' => $right,
                     'level' => ($department['id'] == $root['id'] ? 0: 1),
                     ];
            $this->upsert($data);
            
        }
        $data = ['id' => $root['id'],
                'name' => $root['name'],
                'lkey' => 1,
                'rkey' => $max_right,
                'level' => 0
                ];
            $this->upsert($data);
    }
    
}//End Model Department