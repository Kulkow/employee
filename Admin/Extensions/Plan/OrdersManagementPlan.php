<?php

namespace Modules\Employee\Admin\Extensions\Plan;

class OrdersManagementPlan extends Indicator {
    
    public function getValues() {
        parent::getValues();
        
        $query = $this->conn->newStatement(/*"
            SELECT ROUND(SUM(a.profit*0.1)) cnt, DATE_FORMAT(a.status_date,'%Y-%m') date, a.manager_id user_id
            FROM (
                (SELECT 
                    op.manager_id, 
                    op.creator_id, 
                    o.owner_id, 
                    ls.date status_date, 
                    op.warehouse_id,
                    IF(os.code='IS_TRANSFERED',1,-1)*op.quantity*(op.price-op.purchase_price-op.transport_charges) profit
                FROM order_position op
                INNER JOIN `order` o ON o.id=op.order_id
                INNER JOIN log_status ls ON ls.order_position_id=op.id
                INNER JOIN order_status os ON os.id=ls.status_id
                LEFT OUTER JOIN log_status ls2 ON ls2.order_position_id=op.id AND (ls2.date<ls.date OR ls2.date=ls.date AND ls2.id<ls.id)
                LEFT OUTER JOIN log_status ls3 ON ls3.order_position_id=op.id AND (ls2.date<ls3.date OR ls2.date=ls3.date AND ls2.id<ls3.id) AND (ls3.date<ls.date OR ls3.date=ls.date AND ls3.id<ls.id)
                WHERE (os.code IN ('IS_TRANSFERED','IS_RETURNED') AND ls2.status_id!=ls.status_id OR os.code = 'IS_READY' AND ls2.status_id IN (SELECT id FROM order_status WHERE code='IS_TRANSFERED')) AND ls3.id IS NULL)
                UNION ALL
                (SELECT 
                    op.manager_id, 
                    IF(pctp.manager_id=0,op.creator_id,pctp.manager_id) creator_id, 
                    o.owner_id, 
                    ls.date status_date, 
                    op.warehouse_id,
                    IF(!op.client_price,IF(os.code='IS_TRANSFERED',pctp.amount,-pctp.amount),0) profit
                FROM order_position op
                INNER JOIN price_change_to_position pctp ON pctp.order_position_id=op.id AND !pctp.deactivated AND !op.client_price
                INNER JOIN price_change_type pct ON pctp.change_type_id=pct.id
                INNER JOIN `order` o  ON o.id=op.order_id
                INNER JOIN log_status ls ON ls.order_position_id=op.id
                INNER JOIN order_status os ON os.id=ls.status_id
                LEFT OUTER JOIN log_status ls2 ON ls2.order_position_id=op.id AND (ls2.date<ls.date OR ls2.date=ls.date AND ls2.id<ls.id)
                LEFT OUTER JOIN log_status ls3 ON ls3.order_position_id=op.id AND (ls2.date<ls3.date OR ls2.date=ls3.date AND ls2.id<ls3.id) AND (ls3.date<ls.date OR ls3.date=ls.date AND ls3.id<ls.id)
                WHERE (os.code IN ('IS_TRANSFERED','IS_RETURNED') AND ls2.status_id!=ls.status_id OR os.code = 'IS_READY' AND ls2.status_id IN (SELECT id FROM order_status WHERE code='IS_TRANSFERED')) AND ls3.id IS NULL)
            ) a
            WHERE a.owner_id=:oid: AND DATE_FORMAT(a.status_date,'%Y-%m')>=:f:
            AND DATE_FORMAT(a.status_date,'%Y-%m')<=:t: AND a.manager_id IN (:uid".implode(':,:uid',array_keys($this->users)).":) 
            GROUP BY a.manager_id, DATE_FORMAT(a.status_date,'%Y-%m')       
        "*/"
            SELECT ROUND(SUM(a.profit*0.1)) cnt, DATE_FORMAT(a.status_date,'%Y-%m') date, a.manager_id user_id
            FROM (
                (SELECT op1.manager_id, op1.creator_id, o1.owner_id, op1.status_date, op1.warehouse_id,
                    op1.quantity*(op1.price-op1.purchase_price-op1.transport_charges)*IF(o1.user_id=3199,0.5,1) profit
                FROM order_position op1
                INNER JOIN `order` o1
                ON o1.id=op1.order_id
                WHERE op1.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED'))
                UNION ALL
                (SELECT op2.manager_id manager_id, IF(pctp.manager_id=0,op2.creator_id,pctp.manager_id) creator_id, o2.owner_id, op2.status_date, op2.warehouse_id,
                    IF(pctp.change_type_id IN (11,61),0,pctp.amount) profit
                FROM order_position op2
                INNER JOIN price_change_to_position pctp ON pctp.order_position_id=op2.id AND !pctp.deactivated AND !op2.client_price
                INNER JOIN price_change_type pct ON pctp.change_type_id=pct.id
                INNER JOIN `order` o2  ON o2.id=op2.order_id
                WHERE op2.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED'))
            ) a
            WHERE a.owner_id=:oid: AND DATE_FORMAT(a.status_date,'%Y-%m')>=:f:
            AND DATE_FORMAT(a.status_date,'%Y-%m')<=:t: AND a.manager_id IN (:uid".implode(':,:uid',array_keys($this->users)).":) 
            GROUP BY a.manager_id, DATE_FORMAT(a.status_date,'%Y-%m')       
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
        if ($this->start < '2015-11') {
            $query = $this->db->newStatement(
                "SELECT ROUND(SUM(a.profit*0.1)) cnt, DATE_FORMAT(a.status_date,'%Y-%m') date, a.manager_id user_id
                FROM (
                    (SELECT op1.manager_id, op1.creator_id, o1.owner_id, op1.status_date, op1.warehouse_id,
                        op1.quantity*(op1.price-op1.purchase_price-op1.transport_charges)*IF(o1.user_id=3199,0.5,1) profit
                    FROM order_position op1
                    INNER JOIN `order` o1
                    ON o1.id=op1.order_id
                    WHERE op1.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED'))
                    UNION ALL
                    (SELECT op2.manager_id manager_id, IF(pctp.manager_id=0,op2.creator_id,pctp.manager_id) creator_id, o2.owner_id, op2.status_date, op2.warehouse_id,
                        IF(pctp.change_type_id IN (11,61),0,pctp.amount) profit
                    FROM order_position op2
                    INNER JOIN price_change_to_position pctp ON pctp.order_position_id=op2.id AND !pctp.deactivated AND !op2.client_price
                    INNER JOIN price_change_type pct ON pctp.change_type_id=pct.id
                    INNER JOIN `order` o2  ON o2.id=op2.order_id
                    WHERE op2.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED'))
                ) a
                WHERE a.owner_id=:oid: AND DATE_FORMAT(a.status_date,'%Y-%m')>=:f:
                AND DATE_FORMAT(a.status_date,'%Y-%m')<=:t: AND a.manager_id IN (:uids:) 
                GROUP BY a.manager_id, DATE_FORMAT(a.status_date,'%Y-%m')       
            ");
            $query->setInteger('oid', OWNER_ID);
            $query->setVarChar('f',$this->start);
            $query->setVarChar('t',$this->end);
            $query->setArray('uids',$this->ids);
            return $this->PreSetCalculate($query->getAllRecords());
        } else {
            $selectWrapper = "
                ROUND(profit*0.1) cnt
                ";                
            $select = "
                op.manager_id user_id, DATE_FORMAT(b.datetime,'%Y-%m') date
                ";
            $clause = "
                op.manager_id IN (:user_id:)
                ";
            $params = [
                'since' => date('Y-m-d 00:00:00', strtotime('first day of this month', strtotime($this->start.'-01'))), 
                'till' => date('Y-m-d 23:59:59', strtotime('last day of this month', strtotime($this->start.'-01'))),
                'user_id' => $this->ids
                ];
            $group = "op.manager_id, DATE_FORMAT(b.datetime,'%Y-%m')";

            return $this->PreSetCalculate(\Model::admin('Balance')->buildOrderStatistics(compact("selectWrapper", "select", "clause", "params", "group")));
        }
    }
    
}
