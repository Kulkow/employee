<?php
/**
* Управление ставками пользователями
*
**/

namespace Modules\Employee\Admin\Controllers;

/**
 * @acesss (SALARY_SHEET)
 */
class EsalaryController extends BaseController
{
    /**
     * @param integer $id
     *
     * @Method (!AJAX)
     */
    public function indexAction($id)
    {
        $filter = $this->form('ESalaryFilter');
        if ($this->request->is('POST')) {
            $filter->handle($this->request->post);
        }
        $employees = $this->model('Employee')->getByList($filter->getSafeData());
        return $this->render('salary/list', [
            'employees' => $employees,
            'filter' => $filter->createBuilder(),
            'menu' => $this->menu
        ]);
    }
    
    /**
     * @param integer $id
     * 
     * @Method (AJAX)
     */
    public function editAction($id)
    {
        $form = $this->form('ESalaryAdd');
        $esalary = $this->model('EmployeeSalary')->getById($id);
        if ($this->request->is('POST')){
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $esalary = $form->save();
                return [
                    'esalary' => $esalary,
                ];
            }
        }
        else {
            if(null == $esalary){
                //$form->setData('user_id', $id);
            }else{
                $form->setData($esalary);
            }
            return $this->renderPartial('esalary/edit', [
                'form' => $form->createBuilder(),
                'esalary' => $esalary,
            ]);
        }
    }
    
    /**
     * @param integer $id
     * 
     * @Method (AJAX)
     */
    public function userAction($id)
    {
        $form = $this->form('ESalaryAdd');
        $esalary = $this->model('EmployeeSalary')->getByUserId($id);
        if ($this->request->is('POST')){
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $esalary = $form->save();
                return [
                    'esalary' => $esalary,
                ];
            }
        }
        else {
            if(null == $esalary){
                $form->setData('user_id', $id);
            }else{
                $form->setData($esalary);
            }
            return $this->renderPartial('esalary/edit', [
                'form' => $form->createBuilder(),
                'esalary' => $esalary,
            ]);
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function clearAction($id)
    {
        $eplan = $this->model('EmployeeSalary')->getById($id);
        if($eplan){
            $this->model('EmployeeSalary')->delete($id);
            return ['ok' => 1];
        }
        return $this->notFound();
    }
    
    /**
     * @param integer $id
     * @Method (!AJAX)
     */
    public function migrateAction($id)
    {
        return 1;
        $this->model('EmployeeSalary')->mirgate();
    }
}