<?php
namespace Modules\Employee\Admin\Models;

use Modules\Employee\Admin\Models\EmployeePlan;
use Modules\Employee\Admin\Models\Bonus;
use Modules\Employee\Admin\Models\PlanSheet;
use Modules\Employee\Admin\Models\Plan;
use Modules\Employee\Admin\Extensions\Indicator;
use Modules\Employee\Admin\Extensions\Calendar;

/**
* Расчет за месяц
*/
class Calculate extends \Classes\Base\Model
{
    const OKLAD_ID = 10;
    
    protected $table = 'plan';
    
    public $users = []; 
    public $ids = [];
    
    public $start = NULL; // format time
    public $end = NULL; // format time
    public $start_day = NULL; // format time
    public $end_day = NULL; // format time
    public $mount = NULL; // number Year
    public $year = NULL; // number Year
    
    protected $esalary = NULL; //  базовая ставка или ставки на обучение
    
    public $allplans = []; // плановые показатели
    public $plans = []; // все  показатели пользователей у которых нет плана
    public $plan_sheets = []; // плановые показатели с планами
    public $base_plans = []; // плановые показатели
    public $users_plans = []; // плановые показатели
    
    public $tree = []; // сотрудники подразделений
    
    protected $bonus = FALSE; // бонусы и депремирования
    
    protected $format = "Y-m"; // Формат даты
    
    public $is_fact = false;
    
    /**
    * Рассчитать планы на выбранный месяц
    * 
    */
    public function init($month = NULL, $year = NULL, $date = NULL, $userId = NULL, $tree = null){
        if(! is_array($userId)) $userId = [$userId];
        $this->users = $this->setUsers($userId);
        $this->ids = array_keys($this->users);
        //установим период расчета плановых показателей
        $month = ! $month ? date('m') : $month ;
        $year = ! $year ? date('Y') : $year ;
        
        $this->start_day = $year.'-'.$month.'-01';
        $_date = new \DateTime($this->start_day);
        $_date->add(new \DateInterval('P1M'));
        $_date->sub(new \DateInterval('P1D'));
        $this->end_day = $_date->format('Y-m-d');
        
        $this->start = date($this->format, strtotime($this->start_day));
        $this->end = date($this->format, strtotime($this->end_day));
        $this->mount = intval($month);
        $this->year = intval($year);

        $plans = [];
        $baseplans = [];
        
        $this->plan_sheets = $this->getPlanSheets($this->ids);
        $this->allplans = $this->getPlanUsers($this->ids);
        $planAlias = [];
        $pids = [];
        foreach($this->allplans as $_plan){
            if(! isset($planAlias[$_plan['alias']])){
                $planAlias[$_plan['alias']] = ['users' => [],
                                               'departments' => [],
                                               'id' => $_plan['plan_id'],
                                               'is_common' => $_plan['is_common'],
                                               ];
            }
            if(0 == $_plan['is_plan_based']){
                if(! isset($plans[$_plan['user_id']])){
                    $plans[$_plan['user_id']] = [];
                }
                $_u_key = $_plan['plan_id'].'_'.intval($_plan['department_id']);
                $plans[$_plan['user_id']][$_u_key] = $_plan;
                if($_plan['user_id']){
                    $planAlias[$_plan['alias']]['users'][$_plan['user_id']] = $_plan['user_id'];
                }
                if($_plan['department_id']){
                    $planAlias[$_plan['alias']]['departments'][] = $_plan['department_id'];
                }
            }else{
                if(! isset($baseplans[$_plan['user_id']])){
                    $baseplans[$_plan['user_id']] = [];
                }
                $_u_key = $_plan['plan_id'].'_'.intval($_plan['department_id']);
                $baseplans[$_plan['user_id']][$_u_key] = $_plan;
                if(! empty($_plan['pid'])){
                    $pids[$_plan['plan_id']] = $_plan['pid'];
                }
                if(! empty($_plan['department_id'])){
                    $planAlias[$_plan['alias']]['departments'][] = $_plan['department_id'];
                }
                if(! empty($_plan['user_id'])){
                    $planAlias[$_plan['alias']]['users'][$_plan['user_id']] = $_plan['user_id'];
                }
            }
        }
        $help = $this->getHelpPlan();
        // прикрепим факт 
        if($tree){
            $tree = $this->getTree();
            $baskets = $this->addBasket($this->allplans, $tree); // все те кто в подразделении, но нет выставленного плана
            foreach($baskets as $basket){
                $_help = \Arr::get($help, $basket['plan_id'], null);
                if($_help){
                    $vplan = $_help;
                    $vplan['plan_id'] = $_help['id'];
                    $vplan['department_id'] = 0;
                    $vplan['user_id'] = $basket['user_id'];
                    $vplan['start'] = $basket['start'];
                    $vplan['end'] = $basket['end'];
                    $vplan['value'] = 0;
                    $vplan['ep_id'] = 0;
                    $vplan['hidden'] = 1;
                    $baseplans[$basket['user_id']][] = $basket;
                    if(empty($planAlias[$_help['alias']])){
                        $planAlias[$_help['alias']]['is_common'] = 0;
                    }
                    $planAlias[$_help['alias']]['users'][$basket['user_id']] = $basket['user_id'];
                    if(empty($planAlias[$_help['alias']]['departments'])){
                        $planAlias[$_help['alias']]['departments'] = [];
                    }
                }else{
                    echo 'NOT PLAN ';
                    print_r($basket);
                }
            }
        }
        $this->users_plans = $planAlias;
        $this->plans = $plans;
        $this->base_plans = $baseplans;
        $this->esalary = $this->getESalary($this->ids); // base rates
        //if(FALSE === $this->bonus){
            $this->bonus = $this->getBonus($this->ids);
        //}
        return $this;
    }
    
    public function _calculate($name = '', $user_ids = [],$departments = []){
        $nameCalculate = "Modules\\Employee\\Admin\\Extensions\\Plan\\".$name.'Plan';
        if(! class_exists($nameCalculate)){
            return [];
        }
        $calculate = new $nameCalculate($this->db, $this->user, $this->start_day, $this->end_day, $user_ids, $departments);
        return $calculate->calculate(); // return 
    }
    
    
    public function GetCurrentDayPercent(){
        $end = new \DateTime($this->end_day);
        $start = new \DateTime($this->start_day);
        $c = new \DateTime();
        $interval = $start->diff($c);
        $count = ! $interval->invert ? $interval->days : 0;
        $all = $end->format('d');
        $count = $count > $all ? $all : $count;
        return round($count/$all,2);
    }
    
    public function getTempo($c_day_percent = null, $_percent = null, $is_negative = false){
        if ($c_day_percent > $_percent){
            $tempo = -($c_day_percent-$_percent)*100;
        }
        else{
            $tempo = (-$c_day_percent+$_percent)*100;
        }
        return $tempo; 
        
        $t = $this->GetCurrentDayPercent();
        $is_negative = intval($is_negative);
        if($plan){
            if(! $is_negative){
                return round($fact/($plan * $t) * 100);
            }else{
                //((($_f-$_p/$c_count*$c_day)/$_p)*100)}
                return round($fact/($plan * $t) * 100);
            }
        }else{
            return 0;
        }
    }
    
    public function calculate($id = NULL){
        return \Arr::get($this->calculate_all(), $id, []);
    }
   
    /***
    * рассчет зарплаты за месяц
    **/
    public function calculate_all(){
        $users = [];
        $cdPercent = $this->GetCurrentDayPercent();
        $factAll = [];
        if(! $this->is_fact){
            foreach($this->users_plans as $alias => $dusers){
                $dusers['users'] = array_unique($dusers['users']);
                $dusers['departments'] = array_unique($dusers['departments']);
                $factAll[$alias] = $this->_calculate($alias, $dusers['users'], $dusers['departments']);
            }
        }
        foreach($this->ids as $id){
            $total = 0;
            $max = 0;
            $salary = [];
            $base = \Arr::path($this->esalary,$id.'.base',0);
            $plans = \Arr::get($this->plans, $id, []);
            $baseplans = \Arr::get($this->base_plans, $id, []);
            //Не плановые показатели
            foreach($plans as $_u_p_key => $plan){
                    $plan['u_key'] = $_u_p_key;
                    $_salary = $plan;
                    $_salary['value'] = \Arr::get($plan,'value', 0);
                    $_salary['plan'] = 0;
                    $_salary['fact'] = \Arr::get($factAll,$_salary['alias'], []);
                    $department_id = \Arr::get($plan,'department_id', 0);
                    $user_id = \Arr::get($plan,'user_id', 0);
                    if($plan['is_common']){
                        if($department_id){
                            $_salary['fact'] = \Arr::path($_salary['fact'], $department_id.'.cnt'); //department
                            if(empty($_salary['fact'])){
                                $_salary['fact'] = \Arr::path($_salary['fact'], 'cnt'); //department
                            }
                        }else{
                            $_salary['fact'] = \Arr::get($_salary['fact'], 'cnt'); //no department    
                        }
                        $key_sheet = $plan['plan_id'].($department_id ? '_'.$department_id : '');
                    }else{
                        $_salary['fact'] = \Arr::path($_salary['fact'], $id.'.cnt');
                        $key_sheet = $plan['plan_id'].($user_id ? '_'.$user_id : '').($department_id ? '_'.$department_id : '');
                    }
                    $sheet = \Arr::get($this->plan_sheets, $key_sheet, []);
                    if($sheet){
                        $_salary['plansheet_id'] =  $sheet['plansheet_id'];
                    }else{
                        if($plan['is_common']){
                            $sheet = \Arr::get($this->plan_sheets, $plan['plan_id'], []);
                            if($sheet){
                                $_salary['plansheet_id'] =  $sheet['plansheet_id'];
                            }
                        }
                    }
                    //
                    if($this->is_fact){
                        if($sheet){
                            $_salary['fact'] = $sheet['fact'];
                        }else{
                            $_salary['fact'] = 1;
                        }
                    }
                    
                    $_total = ($_salary['value'] * $_salary['fact']);
                    $_total = round($_total, -2); //округлить до рублей
                    
                    $_salary['total'] = $_total;
                    $_u_key = $_salary['plan_id'].'_'.$_salary['department_id'];
                    $salary[$_u_key] = $_salary;
                    $total = $total + $_total;
                    $max = $max + $_total; //теоритический максимум
            }
            
            $K = $K_MAX = 0;
            $is_plan_user = 0;
            $_summary_percent = 0;
            $_summaryValue = 0;
            
           //Плановые показатели
            foreach($baseplans as $_u_p_key => $plan){
                $plan['u_key'] = $_u_p_key;
                $department_id = \Arr::get($plan, 'department_id', 0);
                $user_id = \Arr::get($plan, 'user_id', 0);
                if(! $is_plan_user) $is_plan_user = 1;
                $_salary = $plan;
                $sheet = [];
                $_salary['plan'] = 0;
                if($plan['is_common']){
                    $key_sheet = $plan['plan_id'].($department_id ? '_'.$department_id : '');
                }else{
                    $key_sheet = $plan['plan_id'].($user_id ? '_'.$user_id : '').($department_id ? '_'.$department_id : '');    
                }
                $sheet = \Arr::get($this->plan_sheets, $key_sheet, []);
                if($sheet){
                    $_salary['plan'] =  $sheet['plan'];
                    $_salary['plansheet_id'] =  $sheet['plansheet_id'];
                }else{
                    if($plan['is_common']){
                        $sheet = \Arr::get($this->plan_sheets, $plan['plan_id'], []);
                        if($sheet){
                            $_salary['plansheet_id'] =  $sheet['plansheet_id'];
                        }
                    }    
                }
                $_users = \Arr::path($this->users_plans, $plan['alias'].'.users', []);
                $departments = \Arr::get($this->users_plans, $plan['alias'].'.departments', []);
                $_salary['value'] = \Arr::get($plan, 'value', 0);
                $_summaryValue += $_salary['value'];
                $_salary['fact'] = \Arr::get($factAll,$_salary['alias'], []);
                
                $fact = \Arr::get($_salary['fact'],'cnt',0);
                if(! $fact){
                    $fact = \Arr::path($_salary['fact'],$department_id.'.cnt',0);
                    if(! $fact){
                        $fact = \Arr::path($_salary['fact'],$id.'.cnt',0);    
                    }
                }
                //факт из plan_sheet
                if($this->is_fact){
                    if($sheet){
                        $fact = $sheet['fact'];
                    }
                }
                $_salary['fact'] = $fact;
                $_is_negative = 0;
                if($plan['is_negative'] == 1){
                    if($_salary['plan']){
                        $percent = 2 - $fact/$_salary['plan'];
                    }else{
                        $percent = 1;
                    }
                    $_is_negative = 1;
                }else{
                    if($_salary['plan']){
                        $percent = $fact / $_salary['plan'];
                    }else{
                        $percent = 0;
                    }
                }
                $percent = round($percent, 2);
                
                $_salary['tempo'] = $this->getTempo($cdPercent, $percent, $_salary['is_negative']);
                $_salary['summary_percent'] = ($percent) * ($_salary['value'] / 100) * 100;
                $_salary['summary_percent'] = round($_salary['summary_percent']); //ROUND 
                $_summary_percent += $_salary['summary_percent'];
                
                $_K = 0;
                if(0 < $cdPercent){
                    //$_K = $percent * $_salary['value']/(100/$cdPercent);
                    $_K = $percent * $_salary['value']/100/$cdPercent;
                }
                /*$_K = round($_K,3);*/
                
                $K1 = $K;
                $K = $K + $_K;
                $_salary['K'] = $K;
                /*max
                 */
                $max_percent = 1;
                $K_MAX = $K_MAX + $max_percent * $_salary['value']/(100/1);
                $salary[] = $_salary;
            }
            $total_plan = 0;
            if($is_plan_user){
                $_total = $_summaryValue ? ((atan($K * 3.5 - 3.5) * ($base * 0.5 ) * 2 /  pi() + $base) * $cdPercent) : 0;
                $_total = round($_total, -2); //округлить до рублей
                $total = $total + $_total;
                $_total_max = $_summaryValue ? ((atan($K_MAX * 3.5 - 3.5) * ($base * 0.5 ) * 2 /  pi() + $base) * 1) : 0;
                $max = $max + $_total_max;
                $total_plan = $_total;
            }
            // bonus
            
            $bonuses = \Arr::get($this->bonus, $id,[]);
            array_walk($bonuses,function(&$bonus){
                $bonus = Bonus::prepare($bonus);
                //$bonus['approved'] = Bonus::is_approved($bonus);
            });
            $total_bonus = 0;
            $total_skip = 0; // За пропуски
            $plus = 0;
            $lateness = []; // Опоздание
            $is_request = 0;
            $skip_time = 0;
            foreach($bonuses as $bonus){
                if(Bonus::is_approved($bonus)){
                    if($bonus['amount'] < 0){
                        $total_bonus += $bonus['amount']; // все депремирования
                    }else{
                        $plus += $bonus['amount'];
                    }
                    if(! empty($bonus['skip_time'])){
                        $skip_time += $bonus['skip_time'];
                    }
                }
                if($bonus['is_request']){
                    $is_request++;;
                }
            }
            
            $total_bonus = round($total_bonus, -2); //ROUND до рублей
            $plus = round($plus, -2); //ROUND до рублей
            $max = $max + $plus;
            $total = $total + $total_bonus + $plus;
            $total = round($total, -2); //ROUND до рублей
            $income = $total - $total_bonus;
            
            $esalary = \Arr::get($this->esalary, $id);
            $basic = $this->getByOkladUserId($plans, $baseplans, $esalary);
            $hour = $basic/(25*8);
            $basic_interval = $this->getByOkladUserIdInterval($plans, $baseplans, $esalary);
            
            $users[$id] = ['salary' => $salary,
                           'bonuses' => $bonuses,
                           'skip_time' => $skip_time,
                           'total' => $total, // с вычетом и учетом депремирований
                           'income' => $income, // без учета депремирований - заработал
                           'plus' => $plus, // Плюсы
                           'total_plan' => $total_plan, // только плановые
                           'total_bonus' => $total_bonus, // депремирования
                           'total_skip' => $total_skip,
                           'summary_percent' => $_summary_percent, // суммарный процент от всех плановых показателей
                           'max' => ($max > $total ? $max : $total),
                           'K' => $K,
                           'base' => $base,
                           'base_hour' => $hour,
                           'base_min' => $basic_interval['min'],
                           'base_max' => $basic_interval['max'],
                           'esalary' => $esalary,
                           'basic' => $basic,
                           'is_request' => $is_request, //Есть ли не обработанные запросы
                           ];
        }
        return $users;
    }
    
    /* получить фактические значения */
    public function getData($id){
        $user = [];
        $salary = [];
        $base = \Arr::path($this->esalary,$id.'.base',0);
        $plans = \Arr::get($this->plans, $id, []);
        $baseplans = \Arr::get($this->base_plans, $id, []);
        $total_nobase = 0;
        //Не плановые показатели
        foreach($plans as $plan){
            $_salary = $plan;
            $_salary['value'] = \Arr::get($plan,'value', 0);
            $_salary['plan'] = 0;
            $department_id = \Arr::get($plan,'department_id', 0);
            $user_id = \Arr::get($plan,'user_id', 0);
            if($plan['is_common']){
                $key_sheet = $plan['plan_id'].($department_id ? '_'.$department_id : '');
            }else{
                $key_sheet = $plan['plan_id'].($user_id ? '_'.$user_id : '').($department_id ? '_'.$department_id : '');
            }
            $sheet = \Arr::get($this->plan_sheets, $key_sheet, null);
            if(! $sheet){
                if($plan['is_common']){
                    $sheet = \Arr::get($this->plan_sheets, $plan['plan_id'], []);
                }
            }
            $_salary['fact'] = 0;
            if($sheet){
                $_salary['fact'] = \Arr::get($sheet, 'fact', 0);    
            }
            $_total = ($_salary['value'] * $_salary['fact']);
            $_total = round($_total, -2); //округлить до рублей
            $total_nobase += $_total;
            $_salary['total'] = $_total;
            $salary[] = $_salary;
        }
        $_summary_percent = 0;
       //Плановые показатели
        foreach($baseplans as $plan){
            $department_id = \Arr::get($plan, 'department_id', 0);
            $user_id = \Arr::get($plan, 'user_id', 0);
            $_salary = $plan;
            $_salary['plan'] = 0;
            if($plan['is_common']){
                $key_sheet = $plan['plan_id'].($department_id ? '_'.$department_id : '');
            }else{
                $key_sheet = $plan['plan_id'].($user_id ? '_'.$user_id : '').($department_id ? '_'.$department_id : '');    
            }
            $sheet = \Arr::get($this->plan_sheets, $key_sheet, 0);
            if(! $sheet){
                if($plan['is_common']){
                    $sheet = \Arr::get($this->plan_sheets, $plan['plan_id'], []);
                }    
            }
            $_salary['value'] = \Arr::get($plan, 'value', 0);
            $_salary['fact'] = 0;
            if($sheet){
                $_salary['fact'] = \Arr::get($sheet, 'fact', 0);
                $_salary['plan'] = \Arr::get($sheet, 'plan', 0);  
            }
            if($_salary['plan']){
                $_salary['percent'] = $_salary['fact'] / $_salary['plan'] * 100;
                $_salary['percent'] = round($_salary['percent'], 2);
            }else{
                $_salary['percent'] = 0;
            }
            $_salary['tempo'] = $this->getTempo($_salary['fact'], $_salary['plan']);
            $_salary['summary_percent'] = ($_salary['percent'] / 100) * ($_salary['value'] / 100) * 100;
            $_salary['summary_percent'] = round($_salary['summary_percent']); //ROUND 
            $_summary_percent += $_salary['summary_percent'];
            $salary[] = $_salary;
        }
        $bonuses = \Arr::get($this->bonus, $id,[]);
        array_walk($bonuses,function(&$bonus){
            $bonus = Bonus::prepare($bonus);
            //$bonus['approved'] = Bonus::is_approved($bonus);
        });
        $user = ['salary' => $salary,
                'base' => $base,
                'bonuses' => $bonuses,
                'summary_percent' => $_summary_percent,
                'esalary' => \Arr::get($this->esalary, $id),
                'total_nobase' => $total_nobase
                ];
        return $user;
    }
    
    /* получить фактические показатели*/
    public function getFact(){
        $factAll = [];
        foreach($this->users_plans as $alias => $dusers){
            $dusers['users'] = array_unique($dusers['users']);
            $dusers['departments'] = array_unique($dusers['departments']);
            $factAll[$alias] = $this->_calculate($alias, $dusers['users'], $dusers['departments']);
        }
        return $factAll;
    }
    
    public function getFactSheet(){
        $factAll = [];
        foreach($this->users_plans as $alias => $dusers){
            $dusers['users'] = array_unique($dusers['users']);
            $dusers['departments'] = array_unique($dusers['departments']);
            $_factAll = ["id" => $dusers["id"], "is_common" => $dusers["is_common"]];
            $_factAll['fact'] = $this->_calculate($alias, $dusers['users'], $dusers['departments']);
            $factAll[$alias] = $_factAll;
        }
        return $factAll;
    }
    
    
    /* обновить фактические показатели*/
    public function updateFact(){
        $PlanSheet = new PlanSheet($this->db, $this->user);
        $mPlan = new Plan($this->db, $this->user);
        $sheets = [];
        $planTypes = $mPlan->getByType();
        foreach($this->getPlanSheetLists() as $_sheet){
            $d_id = intval(\Arr::get($_sheet, 'department_id', 0));
            $p_id = \Arr::get($_sheet, 'sheet_plan_id', 0); //plan_id
            $u_id = \Arr::get($_sheet, 'user_id', 0);
            $t_id = \Arr::get($_sheet, 'sheet_type', $p_id); //type
            $s = \Arr::get($_sheet, 'date', 0);
            $unikey = $d_id.'-'.$p_id.'-'.$u_id.'-'.$s.'-'.$t_id;
            $sheets[$unikey] = $_sheet;
        }
        foreach($this->users_plans as $alias => $dusers){
            $dusers['users'] = array_unique($dusers['users']);
            $dusers['departments'] = array_unique($dusers['departments']);
            $_factAll = ["id" => $dusers["id"], "is_common" => $dusers["is_common"]];
            $_factAll['users'] = $dusers['users'];
            $_factAll['departments'] = $dusers['departments'];
            $_factAll['fact'] = $this->_calculate($alias, $dusers['users'], $dusers['departments']);
            if(! \Arr::get($_factAll['fact'], 'cnt', false)){
                foreach($_factAll['fact'] as $_id => $fact){
                    $p_id = \Arr::get($_factAll, 'id', 0);
                    $s = \Arr::get($fact, 'date', 0);
                    $s = date('Y-m-d', strtotime($s));
                    if(! empty($dusers["is_common"])){
                        $u_id = 0;
                        if(in_array($_id,$dusers['departments'])){
                            $d_id = $_id;
                        }else{
                            $d_id = 0;
                        }
                    }else{
                        $d_id = 0;
                        $u_id = $_id;
                    }
                    $key = ($d_id > 0 ? $d_id.'_' : '').$p_id;
                    $_t = \Arr::get($planTypes, $key, $p_id);
                    $unikey = $d_id.'-'.$p_id.'-'.$u_id.'-'.$s.'-'.$_t;
                    if($sheet = \Arr::get($sheets, $unikey, false)){
                        $_data = ['id' => $sheet['plansheet_id'],
                                  'fact_amount' => $fact['cnt'],
                                ];
                        $PlanSheet->upsert($_data);
                    }else{
                        $insertsheet = ['fact_amount' => $fact['cnt'],
                                        'date' => $s,
                                        'type' => $_t,
                                        'plan_id' => $p_id,
                                        'department_id' => $d_id,
                                        'manager_id' => $u_id,
                                        ];
                        $PlanSheet->insert($insertsheet);
                    }
                }
            }else{
                $d_id = $u_id = 0;
                $p_id = \Arr::get($_factAll, 'id', 0);
                $s = \Arr::path($_factAll, 'fact.date', 0);
                $s = date('Y-m-d', strtotime($s));
                $key = ($d_id ? $d_id.'_' : '').$p_id;
                $_t = \Arr::get($planTypes, $key, $p_id);
                $unikey = $d_id.'-'.$p_id.'-'.$u_id.'-'.$s.'-'.$_t;
                if($sheet = \Arr::get($sheets, $unikey, false)){
                    $_data = ['id' => $sheet['plansheet_id'],
                              'fact_amount' => $_factAll['fact']['cnt'],
                            ];
                    $PlanSheet->upsert($_data);
                }else{
                    $key = ($d_id ? $d_id.'_' : '').$p_id;
                    $insertsheet = ['fact_amount' => $_factAll['fact']['cnt'],
                                    'date' => $s,
                                    'type' => \Arr::get($planTypes, $key, $p_id),
                                    'plan_id' => $p_id,
                                    'department_id' => $d_id,
                                    'manager_id' => $u_id,
                                    ];
                    $PlanSheet->insert($insertsheet);
                }
            }
        }
        return;
    }
    
    public function getUser($id = NULL){
        return \Arr::get($this->users,$id, NULL);
    }
    
    /**
    * Установим пользователей
    **/
    public function setUsers(array $ids){
        $employees = [];
        $query = $this->db->newStatement("
            SELECT
                ed.*,
                ed.user_id as id
            FROM employee_data as ed
            WHERE ed.user_id IN (:ids:)
        ");
        if(! empty($ids)){
            $query->setArray('ids', $ids);
            $_employees = $query->getAllRecords();
            foreach($_employees as $_employee){
                $employees[$_employee['id']] = $_employee;
            }
        }
        return $employees;
    }
    
   
    /**
    * $ids - Плановые показатели по текущему месяцу
    **/
    public function getPlanSheets(array $ids){
        $sheets = $where = $params = [];
        $where['date'] = "ps.date = (:date:)";
        $params['date'] = $this->start_day;
       
        if($ids){
           $where['users'] = "ps.manager_id IN (:users:)";
           $ids[] = 0; // общие плановые показатели
           $params['users'] = $ids; 
        }
        $where = ! empty($where) ? "WHERE ".implode(' AND ',$where) : '';
        $query = $this->db->newStatement("
        SELECT
            p.*,
            ps.plan_id AS plan_id,
            ps.id AS plansheet_id,
            ps.manager_id AS user_id,
            ps.plan_amount AS plan,
            ps.fact_amount AS fact,
            ps.department_id AS department_id,
            ps.date
        FROM plan_sheet ps
        LEFT JOIN plan p ON ps.plan_id = p.id
        {$where}
            ");
        $query->bind($params);
        foreach($query->getAllRecords() as $sheet){
            $d_id = \Arr::get($sheet, 'department_id', 0);
            $u_id = \Arr::get($sheet, 'user_id', 0);
            $p_id = \Arr::get($sheet, 'plan_id', 0);
            $key = $p_id.($u_id ? '_'.$u_id : '').($d_id ? '_'.$d_id : ''); // plan_id _ user_id _ department_id
            $sheets[$key] = $sheet;
            if(! $this->is_fact){
                if(\Arr::get($sheet, 'fact', false)){
                    $this->is_fact = true;
                }
            }
        }
        return $sheets;
    }
    
    // Для обновления факт
    public function getPlanSheetLists($ids = []){
        $where = [];
        $params = [];
        $where['date'] = "ps.date = (:date:)";
        $params['date'] = $this->start_day;
        if(! empty($ids)){
           $where['users'] = "ps.manager_id IN (:users:)";
           $ids[] = 0; // общие плановые показатели
           $params['users'] = $ids; 
        }
        $where = ! empty($where) ? "WHERE ".implode(' AND ',$where) : '';
        $query = $this->db->newStatement("
        SELECT
            p.*,
            ps.id AS plansheet_id,
            ps.manager_id AS user_id,
            ps.plan_id AS sheet_plan_id,
            ps.type AS sheet_type,
            ps.plan_amount AS plan,
            ps.department_id AS department_id,
            ps.date
        FROM plan_sheet ps
        LEFT JOIN plan p ON ps.plan_id = p.id
        {$where}
        ORDER BY p.id");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    
    // все планы пользователей
    public function getPlanUsers(array $ids){
        $plans = [];
        $where = [];
        $params = [];
        if($ids){
           $where['users'] = "ep.user_id IN (:users:)";
           $params['users'] = $ids; 
        }
        $where['start'] = "(
                (ep.start >=  :start: AND ep.end <= :end:)
                OR
                (ep.start <= :start: AND ep.end >= :start:)
                OR
                (ep.start <= :start: AND ep.end IS NULL)
                OR
                (ep.start >= :start: AND ep.start <= :end: AND ep.end IS NULL)
                )";
        $params['start'] = $this->start_day;
        $params['end'] = $this->end_day;
        
        $where = ! empty($where) ? "WHERE ".implode(' AND ',$where) : '';
        $query = $this->db->newStatement("
        SELECT
            p.is_plan_based,
            p.is_negative,
            p.is_common,
            p.name,
            p.alias,
            p.is_discrete,
            p.measurement,
            p.pid,
            ep.id as ep_id,
            ep.plan_id,
            ep.user_id,
            ep.department_id,
            ep.value,
            ep.start,
            ep.end
        FROM employee_plan ep
        LEFT JOIN plan p ON p.id = ep.plan_id
        {$where}
            ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    /**
    * Базовые ставки пользователей
    **/
    public function getESalary(array $ids){
        $esalarys = [];
        $_esalary = new EmployeeSalary($this->db, $this->user);
        $esalary = $_esalary->getByList(['users' => $ids, 'start' => $this->start_day, 'end' => $this->end_day]);
        foreach($esalary as $es){
            $esalarys[$es['user_id']] = $es;    
        }
        return $esalarys;
    }
    
    public function getTree(array $ids = []){
        $tree = ['departments' => [], 'users' => []];
        $mEmployee = new Employee($this->db, $this->user);
        $dfilter = ['users' => $ids, 'start' => $this->start_day, 'end' => $this->end_day];
        $employees_department = $mEmployee->getByList($dfilter);
        foreach($employees_department as $ed){
            if(! isset($tree['departments'][$ed['department_id']])) $tree['departments'][$ed['department_id']] = [];
            $tree['departments'][$ed['department_id']][$ed['id']] = $ed['id'];
            if(! isset($tree['users'][$ed['id']])) $tree['users'][$ed['id']] = [];
            $tree['users'][$ed['id']][$ed['department_id']] = $ed['department_id'];
        }
        return $tree;
    }
    
    /**
    * Получим факт сотрудников подразделения
    */
    public function addBasket(array $eplans, array $_tree){
        $baskets = []; //add basket employee
        
        $personals = [];
        $commons = [];
        $usersplans = [];
        $departmentsplans = [];
        foreach($eplans as $row){
            $user_id = $row['user_id'];
            $plan_id = $row['plan_id'];
            if($row['is_common']){
                $departent_id = $row['department_id'];
                if(! isset($commons[$plan_id]))   $commons[$plan_id] = [];
                $commons[$plan_id][$departent_id] = $row;
                if(! isset($departmentsplans[$departent_id])) $departmentsplans[$departent_id] = [];
                if(! isset($departmentsplans[$departent_id][$plan_id])) {
                    $row['_users'] = [];
                    $departmentsplans[$departent_id][$plan_id] = $row;
                }
                $departmentsplans[$departent_id][$plan_id]['_users'][$row['user_id']] = $row['user_id'];
            }else{
                if(! isset($personals[$user_id]))   $personals[$user_id] = [];
                $personals[$user_id][$plan_id] = $row;
                if(! isset($usersplans[$plan_id]))   $usersplans[$plan_id] = [];
                $usersplans[$plan_id][$user_id] = $row;
            }
        }
        /*array_walk($tree, function(&$d, $d_id) use ($departmentsplans, $usersplans, $personals){*/
        foreach($_tree['departments'] as $d_id => $d){
            $d_plans = [];
            $users = $d;
            $u = $d; //all user
            
            $dp = \Arr::get($departmentsplans, $d_id, []);
            foreach($dp as $_plan_id => $common){
                if($common['pid']){
                    $personal = \Arr::get($usersplans, $common['pid'], []);
                    $personal = array_intersect_key($personal, $users);
                    $u = array_diff($u, array_keys($personal));
                }else{
                    
                }
                $intersect = array_intersect_key($users, $common['_users']);
                if(!empty($intersect)){
                    $common['intersect'] = $intersect;
                    $d_plans[] = $common;
                }elseif($d_id == 0){
                    $d_plans[] = $common;
                }
            }
            foreach($u as $u_id){
                // нет планов в явном виде - запихиваем их во все общие
                foreach($d_plans as $key => $_common){
                    if(0 < $_common['pid']){
                        $baskets[] = [
                        'plan_id' => $_common['pid'],
                        'user_id' => $u_id,
                        'department_id' => $d_id,
                        'start' => $_common['start'],
                        'end' => $_common['end'],
                        ];
                    }
                }
            }
        }
        //});
        return $baskets;
    }
    
    
    /**
    * $ids - бонусы пользователей
    **/
    public function getBonus(array $ids){
        $bonuses = [];
        $bonus = new Bonus($this->db, $this->user);
        foreach($bonus->getByList(['month' => $this->mount, 'year' => $this->year, 'user_id' => $ids]) as $bonus){
            if(! isset($bonuses[$bonus['user_id']])){
                $bonuses[$bonus['user_id']] = [];
            }
            $bonuses[$bonus['user_id']][] = $bonus;
        }
        return $bonuses;
    }
    
    public function setBonus($_bonuses){
        $bonuses = [];
        foreach($_bonuses as $bonus){
            if(! isset($bonuses[$bonus['user_id']])){
                $bonuses[$bonus['user_id']] = [];
            }
            $bonuses[$bonus['user_id']][] = $bonus;
        }
        $this->bonus = $bonuses;
        return $this;
    }
    
    public function getHelpPlan(){
        $plan = new Plan($this->db, $this->user);
        return $plan->getByList();
    }
    
    
    public function getByOkladUserId($plans, $baseplans, $esalary){
        $return = $oklad = $base = 0;
        $is_plan = $is_job = $is_oklad = false;
        if(null != $esalary){
            $base = \Arr::get($esalary, 'base', 0);
        }
        foreach($plans as $_plan){
            if(self::OKLAD_ID == $_plan['plan_id']){
                $oklad = $_plan['value'];
                $is_oklad = 1;
            }else{
                if(1 == $_plan['is_plan_based']){
                    $is_plan = 1;
                }else{
                    if(0 < $_plan['is_discrete']){
                        $is_job = 1;
                    }
                }
            }
        }
        if(count($baseplans) > 0){
            $is_plan = 1;
        }
        if($is_oklad){
            if($is_plan){
                $return = $oklad+$base;
            }else{
                $return = $oklad;
            }
        }else{
            if($is_plan){
                $return = $base;
            }else{
                $return = $base;
            }
        }
        return $return;
    }
    
    public function getByOkladUserIdInterval($plans, $baseplans, $esalary){
        $oklad = $base = 0;
        $return = ['min' => 0, 'max' => 0];
        
        $is_plan = $is_job = $is_oklad = false;
        if(null != $esalary){
            $base = \Arr::get($esalary, 'base', 0);
        }
        foreach($plans as $_plan){
            if(self::OKLAD_ID == $_plan['plan_id']){
                $oklad = $_plan['value'];
                $is_oklad = 1;
            }else{
                if(1 == $_plan['is_plan_based']){
                    $is_plan = 1;
                }else{
                    if(0 < $_plan['is_discrete']){
                        $is_job = 1;
                    }
                }
            }
        }
        if(count($baseplans) > 0){
            $is_plan = 1;
        }
        if($is_oklad){
            if($is_plan){
                $return['min'] = $oklad+round(0.5*$base);
                $return['max'] = $oklad+round(1.5*$base);
            }else{
                $return['min'] = $oklad;
                $return['max'] = $oklad;
            }
        }else{
            if($is_plan){
                $return['min'] = round(0.5*$base);
                $return['max'] = round(1.5*$base);
            }else{
                $return['min'] = round(0.5*$base);
                $return['max'] = round(1.5*$base);
            }
        }
        return $return;
    }
    
    
    
    /**
    * Подгототовим данные для плановых показателей
    * по месяцам
    *
    * plan_id = []
    **/
    public function preperePlanSheets(){
        $plan_sheets = [];
        foreach($this->plan_sheets as $plan_sheet){
            if(! isset($plan_sheets[$plan_sheet['id']])){
                $plan_sheets[$plan_sheet['id']] = [];
            }
            if($plan_sheet['is_common']){
                $plan_sheets[$plan_sheet['id']] = $plan_sheet;
            }else{
                $plan_sheets[$plan_sheet['id']][$plan_sheet['user_id']] = $plan_sheet;
            }
            
        }
        return $plan_sheets;
    }
}


?>