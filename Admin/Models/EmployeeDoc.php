<?php

namespace Modules\Employee\Admin\Models;

/*
* Данные пользователей пользователей
*
**/
class EmployeeDoc extends \Classes\Base\Model
{
    
    protected $table = 'employee_doc';
    
    protected $pk = 'user_id';

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `employee_doc` (
            `user_id` int(11) NOT NULL,
            `passport` tinyint(1) DEFAULT '0' NOT NULL,
            `bonus` tinyint(1) DEFAULT '0' NOT NULL,
            `contract` tinyint(1) DEFAULT '0' NOT NULL,
            `material` tinyint(1) DEFAULT '0' NOT NULL,
            `reglament` tinyint(1) DEFAULT '0' NOT NULL,
            `commercial` tinyint(1) DEFAULT '0' NOT NULL,
            `workbook` tinyint(1) DEFAULT '0' NOT NULL,
            PRIMARY KEY (`user_id`)
          ) ENGINE=Aria  DEFAULT CHARSET=utf8;
          DROP TABLE IF EXISTS `employee_doc`
          CREATE TABLE IF NOT EXISTS `employee_doc_map` (
            `id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `field` varchar(255) NOT NULL,
            `notrequired` tinyint(1) DEFAULT '0' NOT NULL,
            PRIMARY KEY (`id`)
            UNIQUE  `user_id` (  `user_id` ,  `field` )
          ) ENGINE=Aria  DEFAULT CHARSET=utf8;
          ";
    }
    
    public function getDocs()
    {
        return ['passport' => 'Ксерокопия паспорта',
                'bonus' => 'Депремирования',
                'contract' => 'Трудовой договор',
                'material' => 'Материальная ответственность',
                'reglament' => 'Правила работы',
                'commercial' => 'Коммерческая тайна',
                'workbook' => 'Трудовая книжка',
        ];
    }
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                ed.*,
                ed.user_id as id,
                u.role_id as role_id,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_doc ed
            LEFT JOIN user u ON ed.user_id=u.id
            WHERE ed.user_id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function progress($doc = null, $map = null) 
    {
        $docs = $this->getDocs();
        $all = count($docs);
        $required = 0;
        $count = 0;
        if(null !== $doc){
            foreach($docs as $field => $name){
                $isrequired = 1;
                if(isset($map[$field])){
                    if('1' == $map[$field]){
                        $isrequired = false;
                    }
                }
                if($isrequired){
                    $required++;
                }
                
                if(\Arr::get($doc, $field, null)){
                   $count++;
                }
            }
        }
        if($required > 0){
            $percent = round($count*100/$required); 
        }else{
            $percent = round($count*100/$all);
        }
        if($percent > 100) $percent = 100;
        return ['count' => $count, 'all' => $all, 'required' => $required,'percent' => $percent];
    }
    
    public function getByList(array $filter = [], $order_by = 'ed.user_id')
    {
        $users = $params = $criteria = [];
        $criteria['owner_id'] = "u.owner_id = :owner_id:";
        $params['owner_id'] = OWNER_ID;
        foreach($filter as $key => $value){
            if(! is_array($value)){
                $criteria[$key] = "ed.".$key." = :".$key.":";
            }else{
                $criteria[$key] = "ed.".$key." IN (:".$key.":)";
            }
            $params[$key] = $value;
        }
        
                
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ed.*,
                u.id,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_doc ed
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

    

}