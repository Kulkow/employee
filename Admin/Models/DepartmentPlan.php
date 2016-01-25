<?php

namespace Modules\Employee\Admin\Models;

use Modules\Employee\Admin\Models\Employee;

/*
* Текущие планы поздразделений
*
**/
class DepartmentPlan extends \Classes\Base\Model
{
    
    protected $table = 'employee_department_plan';

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `employee_department_plan` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `department_id` int(11) NOT NULL,
            `plan_id` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `plan_id` (`plan_id`),
            KEY `department_id` (`department_id`)
          ) ENGINE=Aria  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
    }
    
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                edp.*,
                p.name as plan,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common
            FROM employee_department_plan edp
            LEFT OUTER JOIN plan p ON p.id = edp.plan_id
            WHERE edp.id = :id:
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    /**
    * Список плановых показателей подразделения
    **/
    public function getByDeparmentId($departmentid)
    {
        $query = $this->db->newStatement("
            SELECT
                edp.id,
                ed.id department_id,
                p.id as plan_id,
                p.name as name,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common
            FROM employee_department_plan edp
            LEFT OUTER JOIN pm_department ed ON ed.id = edp.department_id
            LEFT OUTER JOIN plan p ON p.id = edp.plan_id
            WHERE ed.id = :departmentid:
            ORDER BY p.name
        ");
        $query->setInteger('departmentid', $departmentid);
        $plans = [];
        foreach($query->getAllRecords() as $plan){
            $plans[$plan["plan_id"]] = $plan;
        }
        return $plans;
    }
    
    public function getByList(array $filter)
    {
        $criteria = [];
        $params = [];
        if(! empty($filter['departments']) && count($filter['departments'])){
            $criteria['departments'] = "ed.id IN (:departments:)";
            $params['departments'] = $filter['departments'];
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                edp.*,
                ed.name as department,
                ed.lkey as department_lkey,
                ed.number as department_number,
                ed.level as department_level,
                p.name,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.pid,
                p.alias,
                p.is_discrete,
                p.is_common
            FROM employee_department_plan edp
            LEFT OUTER JOIN pm_department ed ON ed.id = edp.department_id
            LEFT OUTER JOIN plan p ON p.id = edp.plan_id
            {$where}
            ORDER BY ed.id, p.is_plan_based desc,p.is_common desc
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    /**
    * Ключи
    **/
    public function getByMap($filter = [])
    {
        $criteria = [];
        $params = [];
        if(! empty($filter['departments']) && count($filter['departments'])){
            $criteria['departments'] = "edp.department_id IN (:departments:)";
            $params['departments'] = $filter['departments'];
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                edp.*,
                p.name,
                p.is_common,
                p.is_plan_based
            FROM employee_department_plan edp
            LEFT OUTER JOIN plan p ON p.id = edp.plan_id
            {$where}
            ORDER BY edp.department_id
        ");
        $query->bind($params);
        $maps = [];
        foreach($query->getAllRecords() as $row){
            if(! isset($maps[$row['department_id']])){
                $maps[$row['department_id']] = [];
            }
            $maps[$row['department_id']][$row['plan_id']] = $row;
        }
        return $maps;
    }
    
}