<?php
/**
* Управление планами пользователей
*
**/
namespace Modules\Employee\Admin\Controllers;
use Modules\Employee\Admin\Extensions\Calculate;
use Modules\Employee\Admin\Extensions\Calendar;


class PlansheetController extends InitController
{
    public $menu = 'plansheet';
    
    /**
     * @param integer $id
     *
     * @Method (!AJAX)
     */
    public function indexAction()
    {
        if(! $this->is_allow){
            if(! $this->is_chief){
                $this->forbidden();
            }else{
                return $this->getPlanList($this->is_chief);        
            }
        }
        return $this->getPlanList();
    }
    
    protected function getPlanList($chief = null)
    {
        //models
        $plansheet = $this->model('PlanSheet');
        
        //filter
        $filter = $this->form('PlanSheetFilter');
        $filter->setData($this->request->query->all());
        $filter = $filter->init();
        $period = Calendar::getPeriod($plansheet->getMaxPeriod());
        
        //add Plan Sheet current mount
        $uids = [];
        $dis = [];
        $departments = []; //плановые показатели
        $allowdepartments = [];
        if($chief){
            $tree = [];
            $dism = [];//только мое поздразделение
            foreach($this->model('Department')->getChildren($chief) as $_d){
                $tree[$_d['lkey']]  = $_d;
            }
            foreach($tree as $d_p){
                $dism[$d_p['id']] = $d_p['id'];
            }
            //add department chief
            $chief_department = $this->model('EmployeePlan')->getDepartments($this->user->id, []);
            $main_tree = $this->model('Department')->getList(['id' => $chief_department]);
            foreach($main_tree as $_d){
                $tree[$_d['lkey']]  = $_d;
            }
            foreach($tree as $d_p){
                $dis[$d_p['id']] = $d_p['id'];
            }
            $allowdepartments = $dis;
            $filters = ['department_id' => $dism];
            foreach($this->model('EmployeeData')->getByList($filters) as $_employee){
                $uids[$_employee['user_id']] = $_employee['user_id'];
            }
            $allowusers = $uids;
        }else{
            $tree = $this->model('Department')->getTree();
            unset($tree[0]);
        }
        
        $dfilter = $filter->getData(['start', 'end']);
        if($chief){
            $dfilter['users'] = $uids;   
        }

        $_start = $filter->getData('start');
        $_end = $filter->getData('end');
        $employees_department = $this->model('Employee')->getByList($dfilter, $_start);
        $user_ids = [];
        $_tree = [];
        foreach($employees_department as $_user){
            $user_ids[$_user['id']] = $_user['id'];
            
            $d_id = $_user['department_id'];
            if(! empty($d_id)){
                if(! isset($_tree[$d_id])) $_tree[$d_id] = [];
                $_tree[$d_id][$_user['id']] = $_user;
            }
        }
        array_walk($tree, function(&$_d) use ($_tree){
            $_d['users'] = \Arr::get($_tree, $_d['id'], []);
        });
        
        //add
        $form = $this->form('PlanSheetMonth');
        $form->setData($this->request->query->all());
        $date = $form->getData();

        
        $c_month = date('m');
        $c_year = date('Y');
        $month = \Arr::get($date, 'month', $c_month);
        $year = \Arr::get($date, 'year', $c_year);
        $filter->setData("month", $month);
        $filter->setData("year", $year);
                
        // Планы сотрудников
        //$employee_plans = $plansheet->getListEplans($month, $year, false, ($chief ? $dis : false));
        $employee_plans = $plansheet->getListEplans($_start, $_end, false, ($chief ? $dis : false));
        $allusers = $this->model('EmployeeData')->getByList([],$_start);
        $tree = $plansheet->glue($employee_plans, $tree, $allusers);
        if($chief){
            foreach($tree as $_k => $_d){
                if(! in_array($_d['id'],$allowdepartments)){
                    if(in_array($_k, ['1000_v2', '1000_v3', '1000_v1', 0])){
                        unset($tree[$_k]);    
                    }
                }
            }
        }
        ksort($tree);
        $start_sheet = $year.'-'.$month.'-01';
        $p = Calendar::getPeriodMonth($start_sheet);
        $end_sheet = $p['end']; 
        $date = ['start' => $start_sheet,
                 'end' => $end_sheet,
                ];
        
        // Fact current
        $calculate = $this->model('Calculate')->init($month, $year, NULL, $user_ids, true);
        $facts = [];
        foreach($calculate->getFactSheet() as $_alias => $_facts){
            if(! \Arr::path($_facts, 'fact.cnt', null)){
                foreach($_facts['fact'] as $_fact){
                    $_u_id = \Arr::get($_fact, 'user_id', 0);
                    $_d_id = \Arr::get($_fact, 'department_id', 0);
                    $_common = \Arr::get($_facts, 'is_common', 0);
                    $_p_id = \Arr::get($_facts, 'id', 0);
                    if(! $_common){//личный
                        $key = $_u_id.'_'.$_p_id;
                    }else{
                        $key = ($_d_id ? $_d_id.'_' : '').$_p_id;
                    }
                    $fact = \Arr::get($_fact, 'cnt', null);
                    $facts[$key] = $fact;
                }
            }else{
                $_p_id = \Arr::get($_facts, 'id', 0);
                $fact = \Arr::path($_facts, 'fact.cnt', null);
                $facts[$_p_id] = $fact;
            } 
        }
        //Заполнить форму 
        $sheets = $plansheet->getByStatistic($date); // Показатели на выбранную 
        $is_current_mount = false;
        if(! empty($sheets)){
            $form->init($sheets);
            if($c_month == $month and $c_year == $year){
                $is_current_mount = true;        
            }
        }else{
            $form->init();
        }

        /**данные*/
        $sheets_info = $plansheet->getByInfo([$filter->getData('start'), $filter->getData('end')], $form->getData('start')); // данные
        $helpers = \Arr::get($sheets_info,'helpers',[]);
        if($helpers)  unset($sheets_info['helpers']);
        
        $call_helpers = \Arr::get($sheets_info,'call_helpers',[]);
        if($call_helpers)  unset($sheets_info['call_helpers']);
        
        $info = []; $cstamp = strtotime($form->getData('start'));
        foreach($sheets_info as $col => $sheet){
            $stamp = strtotime($col);
            if($stamp != $cstamp){
                $info[$stamp] = $sheet;
            }
        }
        
        // Plan Org - All
        $allyear = $filter->getData('allyear', null);
        // текущие планы организации
        $mPlanOrg = $this->model('PlanOrg');
        $callOrg = $mPlanOrg->getByDate($year.'-'.$month.'-01');
        if(null == $callOrg){
            if(! $allyear){
                $allyear = ($year-1).'-'.$month.'-01';
            }
            $cplanorg = ['date' => $year.'-'.$month.'-01', 'guiding_year' => $allyear, 'owner_id' => OWNER_ID];
            $c_id =  $mPlanOrg->insert($cplanorg);
            $callOrg = $mPlanOrg->getById($c_id);
        }else{
            if($allyear = \Arr::get($callOrg, 'guiding_year', null)){
                $filter->setData('allyear', $allyear);
            }
        }
        $factOrg = $mPlanOrg->getByFact($year.'-'.$month.'-01');
        if($factOrg){
            $factOrg = \Arr::get($factOrg, 'profit', 0);
        }
        if ($this->request->is('POST')) {
            $filter->handle($this->request->post);
            $isfilter = $this->request->post->getAsArray('PlanSheetFilter');
            if($isfilter){
                if ($filter->validate()) {
                    if($allyear = $filter->getData('allyear', null)){
                        $uporg = ['id' => $callOrg['id'], 'guiding_year' => $allyear];
                        $mPlanOrg->upsert($uporg);
                    }
                    $query = $filter->buildQuery();
                    $this->redirect('/admin/employee/plansheet/'.$query);
                }
            }
        }
        
        $all = $all_col = [];
        
        $help_percent = 1;
        $call_o_1 = 0;
        $call_o_2 = 0;
        $_h = $plansheet->getDatehelperOrg($year.'-'.$month.'-01', $allyear); //даты год назад - месяц назад

        $prev_org = null;
        $periodplanorg = strtotime(($year-3).'-01-01');
        foreach($plansheet->getByOrg($year.'-'.$month.'-01') as $_date => $_all){// данные из баланса
            $stamp = strtotime($_date);
            $all[$stamp] = $_all;
            $_year = date('Y', $stamp);
            $_date_all = date('Y-m-d', $stamp);
            if($stamp > $periodplanorg){
                if(! isset($all_col[$_year])) $all_col[$_year] = [];
                $all_col[$_year][] = $stamp;
            }
            if($_date_all == $_h['call1']){
                $call_o_1 = $_all['profit'];
            }
            if($_date_all == $_h['call2']){
                $call_o_2 = $_all['profit'];
            }
        }
        if($call_o_1 >0){
          $help_percent = $call_o_2/$call_o_1;   
        }
        
        $mOrg = $this->model('PlanOrg');
        $prev_org = null;
        foreach($all as $stamp => $_all){
            $_all['help'] = $mOrg->getCalculate($stamp, $_all, $all);
            $_all['prev'] = $prev_org;
            $all[$stamp] = $_all;
            $prev_org = $_all['profit'];
        }
        
        $help_percents = [];
        foreach($info as $_key => $_info){
            $_y = date('Y', $_key);
            $date = date('Y-m-d', $_key);
            $_all = \Arr::get($all, $_key, []);
            $allyear = \Arr::get($_all, 'guiding_year', $_y - 1);
            $_h = $plansheet->getDatehelperOrg($date, $allyear);
            $_stamp1 = strtotime($_h['call1']);
            $_stamp2 = strtotime($_h['call2']);
            $call_o_1 = \Arr::path($all, $_stamp1.'.profit', 0);
            $call_o_2 = \Arr::path($all, $_stamp2.'.profit', 0);
            if($call_o_1 >0){
                $help_percents[$_key] = $call_o_2/$call_o_1;
            }else{
                $help_percents[$_key] = 1;
            }
        }
        
        
        $count_year = [1,2,3];
        $count_mouth = [2,3,4,5,6,7,8,9,10,11,12];
        array_walk($count_year, function(&$_year){
            $_year = ['id' => $_year,
                      'name' => $_year.' год'];
        });
        array_walk($count_mouth, function(&$_mouth){
            $_mouth = ['id' => $_mouth,
                      'name' => $_mouth.' месяц'];
        });
        $months = range(1,12);
        
        $_c_day = time() - strtotime($year.'-'.$month.'-01');
        $_c_day = round($_c_day /(24*60*60));
        $count = date('t', strtotime($year.'-'.$month.'-01'));
        $c_percent = $_c_day < $count ? $_c_day/$count : 1;
        
        $days_percent =  $calculate->GetCurrentDayPercent();

        foreach($tree as $_d_key => $_department){
            foreach($_department['plans'] as $_p_key => $_plans){
                $key_sheet = "common_".(! empty($_plans['department_id']) ? $_plans['department_id'].'_' : '').$_plans['plan_id'];
                $helper = \Arr::get($helpers, $key_sheet, 0);
                $_c_common = $plansheet->getHelpInfoAll($help_percent,$helper);
                $s = 0;
                $tree[$_d_key]['plans'][$_p_key]['calculate'] = $_c_common;
                if(! empty($_plans['users'])) {
                    foreach ($_plans['users'] as $_u_key => $_u_plan) {
                        $key_sheet = \Arr::get($_u_plan, 'key', 0);
                        $helper = \Arr::get($helpers, $key_sheet, 0);
                        $u_calculate = $plansheet->getHelpInfoAll($help_percent, $helper);
                        $tree[$_d_key]['plans'][$_p_key]['users'][$_u_key]['calculate'] = $u_calculate;
                        $_plans['users'][$_u_key]['calculate'] = $u_calculate;
                        if(empty($_u_plan['end'])) {
                            $s += $u_calculate; //рассчетная сумма
                        }
                    }
                }
                if($_c_common > $s){
                    //echo $_plans['name'] .':'. $_c_common.'='.$s.'<br />';
                    //раскидать
                    $_dif_plan = $_c_common - $s;
                    //личный_план_k = личный_план_k * (общий_план/сумма(личный_план_n)-1)
                    $_s = 0;
                    if(! empty($_plans['users'])) {
                        foreach ($_plans['users'] as $_u_key => $_u_plan) {
                            $u_calculate = \Arr::get($_u_plan, 'calculate', '');
                            //$u_calculate = round($u_calculate + $u_calculate/($s)*$_dif_plan);
                            if($s > 0) {
                                $u_calculate = round($u_calculate * $_c_common / $s, -2);
                            }else{
                                $u_calculate =  0;
                            }
                            if(empty($_u_plan['end'])) {
                                $_s += $u_calculate;
                                $tree[$_d_key]['plans'][$_p_key]['users'][$_u_key]['calculate'] = $u_calculate;
                            }
                        }
                        $tree[$_d_key]['plans'][$_p_key]['calculate'] = $_s;
                    }
                }
            }
        }

        return $this->render('plansheet/list', [
            //'cdepartment' => $department,
            'departments' => $tree,
            'tree' => $tree,
            'form' => $form->createBuilder(),
            'menu' => $this->menu,
            'info' => $info,
            'info_col' => array_keys($info),
            'all' => $all,
            'all_col' => $all_col,
            'months' => $months, // 
            'period' => $period, // период для фильтра
            'filter' => $filter->createBuilder(),
            'count_year' => $count_year,
            'count_mouth' => $count_mouth,
            'plansheet' => $plansheet, //планы
            'helpers' => $helpers, //подсказки из предыдущих годов
            'facts' => $facts, // факт из plansheet
            'call_helpers' => $call_helpers,
            'cmonth' => $month,// текущий месяц
            'cyear' => $year, // текущий год
            'cstamp' => strtotime($year.'-'.$month.'-01'), // текущий stamp
            'c_percent' => $c_percent,
            'c_day' => $_c_day < $count ? $_c_day : $count,
            'c_count' => $count,
            'c_day_percent' => $days_percent,
            'help_percent' => $help_percent,
            'help_percents' => $help_percents,
            'factOrg' => $factOrg, //факт баланс в текущем месяце
            'is_chief' => $this->is_chief,
            'is_rule' => $this->is_allow,
            'allowdepartments' => $allowdepartments,
            'is_bookkeper' => $this->is_bookkeper,
            'timeout' => $this->timeout,
        ]);
    }
    
    /**
     * @acesss (SALARY_SHEET)
     *
     * @Method (!AJAX)
     */
    public function departmentAction($id)
    {
        //models
        $plansheet = $this->model('PlanSheet');
        
        //filter
        $filter = $this->form('PlanSheetFilter');
        $filter->setData($this->request->query->all());
        $filter = $filter->init();
        $period = Calendar::getPeriod($plansheet->getMaxPeriod());
        
        //add Plan Sheet current mount
        $departments = []; //плановые показатели
        $tree = $this->model('Department')->getTree();
        unset($tree[0]);
        
        $dfilter = $filter->getData(['start', 'end']);
        $dfilter['department_id'] = $id;
        $employees_department = $this->model('Employee')->getByList($dfilter);
        $department = $this->model('Department')->getById($id);
        
        $user_ids = [];
        foreach($employees_department as $_user){
            $user_ids[] = $_user['id'];
        }
        array_walk($tree, function(&$_d) use ($id, $employees_department){
            $_d['users'] = ($_d['id'] == $id) ? $employees_department : [];
        });
        
        //add
        $form = $this->form('PlanSheetMonth');
        $form->setData($this->request->query->all());
        $date = $form->getData();
        
        $c_month = date('m');
        $c_year = date('Y');
        $month = \Arr::get($date, 'month', $c_month);
        $year = \Arr::get($date, 'year', $c_year);
        $filter->setData("month", $month);
        $filter->setData("year", $year);
                
        // Планы сотрудников
        $employee_plans = $plansheet->getListEplans($month, $year);
        $allusers = $this->model('EmploeeData')->getByList();
        $tree = $plansheet->glue($employee_plans, $tree, $allusers);
        
        $start_sheet = $year.'-'.$month.'-01';
        $p = Calendar::getPeriodMonth($start_sheet);
        $end_sheet = $p['end']; 
        $date = ['start' => $start_sheet,
                 'end' => $end_sheet,
                ];
        
        // Fact current
        $calculate = $this->model('Calculate')->init($month, $year, NULL, $user_ids, true);
        $facts = [];
        foreach($calculate->getFactSheet() as $_facts){
            foreach($_facts['fact'] as $_fact){
                $_u_id = \Arr::get($_fact, 'user_id', 0);
                $_d_id = \Arr::get($_fact, 'department_id', 0);
                $_common = \Arr::get($_facts, 'is_common', 0);
                $_p_id = \Arr::get($_facts, 'id', 0);
                if(! $_common){//личный
                    $key = $_u_id.'_'.$_p_id;
                }else{
                    $key = ($_d_id ? $_d_id.'_' : '').$_p_id;
                }
                $facts[$key] = \Arr::get($_fact, 'cnt');
            }
        }
        
        $sheets = $plansheet->getByStatistic($date); // Показатели на выбранную 
        $is_current_mount = false;
        if(! empty($sheets)){
            $form->init($sheets);
            if($c_month == $month and $c_year == $year){
                $is_current_mount = true;        
            }
        }else{
            $form->init();
        }

        if ($this->request->is('POST')) {
            $filter->handle($this->request->post);
            $isfilter = $this->request->post->getAsArray('PlanSheetFilter');
            if($isfilter){
                if ($filter->validate()) {
                    $query = $filter->buildQuery();
                    $this->redirect('/admin/employee/plansheet/department/'.$id.$query);
                }
            }
        }
        /**данные*/
        $sheets_info = $plansheet->getByInfo([$filter->getData('start'), $filter->getData('end')], $form->getData('start'),$filter->getData('count_year',1), $filter->getData('count_month',2)); // данные
        $helpers = \Arr::get($sheets_info,'helpers',[]);
        if($helpers)  unset($sheets_info['helpers']);
        
        $call_helpers = \Arr::get($sheets_info,'call_helpers',[]);
        if($call_helpers)  unset($sheets_info['call_helpers']);
        
        $info = []; $cstamp = strtotime($form->getData('start'));
        foreach($sheets_info as $col => $sheet){
            $stamp = strtotime($col);
            if($stamp != $cstamp){
                $info[$stamp] = $sheet;
            }
        }
        ksort($tree);
        
        // Plan Org - All
        $allyear = $filter->getData('allyear', null);
        if($allyear){
            $allyear = ($year - $allyear)*12;
        }else{
            $allyear = 24; // 2 year
        }
        $all = $all_col = [];
        
        $help_percent = 1;
        $call_o_1 = 0;
        $call_o_2 = 0;
        $_h = $plansheet->getDatehelperOrg($year.'-'.$month.'-01', $allyear); //даты год назад - месяц назад
        $prev_org = null;
        foreach($plansheet->getByOrg($year.'-'.$month.'-01', 36) as $_date => $_all){// данные из баланса
            $stamp = strtotime($_date);
            $all[$stamp] = $_all;
            $_year = date('Y', $stamp);
            $_date_all = date('Y-m-d', $stamp);
            if($prev_org !== null){
                if(! isset($all_col[$_year])) $all_col[$_year] = [];
                $stamp['prev'] = $prev_org;
                $all_col[$_year][] = $stamp;
            }
            $prev_org = $stamp['profit'];

            if($_date_all == $_h['call1']){
                $call_o_1 = $_all['profit'];
            }
            if($_date_all == $_h['call2']){
                $call_o_2 = $_all['profit'];
            }
        }
        if($call_o_1 >0){
          $help_percent = $call_o_2/$call_o_1;   
        }
        
        $count_year = [1,2,3];
        $count_mouth = [2,3,4,5,6,7,8,9,10,11,12];
        array_walk($count_year, function(&$_year){
            $_year = ['id' => $_year,
                      'name' => $_year.' год'];
        });
        array_walk($count_mouth, function(&$_mouth){
            $_mouth = ['id' => $_mouth,
                      'name' => $_mouth.' месяц'];
        });
        $months = range(1,12);
        
        $_c_day = time() - strtotime($year.'-'.$month.'-01');
        $_c_day = round($_c_day /(24*60*60));
        $count = date('t', strtotime($year.'-'.$month.'-01'));
        $c_percent = $_c_day < $count ? $_c_day/$count : 1;
    
        return $this->render('plansheet/department', [
            'cdepartment' => $department,
            'departments' => $tree,
            'tree' => $tree,
            'form' => $form->createBuilder(),
            'menu' => $this->menu,
            'info' => $info,
            'info_col' => array_keys($info),
            'all' => $all,
            'all_col' => $all_col,
            'months' => $months,
            'period' => $period,
            'filter' => $filter->createBuilder(),
            'count_year' => $count_year,
            'count_mouth' => $count_mouth,
            'plansheet' => $plansheet,
            'helpers' => $helpers,
            'facts' => $facts,
            'call_helpers' => $call_helpers,
            'cmonth' => $month,
            'cyear' => $year,
            'c_percent' => $c_percent,
            'help_percent' => $help_percent,
        ]);
    }
    
   
    /**
    * @acesss (SALARY_SHEET)
    *
    * @Method (AJAX)
    */
    public function addAction($id)
    {
        $form = $this->form('EmployeePlanEdit');
        $form->setData('user_id', $id);
        $plans = $this->model('Plan')->getByList();
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            $form->setData('updated', date("Y-m-d H:i:s"));
            $form->setData('created', date("Y-m-d H:i:s"));
            $form->setData('creater', $this->user->id);
            $form->setData('updater', $this->user->id);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $plan = $form->save();
                return [
                    'plan' => $plan,
                ];
            }
        }
        else {
            return $this->renderPartial('eplans/edit', [
                'form' => $form->createBuilder(),
                'plans' => $plans,
                'plan' => NULL
            ]);
        }
    }
    
    /**
    * 
    * @Method (AJAX)
    */
    public function monthAction()
    {
        $form = $this->form('PlanSheetMonth');
        
        if ($this->request->is('POST')) {
            $date = $this->request->post->all();
            $month = \Arr::path($date, 'PlanSheetMonth.month', date('m'));
            $year = \Arr::get($date, 'PlanSheetMonth.year', date('Y'));
            $date = Calendar::getPeriodMonth($year.'-'.$month.'-01');
            $sheets = $this->model('PlanSheet')->getByStatistic($date); // Показатели на выбранную
            if(! empty($sheets)){
                $form->init($sheets);
            }else{
                $form->init();
            }
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            $sheet = $form->save();
            if($errors = \Arr::get($sheet,'errors', NULL)){
                return ['errors' => $errors];
            }else{
                return [];
            }
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function editAction($id)
    {
        $form = $this->form('EmployeePlanEdit');
        $eplan = $this->model('EmployeePlan')->getById($id);
        $plans = $this->model('Plan')->getByList();
        if($eplan){
            $form->setData($eplan);
        }
        if ($this->request->is('POST')) {
            $form->setData('updated', date("Y-m-d H:i:s"));
            $form->setData('updater', $this->user->id);
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $eplan = $form->save();
                return [
                    'plan' => $eplan,
                ];
            }
        }
        else {
            return $this->renderPartial('eplans/edit', [
                'form' => $form->createBuilder(),
                'plans' => $plans,
                'plan' => $eplan
            ]);
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function removeAction($id)
    {
        $eplan = $this->model('PlanSheet')->getById($id);
        if($eplan){
            if($this->model('PlanSheet')->delete($id)){
                return ['ok' => 1];
            }
        }
    }
    
    /**
    * @acesss (SALARY_SHEET)
    * @Method (!AJAX)
    */
    public function generateAction()
    {
        $content = '';
        $query = $this->request->query->all();
        $year = \Arr::get($query, 'year', 2015);
        $month = \Arr::get($query, 'month', 0);
        $month = intval($month);
        $months = [1,2,3,4,5,6,7,8,9, 10, 11,12, 13];
        if($month){
            $index = array_search($month, $months);
            if(false !== $index){
                $index++;
                $next = \Arr::get($months, $index, 0);
                if($next){
                    $ids = [];
                    $filter = Calendar::getPeriodMonth($year.'-'.$month.'-01');
                    foreach($this->model('Employee')->getByListFull($filter) as $employee){
                        $ids[$employee['user_id']] = $employee['user_id'];
                    }
                    $calculate = $this->model('Calculate')->init($month, $year, NULL, $ids, true);
                    $update = $calculate->updateFact();
                    $content .= '<p>Обновлены фактические значения за '.$month.'-'.$year.'</p>';
                    $content .= '<a href="/admin/employee/plansheet/generate?month='.$next.'">Сгенерировать следующий месяц</a>';
                }else{
                    $content .= 'Обновлены фактические показватели все заданные месяцы 2015';
                }
            }
        }
        return $this->render('generate', ['content' => $content]);
    }
    
    /**
    * 
    * @Method (!AJAX)
    */
    public function updatefactAction($id)
    {
        $content = '';
        $query = $this->request->query->all();
        $year = \Arr::get($query, 'year', date('Y'));
        $month = \Arr::get($query, 'month', date('m'));
        $month = intval($month);
        $year = intval($year);
        $stamp = strtotime($year.'-'.$month.'-01');
        $current = strtotime(date('Y-m-d'));
        if($stamp >= $current){
            $content = 'нельзя обновить данные за текущий месяц';
        }else{
            $employee = $this->model('EmployeeData')->getById($id);
            if(null == $employee){
                $this->notFound();
            }else{
                $ids = [$id => $id];
                $calculate = $this->model('Calculate')->init($month, $year, NULL, $ids, true);
                $update = $calculate->updateFact();
                $content .= '<p>Обновлены фактические значения за '.$month.'-'.$year.'</p>';
                $content .= '<p> '.$employee['name'].'</p>';
            }
        }
        return $this->render('generate', ['content' => $content]);
    }
    
    /**
    * 
    * @Method (!AJAX)
    */
    public function updatetypeAction($id)
    {
        $content = '';
        $query = $this->request->query->all();
        $year = \Arr::get($query, 'year', date('Y'));
        $month = \Arr::get($query, 'month', date('m'));
        $month = intval($month);
        $year = intval($year);
        $stamp = strtotime($year.'-'.$month.'-01');
        $current = strtotime(date('Y-m-d'));
        if($stamp >= $current){
            $content = 'нельзя обновить данные за текущий месяц';
        }else{
            $planTypes = $this->model('Plan')->getByType();
            foreach($this->model('PlanSheet')->getByPerion($year.'-'.$month.'-01') as $sheet){
                //print_r($sheet);
                $p_id = \Arr::get($sheet, 'id');
                $type = \Arr::get($sheet, 'type');
                $d_id = \Arr::get($sheet, 'department_id');
                $key = ($d_id > 0 ? $d_id.'_' : '').$p_id;
                $_t = \Arr::get($planTypes, $key, $p_id);
                if($_t != $type){
                    echo 'UPDATE';
                }else{
                    $content .=$type.'-'.$_t.'<br />';
                }
            }
            /*$employee = $this->model('EmployeeData')->getById($id);
            if(null == $employee){
                $this->notFound();
            }else{
                $ids = [$id => $id];
                $calculate = $this->model('Calculate')->init($month, $year, NULL, $ids, true);
                $update = $calculate->updateFact();
                $content .= '<p>Обновлены фактические значения за '.$month.'-'.$year.'</p>';
                $content .= '<p> '.$employee['name'].'</p>';
            }*/
        }
        return $this->render('generate', ['content' => $content]);
    }
    
    
    /**
     * 
     * @Method (!AJAX)
     */
    public function upoldAction()
    {
        $employees = $this->model('EmployeeData')->getByList(); // те кто сейчас работает
        
        $mEmployee = $this->model('Employee');
        $mEPlan = $this->model('EmployeePlan');
        $plan_classes = ['TotalOrdersCreation' => 2,//Операторы
                         'TotalShopOrdersCreation' => 3,//Консультанты
                         'TotalOrdersManagement' => 6,//Закуперы
                         //'DefectManagement'  => 0, //Брак
                        ];
        foreach($plan_classes as $plan_class => $department_id){
            $data = [];
            $plan_id = 0;
            switch($plan_class){
                case 'TotalOrdersCreation':
                    $plan_id = 1;
                break; 
                
                case 'TotalShopOrdersCreation':
                    $plan_id = 1;
                    /**
                    OR a.creator_id=2544 AND DATE_FORMAT(a.status_date,'%Y-%m')='2013-02' +
                    OR a.creator_id IN (8049,8038,8161,7061) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2013-08-01' +
                    OR a.creator_id IN (8407,8599) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2013-09-01' +
                    OR a.creator_id IN (8567) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2013-09-01' AND DATE_FORMAT(a.status_date,'%Y-%m-%d')<'2014-10-01' +
                    OR a.creator_id IN (8230) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2013-09-01' AND DATE_FORMAT(a.status_date,'%Y-%m-%d')<'2014-06-01' + 
                    OR a.creator_id IN (8724,8744,8942) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2013-10-01' + 
                    OR a.creator_id IN (8989) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2013-11-01'+
                    OR a.creator_id IN (9358) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2013-12-01'+
                    OR a.creator_id IN (9469) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2013-12-01' AND DATE_FORMAT(a.status_date,'%Y-%m-%d')<'2014-06-01' +
                    OR a.creator_id IN (9970) AND (DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2014-01-01' AND DATE_FORMAT(a.status_date,'%Y-%m-%d')<'2014-06-01'
                                                OR DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2014-07-01') +
                    OR a.creator_id IN (10798,10898) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2014-04-01'+
                    OR a.creator_id IN (10455) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2014-04-10'+
                    OR a.creator_id IN (126) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2014-04-15' +
                    OR a.creator_id IN (11217) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2014-05-01' +
                    OR a.creator_id IN (11300,11217) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2014-05-01' +
                    OR a.creator_id IN (11564) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2014-07-01' +
                    OR a.creator_id IN (12690) AND DATE_FORMAT(a.status_date,'%Y-%m-%d')>='2014-10-01'
                    */
                    $data[] = ['start' => '2012-01-01', 'end' => '2013-01-31', 'department_id' => $department_id, 'user_id' => 2544];
                    $data[] = ['start' => '2013-08-01', 'end' => null, 'department_id' => $department_id, 'user_id' => [8049,8038,8161,7061] ];
                    $data[] = ['start' => '2013-09-01', 'end' => null, 'department_id' => $department_id, 'user_id' => [8407,8599] ];
                    $data[] = ['start' => '2013-09-01', 'end' => '2013-09-30', 'department_id' => $department_id, 'user_id' => 8567 ];
                    $data[] = ['start' => '2013-09-01', 'end' => '2014-05-30', 'department_id' => $department_id, 'user_id' => 8230 ];
                    $data[] = ['start' => '2013-10-01', 'end' => null, 'department_id' => $department_id, 'user_id' => [8724,8744,8942] ];
                    $data[] = ['start' => '2013-11-01', 'end' => null, 'department_id' => $department_id, 'user_id' => 8989 ];
                    $data[] = ['start' => '2013-12-01', 'end' => null, 'department_id' => $department_id, 'user_id' => 8989 ];
                    $data[] = ['start' => '2013-12-01', 'end' => '2014-05-30', 'department_id' => $department_id, 'user_id' => 9469 ];
                    $data[] = ['start' => '2014-01-01', 'end' => '2014-05-30', 'department_id' => $department_id, 'user_id' => 9970 ];
                    $data[] = ['start' => '2014-07-01', 'end' => null, 'department_id' => $department_id, 'user_id' => 9970 ];
                    $data[] = ['start' => '2014-04-01', 'end' => null, 'department_id' => $department_id, 'user_id' => [10798,10898] ];
                    $data[] = ['start' => '2014-04-10', 'end' => null, 'department_id' => $department_id, 'user_id' => 10455 ];
                    $data[] = ['start' => '2014-04-15', 'end' => null, 'department_id' => $department_id, 'user_id' => 126 ];
                    
                    $data[] = ['start' => '2014-04-15', 'end' => null, 'department_id' => $department_id, 'user_id' => 126 ];
                    $data[] = ['start' => '2014-05-01', 'end' => null, 'department_id' => $department_id, 'user_id' => 11217 ];
                    $data[] = ['start' => '2014-05-01', 'end' => null, 'department_id' => $department_id, 'user_id' => [11300,11217] ];
                    $data[] = ['start' => '2014-07-01', 'end' => null, 'department_id' => $department_id, 'user_id' => 11564 ];
                    $data[] = ['start' => '2014-10-01', 'end' => null, 'department_id' => $department_id, 'user_id' => 12690 ];
                    
                    $data[] = ['start' => '2013-10-01', 'end' => null, 'department_id' => $department_id, 'user_id' => '3660'];
                    
                break; 
            
                case 'TotalOrdersManagement':
                    $plan_id = 2;
                    
                    /**
                    a.manager_id IN (2519,4688) OR
                    a.manager_id=5239 AND DATE_FORMAT(a.status_date,'%Y-%m')<'2013-02' OR
                    a.manager_id IN (3660) AND DATE_FORMAT(a.status_date,'%Y-%m')>='2013-10' OR
                    a.manager_id IN (10529) AND DATE_FORMAT(a.status_date,'%Y-%m')>='2014-05
                    */
                    
                    $data[] = ['start' => '2012-01-01', 'end' => '2013-01-31', 'department_id' => $department_id, 'user_id' => 5239];
                    $data[] = ['start' => '2013-10-01', 'end' => null, 'department_id' => $department_id, 'user_id' => 3660];
                    $data[] = ['start' => '2014-05-01', 'end' => null, 'department_id' => $department_id, 'user_id' => 10529];
                
                break;
                
                case 'DefectManagement':
                
                break; //Операторы
                
                case 'TotalOrdersCreation':
                
                break; //Операторы
            }
            $m = [];
            foreach($data as $row){
                if(is_array($row['user_id'])){
                    $m = array_merge($m, $row['user_id']);
                }else{
                    $m = array_merge($m, [$row['user_id']]);
                }
            }
            $last = $this->model('PlanSheet')->lastPlansheet($m);
            foreach($data as $row){
                if(is_array($row['user_id'])){
                    $_u_ids = $row['user_id'];
                    foreach($_u_ids as $_u_id){
                        $row['user_id'] = $_u_id;
                        if(null === $row['end']){
                            if(! \Arr::get($employees, $row['user_id'], null)){
                                $row['end'] = '2015-01-01'; //close 2015
                            }else{
                                $_last = \Arr::get($last, $row['user_id'], null);
                                if($_last){
                                    if(strtotime('2015-09-01') < strtotime($_last)){
                                        $row['end'] = $_last; 
                                    }else{
                                        $row['end'] = '2015-01-01'; //close 2015    
                                    }
                                }else{
                                    $row['end'] = '2015-01-01'; //close 2015
                                }
                            }
                        }
                        $mEmployee->upsert($row);
                        $_s = ['start' => $row['start'],
                               'end' => $row['end'],
                               'plan_id' => $plan_id,
                               'user_id' => $_u_id,
                            ];
                        $mEPlan->upsert($_s);
                    }
                }else{
                    if(null === $row['end']){
                        if(! \Arr::get($employees, $row['user_id'], null)){
                            $row['end'] = '2015-01-01'; //close 2015
                        }else{
                            $_last = \Arr::get($last, $row['user_id'], null);
                            if($_last){
                                if(strtotime('2015-09-01') < strtotime($_last)){
                                    $row['end'] = $_last; 
                                }else{
                                    $row['end'] = '2015-01-01'; //close 2015    
                                }
                            }else{
                                $row['end'] = '2015-01-01'; //close 2015
                            }
                        }
                    }
                    $mEmployee->upsert($row);
                    $_s = ['start' => $row['start'],
                           'end' => $row['end'],
                           'plan_id' => $plan_id,
                           'department_id' => $row['department_id'],
                           'user_id' => $row['user_id'],
                        ];
                    $mEPlan->upsert($_s);
                }
            }
        }
    }
}