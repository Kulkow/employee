<?php

namespace Modules\Employee\Admin\Models;

/*
* Планы пользователей
*
**/
class Plan extends \Classes\Base\Model
{
    
    protected $table = 'plan';
    

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `plan` (
            `id` tinyint(4) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `measurement` varchar(30) DEFAULT NULL,
            `is_negative` tinyint(1) DEFAULT '0',
            `is_plan_based` tinyint(1) DEFAULT '1',
            `alias` varchar(100) DEFAULT NULL,
            `is_discrete` tinyint(1) DEFAULT '0', // За единицу для не плановых
            `is_common` tinyint(1) DEFAULT '0', // обший для подразделения
            PRIMARY KEY (`id`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
          ALTER TABLE `plan` ADD `pid` INT( 11 ) NOT NULL AFTER `id` 
          ";
    }
    
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                p.*
            FROM plan p
            WHERE id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function getByList(array $filter = [])
    {
        $plans = [];
        $params = [];
        $criteria = [];
        foreach($filter as $key => $value){
            if(! is_array($value)){
                $criteria[$key] = "p.".$key." = :".$key.":";
            }else{
                $criteria[$key] = "p.".$key." IN (:".$key.":)";
            }
            $params[$key] = $value;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                p.*
            FROM plan p
            {$where}
            ORDER BY p.is_plan_based desc,p.name
        ");
        $query->bind($params);
        foreach($query->getAllRecords() as $plan){
            $plans[$plan['id']] = $plan;
        }
        return $plans;
    }
    
    public function getByType()
    {
        $plans = [];
        foreach($this->getByList(['is_common' => 1]) as $plan){
            $key = (! empty($plan['department_id']) ? $plan['department_id'].'_' : '').(! empty($plan['plan_id']) ? $plan['plan_id'] : $plan['id']);
            $plans[$key] =  $plan['id'];
        }
        return $plans;
    }
    
    
    /**
    * Список плановых показателей пользователя
    * Существующих или несуществующих у пользователя
    **/
    public function getByUser($id = NULL,$exists = FALSE)
    {
        $query = $this->db->newStatement("
            SELECT p . *
            FROM plan p
            WHERE p.id NOT
            IN (
            SELECT plan_id
            FROM employee_plan
            WHERE user_id = :id:
            ) 
            ORDER BY p.id
        ");
        $query->setInteger('id', $id);
        return $query->getAllRecords();
    }
    
    public static function adapterInput($value, $plan, $adapter){
        $value = str_replace(',','.',$value);
        if(null !== $plan){
            if(0 < $plan['is_plan_based']){
                //$value = rtrim($value, 0);
            }else{
                if(0 < $plan['is_discrete']){
                    $value = $adapter->input($value);
                }else{
                    $value = $value/100;
                }
            }
        }
        return $value;
    }
    
    public static function adapterOut($value, $plan, $adapter){
        $value = str_replace(',','.',$value);
        if(null !== $plan){
            if(0 < $plan['is_plan_based']){
                $value = rtrim($value, 0);
                $value = rtrim($value, '.');
            }else{
                if(0 < $plan['is_discrete']){
                    $value = $adapter->output($value);
                }else{
                    $value = $value*100;
                }
            }
        }
        return $value;
    }
    
    public function getByGroups()
    {
        $groups = [
                  'isplans' => ['name' => 'Плановые личные', 'items' => [],
                                ],
                  'isplancommons' => ['name' => 'Плановые общие', 'items' => [],
                                ],
                  'piecework' => ['name' => 'Сдельные', 'items' => [],
                                ],
                ];
        foreach($this->getByList() as $plan){
            $key = 'isplans';
            if(0 < $plan['is_plan_based']){
                if(0 < $plan['is_common']){
                    $key = 'isplancommons';
                }else{
                    $key = 'isplans';
                }
            }else{
                $key = 'piecework';
            }
            $groups[$key]['items'][] = $plan;
        }
        return $groups;
    }
    
    
    public function getHelp($id)
    {
        $plan = $this->getById($id);
        if(null == $plan){
            return null;
        }
        if(0 < $plan['is_plan_based']){
            return ['label' => 'Процент влияния', 'prefix' =>  '%', 'is_common' => $plan['is_common']];
        }else{
            if(0 < $plan['is_discrete']){
                return ['label' => 'рублей', 'prefix' =>  'за шт', 'is_common' => $plan['is_common']];
            }else{
                return ['label' => 'Процент', 'prefix' =>  '%', 'is_common' => $plan['is_common']];
            }
        }
        //if()
    }
}