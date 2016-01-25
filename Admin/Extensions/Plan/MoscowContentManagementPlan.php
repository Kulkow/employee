<?php

namespace Modules\Employee\Admin\Extensions\Plan;

class MoscowContentManagementPlan extends Indicator {
    
    public function getValues() {
        parent::getValues();
        $query = $this->conn->newStatement("
            SELECT SUM(t.daily) cnt, employee_id user_id, DATE_FORMAT(date,'%Y-%m')  date FROM (
                SELECT ROUND(IF(SUM(TIME_TO_SEC(finish)-TIME_TO_SEC(start))>4,14400,SUM(TIME_TO_SEC(finish)-TIME_TO_SEC(start)))/3600) daily, employee_id, DATE_FORMAT(start,'%Y-%m-%d') date 
                FROM timesheet 
                WHERE DATE_FORMAT(start,'%Y-%m')>=:f: AND DATE_FORMAT(start,'%Y-%m')<=:t: AND employee_id IN (:uid".implode(':,:uid',array_keys($this->users)).":) AND DATE_FORMAT(start,'%Y-%m-%d')=DATE_FORMAT(finish,'%Y-%m-%d')
                GROUP BY employee_id, DATE_FORMAT(start,'%Y-%m-%d')
            ) t
            GROUP BY employee_id, DATE_FORMAT(date,'%Y-%m') 
        ");
        $query->setVarChar('f',$this->start);
        $query->setVarChar('t',$this->end);
        foreach ($this->users as $key=>$curr) {
            $query->setInteger('uid'.$key, $curr);
        }
        return $this->processResult($query->getAllRecords());
    }
    
    public function calculate() {
        if(empty($this->ids)){
            return FALSE;
        }
        $query = $this->db->newStatement("
            SELECT SUM(t.daily) cnt, employee_id user_id, DATE_FORMAT(date,'%Y-%m')  date FROM (
                SELECT ROUND(IF(SUM(TIME_TO_SEC(finish)-TIME_TO_SEC(start))>4,14400,SUM(TIME_TO_SEC(finish)-TIME_TO_SEC(start)))/3600) daily, employee_id, DATE_FORMAT(start,'%Y-%m-%d') date 
                FROM timesheet 
                WHERE DATE_FORMAT(start,'%Y-%m')>=:f: AND DATE_FORMAT(start,'%Y-%m')<=:t: AND employee_id IN (:uids:) AND DATE_FORMAT(start,'%Y-%m-%d')=DATE_FORMAT(finish,'%Y-%m-%d')
                GROUP BY employee_id, DATE_FORMAT(start,'%Y-%m-%d')
            ) t
            GROUP BY employee_id, DATE_FORMAT(date,'%Y-%m') 
        ");
        $query->setVarChar('f',$this->start);
        $query->setVarChar('t',$this->end);
        $query->setArray('uids',$this->ids);
        return $this->PreSetCalculate($query->getAllRecords());
    }
    
}