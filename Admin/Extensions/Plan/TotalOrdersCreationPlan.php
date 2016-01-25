<?php

namespace Modules\Employee\Admin\Extensions\Plan;

class TotalOrdersCreationPlan extends Indicator {

	
	public function calculate() {
        if(empty($this->ids)){
            return FALSE;
        }
        if ($this->start < '2015-11') {
            $query = $this->db->newStatement("
                SELECT ROUND(SUM(a.profit*0.1)) cnt, DATE_FORMAT(a.status_date,'%Y-%m') date, pe.department_id
                FROM (
                    (SELECT op1.manager_id, op1.creator_id, o1.owner_id, op1.status_date, op1.warehouse_id,
                        op1.quantity*(op1.price-op1.purchase_price-op1.transport_charges)*IF(o1.user_id=3199,0.5,1) profit
                    FROM order_position op1
                    INNER JOIN `order` o1 ON o1.id=op1.order_id
                    WHERE op1.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED'))
                    UNION ALL
                    (SELECT op2.manager_id manager_id, IF(pctp.manager_id=0,op2.creator_id,pctp.manager_id) creator_id, o2.owner_id, op2.status_date, op2.warehouse_id,
                        pctp.amount profit
                    FROM order_position op2
                    INNER JOIN price_change_to_position pctp ON pctp.order_position_id=op2.id AND !pctp.deactivated AND !op2.client_price
                    INNER JOIN price_change_type pct ON pctp.change_type_id=pct.id
                    INNER JOIN `order` o2 ON o2.id=op2.order_id
                    WHERE op2.status_id=(SELECT id FROM order_status WHERE code='IS_TRANSFERED'))
                ) a
                INNER JOIN pm_employee pe ON pe.user_id=a.creator_id AND pe.department_id IN (:department_id:) AND pe.`start`<=:till: AND (pe.`end`>=:since: OR pe.`end` IS NULL)
                WHERE a.owner_id=:oid: AND DATE_FORMAT(a.status_date,'%Y-%m')>=:f: AND DATE_FORMAT(a.status_date,'%Y-%m')<=:t: AND a.status_date BETWEEN CONCAT(pe.`start`,' 00:00:00') AND CONCAT(IFNULL(pe.`end`,DATE_FORMAT(NOW(),'%Y-%m-%d')),' 23:59:59')
                GROUP BY pe.department_id, DATE_FORMAT(a.status_date,'%Y-%m')
            ");
            $query->setInteger('oid', OWNER_ID);
            $query->setVarChar('f',$this->start);
            $query->setVarChar('t',$this->end);
            $query->setDate('since', $this->start_day); 
            $query->setDate('till', $this->end_day);
            $query->setArray('department_id',$this->departments);
            return $this->PreSetCalculate($query->getAllRecords());
        }
        else {
            $selectWrapper = "
                ROUND(profit*0.1) cnt
                ";                
            $select = "
                pe.department_id, DATE_FORMAT(b.datetime,'%Y-%m') date
                ";
            $from = "
                INNER JOIN pm_employee pe ON pe.user_id=op.creator_id AND pe.department_id IN (:department_id:) AND pe.`start`<=DATE_FORMAT(:till:,'%Y-%m-%d') AND (pe.`end`>=DATE_FORMAT(:since:,'%Y-%m-%d') OR pe.`end` IS NULL)
                ";
            $clause = "b.datetime BETWEEN CONCAT(pe.`start`,' 00:00:00') AND CONCAT(IFNULL(pe.`end`,DATE_FORMAT(NOW(),'%Y-%m-%d')),' 23:59:59')";
            $params = [
                'since' => date('Y-m-d 00:00:00', strtotime('first day of this month', strtotime($this->start.'-01'))), 
                'till' => date('Y-m-d 23:59:59', strtotime('last day of this month', strtotime($this->start.'-01'))),
                'department_id' => $this->departments
                ];
            $group = "pe.department_id, DATE_FORMAT(b.datetime,'%Y-%m')";
            return $this->PreSetCalculate(\Model::admin('Balance')->buildOrderStatistics(compact("selectWrapper", "select", "from", "clause", "params", "group")));             
        }
    }

}