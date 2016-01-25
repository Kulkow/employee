<?php

namespace Modules\Employee\Admin\Models;

/*
* Данные пользователей пользователей
*
**/
class EmployeeContactMap extends \Classes\Base\Model
{
    
    protected $table = 'employee_contact_map';
    
    protected $pk = 'id';

    protected function init_table(){
        return "
          CREATE TABLE IF NOT EXISTS `employee_contact_map` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `field` varchar(255) NOT NULL,
            `notrequired` tinyint(1) DEFAULT '0' NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE  user_id (  `user_id` ,  `field` )
          ) ENGINE=Aria  DEFAULT CHARSET=utf8;
          ";
    }
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                ecm.*,
                ecm.user_id as id,
                u.role_id as role_id,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_contact_map ecm
            LEFT JOIN user u ON ecm.user_id=u.id
            WHERE ecm.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function getByUserId($id)
    {
        return $this->getByList(['user_id' => $id]);
    }
    
    public function getMapUser($list)
    {
        $map = [];
        if(null !== $list){
            foreach($list as $item)
            if(! empty($item['field'])){
                $map[$item['field']] = $item['notrequired'];
            }
        }
        return $map;
    }
    
    
    public function getByList(array $filter = [], $order_by = 'ecm.user_id')
    {
        $users = $params = $criteria = [];
        $criteria['owner_id'] = "u.owner_id = :owner_id:";
        $params['owner_id'] = OWNER_ID;
        foreach($filter as $key => $value){
            if(! is_array($value)){
                $criteria[$key] = "ecm.".$key." = :".$key.":";
            }else{
                $criteria[$key] = "ecm.".$key." IN (:".$key.":)";
            }
            $params[$key] = $value;
        }
                
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ecm.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_contact_map ecm
            LEFT JOIN user u ON ecm.user_id=u.id
            {$where}
            ORDER BY {$order_by}
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }

    

}