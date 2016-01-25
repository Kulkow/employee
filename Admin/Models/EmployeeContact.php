<?php

namespace Modules\Employee\Admin\Models;

/*
* Данные пользователей пользователей
*
**/
class EmployeeContact extends \Classes\Base\Model
{
    
    protected $table = 'employee_contact';
    
    protected $pk = 'user_id';

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `employee_contact` (
            `user_id` int(11) NOT NULL,
            `phone` varchar(255) NOT NULL,
            `additional_phones` varchar(255) NOT NULL,
            `address` varchar(255) NOT NULL,
            `skype` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `mother_name` varchar(255) NOT NULL,
            `mother_phone` varchar(255) NOT NULL,
            `mother_address` varchar(255) NOT NULL,
            `father_name` varchar(255) NOT NULL,
            `father_phone` varchar(255) NOT NULL,
            `father_address` varchar(255) NOT NULL,
            `relative_name` varchar(255) NOT NULL,
            `relative_phone` varchar(255) NOT NULL,
            `relative_address` varchar(255) NOT NULL,
            `friend_name` varchar(255) NOT NULL,
            `friend_phone` varchar(255) NOT NULL,
            `friend_address` varchar(255) NOT NULL,
             PRIMARY KEY (`user_id`)
          ) ENGINE=Aria  DEFAULT CHARSET=utf8;
          ";
    }

    public function getContacts()
    {
        return ['phone' => ['group' => 'user', 'message' => 'Телефон'],
                //'additional_phones' => ['group' => 'user', 'message' => 'Телефоны родственников'],
                'address' => ['group' => 'user', 'message' =>'Адрес'],
                'skype' => ['group' => 'user', 'message' => 'Skype'],
                'email' => ['group' => 'user', 'message' => 'E-mail' ],
                
                'mother_name' => ['group' => 'mother', 'message' => 'Мать'],
                'mother_phone' => ['group' => 'mother', 'message' => 'Телефон матери'],
                'mother_address' => ['group' => 'mother', 'message' => 'Адрес матери'],
                
                'father_name' => ['group' => 'father', 'message' => 'Отец'],
                'father_phone' => ['group' => 'father', 'message' => 'Телефон отца'],
                'father_address' => ['group' => 'father', 'message' => 'Адрес отца'],
                
                'relative_name' => ['group' => 'relative', 'message' => 'Родственник'],
                'relative_phone' => ['group' => 'relative', 'message' => 'Телефон родственника'],
                'relative_address' => ['group' => 'relative', 'message' => 'Адрес родственника'],
                
                'friend_name' => ['group' => 'friend', 'message' => 'Друг'],
                'friend_phone' => ['group' => 'friend', 'message' => 'Телефон друга'],
                'friend_address' => ['group' => 'friend', 'message' => 'Адрес друга'],
        ];
    }
    
    public function getGroupContacts()
    {
        $contacts = $this->getContacts();
        $groups = [];
        $_groups = ['user' => 'Личные данные',
                   'mother' => 'Данные матери',
                   'father' => 'Данные отца',
                   'relative' => 'Данные родственника',
                   'friend' => 'Данные друга',
                   ];
        foreach($contacts as $field => $contact){
            $group = \Arr::get($contact, 'group', 'user');
            if(! isset($groups[$group])) $groups[$group] = ['name' => $_groups[$group], 'contacts' => []];
            $groups[$group]['contacts'][$field] = $contact;
        }
        return $groups;
    }
    
    public function getById($id)
    {
        $keys = array_keys($this->getContacts());
        array_walk($keys, function(&$key){
           $key = "ec.".$key;
        });
        $fields = implode(', ',$keys);
        $query = $this->db->newStatement("
            SELECT
                ec.*,
                u.role_id as role_id,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_contact as ec
            LEFT JOIN user u ON ec.user_id=u.id
            WHERE ec.user_id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function progress($item = null, $map = null) 
    {
        $contacts = $this->getContacts();
        $all = count($contacts);
        $required = 0;
        $count = 0;
        if(null !== $item){
            foreach($item as $field => $name){
                if(\Arr::get($contacts, $field,null)){
                    $isrequired = 1;
                    if(isset($map[$field])){
                        if('1' == $map[$field]){
                            $isrequired = false;
                        }
                    }
                    if($isrequired){
                        $required++;
                    }
                    
                    if(\Arr::get($item, $field, null)){
                       $count++;
                    }
                }
            }
        }
        if($required > 0){
            $percent = round($count*100/$required);
            if($percent > 100) $percent = 100;
        }else{
            $percent = round($count*100/$all);
        }
        return ['count' => $count, 'all' => $all, 'required' => $required, 'percent' => $percent];
    }
    
    public function getByList(array $filter = [], $order_by = 'ec.user_id')
    {
        $users = $params = $criteria = [];
        $criteria['owner_id'] = "u.owner_id = :owner_id:";
        $params['owner_id'] = OWNER_ID;
        foreach($filter as $key => $value){
            if(! is_array($value)){
                $criteria[$key] = "ec.".$key." = :".$key.":";
            }else{
                $criteria[$key] = "ec.".$key." IN (:".$key.":)";
            }
            $params[$key] = $value;
        }
        
                
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                ec.*,
                u.id,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM employee_contact ec
            LEFT JOIN user u ON ec.user_id=u.id
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