<?php

namespace Modules\Employee\Admin\Controllers;
use Modules\Employee\Admin\Extensions\Calculate;
use Modules\Employee\Admin\Extensions\Calendar;
use Modules\Employee\Admin\Models\Bonus;
use Modules\Employee\Admin\Models\Salary;
use Modules\Employee\Admin\Models\SalaryLog;
use Modules\Employee\Admin\Models\SalaryAvans;


class SalaryController extends InitController
{
    public $menu = "salary";

    /**
     * @Method (!AJAX)
     */
    public function indexAction()
    {
        if(! $this->is_allow){
            if(! $this->is_chief AND ! $this->is_bookkeper){
                $this->forbidden();
            }else{
                return $this->getSalaryList($this->is_chief);        
            }
        }
        return $this->getSalaryList();
        
    }
    
    protected function getSalaryList($chief = null)
    {
        $is_current = false;
        $ids = [];
        $allow_departments = [];
        if($chief){
            $_tree = $this->model('Department')->getTreeDepartmentSalary($chief);
            foreach($_tree as $department){
                if(! empty($department['id'])){
                    $allow_departments[$department['id']] = $department['id'];
                }
            }
            $tree = $this->model('Department')->getTreeSalary(); // tree Department Employees  
        }else{
            $tree = $this->model('Department')->getTreeSalary(); // tree Department Employees  
        }
        $edepartments = [];
        foreach($tree as $department){
            if($this->is_allow){
                $allow_departments[$department['id']] = $department['id'];
            }
            foreach(\Arr::get($department, 'users', []) as $user){
                $ids[] = \Arr::get($user, 'id');        
            }
            $edepartments[$department['id']] =  ['id' => $department['id'], 'name' => $department['name']];
        }
        
        $eplan = $this->model('EmployeePlan');
        $filter = $this->form('SalaryUserFilter');
        $filter->setData($this->request->query->all());
         if ($this->request->is('POST')) {
            $filter->handle($this->request->post);
            if($filter->validate()){
                $query = $filter->buildQuery();
                $this->redirect('/admin/employee/salary/'.$query);
            }
        }
        $month = $filter->getData("month", date("m"));
        $year = $filter->getData("year", date("Y"));
        $filter->setData("month", $month);
        $filter->setData("year", $year);
        
        $i = (strtotime(date('Y-m').'-01') - strtotime($year.'-'.$month.'-01'));
        if((strtotime(date('Y-m-d')) - strtotime($year.'-'.$month.'-01')) <= 0){
            $is_current = 1;
        }
        
        $osalary = $this->model("Salary");
        $fixedsalarys = $osalary->getByMount($month, $year); // созданная запись в БД
        $is_generate = 1;
        $period = Calendar::getPeriod($this->model('PlanSheet')->getMaxPeriod());
        $_vaxes = $_avanses = [];
        if(empty($fixedsalarys)){
            $is_generate = null;
            //Avanses
            $mSAvans = $this->model('SalaryAvans');
            $filteravans = ['date' => $year.'-'.$month.'-01', 'ready' => $mSAvans::READY];
            foreach($this->model('SalaryAvans')->getByList($filteravans) as $_avans){
                $_avanses[$_avans['user_id']] = $_avans; 
            }
            unset($filteravans['ready']);
            foreach($this->model('SalaryVax')->getByList($filteravans) as $vax){
                $_vaxes[$vax['user_id']] = $vax; 
            }
        }
        $month_prev = Calendar::getPrevMonth($year.'-'.$month.'-01');
        $month_prev = Calendar::getMonthYear($month_prev);
        $calculate = $this->model('Calculate')->init($month, $year, NULL, $ids);
        $salarys = $calculate->calculate_all();

        $prev_calculate = $this->model('Calculate')->init($month_prev['month'], $month_prev['year'], NULL, $ids);
        $prev_salarys = $prev_calculate->calculate_all();

        if($chief){
            //callback bonus
            array_walk($salarys, function(&$salary, $_user_id){
                $bonuses = \Arr::get($salary,'bonuses',[]);
                $is_request = 0;
                foreach($bonuses as $bonus){
                    if($bonus['is_request'] AND empty($bonus['approved_id'])){
                        $is_request++;;
                    }
                }
                $salary['is_request'] = $is_request;
            });
        }
        
        //Statuses 
        $mDoc = $this->model('EmployeeDoc');
        $mDocMap = $this->model('EmployeeDocMap');
        $mContact = $this->model('EmployeeContact');
        $mContactMap = $this->model('EmployeeContactMap');
        $mTimeManager = $this->model('ManagerTimeSheet');
        
        //Doc
        $docs = $mDoc->getByList(['user_id' => $ids]);
        $_docmaps = $mDocMap->getByList(['user_id' => $ids]);
        foreach($mDocMap->getByList(['user_id' => $ids]) as $_row){
            if(! isset($_docmaps[$_row['user_id']])) $_docmaps[$_row['user_id']] = [];
            $_docmaps[$_row['user_id']][] = $_row;
        }
        
        //Contact
        $contacts = $mContact->getByList(['user_id' => $ids]);
        $_contactmaps = [];
        foreach($mContactMap->getByList(['user_id' => $ids]) as $_row){
            if(! isset($_contactmaps[$_row['user_id']])) $_contactmaps[$_row['user_id']] = [];
            $_contactmaps[$_row['user_id']][] = $_row;
        }
        
        //Timemanager
        $tmanagers = [];
        foreach($mTimeManager->getByList(['user_id' => $ids]) as $t){
            $tp = $mTimeManager->progress($t);
            $tmanagers[$t['user_id']] = $tp;
        }
        
        foreach($ids as $id){
            $doc = \Arr::get($docs, $id);
            $_docmap = \Arr::get($_docmaps, $id);
            $docmap = $mDocMap->getMapUser($_docmap);
            $doc_progress = $mDoc->progress($doc, $docmap);
            
            $contact = \Arr::get($contacts, $id);
            $_contactmap = \Arr::get($_contactmaps, $id);
            $contactmap = $mContactMap->getMapUser($_contactmap);
            $contact_progress = $mContact->progress($contact, $contactmap);

            $tp = \Arr::get($tmanagers, $id, null);
            $statuses[$id] = $this->getPercentStatus($doc_progress, $contact_progress, $tp);
        }
        //roles
        $roles = $this->model('User')->getRoles($ids);

        // get avans
        $filteravans = ['date' => $year.'-'.$month.'-01'];
        $avanses = []; //выданные авансы
        $outs = []; // выданные ЗП
        $allouts = []; // выданные деньги
        $filteravans = ['date' => $year.'-'.$month.'-01'];
        foreach($this->model('SalaryLog')->getByList($filteravans) as $_log){
            if(! isset($allouts[$_log['user_id']])) $allouts[$_log['user_id']] = [];
            if(! empty($_log['avans']) OR ! empty($_log['out'])){
                $allouts[$_log['user_id']][] = $_log;
            }
            if(! empty($_log['avans'])){
                if(! isset($avanses[$_log['user_id']])) $avanses[$_log['user_id']] = [];
                $avanses[$_log['user_id']][] = $_log;
            }
            if(! empty($_log['out'])){
                if(! isset($outs[$_log['user_id']])) $outs[$_log['user_id']] = [];
                $outs[$_log['user_id']][] = $_log;
            }
        }
        $prevplans = [];
        array_walk($prev_salarys, function(&$salary, $_user_id) use (&$prevplans){
            if(! isset($prevplans[$_user_id])){
                $prevplans[$_user_id] = [
                    'no_plan_based' => [],
                    'is_plan_based' => [],
                ];
            }
            foreach($salary['salary'] as $_plan){
                $_u_p_key = $_plan['u_key'];
                if($_plan['is_plan_based'] == 1){
                    if(empty($_plan['hidden'])){
                        $prevplans[$_user_id]['is_plan_based'][$_u_p_key] =  $_plan;
                    }
                }else{
                    $prevplans[$_user_id]['no_plan_based'][$_u_p_key] =  $_plan;
                }
            }
        });
        array_walk($salarys, function(&$salary, $_user_id) use ($osalary, $avanses, $outs, $fixedsalarys, $allouts, $statuses, $_vaxes, $_avanses, $allouts, $roles, $tmanagers, $prevplans){
            $avans_log_summa = $outs_log_summa = 0;
            $fixedsalary = \Arr::get($fixedsalarys,$_user_id, []);
            $allout = \Arr::get($allouts,$_user_id, []);
            $salary = $osalary->prepare($fixedsalary, $salary, $allout);
            $salary['doc_status'] = \Arr::get($statuses, $_user_id, 0);

            $tmanager = \Arr::path($tmanagers,$_user_id.'.count', 0);
            $has_schedule = \Arr::path($roles,$_user_id.'.has_schedule', 0);
            $_profile = \Arr::path($statuses, $_user_id.'.percent', 0);
            $allow_out = $osalary->allow_out($tmanager, $has_schedule, $salary['skip_time'], $_profile);
            $salary['allow_out'] = \Arr::get($allow_out, 'allow', true);
            $_a_errors = \Arr::get($allow_out, 'errors');
            $salary['allow_out_errors'] = implode(', ',$_a_errors);
            $_plans = ['is_plan_based' => [],
                   'no_plan_based' => []
                   ];
            $prev_user = \Arr::get($prevplans, $_user_id, []);
            foreach($salary['salary'] as $_plan){
                $_u_p_key = $_plan['u_key'];
                if($_plan['is_plan_based'] == 1){
                    $_plan['prev'] = \Arr::path($prev_user,'is_plan_based.'.$_u_p_key, false);
                    if(empty($_plan['hidden'])){
                        $_plans['is_plan_based'][] =  $_plan;
                    }
                }else{
                    $_plan['prev'] = \Arr::path($prev_user,'no_plan_based.'.$_u_p_key, false);
                    $_plans['no_plan_based'][] =  $_plan;       
                }
            }
            $salary['help_plans'] = $_plans;

            $avans_log = \Arr::get($avanses, $_user_id, null);
            $outs_log = \Arr::get($outs, $_user_id, null);
            $allout = \Arr::get($allouts, $_user_id, null);
            
            $salary['avans_log'] = ! empty($avans_log) ? $avans_log : null;
            $salary['outs_log'] = ! empty($outs_log) ? $outs_log : null;
            $salary['outs'] = ! empty($allout) ? $allout : null;
            
            if($salary['avans_log']){
                foreach($salary['avans_log'] as $_l){
                    $avans_log_summa += $_l['avans'];
                }
            }
            if($salary['outs_log']){
                foreach($salary['outs_log'] as $_l){
                    $outs_log_summa += $_l['out'];
                }
            }
            $salary['avans_log_summa'] = $avans_log_summa;
            $salary['outs_log_summa'] = $outs_log_summa;
            
            $salary['user_id'] = $_user_id;
            $fixedsalary = \Arr::get($fixedsalarys, $_user_id, null);
            if($fixedsalary){
                $salary['avans'] = $fixedsalary['avans'];
                $salary['out'] = $fixedsalary['out'];
                $salary['vax'] = $fixedsalary['vax'];
                $salary['id'] = $fixedsalary['id'];
                $salary['balance'] = $fixedsalary['balance'];
                $salary['series'] = $fixedsalary['series'];
                $salary['outdate'] = $fixedsalary['outdate'];
            }else{
                $salary['avans'] = 0;
                $salary['out'] = 0;
                $salary['vax'] = 0;
                $salary['balance'] = 0;
                $salary['series'] = 0;
                $salary['outdate'] = null;
                $salary['vax_id'] = 0;
                $salary['avans_id'] = 0;
                
                if($_v = \Arr::get($_vaxes, $_user_id, null)){
                    $salary['vax_id'] = $_v['id'];
                    $salary['vax'] = $_v['vax'];
                }
                if($_a = \Arr::get($_avanses, $_user_id, null)){
                    $salary['avans_id'] = $_a['id'];
                    $salary['avans'] = $_a['avans'];
                }
            }
            /*
            *Fix отрицательных ЗП
            **/
            if($salary['balance'] < 0){
                $salary['balance'] = 0;
            }
            if($salary['total'] < 0){
                $salary['total'] = 0;
            }
        });
        $srequest = $this->form('SalaryRequest');
        $srequest->init();
        $amount = $this->model('Salary')->getReady();
        $srequest->setData('amount', $amount);
        
        //Balance
        $months = range(1,12);
        $all = $all_col = [];
        $mOrg = $this->model('PlanOrg');
        foreach($this->model('Plansheet')->getByOrg($year.'-'.$month.'-01') as $_date => $_all){// данные из баланса
            $stamp = strtotime($_date);
            $all[$stamp] = $_all;
            $_year = date('Y', $stamp);
            $_date_all = date('Y-m-d', $stamp);
            if(! isset($all_col[$_year])) $all_col[$_year] = [];
            $all_col[$_year][] = $stamp;
        }
        
        $all_salary = [];
        foreach($this->model('Salary')->getByAll() as $date => $s){
            $stamp = strtotime($date);
            $all_salary[$stamp] = $s;
        }
        $factOrg = $this->model('PlanOrg')->getByFact($year.'-'.$month.'-01');
        if($factOrg){
            $factOrg = \Arr::get($factOrg, 'profit', 0);
        }
        
        return $this->render('salary/list', [
            'tree' => $tree,
            'edepartments' => $edepartments,
            'period' => $period,
            'employees' => [],
            'salarys' => $salarys,
            'prev_salarys' => $prev_salarys,
            'month_prev' => $month_prev,
            'period' => $period,
            'cmount' => $month,
            'cyear' => $year,
            'tmanagers' => $tmanagers,
            'is_current' => $is_current, //текущий месяц
            'filter' => $filter->createBuilder(),
            'srequest' => $srequest->createBuilder(), //Запрос в кассу
            'srequestform' => $srequest, //Запрос в кассу
            'is_generate' => $is_generate,
            'menu' => $this->menu,
            'timeout' => $this->timeout,
            'is_rule' => $this->is_allow,
            'is_chief' => $this->is_chief,
            'allow_departments' => $allow_departments,
            'is_bookkeper' => $this->is_bookkeper,
            'auth_user' => $this->user->id,
            'all' => $all,
            'all_col' => $all_col,
            'all_salary' => $all_salary,
            'factOrg' => $factOrg,
            'months' => $months,
            'cstamp' => strtotime($year.'-'.$month.'-01'), // текущий stamp
        ]);
    }

    protected function getPercentStatus($doc_progress, $contact_progress, $tp){
        $percent1 = \Arr::get($doc_progress, 'percent', 0);
        $percent2 = \Arr::get($contact_progress, 'percent', 0);
        $percent1 = $percent1 * 0.5;
        $percent2 = $percent2 * 0.5;
        $s_percent = round($percent1+$percent2);
        if($s_percent == 100){
            if(0 == \Arr::get($tp,'percent', 0)){
                $s_percent = $s_percent-10;
            }
        }
        return $s_percent;
    }
    
    
    /**
     * @acesss (SALARY_SHEET)
     * @Method (!AJAX)
     * 
     */
    public function checkAction()
    {
        //Проверка ЗП выдавно осталось
        $filter = $this->form('SalaryUserFilter');
        $filter->setData($this->request->query->all());
        $month = $filter->getData("month", date("m"));
        $year = $filter->getData("year", date("Y"));
        $filteravans = ['date' => $year.'-'.$month.'-01'];
        $outs = [];
        foreach($this->model('SalaryLog')->getByList($filteravans) as $_log){
            if(! isset($outs[$_log['user_id']])) $outs[$_log['user_id']] = [];
            $outs[$_log['user_id']][] = $_log;
        }
        $fixedsalarys = $this->model('Salary')->getByMount($month, $year); // созданная запись в БД
        if(null == $fixedsalarys){
            
        }else{
            foreach($fixedsalarys as $_salary){
                $all_out = 0;
                $balance = $_salary['total'];
                foreach(\Arr::get($outs, $_salary['user_id'], []) as $_out){
                    if(!empty($_out['avans'])){
                        $all_out += $_out['avans'];
                    }
                    if(!empty($_out['out'])){
                        $all_out += $_out['out'];
                    }
                }
                if(!empty($_salary['vax'])){
                    $all_out += $_salary['vax'];
                }
                if($_salary['ready'] != Salary::READY_NO){
                    if(!empty($_salary['avans'])){
                        $all_out += $_salary['avans'];
                    }
                    if(!empty($_salary['out'])){
                        $all_out += $_salary['out'];
                    }
                }
                $balance -= $all_out;
                if($balance != $_salary['balance']){
                    echo $_salary['name'].':'.$_salary['balance'].':'.$balance.'<br />';
                    $data = ['id' => $_salary['id'],
                             'balance' => $balance];
                    $this->model('Salary')->upsert($data);
                }
            }
        }
    }

    /**
     * @acesss (SALARY_SHEET)
     * @Method (!AJAX)
     */
    public function printAction(){
        $ids = [];
        $tree = $this->model('Department')->getTreeSalary(); // tree Department Employees
        foreach($tree as $department){
            foreach(\Arr::get($department, 'users', []) as $user){
                $ids[] = \Arr::get($user, 'id');        
            }
        }
        $eplan = $this->model('EmployeePlan');
        $filter = $this->form('SalaryUserFilter');
        $filter->setData($this->request->query->all());
        $month = $filter->getData("month", date("m"));
        $year = $filter->getData("year", date("Y"));
        
        $osalary = $this->model("Salary");
        $salarys = $osalary->getByMount($month, $year); // созданная запись в БД
        
        $is_generate = 1;
        array_walk($salarys, function(&$salary) use ($osalary){
            $salary = $osalary->prepare($salary);
        });
        $cols = ['A' => 'Код',
                 'B' => 'к выдаче',
                 ];
        $widths = ['A' => 10,
                   'B' => 10,
                 ];
        
        require_once DOCROOT.'req/external/excel/Classes/PHPExcel.php';
        require_once(DOCROOT.'req/external/excel/Classes/PHPExcel/Writer/Excel5.php');
        $xls = new \PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle('ЗП за '.$month.' '.$year);
        foreach($widths as $col => $width){
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $row = 1;
        foreach($cols as $col => $_name){
            $sheet->setCellValue($col.$row, $_name);
        }
        $row++;
        foreach($tree as $department){
            foreach(\Arr::get($department, 'users', []) as $user){
                $salary = \Arr::get($salarys, $user['id'], null);
                if($salary){
                    if($salary['balance'] > 0){
                        $data = [
                                 'A' => $salary['series'], 
                                 'B' => \Num::format($salary['balance']), 
                                 ];
                        foreach($cols as $col => $_name){
                            $value = \Arr::get($data, $col, '');
                            $sheet->setCellValue($col.$row, $value);
                        }
                        $row++;
                    }
                }
            }
        }
        header ( "Expires: ".date('c') );
        header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
        header ( "Cache-Control: no-cache, must-revalidate" );
        header ( "Pragma: no-cache" );
        header ( "Content-type: application/vnd.ms-excel" );
        header ( "Content-Disposition: attachment; filename=salary.xls" );
        $objWriter = new \PHPExcel_Writer_Excel5($xls);
        $objWriter->save('php://output');
    }
    
   
    /**
     * @acesss (SALARY_SHEET)
     * @Method (AJAX)
     */
    public function requestAction(){
        $form = $this->form('SalaryRequest');
        $form->setData('updater', $this->user->id);
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $save = $form->save();
                $errors = $form->getErrors();
                if(! empty($errors)){
                    return ['errors' => $errors];
                }else{
                    return $save;    
                }
            }
        }
    }
    
    /**
     * @param integer $id
     * @acesss (SALARY_SHEET)
     * @Method (AJAX)
     */
    public function avansAction($id){
        $form = $this->form('SalaryAvans');
        $form->setData('id', $id);
        $form->setData('updater', $this->user->id);
        $form->setData('updated', date('Y-m-d'));
        $data = $form->getData();
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            $form->init();
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $salary = $form->save();
                return [
                    'salary' => $salary,
                    'amount' => $this->model('Salary')->getReady(),
                ];
            }
        }else{
            return $this->renderPartial('salary/avans', [
                '_id' => $id,
                'form' => $form->createBuilder(),
            ]);    
        }
    }
    
    
    /**
     * @param integer $id
     * @acesss (SALARY_SHEET)
     * @Method (AJAX)
     */
    public function vaxAction($id){
        $form = $this->form('SalaryVax');
        $form->setData('id', $id);
        $form->setData('updater', $this->user->id);
        $form->setData('updated', date('Y-m-d'));
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            $form->init();
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $salary = $form->save();
                return [
                    'salary' => $salary,
                    'amount' => $this->model('Salary')->getReady(),
                ];
            }
        }else{
            return $this->renderPartial('salary/vax', [
                '_id' => $id,
                'form' => $form->createBuilder(),
            ]);    
        }
    }
    
    /**
     * @param integer $id
     * @acesss (SALARY_SHEET)
     * @Method (AJAX)
     */
    public function balanceAction($id){
        $form = $this->form('SalaryBalance');
        $form->setData('id', $id);
        $form->init();
        $form->setData('updater', $this->user->id);
        $form->setData('updated', date('Y-m-d'));
        
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                $salary = $form->salary;
                return [
                    'errors' => $form->getErrors(),
                    'balance' => \Arr::get($salary, 'balance',0),
                ];
            }
            if ($form->isSubmitted()) {
                $salary = $form->save();
                return [
                    'salary' => $salary,
                    'amount' => $this->model('Salary')->getReady(),
                ];
            }
        }
        else{
            return $this->renderPartial('salary/balance', [
                '_id' => $id,
                'form' => $form->createBuilder(),
            ]);    
        }
    }
    
    
    /**
    * @param integer $id
    * @acesss (SALARY_SHEET)
    * @Method (AJAX)
    */
    public function editAction($id){
        $form = $this->form('SalaryEdit');
        $form->setData('id', $id);
        $form->init();
        $form->setData('updater', $this->user->id);
        $form->setData('updated', date('Y-m-d'));
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $salary = $form->save();
                return [
                    'salary' => $salary,
                    'amount' => $this->model('Salary')->getReady(),
                ];
            }
        }
        else{
            return $this->renderPartial('salary/edit', [
                '_id' => $id,
                'form' => $form->createBuilder(),
            ]);    
        }
    }
    
/**
     * @param integer $id
     * 
     * @Method (!AJAX)
     */
    public function departmentAction($id)
    {
        $this->menu = 'salary';
        $department = $this->model('Department')->getById($id);
        if(! $department){
            $this->notFound();
        }
        $employees = $this->model('Employee')->getByDepartment($id);
        $ids = [];
        foreach($employees as $employee){
            $ids[] = $employee['user_id'];
        }
        $mount = 9;
        $year = date('Y');
        $calculate = $this->model('Calculate')->init($mount, $year, NULL, $ids);
        $salarys = $calculate->calculate_all();
        
        return $this->render('salary/department', [
            'salarys' => $salarys,
            'department' => $department,
            'menu' => $this->menu
        ]);
    }
    
    /**
     * 
     * @Method (!AJAX)
     */
    public function cabinetAction()
    {
        $this->menu = 'cabinet';
        return $this->userAction($this->user->id);
    }
    
    /**
     * @param integer $id
     *
     * @Method (!AJAX)
     */
    public function userAction($id)
    {
        return $this->userInfo($id);
    }
    
    /**
     * @param integer $id
     * 
     * @Method (AJAX)
     */
    public function helpAction($id)
    {
        return  $this->userInfo($id, 1);
    }
    
    protected function userInfo($id, $ajax = null)
    {
        if(! $id){
            $this->notFound();
        }
        $is_rule = $this->is_allow;
        if(! $is_rule AND $id != $this->user->id){
            if(! $this->is_chief){
                $this->redirect('/admin/employee/salary/user/'.$this->user->id);
            }else{
                if(!$this->model('EmployeeData')->hasChildren($this->is_chief, $id)){
                    $this->notFound();        
                }
            }
        }
        $employee = NULL;
        $filter = $this->form('SalaryUserFilter');
        $filter->setData($this->request->query->all());
        $month = $filter->getData('month', date('m'));
        $year = $filter->getData('year', date('Y'));
        $filter->setData("month", $month);
        $filter->setData("year", $year);
        
        $periodmonth = Calendar::getPeriodMonth($year.'-'.$month.'-01');
        $eplan = $this->model('EmployeePlan');
        if ($this->request->is('POST')){
            $filter->handle($this->request->post);
            if($filter->validate()){
                $query = $filter->buildQuery();
                if($this->is_allow){
                    $this->redirect('/admin/employee/salary/user/'.$id.$query);
                }else{
                    $this->redirect('/admin/employee/salary/cabinet'.$query);    
                }
            }
        }
        $period = Calendar::getPeriod($this->model('PlanSheet')->getMaxPeriod(NULL, [$id]));
        
        $period_month = Calendar::getPeriodMonth($year.'-'.$month.'-01');
        $start_mount = $period_month['start'];
        $end_mount = $period_month['end'];
        // оклад
        $oklad = $this->model('EmployeeSalary')->getByOkladUserId($id, ['start' => $start_mount, 'end' => $end_mount]);
        
        $work_days = Calendar::GetWorkingDay($start_mount, $end_mount);// кол-во рабочих дней
        $coun_word_day = count($work_days);
        if($coun_word_day){
            //$price_hour = intval($oklad/($coun_word_day*9));
            $price_hour = intval($oklad/Calendar::HOUR_MONTH);
        }else{
            $price_hour = 0;
        }
        
        $mtimesheet = $this->model('TimeSheet');
        $managertimesheet = $this->model('ManagerTimeSheet');
        
        $mBonus = $this->model('Bonus');
        $_bonuses = $mBonus->getByList(['user_id' => $id, 'month' => $month, 'year' => $year]);
        $dates_bonuses = $bonus_plus = []; //даты бонусов и запросы на отработку
        foreach($_bonuses as $_bonus){
            if(! isset($dates_bonuses[$_bonus['date']])) $dates_bonuses[$_bonus['date']] = [];
            $dates_bonuses[$_bonus['date']][] = $_bonus;
            if($mtimesheet::TYPE_SKIP_BONUS == $_bonus['skip_type'] AND $_bonus['is_approved'] != Bonus::STATUS_CANCEL){
               $bonus_plus[$_bonus['date']] = $_bonus; 
            }
        }
        
        //manager timesheet
        $mtimesheet = $this->model('TimeSheet');
        $managertimesheet = $this->model('ManagerTimeSheet');
        $tmanager = $managertimesheet->getByListArray(['user_id' => $id, 'start' => $start_mount, 'end' => $end_mount]);
        $timesheet = $mtimesheet->getByListArray(['user_id' => $id, 'start' => $start_mount, 'end' => $end_mount]);
        $timesheet = \Arr::get($timesheet, $id,[]);
        $check_all_day = $work_days;
        $times = $mtimesheet::cluiningTime($tmanager, $timesheet, $work_days, $end_mount);
        //$skipping = $mtimesheet->getByLatenes($tmanager, $timesheet, $work_days, $end_mount, $price_hour);
        array_walk($times,function(&$sheet) use ($mtimesheet, $price_hour, $id, $dates_bonuses, $mBonus, &$check_all_day){
            $sheet = $mtimesheet->latenes($sheet,$price_hour);
            $bonus = \Arr::get($dates_bonuses, $sheet['date'], []);
            $mBonus->checkSkipAmount($bonus, $sheet['skipping'], $id); //insert -update*/
            $key_d = array_search($sheet['date'], $check_all_day);
            if($key_d !== FALSE){ 
                unset($check_all_day[$key_d]);
            }   
        });
        foreach($check_all_day as $_day){
            if(strtotime($_day) < time()){
                $bonus = \Arr::get($dates_bonuses, $_day, []);
                $mBonus->checkSkipAmount($bonus, [], $id); //insert -update
            }
        }
       
        //$bonuses = $mtimesheet::cluining($bonuses, $skipping); //склейка
        array_walk($tmanager, function(&$sheet) use ($managertimesheet){
            $sheet = $managertimesheet::prepare($sheet);
        });
        
        $is_generate = null;
        $fixedsalary = $this->model('Salary')->getByMount($month, $year, $id); // созданная запись в БД
        if(null !== $fixedsalary){
            $is_generate = 1;
        }
        //рассчет ЗП
        $calculate = $this->model('Calculate');
        $calculate->setBonus($_bonuses);
        $calculate->init($month, $year, NULL, $id);
        $salary = $calculate->calculate($id);
        $esalary = \Arr::path($salary, 'esalary', NULL);
        $allout = [];
        if(null !== $fixedsalary){
            $salary['id'] = $fixedsalary['id'];
            $salary['vax'] = $fixedsalary['vax'];
            $salary['out'] = $fixedsalary['out'];
            $salary['balance'] = $fixedsalary['balance'];
            $_filter = ['date' => $year.'-'.$month.'-01', 'user_id' => $id];
            $allout = $this->model('SalaryLog')->getByList($_filter);
            $salary['outs'] = $allout;
            $_out = 0;
            foreach($salary['outs'] as $_log){
                if($_avans = \Arr::get($_log,'avans', 0)){
                    $_out += $_avans;
                }
                if($_out_log = \Arr::get($_log,'out', 0)){
                    $_out += $_out_log;
                }
            }
            $salary['out_all'] = $_out;
        }
        $salary = $this->model('Salary')->prepare($fixedsalary, $salary, $allout);

        //Statuses
        $mDoc = $this->model('EmployeeDoc');
        $mDocMap = $this->model('EmployeeDocMap');
        $mContact = $this->model('EmployeeContact');
        $mContactMap = $this->model('EmployeeContactMap');
        $mTimeManager = $this->model('ManagerTimeSheet');

        //Doc
        $doc = $mDoc->getById($id);
        $_docmap = $mDocMap->getByUserId($id);
        $docmap = $mDocMap->getMapUser($_docmap);
        $doc_progress = $mDoc->progress($doc, $docmap);

        //Contact
        $econtacts = $mContact->getById($id);
        $_cmap = $mContactMap->getByUserId($id);
        $cmap = $mContactMap->getMapUser($_cmap);
        $contact_progress = $mContact->progress($econtacts, $cmap);

        //Timemanager
        $_tmanager = null;
        foreach($tmanager as $t){
            $_tmanager = $t;
        }
        $tp = $mTimeManager->progress($_tmanager);
        $statuse = $this->getPercentStatus($doc_progress, $contact_progress, $tp);


        //roles
        $roles = $this->model('User')->getRoles([$id]);

        $_tmanager = \Arr::get($tp,'count', 0);
        $has_schedule = \Arr::path($roles,$id.'.has_schedule', 0);
        $_profile = \Arr::get($statuse, 'percent', 0);
        $allow_out = $this->model('Salary')->allow_out($_tmanager, $has_schedule, $salary['skip_time'], $_profile);
        $salary['allow_out'] = \Arr::get($allow_out, 'allow', true);
        $_a_errors = \Arr::get($allow_out, 'errors');
        $salary['allow_out_errors'] = implode(', ',$_a_errors);


        $dids = [];
        $_plans = ['is_plan_based' => [],
                   'no_plan_based' => []
                   ];
        foreach($salary['salary'] as $_plan){
            if($ep_id = \Arr::get($_plan, 'ep_id', NULL)){
                $_plan['id'] = $ep_id;
            }
            if($_plan['is_plan_based'] == 1){
                if(empty($_plan['hidden'])){
                    $_plans['is_plan_based'][] =  $_plan;
                }
            }else{
                $_plans['no_plan_based'][] =  $_plan;       
            }
            if(! empty($_plans['department_id'])){
                $dids[] = $_plans['department_id'];
            }
        }
        $salary['salary'] = $_plans;
        $edepartments = [];
        $ds = $this->model('Department')->getList();
        foreach($ds as $d){
            $edepartments[$d['id']] = $d;
        }
        
        $bonuses = \Arr::get($salary, 'bonuses',[]);
        
        $departments = $this->model('Employee')->getByList(['user_id' => $id, 'start'=> $period_month['start'], 'end'=> $period_month['end']]);
        $dchief = $this->model('Department')->getChiefDepartments([$id]);
        
        $employees = $this->model('EmployeeData')->getByList(['user_id' => $id]);
        reset($employees);
        $employee = array_pop($employees);
        
        //date
        $start_mount = $calculate->start_day;
        $end_mount = $calculate->end_day;
        
        // BONUS
        $addform = $this->form('BonusEdit');
        $bonus_mount = $year.'-'.$month;
        if($bonus_mount == date('Y-m')){
            $bonus_mount = date('Y-m-d');
        }else{
            $bonus_mount = $bonus_mount.'-'.date('d');
        }
        $addform->setData('date', $bonus_mount);
        $addform->setData('manager_id', $id);

        if($is_rule or $this->is_chief){
            $valiebles = [
                ['name' => 'Опоздание',
                 'action' => '',
                 'amount' => '-',
                 'sign' => -1,
                ],
                ['name' => 'Не отработано',
                    'action' => '',
                    'amount' => '-',
                    'sign' => -1,
                ],
                ['name' => 'Недостача',
                    'action' => '',
                    'amount' => '-',
                    'sign' => -1,
                ],
                ['name' => 'Экспедиторский бонус',
                    'action' => '',
                    'amount' => '',
                    'sign' => 1,
                ],
                ['name' => 'Премия руководителя',
                    'action' => '',
                    'amount' => '',
                    'sign' => 1,
                ],
                ['name' => 'Инвентаризация',
                    'action' => '',
                    'amount' => '-',
                    'sign' => -1,
                ],
            ];
        }else{
            $valiebles = ['Премия руководителя' => 1, 'Экспедиторский бонус' => 1];
            //$valiebles['Прошу оплатить .... часов за ,.... число, так как ....'] = 1;
        }
        $valieblesrequest = $this->model('Bonus')->getSelectRequest();
        
        $is_main = false;
        if($id == $this->user->id){
            $is_main = 1;    
        }
        $tpl = [
            'period' => $period,
            'is_generate' => $is_generate,
            'departments' => $departments,
            'edepartments' => $edepartments,
            'dchief' => $dchief,
            'user_id' => $id,
            'employee' => $employee,
            'salary' => $salary,
            'salary_id' => \Arr::get($salary,'id', 0),
            'esalary' => $esalary,
            'period' => $period,
            'filter' => $filter->createBuilder(),
            'menu' => $this->menu,
            'cmount' => $month,
            'cmonth' => $month,// текущий месяц
            'cyear' => $year,
            'auth_user' => $this->user->id,
            'is_rule' => $is_rule,
            'is_chief' => $this->is_chief,
            'is_main' => $is_main,
            'is_bookkeper' => $this->is_bookkeper,
            'addform' => $addform->createBuilder(),
            'valiebles' => $valiebles,
            'valieblesrequest' => $valieblesrequest,
            'price_hour' => $price_hour,
            'oklad' => $oklad,
            'bonuses' => $bonuses,
            'bonus_plus' => $bonus_plus,
            'tmanager' => $tmanager,
            'timesheet' => $timesheet,
            'work_days' => $work_days,
            'times' => $times,
            'isajax' => $ajax,
            'timeout' => $this->timeout,
        ];
        if($ajax){
            return $this->renderPartial('salary/user', $tpl);
        }else{
            return $this->render('salary/user', $tpl);    
        }
        
    }
    
    /**
     * @param integer $id
     *
     * @Method (!AJAX)
     */
    public function clearAction($id)
    {
        // создать logs из запроса
        $this->model('Salary')->updateReady($id);
    }
    
    /**
    *
    * @Method (!AJAX)
    */
    public function removeAction()
    {
        if(! $this->is_allow){
            $this->forbidden();
        }
        $filter = $this->form('SalaryUserFilter');
        $filter->setData($this->request->query->all());
        $month = $filter->getData('month', date("m"));
        $year = $filter->getData('year', date("Y"));
        $criterie = ['date' => $year.'-'.$month.'-01'];
        $this->model('Salary')->delete($criterie);
        $query = $filter->buildQuery();
        $this->redirect('/admin/employee/salary/'.$query);
    }
    
    /**
    *
    * @Method (!AJAX)
    */
    public function logsAction()
    {
        if(! $this->is_allow){
            $this->forbidden();
        }
        $filter = $this->form('SalaryLogFilter');
        $filter->setData($this->request->query->all());
        if ($this->request->is('POST')) {
            $filter->handle($this->request->post);
            if($filter->validate()){
                $query = $filter->buildQuery();
                $this->redirect('/admin/employee/salary/logs/'.$query);
            }
        }
        $afilter = [];
        
        $logs = [];//$this->model('SalaryLog')->getByList($afilter);
        foreach($this->model('SalaryLog')->getByList($afilter) as $log){
            $stamp = strtotime($log['creation_date']);
            if(! isset($logs[$stamp])) {
                $log['logs'] = [];
                $logs[$stamp] = $log;
            }
            $logs[$stamp]['logs'][] = $log;
        }
        ksort($logs);
        return $this->render('salary/logs', [
            'logs' => $logs,
            'filter' => $filter->createBuilder(),
        ]);
        
    }
    
    /**
    *
    * @Method (!AJAX)
    */
    public function cronAction()
    {
        if(! $this->is_allow){
            $this->forbidden();
        }
        // 5 числа генерирует за предыдущей месяц
        $ids = $salarydate = [];
        $tree = $this->model('Department')->getTreeSalary(); // tree Department Employees
        foreach($tree as $department){
            $dsalary = \Arr::get($department, 'datesalary', 21);
            foreach(\Arr::get($department, 'users', []) as $user){
                $_id = \Arr::get($user, 'id');
                $ids[] = \Arr::get($user, 'id');
                $salarydate[$_id] = $dsalary;
            }
        }
        
        
        $filter = $this->form('SalaryUserFilter');
        $oplansheet = $this->model('PlanSheet');
        $filter->setData($this->request->query->all());
        $month = $filter->getData('month', null);
        $year = $filter->getData('year', null);
        
        if(!$month AND !$year){
            $_date = new \DateTime();
            $_date->sub(new \DateInterval('P1M'));
            $month = $_date->format('m');
            $year = $_date->format('Y');
        }
        
        //Avanses
        $filteravans = ['date' => $year.'-'.$month.'-01'];
        $avanses = [];
        foreach($this->model('SalaryAvans')->getByList($filteravans) as $_avans){
            if(! isset($avanses[$_avans['user_id']])) $avanses[$_avans['user_id']] = [];
            $avanses[$_avans['user_id']][] = $_avans; 
        }
        $vaxes = [];
        foreach($this->model('SalaryVax')->getByList($filteravans) as $_vax){
            if(! isset($vaxes[$_vax['user_id']])) $vaxes[$_vax['user_id']] = $_vax;
        }
        // Planorg
        $mPlanOrg = $this->model('PlanOrg');
        $planorg = $mPlanOrg->getByDate($year.'-'.$month.'-01');
        $planorgFact = $mPlanOrg->getByFact($year.'-'.$month.'-01');
        $planorgFact = \Arr::get($planorgFact, 'profit', 0);
        if(null == $planorg){
            $data = ['date' => $year.'-'.$month.'-01',
                     'profit' => $planorgFact,
                     'owner_id' => OWNER_ID,
                    ];
            $mPlanOrg->insert($data);
        }else{
           $data = ['id' => $planorg['id'],
                    'profit' => $planorgFact,
                   ];
            $mPlanOrg->upsert($data);
        }

        
        $modelSalary = $this->model('Salary');
        $salary = $modelSalary->getByMount($month, $year);
        $calculate = $this->model('Calculate')->init($month, $year, NULL, $ids);
        $salarys = $calculate->calculate_all();
        $calculate->updateFact();
        $_series = [];
        foreach($salarys as $user_id => $_salary){
            $_s = \Arr::get($salary,$user_id, null);
            $data = ['creater' => $this->user->id,
                     'updater' => $this->user->id,
                     'created' => date("Y-m-d H:i:s"),
                     'updated' => date("Y-m-d H:i:s"),
                     'date' => $year."-".$month."-01",
                     'base' => \Arr::path($_salary, "esalary.base", 0), //ставка
                     'max' => \Arr::get($_salary, "max", 0), // Мог бы
                     'income' => \Arr::get($_salary, "income", 0), // Заработал без депремирования
                     'bonus' => \Arr::get($_salary, "total_bonus", 0), //депремирования не забудь о минусе
                     'plus' => \Arr::get($_salary, "plus", 0), //бонусы
                     //'balance' => \Arr::get($_salary, "total", 0), // то что осталось выдать
                     'total' => \Arr::get($_salary, "total", 0), // всего к выдаче
                     //'series' => $series,
                     'user_id' => $user_id
                     ];
            /*foreach($_salary['salary'] as $_indicator){
                $plan_id = \Arr::get($_indicator, 'plan_id', 0);
                $department_id = \Arr::get($_indicator, 'department_id', 0);
                $_plan_sheet_id = \Arr::get($_indicator, 'plansheet_id', 0);
                $user_id = \Arr::get($_indicator, 'user_id', 0);
                $is_common = \Arr::get($_indicator, 'is_common', 0);
                $_plan_fact = \Arr::get($_indicator, 'fact', 0);
                if($_plan_sheet_id){
                    $updatesheet = ['id' => $_plan_sheet_id, 'fact_amount' => $_plan_fact];
                    $oplansheet->upsert($updatesheet);
                }else{
                    if($_plan_fact){
                        $insertsheet = ['fact_amount' => $_plan_fact,
                                        'date' => $year."-".$month."-01",
                                        'type' => $plan_id,
                                        'plan_id' => $plan_id,
                                        'department_id' => $department_id,
                                        'manager_id' => $is_common ? 0 : $user_id,
                                        ];
                        $oplansheet->insert($insertsheet);
                    }
                }
            }
            */
            $data['bonus'] = -$data['bonus']; // в базе intval
            $sd = \Arr::get($salarydate, $user_id, 21);
            //defaults
            if($month == 12){
                $data['outdate'] = ($year+1)."-01-".$sd;
            }else{
                $data['outdate'] = $year."-".($month + 1)."-".$sd;
            }
            
            if($_s){
                $data['id'] = $_s['id'];
                $_s['total'] = $data['total'];
                $_s['income'] = $data['income'];
                $_balance = $_s['balance'] + ($data['total'] - $_s['total']);
                $data['balance'] = $_balance;
                //$data['balance'] = $modelSalary->calculatebalance($_s);
            }else{
                $avans = $avans_ready = 0;
                $_avanses = \Arr::get($avanses, $user_id,[]);
                foreach($_avanses as $_avans){
                    if($_avans['ready'] == SalaryAvans::READY_NO){
                        $avans = $avans + $_avans['avans'];
                    }else{
                        $avans_ready = $avans_ready + $_avans['avans'];  // не оприходовано   
                    }
                }
                $_vax = \Arr::get($vaxes, $user_id,null);
                if($_vax){
                    $data['vax'] = $_vax['vax'];    
                }
                
                $series = $modelSalary->generateSeries($month, $year);
                $data['series'] = $series;
                $data['avans'] = $avans_ready;
                $data['balance'] = \Arr::get($_salary, "total", 0) - $avans; // то что осталось выдать
            }
            $modelSalary->upsert($data);    
        }
        $modelSalary->updateAvansReady($year.'-'.$month.'-01');
        
        if($this->user->id){
            $this->redirect('/admin/employee/salary?month='.$month.'&year='.$year);
        }
    }
}