<?php

namespace Modules\Employee\Admin\Controllers;

/**
 * @Role (USER_ADMIN)
 */
class RecruitController extends BaseController
{
    
    /**
     * 
     * @Method (AJAX)
     */
    public function indexAction()
    {
        $form = $this->form('Employee\Account');
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
            return $this->renderPartial('account/login', [
                'form' => $form->createBuilder(),
            ]);
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (!AJAX)
     */
    public function index2Action($id)
    {
        $filter = $this->form('EmployeeFilter');
        $departments = $this->model('Department')->getTree();
        if ($this->request->is('POST')) {
            $filter->handle($this->request->post);
        }
        $employees = $this->model('Employee')->getByList($filter->getSafeData());
        return $this->render('employee/list', [
            'departments' => $departments,
            'employees' => $employees,
            'filter' => $filter->createBuilder(),
            'menu' => $this->menu
        ]);
    }
    
    
    /**
     * 
     * @Method (!AJAX)
     */
    public function accountAction()
    {
        $form = $this->form('Employee\Account');
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
            return $this->render('account/login', [
                'form' => $form->createBuilder(),
            ]);
        }
    }
    
    /**
     * 
     * @Method (!AJAX)
     */
    public function personalAction()
    {
        $form = $this->form('Employee\Personal');
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
            return $this->renderPartial('account/login', [
                'form' => $form->createBuilder(),
            ]);
        }
    }
    
    /**
     * 
     * @Method (!AJAX)
     */
    public function codexAction()
    {
        $form = $this->form('Employee\Codex');
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
            return $this->renderPartial('account/login', [
                'form' => $form->createBuilder(),
            ]);
        }
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
        $user = \Model::factory('User')->search($q, 12);
        return [
            'user' => $user,
        ];
    }
    
}