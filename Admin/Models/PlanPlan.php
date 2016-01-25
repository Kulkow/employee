<?php

namespace Modules\Employee\Admin\Models;

use Modules\Employee\Admin\Models\Employee;

/*
* Связи планов между собой
*
**/
class PlanPlan extends \Classes\Base\Model
{
    
    protected $table = 'plan_plan';

    protected function init_table(){
        return "CREATE TABLE IF NOT EXISTS `plan_plan` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `plan_id` int(11) NOT NULL,
            `plan_pid` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `plan_id` (`plan_id`),
            KEY `plan_pid` (`plan_pid`)
          ) ENGINE=Aria  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
    }
    
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                pp.*,
                p.name as plan,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common
            FROM plan_plan pp
            LEFT OUTER JOIN plan p ON p.id = pp.plan_id
            WHERE pp.id = :id:
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    /**
    * Список плановых показателей подразделения
    * plan_id => personal
    * plan-pid => common
    **/
    public function getByPlansId($plan_id)
    {
        $query = $this->db->newStatement("
            SELECT
                pp.*,
                p.name as plan,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common
            FROM plan_plan pp
            LEFT OUTER JOIN plan p ON p.id = pp.plan_pid
            WHERE pp.plan_id = :plan_id:
            ORDER BY p.name
        ");
        $query->setInteger('plan_id', $plan_id);
        $plans = [];
        foreach($query->getAllRecords() as $plan){
            $plans[$plan["plan_pid"]] = $plan;
        }
        return $plans;
    }
    
    
    public function getByList(array $filter)
    {
        $criteria = [];
        $params = [];
        if(! empty($filter['plan_id']) && count($filter['plan_id'])){
            $criteria['plan_id'] = "pp.plan_id IN (:plan_id:)";
            $params['plan_id'] = $filter['plan_id'];
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                pp.*,
                p.name as plan,
                p.measurement,
                p.is_negative,
                p.is_plan_based,
                p.alias,
                p.is_discrete,
                p.is_common
            FROM plan_plan pp
            LEFT OUTER JOIN plan p ON p.id = pp.plan_id
            {$where}
            ORDER BY pp.plan_id,p.name
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    /**
    * $is_common - для общих планов
    **/
    public function getByMap($is_common = true)
    {
        $map = [];
        foreach($this->getByList([]) as $pplan){
            $personal_id = \Arr::get($pplan, 'plan_id');
            $common_id = \Arr::get($pplan, 'plan_pid');
            if(! $is_common){
                if(! isset($map[$personal_id])){
                    $map[$personal_id] = [];
                }
                $map[$personal_id][$common_id] = $pplan;
            }else{
                if(! isset($map[$common_id])){
                    $map[$common_id] = [];
                }
                $map[$common_id][$personal_id] = $pplan;
            }
            
        }
        return $map;
    }
    
}