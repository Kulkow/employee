<?php

namespace Modules\Employee\Admin\Extensions\Plan;

class TotalSaranskShopOrdersCreationPlan extends Indicator {
    
    public function getValues() {
        parent::getValues();
        
        $query = $this->conn->newStatement("
            SELECT ROUND(SUM(a.profit*0.1)) cnt, DATE_FORMAT(a.status_date,'%Y-%m') date, u.id user_id
            FROM (
                (SELECT op1.manager_id, op1.creator_id, o1.owner_id, op1.status_date, op1.warehouse_id,
                    op1.quantity*(op1.price-op1.purchase_price-op1.transport_charges)*IF(o1.user_id=3199,0.5,1) profit, op1.operator_status_id
                FROM order_position op1
                INNER JOIN `order` o1 ON o1.id=op1.order_id
                WHERE op1.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED'))
                UNION ALL
                (SELECT op2.manager_id manager_id, IF(pctp.manager_id=0,op2.creator_id,pctp.manager_id) creator_id, o2.owner_id, op2.status_date, op2.warehouse_id,
                    pctp.amount profit, op2.operator_status_id
                FROM order_position op2
                INNER JOIN price_change_to_position pctp ON pctp.order_position_id=op2.id AND !pctp.deactivated AND !op2.client_price
                INNER JOIN price_change_type pct ON pctp.change_type_id=pct.id
                INNER JOIN `order` o2 ON o2.id=op2.order_id
                WHERE op2.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED'))
            ) a
            LEFT OUTER JOIN user u ON u.id IN (:uid".implode(':,:uid',array_keys($this->users)).":)
            WHERE a.owner_id=:oid: AND DATE_FORMAT(a.status_date,'%Y-%m')>=:f:
            AND DATE_FORMAT(a.status_date,'%Y-%m')<=:t: AND (
                a.creator_id IN (9031,12845)
            )
            GROUP BY u.id, DATE_FORMAT(a.status_date,'%Y-%m')
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
            SELECT ROUND(SUM(a.profit*0.1)) cnt, DATE_FORMAT(a.status_date,'%Y-%m') date, u.id user_id
            FROM (
                (SELECT op1.manager_id, op1.creator_id, o1.owner_id, op1.status_date, op1.warehouse_id,
                    op1.quantity*(op1.price-op1.purchase_price-op1.transport_charges)*IF(o1.user_id=3199,0.5,1) profit, op1.operator_status_id
                FROM order_position op1
                INNER JOIN `order` o1 ON o1.id=op1.order_id
                WHERE op1.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED'))
                UNION ALL
                (SELECT op2.manager_id manager_id, IF(pctp.manager_id=0,op2.creator_id,pctp.manager_id) creator_id, o2.owner_id, op2.status_date, op2.warehouse_id,
                    pctp.amount profit, op2.operator_status_id
                FROM order_position op2
                INNER JOIN price_change_to_position pctp ON pctp.order_position_id=op2.id AND !pctp.deactivated AND !op2.client_price
                INNER JOIN price_change_type pct ON pctp.change_type_id=pct.id
                INNER JOIN `order` o2 ON o2.id=op2.order_id
                WHERE op2.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED'))
            ) a
            LEFT OUTER JOIN user u ON u.id IN (:uids:)
            WHERE a.owner_id=:oid: AND DATE_FORMAT(a.status_date,'%Y-%m')>=:f:
            AND DATE_FORMAT(a.status_date,'%Y-%m')<=:t: AND (
                a.creator_id IN (9031,12845)
            )
            GROUP BY u.id, DATE_FORMAT(a.status_date,'%Y-%m')
        ");
        $query->setInteger('oid', OWNER_ID);
        $query->setVarChar('f',$this->start);
        $query->setVarChar('t',$this->end);
        $query->setArray('uids',$this->ids);
        return $this->PreSetCalculate($query->getAllRecords());
    }
    
}