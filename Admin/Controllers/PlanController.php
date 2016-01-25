<?php
/**
* Показатели
*
**/
namespace Modules\Employee\Admin\Controllers;


/**
 * @acesss (SALARY_SHEET)
 */
class PlanController extends BaseController
{
    public $menu = 'plan';
    /**
     * 
     * @Method (!AJAX)
     */
    public function indexAction()
    {
        $plans = $this->model('Plan')->getByList();
        return $this->render('plans/index', [
            'plans' => $plans,
            'menu' => $this->menu
        ]);
    }
    
     /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function addAction($id)
    {
        $form = $this->form('PlanEdit');
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
            return $this->renderPartial('plans/edit', [
                'form' => $form->createBuilder(),
                'plan' => NULL
            ]);
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (!AJAX)
     */
    public function editAction($id)
    {
        $form = $this->form('PlanEdit');
        $plan = $this->model('Plan')->getById($id);
        $planPlans = $this->model('PlanPlan')->getByPlansId($id);
        $common_plan = [];
        
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            $form->save();
            /*return [
                'plan' => $plan,
            ];*/
            $this->redirect('/admin/employee/plan/');
        }else{
            if($plan){
                $form->setData($plan);
                $plans = $this->model('Plan')->getByList(['is_common' => 0]);
                $common_plan = $this->model('Plan')->getByList(['is_common' => 1, 'is_plan_based' => 1]);
            }
        }
        //else {
            return $this->render('plans/edit', [
                'form' => $form->createBuilder(),
                'plan' => $plan,
                'plans' => $plans,
                'plan_plans' => $planPlans,
                'common_plan' => $common_plan
            ]);
        //}
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function removeAction($id)
    {
        $plan = $this->model('Plan')->getById($id);
        if($plan){
            if($this->model('Plan')->delete($id)){
                return ['ok' => 1];
            }
        }
    }
    
    /**
     * @param integer $id
     */
    public function addplanAction($id)
    {
        $form = $this->form('PlanPlanAdd');
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
            $form->setData('plan_id', $id);
            $plans = $this->model('Plan')->getByList([]);
            return $this->renderPartial('plans/plan_add', [
                'form' => $form->createBuilder(),
                'plans' => $plans
            ]);
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function removeplanAction($id)
    {
        $plan = $this->model('PlanPlan')->delete($id);
        if (null !== $plan) {
            return [$plan];
        }
    }
    
}