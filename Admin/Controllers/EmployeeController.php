<?php

namespace Modules\Employee\Admin\Controllers;
use Modules\Employee\Admin\Extensions\Calendar;

class EmployeeController extends InitController
{
    /**
     * 
     * @param integer $id
     * @Method (!AJAX)
     */
    public function indexAction($id)
    {
        if(! $this->is_allow AND ! $this->is_bookkeper){
            $this->forbidden();
        }
        $tree = $this->model('Department')->getTreeSalary();
        $ids = [];
        foreach($tree as $department){
            foreach(\Arr::get($department, 'users', []) as $user){
                $ids[] = \Arr::get($user, 'id');        
            }
        }
        
        //Statuses 
        $mDoc = $this->model('EmployeeDoc');
        $mDocMap = $this->model('EmployeeDocMap');
        $mContact = $this->model('EmployeeContact');
        $mContactMap = $this->model('EmployeeContactMap');
        
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
        

        foreach($ids as $id){
            $doc = \Arr::get($docs, $id);
            $_docmap = \Arr::get($_docmaps, $id);
            $docmap = $mDocMap->getMapUser($_docmap);
            $doc_progress = $mDoc->progress($doc, $docmap);
            
            $contact = \Arr::get($contacts, $id);
            $_contactmap = \Arr::get($_contactmaps, $id);
            $contactmap = $mContactMap->getMapUser($_contactmap);
            $contact_progress = $mContact->progress($contact, $contactmap);
            
            $percent1 = \Arr::get($doc_progress, 'percent', 0);
            $percent2 = \Arr::get($contact_progress, 'percent', 0);
            $percent1 = $percent1 * 0.5;
            $percent2 = $percent2 * 0.5;
            $statuses[$id] = round($percent1+$percent2);
        }
        array_walk($tree, function(&$d) use ($statuses){
            foreach($d['users'] as $key => $u){
                $id = $u['id'];
                $d['users'][$key]['doc_status'] = \Arr::get($statuses, $id, 0);
            }
        });
        
        $this->menu = 'employee';
        return $this->render('employee/list', [
            'tree' => $tree,
            'is_rule' => $this->is_allow,
            'is_chief' => $this->is_chief,
            'is_bookkeper' => $this->is_bookkeper,
            'menu' => $this->menu
        ]);
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function infoAction($id)
    {
        $contact = $this->model('EmployeeContact')->getById($id);
        $employee = $this->model('EmployeeData')->getById($id);
        return $this->renderPartial('employee/info', [
                'contact' => $contact,
                'employee' => $employee,
            ]);
    }
    
    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     * @Method (!AJAX)
     */
    public function managerAction($id)
    {
        $this->menu = 'employee';
        $filter = $this->form('EmployeeFilter');
        $move = $this->createForm('move', []);
        $departments = $this->model('Department')->getTree();
        if ($this->request->is('POST')) {
            $filter->handle($this->request->post);
        }
        $employees = $this->model('Employee')->getByList($filter->getSafeData());
        return $this->render('employee/manager', [
            'departments' => $departments,
            'employees' => $employees,
            'filter' => $filter->createBuilder(),
            'move' => $move->createBuilder(),
            'menu' => $this->menu
        ]);
    }
    
    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     * @Method (AJAX)
     */
    public function numberAction($id)
    {
        $form = $this->form('EmployeeNumber');
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $employee = $form->save();
                return [
                    'employee' => $employee,
                ];
            }
        }
    }
    
    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     * @Method (AJAX)
     */
    public function statusAction($id)
    {
        $employee = $this->model('EmployeeData')->getById($id);
        if(null == $employee){
            $this->notFound();
        }
        if ($this->request->is('POST')) {
            $post = $this->request->post->all();
            $statuses = $this->model('Employee')->listStatus();
            $status = \Arr::get($post, 'status', 0);
            $status = intval($status);
            if(! $status){
                return ['errors' => 'Не заполнили статус'];
            }elseif(! \Arr::get($statuses, $status)){
                return ['errors' => 'Нет такого статуса'];
            }
            $data = ['user_id' => $id,
                     'status' => $status];
            $this->model('EmployeeData')->upsert($data);
            return [
                'employee' => $this->model('EmployeeData')->getById($id),
            ];
            
        }
    }
    
    /**
     * @acesss (SALARY_SHEET)
     *
     * @Method (!AJAX)
     */
    public function setstatusallAction()
    {
        $m = $this->model('EmployeeData');
        foreach($this->model('EmployeeData')->getByList() as $employee){
            if($employee['number'] == 1){
                if($employee['status'] == 2){
                    echo 'id:'.$employee['user_id'].'-'.$employee['number'].'<>'.$employee['status'].'<br />';
                    $data = ['user_id' => $employee['user_id'],
                             'status' => 3];
                    $m->upsert($data);
                }
            }
            if($employee['number'] == 2){
                if($employee['status'] == 1){
                    echo 'id:'.$employee['user_id'].'-'.$employee['number'].'<>'.$employee['status'].'<br />';
                    $data = ['user_id' => $employee['user_id'],
                             'status' => 3];
                    $m->upsert($data);
                }
            }
        }
    }
    
    /**
     * @acesss (SALARY_SHEET)
     *
     * @Method (!AJAX)
     */
    public function setstatusdocAction()
    {
        $ids = [];
        $statuses = [];
        $tree = $this->model('Department')->getTreeSalary();
        foreach($tree as $department){
            foreach(\Arr::get($department, 'users', []) as $user){
                $ids[] = \Arr::get($user, 'id');        
            }
        }
        $mDoc = $this->model('EmployeeDoc');
        $mDocMap = $this->model('EmployeeDocMap');
        $mContact = $this->model('EmployeeContact');
        $mContactMap = $this->model('EmployeeContactMap');
        
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
        foreach($ids as $id){
            $doc = \Arr::get($docs, $id);
            $_docmap = \Arr::get($_docmaps, $id);
            $docmap = $mDocMap->getMapUser($_docmap);
            $doc_progress = $mDoc->progress($doc, $docmap);
            
            $contact = \Arr::get($contacts, $id);
            $_contactmap = \Arr::get($_contactmaps, $id);
            $contactmap = $mContactMap->getMapUser($_contactmap);
            $contact_progress = $mContact->progress($contact, $contactmap);
            
            $percent1 = \Arr::get($doc_progress, 'percent', 0);
            $percent2 = \Arr::get($contact_progress, 'percent', 0);
            $percent1 = $percent1 * 0.5;
            $percent2 = $percent2 * 0.5;
            $statuses[$id] = $percent1+$percent2;
        }
        
        return;
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function docAction($id)
    {
        $doc = $this->model('EmployeeDoc')->getById($id);
        $form = $this->form('Employee\Doc');
        $formmap = $this->form('Employee\DocMap');
        if(null != $doc){
            
        }
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            $formmap->handle($this->request->post);
            $user_id = $form->getData('user_id');
            $formmap->setData('user_id', $user_id);
            
            $form->setData('creater', $this->user->id);
            $form->setData('updater', $this->user->id);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $doc = $form->save();
                if($this->is_allow){
                    $docmap = $formmap->save();
                }
                return [
                    'doc' => $doc,
                    'progress' => $this->model('EmployeeDoc')->progress($doc, $docmap),
                ];
            }
        }else{
            $docs = $this->model('EmployeeDoc')->getDocs();
            return $this->renderPartial('employee/doc', [
                'form' => $form->createBuilder(),
                'doc' => $doc,
                'docs' => $docs,
            ]);
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function contactAction($id)
    {
        if(! $this->is_allow AND ! $this->is_bookkeper){
            $this->forbidden();
        }
        $contact = $this->model('EmployeeContact')->getById($id);
        $form = $this->form('Employee\Contact');
        $formmap = $this->form('Employee\ContactMap');
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            $formmap->handle($this->request->post);
            $user_id = $form->getData('user_id');
            $formmap->setData('user_id', $user_id);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $contact = $form->save();
                if($this->is_allow){
                    $contactmap = $formmap->save();
                }
                return [
                    'contact' => $contact,
                    'progress' => $this->model('EmployeeContact')->progress($contact),
                ];
            }
        }else{
            $contacts = $this->model('EmployeeContact')->getContacts();
            if(null != $contact){
                $form->setData($contact);   
            }
            return $this->renderPartial('employee/contact', [
                'form' => $form->createBuilder(),
                'contact' => $contact,
                'contacts' => $contacts,
            ]);
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function updatedepartmentAction($id)
    {
        $form = $this->form('Employee\Department');
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $employee = $form->save();
                return [
                    'employee' => $employee,
                ];
            }
        }else{
            $employee = $this->model('Employee')->getById($id);
            $form->setData($employee);
            $departments = [];
            foreach($this->model('Department')->getList() as $d){
                $departments[] = ['id' => $d['id'], 'name' => str_repeat(' - ',($d['level'] -1 )).$d['name']];
            }  
            return $this->renderPartial('employee/editor/department', [
                'form' => $form->createBuilder(),
                'departments' => $departments,
            ]);
        }
    }
    
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function editor2Action($id)
    {
        if(! $this->is_allow AND ! $this->is_bookkeper){
            $this->forbidden();
        }
        $employee = $this->model('EmployeeData')->getById($id);
        
        $form0 = $this->form('EmployeeEdit');
        $form0->setData($employee);
        
        //Doc
        $doc = $this->model('EmployeeDoc')->getById($id);
        $docs = $this->model('EmployeeDoc')->getDocs();
        $_docmap = $this->model('EmployeeDocMap')->getByUserId($id);
        $docmap = $this->model('EmployeeDocMap')->getMapUser($_docmap);
        $doc_progress = $this->model('EmployeeDoc')->progress($doc, $docmap);
        
        $form1 = $this->form('Employee\Doc');
        if(null != $doc){
            $form1->setData($doc);
        }else{
            $form1->setData('user_id', $id);
        }
        //Contact
        $contacts = $this->model('EmployeeContact')->getGroupContacts();
        $econtacts = $this->model('EmployeeContact')->getById($id);
        $_cmap = $this->model('EmployeeContactMap')->getByUserId($id);
        $cmap = $this->model('EmployeeContactMap')->getMapUser($_cmap);
        $contact_progress = $this->model('EmployeeContact')->progress($econtacts, $cmap);
        $form2 = $this->form('Employee\Contact');
        $form2->setData($employee);
        
        return $this->renderPartial('employee/editor/base2', [
            'employee' => $employee, //employee
            'form0' => $form0->createBuilder(), //login
            'form1' => $form1->createBuilder(), //doc
            'doc' => $doc,
            'docmap' => $docmap,
            'docs' => $docs,
            'doc_progress' => $doc_progress,
            'form2' => $form2->createBuilder(), //contact
            'contacts' => $contacts,
            'econtacts' => $econtacts,
            'cmap' => $cmap,
            'contact_progress' => $contact_progress,
            'is_rule' => $this->is_allow,
            'user_id' => $id,
        ]); 
    }
    
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function editorAction($id)
    {
        $employee = $this->model('EmployeeData')->getById($id);
        
        $form0 = $this->form('EmployeeEdit');
        $form0->setData($employee);
        
        //Doc
        $doc = $this->model('EmployeeDoc')->getById($id);
        $docs = $this->model('EmployeeDoc')->getDocs();
        $_docmap = $this->model('EmployeeDocMap')->getByUserId($id);
        $docmap = $this->model('EmployeeDocMap')->getMapUser($_docmap);
        $doc_progress = $this->model('EmployeeDoc')->progress($doc, $docmap);
        
        $form1 = $this->form('Employee\Doc');
        if(null != $doc){
            $form1->setData($doc);
        }else{
            $form1->setData('user_id', $id);
        }
        //Contact
        $contacts = $this->model('EmployeeContact')->getGroupContacts();
        $econtacts = $this->model('EmployeeContact')->getById($id);
        $_cmap = $this->model('EmployeeContactMap')->getByUserId($id);
        $cmap = $this->model('EmployeeContactMap')->getMapUser($_cmap);
        $contact_progress = $this->model('EmployeeContact')->progress($econtacts, $cmap);
        $form2 = $this->form('Employee\Contact');
        $form2->setData($employee);
        
        //department
        $form3 = $this->form('Employee\Department');
        $form3->setData($employee);
        $departments = [];
        $edepartments = [];
        foreach($this->model('Department')->getList() as $d){
            $departments[] = ['id' => $d['id'], 'name' => str_repeat(' - ',($d['level'] -1 )).$d['number'].'. '.$d['name']];
            $edepartments[$d['id']] = $d;
        }
        
        //Rule
        $form4 = $this->form('Employee\Codex');
        $form4->setData($employee);
        $userrule = $this->model('Codex')->getByUser($id);
        $userrule = $this->model('Codex')->asArrayGroup($userrule);
        $disallow = [3 => 'USER_CLIENT', 17 => 'USER_WHOLESALER', 18 => 'USER_FRANCHISER'];
        if(isset($disallow[$employee['role_id']])){
            unset($disallow[$employee['role_id']]);
        }
        $disallow = array_flip($disallow);
        $roles = $this->model('Codex')->getByAllowList($disallow);
        
        // Grafik
        $form5 = $this->form('TimeManagerEdit');
        $tmanager = $this->model('ManagerTimeSheet')->getByUserId($id);
        $tmanager_progress = $this->model('ManagerTimeSheet')->progress($tmanager);
        if($tmanager){
            $form5->setData($tmanager);
        }else{
            $form5->setData('user_id', $id);
        }
        $days = [];
        foreach(range(1,6) as $_d){
            $days[$_d] = Calendar::weekdays($_d);
        }
        $days['0'] = Calendar::weekdays(0);
        
        $form6 = $this->form('EmployeePlanEdit');
        $form6->setData('user_id', $id);
        $f = Calendar::getPeriodMonth(date('Y-m').'-01');
        $eplans = $this->model('EmployeePlan')->getByUserId($id, $f);
        $plans = $this->model('Plan')->getByGroups();
        
        $form7 = $this->form('ESalaryAdd');
        $esalary = $this->model('EmployeeSalary')->getByUserId($id);
        if(null != $esalary){
            $form7->setData($esalary);
        }else{
            $form7->setData('user_id', $id);
        }
        return $this->renderPartial('employee/editor/base', [
            'employee' => $employee, //employee
            'form0' => $form0->createBuilder(), //login
            'form1' => $form1->createBuilder(), //doc
            'doc' => $doc,
            'docmap' => $docmap,
            'docs' => $docs,
            'doc_progress' => $doc_progress,
            'form2' => $form2->createBuilder(), //contact
            'contacts' => $contacts,
            'econtacts' => $econtacts,
            'cmap' => $cmap,
            'contact_progress' => $contact_progress,
            'form3' => $form3->createBuilder(), //contact
            'departments' => $departments,
            'form4' => $form4->createBuilder(), //rule
            'userrule' => $userrule,
            'roles' => $roles,
            'form5' => $form5->createBuilder(), //grafik
            'days' => $days,
            'tmanager' => $tmanager,
            'tmanager_progress' => $tmanager_progress,
            'form6' => $form6->createBuilder(), //eplans
            'edepartments' => $edepartments,
            'plans' => $plans,
            'eplans' => $eplans,
            'form7' => $form7->createBuilder(), //eplans
            'esalary' => $esalary,
            'is_rule' => 1,
            'user_id' => $id,
        ]); 
    }
    
    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function editAction($id)
    {
        $form = $this->form('EmployeeEdit');
        $employee = $this->model('Employee')->getByDetail($id);
        $statuses = $this->model('Employee')->listStatus();
        if(! $employee){
            return $this->notFound();
        }
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $employee = $form->save();
                return [
                    'employee' => $employee,
                ];
            }
        }
        else {
            $departments = [];
            foreach($this->model('Department')->getList() as $d){
                $departments[] = ['id' => $d['id'], 'name' => str_repeat(' - ',($d['level'] -1 )).$d['name']];
            }
            $form->setData($employee);
            return $this->renderPartial('employee/edit', [
                'form' => $form->createBuilder(),
                'employee' => $employee,
                'statuses' => $statuses,
                'departments' => $departments,
            ]);
        }
    }
    
    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     *
     * @Method (POST,AJAX)
     */
    public function moveAction($id)
    {
        if ($this->request->is('POST')) {
            $move = $this->createForm('move', []);
            $move->handle($this->request->post);
            $users = $move->getData('users');
            $department = $move->getData('department');
            if(! is_array($users)){
                $users = explode(',',$users);
            }
            $this->move($users, $department);
        }
    }
    
    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function departmentAction($id)
    {
        $form = $this->form('EmployeeDepartment');
        $form->setData('department_id', $id);
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if(! $form->validate()){
                return ['errors' => $form->getErrors()];
            }else{
                if($form->isSubmitted()){
                    $department = $form->save();
                    return ['department' => $department];
                }
            }
        }else{
            return $this->renderPartial('employee/department', [
                'form' => $form->createBuilder(),
            ]);
        }
    }
    
    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     * @Method (AJAX)
     */
    public function changedepartmentAction($id)
    {
        $form = $this->form('EmployeeMove');
        $form->setData('user_id', $id);
        $data = $this->request->query->all();
        $department_id = \Arr::get($data, 'department_id',0);
        $form->setData('department_id',$department_id);
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if(! $form->validate()){
                return ['errors' => $form->getErrors()];
            }else{
                if($form->isSubmitted()){
                    $department = $form->save();
                    return ['department' => $department];
                }
            }
        }else{
            $edepartments = $this->model('Employee')->getByList(['user_id' => $id]);
            $departments = [];
            foreach($this->model('Department')->getList() as $d){
                $departments[] = ['id' => $d['id'],
                                  'name' => str_repeat('-', ($d['level'] - 1)).$d['name']
                                  ];
            }
            return $this->renderPartial('employee/move', [
                'form' => $form->createBuilder(),
                'edepartments' => $edepartments,
                'departments' => $departments,
                'department_id' => $department_id,
            ]);
        }
    }
    
    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     * @Method (AJAX)
     */
    public function adddepartmentAction($id)
    {
        $form = $this->form('EmployeeAdd');
        $form->setData('user_id', $id);
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if(! $form->validate()){
                return ['errors' => $form->getErrors()];
            }else{
                if($form->isSubmitted()){
                    $employee = $form->save();
                    return [
                        'employee' => $employee,
                    ];
                }
            }
        }else{
            $departments = [];
            foreach($this->model('Department')->getList() as $d){
                $departments[] = ['id' => $d['id'],
                                  'name' => str_repeat('-', ($d['level'] - 1)).$d['name']
                                  ];
            }
            return $this->renderPartial('employee/department_add', [
                'form' => $form->createBuilder(),
                'departments' => $departments
            ]);
        }
    }
    
    /**
     * @acesss (SALARY_SHEET)
     * 
     * @param integer $id
     * @Method (AJAX)
     */
    public function fireAction($id)
    {
        $form = $this->form('EmployeeFire');
        $emploee = $this->model('Employee')->getByDetail($id);
        $form->setData('user_id', $id);
        $form->setData($emploee);
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if(! $form->validate()){
                return ['errors' => $form->getErrors()];
            }else{
                if($form->isSubmitted()){
                    $employee = $form->save();
                    return ['employee' => $employee];
                }
            }
        }else{
            return $this->renderPartial('employee/fire', [
                'form' => $form->createBuilder(),
                'employee' => $emploee,
            ]);
        }
    }
    
    
    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     * @Method (AJAX)
     */
    public function defaultdepartmentAction($id)
    {
        if ($this->request->is('POST')) {
            $post = $this->request->query->all();
            $deraptment_id = \Arr::get($post, 'department_id', 0);
            $data = ['user_id' => $id,
                     'department_id' => $deraptment_id
                     ];
            $up = $this->model('EmployeeData')->upsert($data);
            return ['department_id' => $deraptment_id];
        }
    }
    
    protected function move(array $ids, $department = NULL)
    {
        return $this->model('Employee')->move($ids,$department);
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function quickAction($id)
    {
        $employee = $this->model('Employee')->getByDetail($id);
        return $this->renderPartial('employee/quick', [
            'employee' => $employee,
        ]);
    }
    
    /**
     * @param string $q [\d\w\@\.\_]{2,}
     *
     * @Method (POST, AJAX)
     */
    public function searchAction($q)
    {
        if (null === $q) {
            return [];
        }
        $employee = $this->model('Employee')->search($q, 10);

        return [
            'employee' => $employee,
        ];
    }

    /**
     * @acesss (SALARY_SHEET)
     * @param integer $departmentId
     */
    public function addAction($departmentId)
    {
        $form = $this->form('EmployeeAdd');
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $employee = $form->save();

                return [
                    'employee' => $employee,
                ];
            }
        }
        else {
            $form->setData('department_id', $departmentId);

            return $this->renderPartial('department/employee_add', [
                'form' => $form->createBuilder(),
            ]);
        }
    }

    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function removeAction($id)
    {
        $form = $this->form('EmployeeClose');
        $employee = $this->model('Employee')->getById($id);
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $employee = $form->save();
                return [
                    'employee' => $employee,
                ];
            }
        }
        else {
            $form->setData('id', $id);
            return $this->renderPartial('employee/close', [
                'form' => $form->createBuilder(),
                'employee' => $employee,
            ]);
        }
        /*$employee = $this->model('Employee')->getById($id);
        if(null == $employee){
            $this->notFound();
        }
        $end = date('Y-m-d');
        $data = ['id' => $id, 'end' => $end];
        $this->model('Employee')->upsert($data);
        return [];*/
    }

    /**
     * @acesss (SALARY_SHEET)
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function clearAction($id)
    {
        $employee = $this->model('Employee')->getById($id);

        if(null == $employee){
            $this->notFound();
        }
        $this->model('Employee')->delete(['id' => $id]);
    }
    

    protected function loadEmployee($id)
    {
        $employee = $this->model('Employee')->getById($id);
        if (null !== $employee) {
            return $employee;
        }
        $this->notFound();
    }
    
    /**
     *
     * @Method (!AJAX)
     */
    public function migrateAction()
    {
        return 1;
        $this->model('EmployeeData')->migrate();
    }
}