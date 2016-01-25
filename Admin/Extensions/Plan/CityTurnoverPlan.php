<?php

namespace Modules\Employee\Admin\Extensions\Plan;

class CityTurnoverPlan extends Indicator {
    
     public function calculate() {
        if(empty($this->ids)){
            return FALSE;
        }
        
        $selectWrapper = "
            ROUND(retailAmount) cnt
            ";                
        $select = "
            pd.id department_id, DATE_FORMAT(b.datetime,'%Y-%m') date
            ";
        $from = "
            INNER JOIN representative r ON r.city_id=o.city_id
            INNER JOIN pm_department pd ON r.id=pd.point_id
            "; 
        $clause = "
            pd.id IN (:department_id:)
            ";
        $params = [
            'since' => $this->start_day.' 00:00:00', // date('Y-m-d 00:00:00', strtotime('first day of this month', strtotime($this->start.'-01'))), 
            'till' => $this->end_day.' 23:59:59', //date('Y-m-d 23:59:59', strtotime('last day of this month', strtotime($this->start.'-01'))),
            'department_id' => $this->departments
            ];
        $group = "pd.id, DATE_FORMAT(b.datetime,'%Y-%m')";
/*
        return [15 => ['cnt' => 159900, 'date' => '2015-12', 'department_id' => 15],
                23 => ['cnt' => 157900, 'date' => '2015-12', 'department_id' => 23],
                24 => ['cnt' => 1349900, 'date' => '2015-12', 'department_id' => 24]
                ];*/
        return $this->PreSetCalculate(\Model::admin('Balance')->buildOrderStatistics(compact("selectWrapper", "select", "from", "clause", "params", "group")));
    }
    
}