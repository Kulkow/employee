<?php

class AbstractPlan {

    var $conn;
    var $start;
    var $end;
    var $users;
    var $values;

    public function __construct($startDate, $endDate='', $userId = 0) {
        require_once 'db/DbFactory.class.php';
        $this->conn = DbFactory::getConnection();
        if (!$startDate) $startDate = date('Y-m');
        if (!$endDate) $endDate = date('Y-m');
        $this->start = $startDate;
        $this->end = $endDate;
        $query = $this->conn->newStatement('SELECT user_id FROM employee_data WHERE status < 5 AND IF(:uid:,user_id=:uid:,1)');
        $query->setInteger('uid',$userId);
        $this->users = array_keys($query->getAllRecords('user_id'));
        $this->values = $this->getValues();
    }

    public function getValues() {
        if (!$this->users) return false;
    }

    public function getValuesByUser($userId) {
        $values = $this->values;
        return isset($values[$userId]) ? $values[$userId] : array();
    }

    public function getValuesByMonth($date) {
        $values = $this->values;
        foreach ($values as $key=>$curr) {
            $values[$key] = $curr[$date];
        }
        return $values;
    }

    public function getValuesByUserMonth($userId, $date) {
        $values = $this->values;
        return $values[$userId][$date];
    }

    protected function processResult($values) {
        foreach ($values as $key=>$value)
            $processed[$value['user_id']][$value['date']] = $value['cnt'];
        return $processed;
    }

    protected function getWorkingDaysData($date) {
        $tmp = explode('-',$date);
        $date = array('Date_Month'=>$tmp[1],'Date_Year'=>$tmp[0]);

        if (!$date) $start = date('Y-m-01');
        else {
            $start = date('Y-m-d',mktime(0, 0, 0, $date['Date_Month'], date("01"), $date['Date_Year']));
        }
        if (!$date) $end = date('Y-m-d',mktime(0, 0, 0, date("m")+1, date("01")-1,   date("Y")));
        else {
            $end = date('Y-m-d',mktime(0, 0, 0, $date['Date_Month']+1, date("01")-1, $date['Date_Year']));
        }

        $time = strtotime($end);
        $sinceBegin = 0;
        $tillEnd = 0;
        if (date('Y-m',$time)<date('Y-m')) {
            $day = date('d',$time)+1;
        }
        elseif (date('Y-m',$time)>date('Y-m')) {
            $day = 1;
        }
        else {
            $day = date('d');
        }

        for ($i=1;$i<$day;$i++) {
            if (date('w',strtotime(date('Y-m-',$time).$i))) $sinceBegin++;
        }
        for ($i=$day;$i<=date('d',$time);$i++) {
            if (date('w',strtotime(date('Y-m-',$time).$i))) $tillEnd++;
        }

        return array($sinceBegin,$tillEnd);
    }
}