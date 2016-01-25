<?php
/**
* Управление планами пользователей
*
**/

namespace Modules\Employee\Admin\Controllers;

use Modules\Employee\Admin\Extensions\Calculate;

/**
 * @acesss (SALARY_SHEET)
 */
class EplanController extends BaseController
{
    public $menu = 'eplan';
    
    /**
     * @param integer $id
     *
     * @Method (!AJAX)
     */
    public function indexAction()
    {
        $filter = $this->form('EmployeePlanFilter');
        $form = $this->form('EmployeePlanEditUser');
        $eplans = $this->model('EmployeePlan')->getByListUsers($filter->getSafeData());
        $plans = $this->model('Plan')->getByList();
        $users = [];
        foreach($eplans as $eplan){
            if(! isset($users[$eplan['user_id']])){
                $users[$eplan['user_id']] = [
                                        'id' => $eplan['user_id'],
                                        'name' => $eplan['u_name'],
                                        'plans' => [],
                                        ];
            }
            $users[$eplan['user_id']]['plans'][] = $eplan;
        }
        return $this->render('eplans/list', [
            'eplans' => $eplans,
            'users' => $users,
            'plans' => $plans,
            'filter' => $filter->createBuilder(),
            'form' => $form->createBuilder(),
            'menu' => $this->menu,
        ]);
    }
    
    /**
      *
     * @Method (!AJAX)
     */
    public function listAction()
    {
        $filter = $this->form('EmployeePlanFilter');
        $eplans = $this->model('EmployeePlan')->getByList($filter->getSafeData());
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if($form->validate()){
                $this->redirect('/admin/employee/eplan/list'.$query);
            }
        }
        return $this->render('eplans/index', [
            'eplans' => $eplans,
            'mounts' => $mounts,
            'years' => $years,
            'filter' => $filter->createBuilder(),
            'menu' => $this->menu,
        ]);
    }
    
    /**
     * @param integer $id
     *
     * @Method (!AJAX)
     */
    public function viewAction($id)
    {
        if(! $id){
            $this->notFound();
        }
        $plans = $this->model('EmployeePlan')->getByUserId($id);
        $mount = date('m');
        $year = date('Y');
        $calculate = $this->model('Calculate')->init($mount, $year, NULL, $id);
        $salary = $calculate->calculate($id);
        $esalary = \Arr::path($salary, 'esalary.'.$id, NULL);
        return $this->render('eplans/view', [
            'plans' => $plans,
            'user_id' => $id,
            'salary' => $salary,
            'esalary' => $esalary,
            'menu' => $this->menu,
        ]);
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function userAction($id)
    {
        if(! $id){
            $this->notFound();
        }
        $plans = $this->model('EmployeePlan')->getByUserId($id);
        return $this->renderPartial('eplans/user', [
            'plans' => $plans,
            'user_id' => $id,
        ]);
    }
    
     /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function addAction($id)
    {
        $form = $this->form('EmployeePlanEdit');
        $plans = $this->model('Plan')->getByGroups();
        
        $help = null;
        $form->setData('user_id', $id);
        if ($this->request->is('POST')) {
            $form->setData('updater', $this->user->id);
            $form->setData('creater', $this->user->id);    
            $post = $this->request->post->all();
            $plan_id = \Arr::path($post, 'EmployeePlanEdit.plan_id', 0);
            $form->setData('plan_id', intval($plan_id));
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $eplan = $form->save();
                $edepartments = $this->model('Department')->getList();
                return [
                    'plan' => $eplan,
                    'html' => $this->renderPartial('eplans/td-edit2', ['plan' => $eplan, 'edepartments' => $edepartments]),
                ];
            }
        }
        else {
            $departments = [];
            foreach($this->model('Department')->getList() as $d){
                $departments[] = ['id' => $d['id'], 'name' => str_repeat('- ',($d['level'] -1 )).$d['number'].' '.$d['name']];
            }
            return $this->renderPartial('eplans/edit', [
                'form' => $form->createBuilder(),
                'plans' => $plans,
                'departments' => $departments,
                'plan' => null,
                'help' => $help
            ]);
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
        $help = null;
        if($eplan){
            $form->setData($eplan);
            $plan_id = \Arr::get($eplan, 'plan_id', 0);
            if($plan_id){
                $help = $this->model('Plan')->getHelp($plan_id);
            }
        }
        if ($this->request->is('POST')) {
            $form->setData('updater', $this->user->id);
            if(! $eplan){
                $form->setData('creater', $this->user->id);    
            }
            $post = $this->request->post->all();
            $plan_id = \Arr::path($post, 'EmployeePlanEdit.plan_id', 0);
            $form->setData('plan_id', intval($plan_id));
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $eplan = $form->save();
                $edepartments = $this->model('Department')->getList();
                return [
                    'plan' => $eplan,
                    'html' => $this->renderPartial('eplans/td-edit2', ['plan' => $eplan, 'edepartments' => $edepartments]),
                ];
            }
        }
        else {
            $departments = [];
            foreach($this->model('Department')->getList() as $d){
                $departments[] = ['id' => $d['id'], 'name' => str_repeat(' ',($d['level'] -1 )).$d['number'].' '.$d['name']];
            }
            return $this->renderPartial('eplans/edit', [
                'form' => $form->createBuilder(),
                'plans' => $plans,
                'departments' => $departments,
                'plan' => $eplan,
                'help' => $help
            ]);
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function valueAction($id)
    {
        $form = $this->form('EmployeePlanValue');
        if ($this->request->is('POST')) {
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
                    'eplan' => $eplan,
                ];
            }
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function helpAction($id)
    {
        $help = $this->model('Plan')->getHelp($id);
        return ['help' => $help];
    }

    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function removeAction($id)
    {
        $eplan = $this->model('EmployeePlan')->getById($id);
        if($eplan){
            $update = ['id' => $id, 'end' => date('Y-m-d')];
            $this->model('EmployeePlan')->upsert($update);
            return ['ok' => 1];
        }
        return $this->notFound();
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function clearAction($id)
    {
        $eplan = $this->model('EmployeePlan')->getById($id);
        if($eplan){
            $this->model('EmployeePlan')->delete($id);
            return ['ok' => 1];
        }
        return $this->notFound();
    }
    
    /**
     * 
     * @Method (!AJAX)
     */
    public function migrateAction()
    {
       return 1;
       $this->model('EmployeePlan')->migrate();
    }
    
}