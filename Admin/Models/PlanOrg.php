<?php

namespace Modules\Employee\Admin\Models;
use Modules\Employee\Admin\Extensions\Calendar;

/**
* Планы организации
*/
class PlanOrg extends \Classes\Base\Model
{
    protected $table = 'plan_org';
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                po.*
            FROM plan_org po
            WHERE po.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function getByDate($date)
    {
        $date = date('Y-m-d', strtotime($date));
        $query = $this->db->newStatement("
            SELECT
                po.*
            FROM plan_org po
            WHERE po.date = :date:
            LIMIT 1
        ");
        $query->setDate('date', $date);
        return $query->getFirstRecord();
    }
    
    public function getByList(array $filter = [], $order_by = 'po.date desc')
    {
        $params = [];
        $criteria = [];
        foreach($filter as $key => $value){
            if($key == 'start'){
                $criteria[$key] = "po.date >= :".$key.":";
                $params[$key] = $value;
            }
            elseif($key == 'end'){
                $criteria[$key] = "po.date <= :".$key.":";
                $params[$key] = $value;
            }else{
                if(! is_array($value)){
                    $first = substr($key, 0, 1);
                    if('!' == $first){
                        $key = substr($key, 1);
                        $criteria[$key] = "po.".$key." != :".$key.":";    
                    }else{
                        $criteria[$key] = "po.".$key." = :".$key.":";
                    }
                }else{
                    $criteria[$key] = "po.".$key." IN (:".$key.":)";
                }
                $params[$key] = $value;
            }
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                po.*
            FROM plan_org po
            {$where}
            ORDER BY {$order_by}
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function getByFact($date){
        if (!$date) $date = date($this->format_date);
        $period = Calendar::getPeriodMonth($date);
        $select = "
            DATE_FORMAT(b.datetime,'%Y-%m') month
            ";
        $params = ['since' => $period['start'].' 00:00:00', 'till' => $period['end'].' 23:59:59'];
        $group = "DATE_FORMAT(b.datetime,'%Y-%m-01')";
        $all = \Model::admin('Balance')->buildOrderStatistics(compact("select", "params", "group"));
        if(! empty($all)){
            return array_pop($all);
        }
        return 0;
    }
    
    public function getCalculate($stamp, $item, $all = []){
        if(! empty($item)){
            $year = date('Y', strtotime($item['date']));
            $guiding_year = \Arr::get($item, 'guiding_year', 0);
            if($guiding_year){
                $diffyear = $year - $guiding_year;    
            }else{
                $diffyear = 1;
            }
            $c = new \DateTime($item['date']);
            $c->sub(new \DateInterval('P'.$diffyear.'Y'));
            $call1 = $c->format('Y-m-d');
            $c->sub(new \DateInterval('P1M'));
            $call2 = $c->format('Y-m-d');
            $c = new \DateTime($item['date']);
            $c->sub(new \DateInterval('P1M'));
            $prev = $c->format('Y-m-d');
            
            $prev = strtotime($prev);
            $call1 = strtotime($call1);
            $call2 = strtotime($call2);
            $_call1 = \Arr::path($all, $call1.'.profit', 0);
            $_call2 = \Arr::path($all, $call2.'.profit', 0);
            $_prev = \Arr::path($all, $prev.'.profit', 0);
            if($_call2){
                $help = ($_call1*$_prev)/$_call2;
                return round($help,-5);
            }
        }
        return 0;
    }
}