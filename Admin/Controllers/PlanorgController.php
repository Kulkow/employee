<?php

namespace Modules\Employee\Admin\Controllers;

/**
 * @acesss (SALARY_SHEET)
 */
class PlanorgController extends BaseController
{
    /**
     *
     * @Method (!AJAX)
     */
    public function indexAction()
    {
        $filter = $this->form('PlanOrgFilter');
        if ($this->request->is('POST')) {
            $filter->handle($this->request->post);
            if ($filter->validate()) {
                $query = $filter->buildQuery();
                $this->redirect('/admin/employee/planorg'.$query);
            }
        }
        $filter->setData($this->request->query->all());
        $plans = $this->model('PlanOrg')->getByList($filter->getSafeData());
        return $this->render('planorg/list', [
            'plans' => $plans,
            'filter' => $filter->createBuilder(),
            'menu' => $this->menu
        ]);
    }
    
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function addAction()
    {
        $form = $this->form('PlanOrgEdit');
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
            return $this->renderPartial('planorg/edit', [
                'form' => $form->createBuilder(),
                'plan' => null,
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
        $form = $this->form('PlanOrgEdit');
        $plan = $this->loadPlan($id);
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
            $form->setData($plan);
            return $this->renderPartial('planorg/edit', [
                'form' => $form->createBuilder(),
                'plan' => $plan,
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
        $plan = $this->loadPlan($id);
        if(null !== $plan){
            $this->model('PlanOrg')->delete($id);
        }
    }
    
    /**
     *
     * @Method (!AJAX)
     */
    public function renerateAction()
    {
        $y = 12*10;
        $all = $this->model('PlanSheet')->getByAll(null, $y);
        $list = [];
        $format_all = 'Y-m';
        foreach($this->model('PlanOrg')->getByList([]) as $plan){
            $key = date($format_all, strtotime($plan['date']));
            $list[$key] = $plan;
        }
        foreach($all as $_plan){
            $key = $_plan['month'];
            $_plan['profit'] = intval($_plan['profit']);
            if($l = \Arr::get($list, $key, false)){
                $data = ['id' => $l['id'],
                         'profit' => $_plan['profit'],
                        ];
                echo 'UPdate<br/>';
                $this->model('PlanOrg')->upsert($data);
            }else{
                $data = ['date' => $key.'-01',
                         'profit' => $_plan['profit'],
                         'owner_id' => OWNER_ID,
                        ];
                echo 'insert<br/>';
                print_r($data);
                $this->model('PlanOrg')->insert($data);
            }
        }
    }

    protected function loadPlan($id)
    {
        $plan = $this->model('PlanOrg')->getById($id);
        if (null !== $plan) {
            return $plan;
        }
        $this->notFound();
    }
}