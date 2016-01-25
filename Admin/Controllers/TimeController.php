<?php
namespace Modules\Employee\Admin\Controllers;
use Modules\Employee\Admin\Models\Bonus;
use Modules\Employee\Admin\Extensions\Calendar;


class TimeController extends \Classes\Base\Controller
{
    public $menu = 'salary';

    
     /**
     * @acesss (SALARY_SHEET)
     *
     * @Method (!AJAX)
     */
    public function cronAction()
    {
        $day = null;
        $query = $this->request->query->all();
        $date = \Arr::get($query, 'date', null);
        if(! $date){
            $date = new \DateTime();
            $date->sub(new \DateInterval('P1D'));
            $day = $date->format('Y-m-d');
        }else{
            $date = new \DateTime($date);
            $day = $date->format('Y-m-d');
        }
        $d = intval(date('w', strtotime($day)));
        
        
        $tree = $this->model('Department')->getTreeSalary();
        $ids = [];
        foreach($tree as $department){
            foreach(\Arr::get($department, 'users', []) as $user){
                $ids[] = \Arr::get($user, 'id');        
            }
        }
        $users = [];
        $esalarys = $this->model('EmployeeSalary')->getInfoUsers();
        foreach($ids as $id){
            if($esalary = \Arr::get($esalarys, $id, null)){
                if(! empty($esalary['isplans']) OR ! empty($esalary['oklad'])){
                    $users[$id] = $esalary;
                }
            }
        }
        $tmanager = $time = [];
        $filter = ['start' => $day, 'end' => $day, 'user_id' => $ids];
        foreach($this->model('ManagerTimeSheet')->getByList($filter) as $m){
            if(! isset($tmanager[$m['user_id']])){
                $start = \Arr::get($m, 's'.$d, null);
                $end = \Arr::get($m, 'e'.$d, null);
                $tmanager[$m['user_id']] = ['start' => $start, 'end' => $end, 'name' => $m['name']];
            }
        }
        $mTime = $this->model('TimeSheet');
        $mBonus = $this->model('Bonus');
        foreach($mTime->getByList($filter) as $m){
            if(! isset($time[$m['user_id']])){
                $time[$m['user_id']] = $m;
            }
        }
        
        $skips = [];
        foreach($users as $user_id => $user){
            $sheet = [];
            $manager = \Arr::get($tmanager, $user_id, null);
            if(empty($manager['start']) OR empty($manager['end'])){
                $manager = null;
            }
            $timesheet = \Arr::get($time, $user_id, null);
            if($manager AND $timesheet){
                $sheet['s'] = $manager['start'];
                $sheet['e'] = $manager['end'];
                $sheet['start'] = \Arr::get($timesheet, 'start', null);
                $sheet['finish'] = \Arr::get($timesheet,'finish', null);
                $sheet['date'] = $day;
            }
            $price_hour = intval($user['total']/(25*8));
            if(! empty($sheet)){
                $skip = $mTime->latenes($sheet,$price_hour);
                if(! empty($skip['skipping'])){
                    $skips[$user_id] = $skip['skipping'];
                }
            }
            if($manager AND ! $timesheet){
                $_price = round(($price_hour*9), 2);
                $skip_time = 9*60;
                $time = $skip_time*60;
                $skip = ['date' => $day,
                        'skip_time' => $skip_time,
                        'time' => $time,
                        'start' => null,
                        'finish' => null,
                        's' => $manager['start'],
                        'e' => $manager['end'],
                        'skip_type' => $mTime::TYPE_SKIP_TIME, // пропустил день
                        'type' => '',
                        'amount' => -$_price
                        ];
                $skip = $mTime::prepare($skip);
                $skips[$user_id][] = $skip;
                
                $_price = round(($price_hour*9), 2)+ $mTime::PRICE_LATENESS0 + $mTime::PRICE_LATENESS10;
                $skip = ['date' => $day,
                        'skip_time' => $skip_time,
                        'time' => $time,
                        'start' => null,
                        'finish' => null,
                        'late' => $manager['start'],
                        's' => $manager['start'],
                        'e' => $manager['end'],
                        'skip_type' => $mTime::TYPE_SKIP_FINE, // пропустил день
                        'type' => '',
                        'amount' => -$_price
                        ];
                $skip = $mTime::prepare($skip);
                $skips[$user_id][] = $skip;
            }

        }
        
        $bonuses = [];
        $filter = ['user_id' => $ids, 'date' => $day, 'creator_id' => 0];
        foreach($mBonus->getByList($filter) as $bonus){
            //if(! isset($bonuses[$bonus['user_id']])) $bonuses[$bonus['user_id']] = ['skip' => [], 'fine' => []];
            if(! isset($bonuses[$bonus['user_id']])) $bonuses[$bonus['user_id']] = [];
            if(empty($bonus['skip_time'])){
                $bonus = $mBonus->getSkipType($bonus);
                $data = ['id' => $bonus['id'], 'skip_time' => $bonus['skip_time'], 'skip_type' => $bonus['skip_type']];
                $mBonus->upsert($data); //
            }
            $bonuses[$bonus['user_id']][] = $bonus;
        }
        
        foreach($skips as $user_id => $s){
            $bonus = \Arr::get($bonuses, $user_id, []);
            $mBonus->checkSkipAmount($bonus, $s, $user_id); //insert -update
        }
        return $this->render('generate', []);
    }
    
     /**
     * @acesss (SALARY_SHEET)
     *
     * @Method (!AJAX)
     */
    public function setskipAction()
    {
        $query = $this->request->query->all();
        $month = \Arr::get($query, 'month', date('m'));
        $year = \Arr::get($query, 'year', date('Y'));
        $mBonus = $this->model('Bonus');
        foreach($mBonus->getByList(['month' => $month, 'year' => $year, 'creator_id' => 0]) as $bonus){
            if(empty($bonus['skip_time'])){
                $bonus = $mBonus->getSkipType($bonus);
                $data = ['id' => $bonus['id'], 'skip_time' => $bonus['skip_time'], 'skip_type' => $bonus['skip_type']];
                $mBonus->upsert($data);
            }
        }
    }
}