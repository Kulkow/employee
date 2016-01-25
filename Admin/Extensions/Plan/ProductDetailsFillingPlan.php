<?php

namespace Modules\Employee\Admin\Extensions\Plan;

class ProductDetailsFillingPlan extends Indicator {
    
    public function getValues() {
        parent::getValues();
        $query = $this->conn->newStatement("
            SELECT COUNT(id) cnt, user_id, DATE_FORMAT(date,'%Y-%m')  date
            FROM log_detail 
            WHERE DATE_FORMAT(date,'%Y-%m') >=:f: AND DATE_FORMAT(date,'%Y-%m') <=:t: AND user_id IN (:uid".implode(':,:uid',array_keys($this->users)).":) 
            GROUP BY user_id, DATE_FORMAT(date,'%Y-%m') 
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
            SELECT COUNT(id) cnt, user_id, DATE_FORMAT(date,'%Y-%m')  date
            FROM log_detail 
            WHERE DATE_FORMAT(date,'%Y-%m') >=:f: AND DATE_FORMAT(date,'%Y-%m') <=:t: AND user_id IN (:uids:) 
            GROUP BY user_id, DATE_FORMAT(date,'%Y-%m') 
        ");
        $query->setVarChar('f',$this->start);
        $query->setVarChar('t',$this->end);
        $query->setArray('uids',$this->ids);
        return $this->PreSetCalculate($query->getAllRecords());
    }
    
}