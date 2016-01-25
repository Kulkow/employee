<?php
namespace Modules\Employee\Admin\Controllers;
use Modules\Employee\Admin\Extensions\Calculate;

/**
 * @acesss (SALARY_SHEET)
 */
class RuleController extends BaseController
{
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function codexAction($id)
    {
        $employee = $this->model('EmployeeData')->getById($id);
        $form = $this->form('Employee\Codex');
        $form->setData($employee);
        $userrule = $this->model('Codex')->getByUser($id);
        $userrule = $this->model('Codex')->asArrayGroup($userrule);
        $roles = $this->model('Codex')->getByList();
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $userrule = $form->save();
                $role_id = $form->getData('role_id');
                return [
                    'role_id' => $role_id,
                    'userrule' => $userrule,
                ];
            }
        }
        else {
            return $this->renderPartial('account/codex', [
                'form' => $form->createBuilder(),
                'roles' => $roles,
                'user_id' => $id,
                'userrule' => $userrule,
            ]);
        }
    }    
}