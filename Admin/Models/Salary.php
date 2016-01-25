<?
namespace Modules\Employee\Admin\Models;
use Modules\Employee\Admin\Models\Bonus;
use Modules\Employee\Admin\Models\ManagerTimeSheet;
use Modules\Employee\Admin\Models\SalaryLog;
use Modules\Employee\Admin\Models\SalaryAvans;
use Modules\Employee\Admin\Extensions\Calendar;

class Salary extends \Classes\Base\Model
{
    const READY_NO = 0; // выдачи не было
    const READY_AVANS = 1; // выдавался аванс
    const READY_BALANCE = 2; // выдавалась ЗП
    const READY_AVANS_BALANCE = 3; // выдавалась ЗП + аванс в один день
    
    protected $table = 'salary';
    
    protected function init_sql(){
        // ЗП за месяц
        "
        CREATE TABLE IF NOT EXISTS `salary` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11),
        `base` int(11) NOT NULL default '0',
        `avans` int(11) NOT NULL default '0',
        `max` int(11) NOT NULL default '0',
        `income` int(11) NOT NULL default '0',
        `total` int(11) NOT NULL default '0',
        `tax` int(11) NOT NULL default '0',
        `balance` int(11) NOT NULL default '0',
        `out` int(11) NOT NULL default '0',
        `bonus` int(11) NOT NULL default '0',
        `plus` int(11) NOT NULL default '0',
        `series` varchar(50) UNIQUE,
        `outdate` date,
        `date` date,
        `created` datetime,
        `updated` datetime,
        `creater` int(11),
        `updater` int(11),
        INDEX user_id (`user_id`),
        PRIMARY KEY (`id`)
      ) ENGINE=Aria DEFAULT CHARSET=utf8;";
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                s.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM salary s
            LEFT JOIN user u ON u.id=s.user_id
            WHERE s.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }

    /**
     * @param array $filter
     * @return array
     */
    public function getByList(array $filter =  [])
    {
        $salarys = [];
        $params = [];
        $criteria = [];
        foreach($filter as $key => $value){
            if(! is_array($value)){
                $first = substr($key, 0, 1);
                if('!' == $first){
                    $key = substr($key, 1);
                    $criteria[$key] = "s.".$key." != :".$key.":";    
                }else{
                    $criteria[$key] = "s.".$key." = :".$key.":";
                }
                
            }else{
                $criteria[$key] = "s.".$key." IN (:".$key.":)";
            }
            $params[$key] = $value;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                s.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM salary s
            LEFT JOIN user u ON u.id=s.user_id
            {$where}
            ORDER BY s.date, u.lastname
        ");
        $query->bind($params);
        foreach($query->getAllRecords() as $salary){
            $salarys[$salary['id']] = $salary;
        }
        return $salarys;
    }

    /**
     * @param int $months
     * @return array
     */
    public function getByAll($months = 36)
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P'.$months.'M'));
        $start = $date->format('Y-m-d');
        
        $params = $salarys = $criteria = [];
        $criteria['start'] = 's.date >= :start: AND s.date <= :end:';
        $params['start'] = $start;
        $params['end'] = date('Y-m-d');
        $criteria['total'] = 's.total > 0';
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                SUM(s.total) as summa,
                s.date
            FROM salary s
            {$where}
            GROUP BY s.date
            ORDER BY s.date
        ");
        $query->bind($params);
        foreach($query->getAllRecords() as $salary){
            $salarys[$salary['date']] = $salary;
        }
        return $salarys;
    }


    /**
     * @return int
     */
    public function getReady(){
        $balance = 0;
        $sAvans = new SalaryAvans($this->db, $this->user);
        foreach($this->getByList(['!ready' => Salary::READY_NO]) as $_salary){
            $salarys[] = $_salary;
            $ready = \Arr::get($_salary, 'ready');
            switch($ready){
                case Salary::READY_AVANS:
                    $balance = $balance + \Arr::get($_salary, 'avans');
                break;
            
                case Salary::READY_BALANCE:
                    $balance = $balance + \Arr::get($_salary, 'out');
                break;
            
                case Salary::READY_AVANS_BALANCE:
                    $balance = $balance + \Arr::get($_salary, 'avans') + \Arr::get($_salary, 'out');
                break;
            }
        }
        foreach($sAvans->getByList(['!ready' => SalaryAvans::READY_NO]) as $_avans){
            $balance = $balance + \Arr::get($_avans, 'avans');
        }
        return $balance;
    }

    /**
     * @param $expense_id
     */
    public function updateReady($expense_id){
        $sLog = new SalaryLog($this->db, $this->user);
        $sAvans = new SalaryAvans($this->db, $this->user);
        foreach($this->getByList(['!ready' => Salary::READY_NO]) as $_salary){
            $insert = ['date' => $_salary['date'],
                       'time' => date('Y-m-d H:i:s'),
                       'user_id' => $_salary['user_id'],
                       'expense_id' => $expense_id,
            ];
            $ready = $_salary['ready'];
            switch($ready){
                case Salary::READY_AVANS:
                    $insert['out'] = 0;
                    $insert['avans'] = \Arr::get($_salary, 'avans');
                    $sLog->insert($insert);
                break;
            
                case Salary::READY_BALANCE:
                    $insert['out'] = \Arr::get($_salary, 'out');
                    $insert['avans'] = 0;
                    $sLog->insert($insert);
                break;
            
                case Salary::READY_AVANS_BALANCE:
                    $insert['out'] = \Arr::get($_salary, 'out');
                    $insert['avans'] = \Arr::get($_salary, 'avans');
                    $sLog->insert($insert);
                break;
            }
        }
        $query = $this->db->newStatement("
            UPDATE `salary`
            SET `ready` = :ready:, `out` = '0', `avans` = '0'
            WHERE `ready` != :ready:
        ");
        $params['ready'] = Salary::READY_NO;
        $query->bind($params);
        $query->execute();
        
        // Avans до фиксации
        foreach($sAvans->getByList(['!ready' => SalaryAvans::READY_NO]) as $_avans){
            $insert = ['date' => $_avans['date'],
                       'time' => date('Y-m-d H:i:s'),
                       'user_id' => $_avans['user_id'],
                       'expense_id' => $expense_id,
            ];
            $insert['out'] = 0;
            $insert['avans'] = \Arr::get($_avans, 'avans');
            $sLog->insert($insert);
        }
        $query = $this->db->newStatement("
            UPDATE `salary_avans`
            SET `ready` = :ready:
            WHERE `ready` != :ready:
        ");
        $params['ready'] = SalaryAvans::READY_NO;
        $query->bind($params);
        $query->execute();
    }


    /**
     * @param null $date
     */
    public function updateAvansReady($date = NULL){
        $query = $this->db->newStatement("
            UPDATE `salary_avans`
            SET `ready` = :ready:
            WHERE `ready` != :ready: AND date=:date:
        ");
        $params['ready'] = SalaryAvans::READY_NO;
        $params['date'] = $date;
        $query->bind($params);
        $query->execute();
    }
    
    
    /**
    * 
    * Зарплаты пользователей пользователя
    **/
    public function getByUser($id = NULL)
    {
        $filter = ["user_id" => $id];
        return $this->getByList($filter);
    }
    
    public function getByMount($mount = NULL, $year = NULL, $user_id = NULL)
    {
        $filter = ["date" => $year.'-'.$mount.'-01'];
        if($user_id){
            $filter['user_id'] = $user_id;
        }
        $susers = [];
        foreach($this->getByList($filter) as $_s){
            $susers[$_s["user_id"]] = $_s;    
        }
        if($user_id){
            return \Arr::get($susers, $user_id, null);
        }
        return $susers;
    }
    
    public function getSeries($series = NULL){
        $query = $this->db->newStatement("
            SELECT 
                s.series
            FROM salary s
            WHERE series=:series:
            LIMIT 1
        ");
        $query->setvarchar('series', $series);
        return  $query->getFirstRecord();
    }
    
    public function generateSeries($mount = NULL, $year = NULL, $series = NULL){
        if(! $mount) $mount = date("m");
        if(! $year) $year = date("Y");
        $_series = $year.'-'.$mount.'-'.rand(1, 100);
        if(NULL == $series){
            while($this->getSeries($_series)){
                return $this->generateSeries($mount, $year);
            }
        }else{
            if(in_array($_series, $series)){
                return $this->generateSeries($mount, $year, $series);
            }
        }
        return $_series;
    }
    
    public function calculatebalance(array $salary){
        $total = \Arr::get($salary,'total',0);
        $avans = \Arr::get($salary,'avans', 0);
        $vax = \Arr::get($salary, 'vax', 0);
        $out = \Arr::get($salary, 'out', 0);
        $balance = $total - $avans - $vax - $out;
        $balance = $total - $avans - $vax - $out;
        return $balance;
    }
    
    /**
    * update bonuses 
    */
    public function updateBonus($id = NULL){
        $salary = $this->getById($id);
        if(null !== $salary){
            $mBonus = new Bonus($this->db, $this->user);
            $stamp = strtotime($salary['date']);
            $filter = Calendar::getMonthYear($salary['date']);
            $filter['user_id'] = $salary['user_id'];
            $bonuses = $mBonus->getByList($filter);
            $plus = 0;
            $total_bonus = 0;
            foreach($bonuses as $bonus){
                if(Bonus::is_approved($bonus)){
                    if($bonus['amount'] < 0){
                        $total_bonus += $bonus['amount']; // все депремирования
                    }else{
                        $plus += $bonus['amount'];
                    }
                }
            }
            $total_bonus = round($total_bonus, -2); //ROUND до рублей
            $plus = round($plus, -2); //ROUND до рублей
            
            $_total = $salary['total'] + $salary['bonus'] - $salary['plus']; //чисто без Демотиваторов ! bonus в БД лежат плюсом
            $total = $_total + $total_bonus + $plus ; //ROUND до рублей
            $total = round($total, -2); //ROUND до рублей
            $balance = $salary['balance'] + ($total - $salary['total']);
            
            $data = ['id' => $salary['id'],
                     'total' => $total,
                     'balance' => $balance,
                     'bonus' => -$total_bonus,
                     'plus' => $plus,
            ];
            $this->upsert($data);
        }
    }


    /**
     * @param null $tmanager
     * @param bool $has_schedule
     * @param null $skip_time
     * @param null $_profile
     * @return array
     */
    public function allow_out($tmanager = null, $has_schedule = null, $skip_time = null, $_profile = null){
        $allow = true;
        $errors = [];
        if(ManagerTimeSheet::MIN_HOUR_WEEK > $tmanager){
            if($has_schedule){
                $errors[] = "График работы меньше ".ManagerTimeSheet::MIN_HOUR_WEEK."ч (".$tmanager.")";
                $allow = false;
            }
        }
        if($_profile < 100){ //профиль заполнен меньше чем на 100 %
            //$errors[] = "Профиль заполнен меньше чем на 100 %";
            //$allow = false;
        }
        return ['allow' => $allow, 'errors' => $errors];
    }
  
    
    public function prepare($salary = [], array $calculate = [], $logs = []){
        $_update = false;
        $data = [];
        $_salary = $calculate;
        if(! empty($salary)){
            $data['id'] = $salary['id'];
            if($salary['total'] != $calculate['total']){
                $_update = 1;
                $data['total'] = $calculate['total']; //update total
                $salary['total'] = $calculate['total'];
                $_salary['balance'] = $data['total'];
            }
            $all_out = 0;
            $balance = $salary['total'];
            if(! empty($logs)){
                foreach($logs as $_out){
                    if(!empty($_out['avans'])){
                        $all_out += $_out['avans'];
                    }
                    if(!empty($_out['out'])){
                        $all_out += $_out['out'];
                    }
                }
            }
            if(!empty($salary['vax'])){
                $all_out += $salary['vax'];
            }
            if($salary['ready'] != Salary::READY_NO){
                if(!empty($salary['avans'])){
                    $all_out += $salary['avans'];
                }
                if(!empty($salary['out'])){
                    $all_out += $salary['out'];
                }
            }
            $balance -= $all_out;
            if($balance != $salary['balance']){
                $_update = 1;
                $data['balance'] = $balance;
                $_salary['balance'] = $balance;
            }
            if($_update){
                //echo $salary['name'].':'.$salary['balance'].':'.$balance.':'.$salary['total'].':'.'<br />';
                $this->upsert($data);
            }
        }
        return $_salary;
    }
    
    public function money($value){
        $moneys = [];
        $bonknotes = [5000,1000,500,100,50];
        $monetes = [10,5,2,1];
        $div = false;
        while($div !== false AND $div > 0){
            $div = $value % $bonknote;    
        }
    }
}