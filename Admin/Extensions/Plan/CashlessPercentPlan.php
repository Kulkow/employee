<?php

namespace Modules\Employee\Admin\Extensions\Plan;

class CashlessPercentPlan extends Indicator {
    
    public function getValues() {
        parent::getValues();
        
        $query = $this->db->newStatement("
            SELECT ROUND(0.02*SUM(op.price*op.quantity*IF(o.user_id=3199,0.5,1))) cnt, DATE_FORMAT(op.status_date,'%Y-%m') date, u.id user_id
            FROM order_position op
            INNER JOIN `order` o ON o.id=op.order_id
            INNER JOIN user u ON u.id IN (:uid".implode(':,:uid',array_keys($this->users)).":) 
            WHERE o.owner_id=:oid: AND DATE_FORMAT(op.status_date,'%Y-%m')>=:f: 
            AND op.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED')
            AND DATE_FORMAT(op.status_date,'%Y-%m')<=:t: AND o.payment_type_id=3
            GROUP BY u.id, DATE_FORMAT(op.status_date,'%Y-%m')          
             
        ");
        $query->setInteger('oid', OWNER_ID);
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
            SELECT ROUND(0.02*SUM(op.price*op.quantity*IF(o.user_id=3199,0.5,1))) cnt, DATE_FORMAT(op.status_date,'%Y-%m') date, u.id user_id
            FROM order_position op
            INNER JOIN `order` o ON o.id=op.order_id
            INNER JOIN user u ON u.id IN (:uids:) 
            WHERE o.owner_id=:oid: AND DATE_FORMAT(op.status_date,'%Y-%m')>=:f: 
            AND op.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED')
            AND DATE_FORMAT(op.status_date,'%Y-%m')<=:t: AND o.payment_type_id=3
            GROUP BY u.id, DATE_FORMAT(op.status_date,'%Y-%m')          
             
        ");
        $query->setInteger('oid', OWNER_ID);
        $query->setVarChar('f',$this->start);
        $query->setVarChar('t',$this->end);
        $query->setArray('uids',$this->ids);
        return $this->PreSetCalculate($query->getAllRecords());
    }
    
}