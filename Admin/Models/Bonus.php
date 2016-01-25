<?
namespace Modules\Employee\Admin\Models;
use Modules\Employee\Admin\Extensions\Calendar;
use Modules\Employee\Admin\Models\TimeSheet;

/**
* Бонусы и депремирование
**/
class Bonus extends \Classes\Base\Model
{
    const STATUS_NEW = 0; // На рассмотрении
    const STATUS_APPROVED = 1; // Одобрено
    const STATUS_CANCEL = 2; //Отменено

    const REQUEST_NO = 0; //запрос дообавлен
    const REQUEST_NEW = 1; //запрос добавлен
    const REQUEST_SUSSES = 2; // Запрос обработан
    const REQUEST_ABORT = 3; // Запрос отклонен

    const REQUEST_TYPE_OUTER = 7; //для внешних Другое
    const REQUEST_TYPE_CHANGE = 4; //для внешних Менялся
    const REQUEST_TYPE_GRAFIC = 6; //для внешних График
    
    
    protected $table = 'bonus';
    
    public $format = 'Y-m-d'; // формат даты 
    
    protected function init_sql(){
      /** бонусы */
      "CREATE TABLE IF NOT EXISTS `bonus` (
        `id` smallint(6) NOT NULL AUTO_INCREMENT,
        `type` varchar(255) DEFAULT NULL,
        `amount` int(11) DEFAULT '0',
        `date` date DEFAULT NULL,
        `is_active` tinyint(1) DEFAULT '1',
        `manager_id` mediumint(9) DEFAULT '0',
        `creator_id` mediumint(9) DEFAULT '0',
        `is_approved` tinyint(1) DEFAULT '0',
        `comment` text,
        `debt_id` mediumint(9) DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM
      ALTER TABLE `bonus` ADD `start` DATE NULL AFTER `date` 
      ALTER TABLE `bonus` ADD `end` DATE NULL AFTER `start`
      ALTER TABLE `bonus` ADD `request` TINYINT(1) default '0' NOT NULL AFTER `end` 
      ";
    }

    public function getSelectRequest()
    {
        // При смене ключа смотри js и контролер Add
        return [
            1 => 'выходной',
            2 => 'праздник',
            3 => 'отпрашивался',
            4 => 'менялся с ...(выбрать сотрудника) на ...(число отработки)',
            5 => 'болел',
            6 => 'работал не по графику с ... (со скольки) до ... (до скольки) вместо (текущий график на день)',
            7 => 'другое'
        ];
    }
    
    public function getByList(array $filter)
    {
        $params = [];
        $where = [];
        if(! empty($filter['month']) AND ! empty($filter['year'])){
            $period = Calendar::getPeriodMonth($filter['year'].'-'.$filter['month'].'-01');
            $criteria[] = "b.date >= :start:";
            $criteria[] = "b.date <= :end:";
            $params['start'] = $period['start'];
            $params['end'] = $period['end'];
        }
        if(! empty($filter['date'])){
            if(is_array($filter['date']) AND count($filter['date']) > 0){
                if(count($filter['date']) == 1){
                    $filter['date'] = array_pop($filter['date']);
                }
            }
            if(is_array($filter['date'])){
                list($start, $end) = $filter['date']; 
                $start = date($this->format, strtotime($start));
                $end = date($this->format, strtotime($end));
                $criteria[] = "b.date >= :start:";
                $criteria[] = "b.date <= :end:";
                $params['start'] = $start;
                $params['end'] = $end;
            }else{
                $date = date($this->format, strtotime($filter['date']));
                $criteria[] = "b.date = :date:";
                $params['date'] = $date;
            }
        }
        if(isset($filter['user_id'])){
            if(is_array($filter['user_id'])){
                $criteria['user_id'] = "b.manager_id IN (:user_ids:)";
                $params['user_ids'] = $filter['user_id'];
            }else{
                $criteria['user_id'] = "b.manager_id = :user_id:";
                $params['user_id'] = $filter['user_id'];
            }
        }
        if(isset($filter['creator_id'])){
            if(is_array($filter['creator_id'])){
                $criteria['creator_id'] = "b.creator_id IN (:creator_ids:)";
                $params['creator_ids'] = $filter['creator_id'];
            }else{
                $criteria['creator_id'] = "b.creator_id = :creator_id:";
                $params['creator_id'] = $filter['creator_id'];
            }
        }
        if(isset($filter['is_approved'])){
            if(is_array($filter['is_approved'])){
                $criteria['is_approved'] = "b.is_approved IN (:is_approveds:)";
                $params['is_approveds'] = $filter['is_approved'];
            }else{
                $criteria['is_approved'] = "b.is_approved = :is_approved:";
                $params['is_approved'] = $filter['is_approved'];
            }
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                b.*,
                u.id as user_id,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name,
                TRIM(CONCAT_WS(' ', c.lastname, c.firstname, c.secondname)) creater_name,
                TRIM(CONCAT_WS(' ', a.lastname, a.firstname, a.secondname)) approved_name
            FROM bonus b
            INNER JOIN user u ON b.manager_id = u.id
            LEFT OUTER JOIN user c ON b.creator_id = c.id
            LEFT OUTER JOIN user a ON b.approved_id = a.id
            {$where}
            ORDER BY b.date asc
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                b.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name,
                TRIM(CONCAT_WS(' ', c.lastname, c.firstname, c.secondname)) creater_name,
                TRIM(CONCAT_WS(' ', a.lastname, a.firstname, a.secondname)) approved_name
            FROM bonus as b
            INNER JOIN user u ON u.id = b.manager_id
            LEFT JOIN user c ON c.id = b.creator_id
            LEFT JOIN user a ON b.approved_id = a.id
            WHERE b.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public static function prepare(array $bonus)
    {
        $bonus['approved'] = self::is_approved($bonus);
        if(empty($bonus['request'])){
            $creator_id = \Arr::get($bonus, 'creator_id',0);
            $manager_id = \Arr::get($bonus, 'manager_id',0);
            if($manager_id == $creator_id AND ! empty($creator_id)){
                $bonus['request'] = 1;
            }
        }
        $bonus['is_request'] = self::is_request($bonus);
        return $bonus;
    }
    
    // Участвует в рассчете или одобрено или ПО CRON
    public static function is_approved(array $bonus)
    {
        $creator_id = \Arr::get($bonus, 'creator_id',0);
        $manager_id = \Arr::get($bonus, 'manager_id',0);
        $is_approved = \Arr::get($bonus, 'is_approved', 0);
        if($creator_id){
            if($creator_id == $manager_id){
                return ($is_approved == self::STATUS_APPROVED); //HACK OLD
            }else{
                return $is_approved == self::STATUS_APPROVED;
                //return (in_array($is_approved, [self::STATUS_NEW,self::STATUS_APPROVED]));
            }
        }else{
            //cron
            return (in_array($is_approved, [self::STATUS_NEW,self::STATUS_APPROVED]));
        }
    }
    
    // Не учтенный запрос
    public static function is_request(array $bonus)
    {
        $request = \Arr::get($bonus, 'request',0);
        $is_approved = \Arr::get($bonus, 'is_approved', 0);
        if(! empty($request)){
            if($is_approved == self::STATUS_CANCEL){
                return false;
            }
            return self::REQUEST_NEW == $request;
        }
        return false;
    }
    
    public static function getSkipType(array $bonus)
    {
        if(! empty($bonus)){
            //Опоздание к 10:00. Пропущено минут: 3.
            preg_match('/Опоздание/i', $bonus['type'],$is_fine);
            preg_match('/Пропущено минут: (\d+)/i',$bonus['type'], $is_fine2);
            //Не отработано 2 минут.
            preg_match("/Не отработано (\d+) минут/i",$bonus['type'], $is_skip);
            if(! empty($is_fine) || ! empty($is_fine2)){
                $bonus['skip_type'] = TimeSheet::TYPE_SKIP_FINE;
                if(! empty($is_fine2[1])){
                    $bonus['skip_time'] = intval($is_fine2[1]);
                }
            }
            if(! empty($is_skip) || ! empty($is_skip)){
                $bonus['skip_type'] = TimeSheet::TYPE_SKIP_TIME;
                if(! empty($is_skip[1])){
                    $bonus['skip_time'] = intval($is_skip[1]);
                }
            }
        }
        return $bonus;
    }
    
    
    /**
    * Проверяет сумму депремирований сотрудника за какой то день
    **/
    public function checkSkipAmount(array $bonuses, array $skips, $user_id)
    {
        $mTime = new Timesheet($this->db, $this->user);
        $bonus = ['fine' => [], 'skip' => []];
        $ids = $remove = [];
        foreach($bonuses as $b){
            //Предположим что мало вероятно два одинаковых с одним временем
            if(0 == $b['creator_id']){
                if($mTime::TYPE_SKIP_FINE == $b['skip_type']){
                    $bonus['fine'][$b['skip_time']] = $b;
                }
                if($mTime::TYPE_SKIP_TIME == $b['skip_type']){
                    $bonus['skip'][$b['skip_time']] = $b;
                }
                $ids[$b['id']] = $b['id'];
            }
        }
        foreach($skips as $skip){
            $insert = true;
            $update = false;
            if($skip['skip_type'] == $mTime::TYPE_SKIP_FINE){
                if($update = \Arr::path($bonus, 'fine.'.$skip['skip_time'], null)){
                    $insert = false;    
                }
            }
            if($skip['skip_type'] == $mTime::TYPE_SKIP_TIME){
                if($update = \Arr::path($bonus, 'skip.'.$skip['skip_time'], null)){
                    $insert = false;    
                }
            }
            if($insert){
                $data = ['manager_id' => $user_id,
                        'creator_id' => 0,
                        'is_approved' => $this::STATUS_APPROVED,
                        'type' => $skip['type'],
                        'amount' => $skip['amount'],
                        'date' => $skip['date'],
                        'skip_type' => $skip['skip_type'],
                        'skip_time' => $skip['skip_time'],
                    ];
                $this->insert($data);
            }else{
                //exists
                //UPDATE
                if($update){
                    if($update['amount'] != $skip['amount'] OR (! empty($_GET['update_bonus']))){
                        //if($update['type'] == $skip['type']){
                            $data = ['id' => $update['id'],
                                    'amount' => $skip['amount'],
                                    'skip_type' => $skip['skip_type'],
                                    'skip_time' => $skip['skip_time'],
                                    'type' => $skip['type'],
                                ];
                            $this->upsert($data);
                        //}
                    }
                    unset($ids[$update['id']]);
                }else{
                }
            }
        }
        //remove no update
        if(! empty($ids)){
            $this->delete(['id' => $ids]);
        }
    }
    
    public function plus($data, $price_hour)
    {
        if(empty($data['date']) AND empty($data['hour']) AND empty($data['user_id'])){
            return false;
        }
        $hour = $data['hour'];
        $type = "Отработано ".$data['hour']." :hour: ";
        $h = \Text::declension($data['minute'], ['час', 'часа', 'часов']);
        $type = str_replace(":hour:",$h,$type);
        if(! empty($data['minute'])){
            $type .= " ".$data['minute']." :minute:";
            $m = \Text::declension($data['minute'], ['минута', 'минуты', 'минут']);
            $type = str_replace(":minute:",$m,$type);
            $hour = $hour + ($data['minute']/60);
        }
        $data['type'] = $type;
        $data['request'] = $this::REQUEST_NEW;
        $data['manager_id'] = $data['user_id'];
        $data['creator_id'] = $data['user_id'];
        $data['amount'] = round($price_hour*$hour);
        $data['skip_time'] = $hour*60;
        $data['skip_type'] = TimeSheet::TYPE_SKIP_BONUS;
        unset($data['minute']);
        unset($data['hour']);
        unset($data['user_id']);
        $id = $this->insert($data);
        return $this->getById($id);
    }
  
}