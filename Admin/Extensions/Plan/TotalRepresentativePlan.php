<?php

namespace Modules\Employee\Admin\Extensions\Plan;

class TotalRepresentativePlan extends Indicator {
    
    public function getValues() {
        parent::getValues();
        
        $query = $this->conn->newStatement("
            SELECT ROUND(SUM(IF(
                    !bop.amount,
                    IF(b.payment_type_id=3,IF(cag.with_vat,0.984,0.98),1)*bo.amount,
                    IF(bop.change_id,-(1-pct.profit)*bop.amount,IF(bo.type='cancel',-1,1)*(bop.amount*IF(b.payment_type_id=3,IF(cag.with_vat,0.984,0.98),1)-(op.purchase_price+op.transport_charges)*IF(bop.quantity,bop.quantity,op.quantity)))
                ))*0.1) cnt, DATE_FORMAT(b.datetime,'%Y-%m') date, u.id user_id
            FROM balance_operation bo
            INNER JOIN balance_operation_position bop on bop.operation_id=bo.id
            INNER JOIN balance b on b.id=bo.balance_id
            INNER JOIN order_position op on op.id=bop.position_id
            INNER JOIN `order` o ON o.id=op.order_id
            INNER JOIN client_address ca ON ca.id=o.address_id
            INNER JOIN client c ON c.id=ca.client_id
            INNER JOIN pm_employee pe ON pe.user_id=c.manager_id
            LEFT OUTER JOIN user u ON u.id IN (:uid".implode(':,:uid',array_keys($this->users)).":)
            LEFT OUTER JOIN counteragent cag ON cag.id=bo.seller_id
            LEFT OUTER JOIN price_change_type pct ON pct.id=bop.change_id
            WHERE bo.type IN ('cancel','payment','forfeit','markdown') AND c.manager_id=pe.user_id AND o.owner_id=:oid: AND DATE_FORMAT(b.datetime,'%Y-%m')>=:t: AND DATE_FORMAT(b.datetime,'%Y-%m')<=:t:
            GROUP BY u.id, DATE_FORMAT(b.datetime,'%Y-%m')
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
        $selectWrapper = "
            ROUND(profit*0.1) cnt
            ";                
        $select = "
            pe.department_id, DATE_FORMAT(b.datetime,'%Y-%m') date
            ";
        $from = "
            INNER JOIN client_address ca ON ca.id=o.address_id
            INNER JOIN client c ON c.id=ca.client_id
            INNER JOIN pm_employee pe ON pe.user_id=c.manager_id AND pe.department_id IN (:department_id:) AND pe.`start`<=DATE_FORMAT(:till:,'%Y-%m-%d') AND (pe.`end`>=DATE_FORMAT(:since:,'%Y-%m-%d') OR pe.`end` IS NULL)
            ";
        $clause = "
            c.manager_id=pe.user_id AND b.datetime BETWEEN CONCAT(pe.`start`,' 00:00:00') AND CONCAT(IFNULL(pe.`end`,DATE_FORMAT(NOW(),'%Y-%m-%d')),' 23:59:59')
            ";
        $params = [
            'since' => date('Y-m-d 00:00:00', strtotime('first day of this month', strtotime($this->start.'-01'))), 
            'till' => date('Y-m-d 23:59:59', strtotime('last day of this month', strtotime($this->start.'-01'))),
            'department_id' => $this->departments
            ];
        foreach ($this->users as $key=>$curr) {
            $params['uid'.$key] = $curr;
        }
        $group = "pe.department_id, DATE_FORMAT(b.datetime,'%Y-%m')";
        
        return $this->PreSetCalculate(\Model::admin('Balance')->buildOrderStatistics(compact("selectWrapper", "select", "from", "clause", "params", "group")));
    }
    
}/*SELECT (IF(
                bo.type in ('forfeit','markdown'),
                bo.amount,
                if(bo.type='cancel',-1,1)*(bop.amount-IF(b.payment_type_id=3,ABS(bop.amount/(bo.amount+bo.info))*bo.info,0)-(op.purchase_price+op.transport_charges)*op.quantity)
            ))/100, o.id, b.datetime date, op.creator_id user_id, bo.id
            FROM balance_operation bo
            INNER JOIN balance_operation_position bop on bop.operation_id=bo.id
            INNER JOIN balance b on b.id=bo.balance_id
            INNER JOIN order_position op on op.id=bop.position_id
            INNER JOIN `order` o ON o.id=op.order_id
            WHERE bo.type IN ('cancel','payment','forfeit','markdown') AND !bop.change_id AND o.owner_id=1 AND DATE_FORMAT(b.datetime,'%Y-%m')>='2015-09'
            AND DATE_FORMAT(b.datetime,'%Y-%m')<='2015-09' AND op.creator_id IN (126) 

order by b.datetime,bop.operation_id,bop.position_id,bop.change_id*/