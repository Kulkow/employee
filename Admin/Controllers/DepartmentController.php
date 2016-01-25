<?php

namespace Modules\Employee\Admin\Controllers;

/**
 * @acesss (SALARY_SHEET)
 */
class DepartmentController extends BaseController
{
    /**
     * @Method (GET, !AJAX)
     */
    public function allAction()
    {
        $this->model('Department')->setAllNumber();
    }
    
        
    /**
     * @Method (GET, !AJAX)
     */
    public function listAction()
    {
        $departments = $this->model('Department')->getTree();
        return $this->render('department/list', [
            'departments' => $departments,
        ]);
    }

    /**
     * @param integer $id
     *
     * @Method (GET, !AJAX)
     */
    public function indexAction($id)
    {
        $department = $this->loadDepartment($id);
        $employees = $this->model('Employee')->getByDepartment($department['id']);
        $plans = $this->model('DepartmentPlan')->getByDeparmentId($department['id']);
        return $this->render('department/view', [
            'department' => $department,
            'employees' => $employees,
            'plans' => $plans
        ]);
    }

    /**
     * @param integer $id
     * 
     * @Method (AJAX)
     */
    public function addAction($id)
    {
        $form = $this->form('DepartmentEdit');
        $form->setData('pid',$id);
        return $this->edit($form);
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function numberAction($id)
    {
        $form = $this->form('DepartmentNumber');
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $department = $form->save();
                return [
                    'department' => $department,
                ];
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
        $form = $this->form('DepartmentEdit');
        if ($this->request->is('GET')) {
            $department = $this->loadDepartment($id);
            $form->setData($department);
        }

        return $this->edit($form);
    }

    protected function edit($form)
    {
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $department = $form->save();

                return [
                    'department' => $department,
                ];
            }
        }
        else {
            return $this->renderPartial('department/edit', [
                'form' => $form->createBuilder(),
            ]);
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
    */
    public function move($form)
    {
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $department = $form->save();

                return [
                    'department' => $department,
                ];
            }
        }
        else {
            return $this->renderPartial('department/edit', [
                'form' => $form->createBuilder(),
            ]);
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (POST, AJAX)
     */
    public function removeAction($id)
    {
        $department = $this->model('Department')->remove($id);
        if (null !== $department) {
            return [$department];
        }
    }
    
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function upAction($id)
    {
        $department = $this->model('Department')->up($id);
        if (null !== $department) {
            return [$department];
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function downAction($id)
    {
        $department = $this->model('Department')->down($id);
        if (null !== $department) {
            return [$department];
        }
    }

    /**
     * @param integer $id
     *
     * @Method (POST, AJAX)
     */
    public function removeChiefAction($id)
    {
        $department = $this->loadDepartment($id);
        $this->model('Department')->update(['chief_id' => null], $department['id']);
    }

    /**
     * @param integer $departmentId
     */
    public function addplanAction($departmentId)
    {
        $form = $this->form('DepartmentPlanAdd');
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
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
            $form->setData('department_id', $departmentId);
            $plans = $this->model('Plan')->getByList([]);
            return $this->renderPartial('department/plan_add', [
                'form' => $form->createBuilder(),
                'plans' => $plans
            ]);
        }
    }
    
    
    /**
     * @param integer $id
     *
     * @Method (POST, AJAX)
     */
    public function removeplanAction($id)
    {
        if($this->model('DepartmentPlan')->getById($id)){
            $this->model('DepartmentPlan')->delete($id);
        }else{
            $this->notFound();
        }
    }

    protected function loadDepartment($id)
    {
        $department = $this->model('Department')->getById($id);
        if (null !== $department) {
            return $department;
        }
        $this->notFound();
    }
    
    /**
     * 
     *
     * @Method (!AJAX)
     */
    public function migrateAction()
    {
        $this->model('Department')->migrate();
    }
    
}