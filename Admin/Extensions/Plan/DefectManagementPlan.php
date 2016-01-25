<?php

namespace Modules\Employee\Admin\Extensions\Plan;

class DefectManagementPlan extends Indicator {
    
    public function getValues() {
        parent::getValues();
        
        $date = $this->start;
        $results = array();
        
        while ($date <= $this->end) {
            $query = $this->conn->newStatement("
                SELECT SUM(IF(d.position_id,op.purchase_price* op.quantity,d.price_purchase*d.quantity)) cnt, u.id user_id, :t:  date
                FROM defect d
                LEFT OUTER JOIN order_position op ON op.id=d.position_id
                LEFT OUTER JOIN `order` o ON o.id=op.order_id
                LEFT OUTER JOIN product p ON p.id=d.product_id
                LEFT OUTER JOIN user u ON u.id IN (:uid".implode(':,:uid',array_keys($this->users)).":) 
                WHERE (d.position_id AND o.owner_id=:oid: OR !d.position_id AND p.owner_id=:oid:) AND DATE_FORMAT(d.date_creation,'%Y-%m')<=:t: AND (
                    DATE_FORMAT(d.product_solved,'%Y-%m')>:t: OR d.product_solved IS NULL
                    OR d.supposed_compensation AND (DATE_FORMAT(d.supplier_solved,'%Y-%m')>:t: OR d.supplier_solved IS NULL)
                    OR d.position_id AND (DATE_FORMAT(d.client_solved,'%Y-%m')>:t: OR d.client_solved IS NULL)
                )
                GROUP BY u.id, :t:                 
            ");
            $query->setVarChar('t',$date);
            $query->setInteger('oid',OWNER_ID);
            foreach ($this->users as $key=>$curr) {
                $query->setInteger('uid'.$key, $curr);
            }
            $results = array_merge($results,$query->getAllRecords());
            $date = date('Y-m',mktime(0, 0, 0, date('m', strtotime($date.'-01'))+1, date("01"), date('Y', strtotime($date.'-01'))));
        }
        return $this->processResult($results);
    }
    
    
    public function calculate() {
        if(empty($this->ids)){
            return FALSE;
        }
        $date = $this->start;
        $results = array();
        
        while ($date <= $this->end) {
            $query = $this->db->newStatement("
                SELECT SUM(IF(d.position_id,op.purchase_price* op.quantity,d.price_purchase*d.quantity)) cnt, u.id user_id, :t:  date
                FROM defect d
                LEFT OUTER JOIN order_position op ON op.id=d.position_id
                LEFT OUTER JOIN `order` o ON o.id=op.order_id
                LEFT OUTER JOIN product p ON p.id=d.product_id
                LEFT OUTER JOIN user u ON u.id IN (:uids:) 
                WHERE (d.position_id AND o.owner_id=:oid: OR !d.position_id AND p.owner_id=:oid:) AND DATE_FORMAT(d.date_creation,'%Y-%m')<=:t: AND (
                    DATE_FORMAT(d.product_solved,'%Y-%m')>:t: OR d.product_solved IS NULL
                    OR d.supposed_compensation AND (DATE_FORMAT(d.supplier_solved,'%Y-%m')>:t: OR d.supplier_solved IS NULL)
                    OR d.position_id AND (DATE_FORMAT(d.client_solved,'%Y-%m')>:t: OR d.client_solved IS NULL)
                )
                GROUP BY u.id, :t:                 
            ");
            $query->setVarChar('t',$date);
            $query->setInteger('oid',OWNER_ID);
            $query->setArray('uids',$this->ids);
            $results = array_merge($results,$query->getAllRecords());
            $date = date('Y-m',mktime(0, 0, 0, date('m', strtotime($date.'-01'))+1, date("01"), date('Y', strtotime($date.'-01'))));
        }
        return $this->PreSetCalculate($results);
    }
    
}