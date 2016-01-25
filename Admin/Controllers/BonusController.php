<?php
namespace Modules\Employee\Admin\Controllers;
use Modules\Employee\Admin\Models\Bonus;
use Modules\Employee\Admin\Extensions\Calendar;

/**
 * @acesss (ADMIN_PANEL_ACCESS)
 */
class BonusController extends InitController
{
    public $menu = 'bonus';

    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function viewAction($id)
    {
        $form = $this->form('BonusEdit');
        $valiebles = ['Опоздание' => -1, 'Не отработано' => -1, 'Недостача' => -1, 'Экспедиторский бонус' => 1];
        $filter = $this->form('BonusFilter');
        $filter->setData('user_id', $id);
        if ($this->request->is('POST')) {
            $filter->handle($this->request->post);
            if ($filter->validate()) {
                $query = $filter->buildQuery();
                $this->redirect('/admin/employee/bonus/view/'.$id.$query);
            }
        }
        $filter->setData($this->request->query->all());
        $data = $filter->getSafeData();
        $bonuses = $this->model('Bonus')->getByList($data);
        array_walk($bonuses,function(&$bonus){
            $bonus['approved'] = Bonus::is_approved($bonus);
        });
        
        $month = $filter->getData('month', date('m'));
        $year = $filter->getData('year', date('Y'));
        
        $bonus_mount = $year.'-'.$month;
        if($bonus_mount == date('Y-m')){
            $bonus_mount = date('Y-m-d');
        }else{
            $bonus_mount = $bonus_mount.'-'.date('d');
        }
        $form->setData('date', $bonus_mount);
        $form->setData('manager_id', $id);
        
        // оклад
        $oklad = $this->model('EmployeeSalary')->getByOkladUserId($id);
        $start = date('Y-m-d', strtotime($bonus_mount));
        
        $_date = new \DateTime($start);
        $_date->add(new \DateInterval('P1M'));
        $_date->sub(new \DateInterval('P1D'));
        $end = $_date->format('Y-m-d');
        
        $coun_word_day = Calendar::GetWorkingDayCount($start, $end);// кол-во рабочих дней
        if($coun_word_day){
            $price_hour = $oklad/($coun_word_day*8);
        }else{
            $price_hour = 0;
        }
        return $this->renderPartial('bonus/view', [
            'filter' => $filter->createBuilder(),
            'bonuses' => $bonuses,
            'user_id' => $id,
            'auth_user' => $this->user->id,
            'is_rule' => $this->is_allow,
            'form' => $form->createBuilder(),
            'valiebles' => $valiebles,
            'auth_user' => $this->user->id,
            'price_hour' => $price_hour
        ]);
    }
    
     /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function addAction($id)
    {
        $form = $this->form('BonusEdit');
        $valiebles = ['Опоздание' => -1, 'Не отработано' => -1, 'Недостача' => -1, 'Экспедиторский бонус' => 1];
        $form->setData('manager_id', $id);
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            $form->setData('creator_id', $this->user->id);
            if($this->is_allow){
                $form->setData('is_approved', Bonus::STATUS_APPROVED);
                $form->setData('request', Bonus::REQUEST_SUSSES);    
            }else{
                $form->setData('is_approved', Bonus::STATUS_NEW);
            }
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $bonus = $form->save();
                $s = null;
                if($salary_id = $form->getData('salary_id',0)){
                    $mSalary = $this->model('Salary');
                    $mSalary->updateBonus($salary_id);
                    $s = $mSalary->getById($salary_id); 
                }
                $bonus = Bonus::prepare($bonus);
                $is_main = false;
                if($bonus['manager_id'] == $this->user->id){
                    $is_main = 1;    
                }
                return [
                    'bonus' => $bonus,
                    'salary' => $s,
                    'html' => $this->renderPartial('bonus/tr', ['bonus' => $bonus,
                                                                'is_rule' => $this->is_allow,
                                                                'is_chief' => $this->is_chief,
                                                                'is_main' => $is_main,
                                                                'salary_id' => $salary_id,
                                                                'auth_user' => $this->user->id,
                                                                ]),
                ];
            }
        }
        else {
            return $this->renderPartial('bonus/add', [
                'form' => $form->createBuilder(),
                'valiebles' => $valiebles,
                'bonus' => NULL
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
        $form = $this->form('BonusEdit');
        $bonus = $this->model('Bonus')->getById($id);
        if($bonus){
            if(! $this->allow(false, $bonus['manager_id'])){
                return $this->forbidden();
            }
            $form->setData($bonus);
            $query = $this->request->query->all();
            if($salary_id = \Arr::get($query, 'salary_id',0)){
                $form->setData('salary_id', $salary_id);    
            }
        }
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $bonus = $form->save();
                $s = null;
                if($salary_id = $form->getData('salary_id',0)){
                    $mSalary = $this->model('Salary');
                    $mSalary->updateBonus($salary_id);
                    $s = $mSalary->getById($salary_id); 
                }
                return [
                    'bonus' => $bonus,
                    'salary' => $s,
                ];
            }
        }
        else {
            return $this->renderPartial('bonus/edit', [
                'form' => $form->createBuilder(),
                'bonus' => $bonus,
                'is_rule' => $this->is_allow
            ]);
        }
    }
    
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function edittypeAction($id)
    {
        $form = $this->form('BonusType');
        $bonus = $this->model('Bonus')->getById($id);
        if(null == $bonus){
            $this->notFound();
        }
        if($bonus){
            $bonus = $this->model('Bonus')->prepare($bonus);
            if(! $this->is_allow){
                if($bonus['manager_id'] == $this->user->id AND 0 == $bonus['approved'] AND 0 == $bonus['approved_id']){
                    
                }
                else{
                    return $this->forbidden();
                }
            }
            $form->setData($bonus);
        }
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $bonus = $form->save();
                return [
                    'bonus' => $bonus,
                ];
            }
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function editdateAction($id)
    {
        $form = $this->form('BonusDate');
        $bonus = $this->model('Bonus')->getById($id);
        if(null == $bonus){
            $this->notFound();
        }
        if($bonus){
            if(! $this->allow(false, $bonus['manager_id'])){
                return $this->forbidden();
            }
            $form->setData($bonus);
        }
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $bonus = $form->save();
                return [
                    'bonus' => $bonus,
                ];
            }
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function editamountAction($id)
    {
        $form = $this->form('BonusAmount');
        $bonus = $this->model('Bonus')->getById($id);
        if(null == $bonus){
            $this->notFound();
        }
        if($bonus){
            $bonus = $this->model('Bonus')->prepare($bonus);
            if(! $this->is_allow){
                if($bonus['manager_id'] == $this->user->id AND 0 == $bonus['approved'] AND 0 == $bonus['approved_id']){
                    
                }
                else{
                    return $this->forbidden();
                }
            }
            $form->setData($bonus);
            $query = $this->request->query->all();
            if($salary_id = \Arr::get($query, 'salary_id',0)){
                $form->setData('salary_id', $salary_id);    
            }
        }
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $bonus = $form->save();
                $s = null;
                if($salary_id = $form->getData('salary_id',0)){
                    $mSalary = $this->model('Salary');
                    $mSalary->updateBonus($salary_id);
                    $s = $mSalary->getById($salary_id); 
                }
                return [
                    'bonus' => $bonus,
                    'salary' => $s,
                ];
                return [
                    'bonus' => $bonus,
                ];
            }
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function plusAction($id)
    {
        $form = $this->form('BonusPlus');
        $form->setData('user_id', $id); 
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $bonus = $form->save();
                $is_main = false;
                if($bonus['manager_id'] == $this->user->id){
                    $is_main = true;
                }
                $bonus = $this->model('Bonus')->prepare($bonus);
                return [
                    'bonus' => $bonus,
                    'html' => $this->renderPartial('bonus/tr', ['bonus' => $bonus,
                                                                'is_rule' => $this->is_allow,
                                                                'is_chief' => $this->is_chief,
                                                                'is_main' => $is_main,
                                                                'salary_id' => 0,
                                                                'auth_user' => $this->user->id,
                                                                ]),
                ];
            }
        }
    }
    
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function removeallAction($id)
    {
        if(! $this->allow(false, $id)){
            $this->forbidden();
        }
        $query = $this->request->query->all();
        $month = \Arr::get($query, 'month', null);
        $year = \Arr::get($query, 'year', null);
        if(! $month AND ! $year){
            return ['errors' => 'Не задан месяц'];
        }
        if(! $id){
            return ['errors' => 'Не задан сотрудник'];
        }
        $filter = ['month' => $month, 'year' => $year];
        $filter['user_id'] = $id; //только этого сотрудника
        $filter['creator_id'] = 0; //только по крону
        $filter['is_approved'] = [0,1]; //только не отмененые
        $mBonus = $this->model('Bonus');
        $bonus = $this->model('Bonus')->getByList($filter);
        if(null !== $bonus){
            $ids = [];
            foreach($bonus as $_bonus){
                $ids[] = $_bonus['id'];
            }
            if(! empty($ids)){
                $data = ['is_approved' => $mBonus::STATUS_CANCEL, 'request' => 2];
                $criteria = ['id' => $ids];
                $this->model('Bonus')->update($data, $criteria);
                $salary = null;
                if($salary_id = \Arr::get($query,'salary_id',0)){
                    $mSalary = $this->model('Salary');
                    $mSalary->updateBonus($salary_id);
                    $salary = $mSalary->getById($salary_id); 
                }
                return ['ids' => $ids, 'salary' => $salary];
            }
        }
        return null;
    }
    
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function removeAction($id)
    {
        $bonus = $this->model('Bonus')->getById($id);
        if($bonus){
            if(! $this->allow(false, $bonus['manager_id'])){
                return $this->forbidden();
            }
            $data = ['id' => $id,
                     'is_approved' => Bonus::STATUS_CANCEL
                     ];
            $bonus = $this->model('Bonus')->prepare($bonus);
            if(1 == $bonus['request']) $data['request'] = 2;
            $this->model('Bonus')->upsert($data);
            $query = $this->request->query->all();
            $s = null;
            if($salary_id = \Arr::get($query, 'salary_id',0)){
                $mSalary = $this->model('Salary');
                $mSalary->updateBonus($salary_id);
                $s = $mSalary->getById($salary_id);
            }else{
                $date = \Arr::get($bonus, 'date', null);
                if($date){
                    $mCalculate = $this->model('Calculate');
                    $m = date('m', strtotime($date));
                    $y = date('Y', strtotime($date));
                    $user_id = \Arr::get($bonus, 'manager_id', null);
                    $calculate = $mCalculate->init($m, $y, null, $user_id);
                    $s = $calculate->calculate($user_id);
                }
            }
            return ['bonus' => $this->model('Bonus')->getById($id),
                    'salary' => $s,
                    ];
        }else{
            return ['errors' => 'not Found'];
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function requestAction($id)
    {
        $form = $this->form('BonusRequest');
        $bonus = $this->model('Bonus')->getById($id);
        if($bonus){
            $form->setData($bonus);
        }else{
            $this->notFound();
        }
        if ($this->request->is('POST')){
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $bonus = $form->save();
                return [
                    'bonus' => $bonus,
                ];
            }
        }
        else {
            return $this->renderPartial('bonus/request', [
                'form' => $form->createBuilder(),
                'bonus' => $bonus,
                'id' => $id,
            ]);
        }
    }
    
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function upAction($id) // Перенос на другой месяц
    {
        $mbonus = $this->model('Bonus');
        $bonus = $mbonus->getById($id);
        if($bonus){
            if(! $this->allow(false, $bonus['manager_id'])){
                return $this->forbidden();
            }
            $c = new \DateTime($bonus['date']); 
            $c->add(new \DateInterval('P1M'));
            $next = $c->format('Y-m-d');
            $data = ['id' => $id,
                    'is_approved' => Bonus::STATUS_APPROVED,
                    'date' => $next
                 ];
            $mbonus->upsert($data);
            $query = $this->request->query->all();
            $s = null;
            if($salary_id = \Arr::get($query, 'salary_id',0)){
                $mSalary = $this->model('Salary');
                $mSalary->updateBonus($salary_id);
                $s = $mSalary->getById($salary_id);
            }
            return ['bonus' => $mbonus->getById($id),
                    'salary' => $s,
                    ];
        }else{
            return ['errors' => 'not Found'];
        }
    }
    
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function approvedAction($id) // утвердить
    {
        $mbonus = $this->model('Bonus');
        $bonus = $mbonus->getById($id);
        if($bonus){
            if(! $this->is_allow){
                return $this->forbidden();
            }
            $data = ['id' => $id,
                    'is_approved' => Bonus::STATUS_APPROVED,
                 ];
            $bonus = $this->model('Bonus')->prepare($bonus);
            if(1 == $bonus['request']) $data['request'] = 2;
            $mbonus->upsert($data);
            $query = $this->request->query->all();
            $s = null;
            if($salary_id = \Arr::get($query, 'salary_id',0)){
                $mSalary = $this->model('Salary');
                $mSalary->updateBonus($salary_id);
                $s = $mSalary->getById($salary_id);
            }else{
                $date = \Arr::get($bonus, 'date', null);
                if($date){
                    $mCalculate = $this->model('Calculate');
                    $m = date('m', strtotime($date));
                    $y = date('Y', strtotime($date));
                    $user_id = \Arr::get($bonus, 'manager_id', null);
                    $calculate = $mCalculate->init($m, $y, null, $user_id);
                    $s = $calculate->calculate($user_id);
                }
            }
            return ['bonus' => $mbonus->getById($id),
                    'salary' => $s,
                    ];
        }else{
            return $this->notFound();
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function cancelrequestAction($id) // отклонить запрос
    {
        $mbonus = $this->model('Bonus');
        $bonus = $mbonus->getById($id);
        if($bonus){
            if(! $this->allow(false, $bonus['manager_id'])){
                if($this->user->id != $bonus['manager_id']) {
                    return $this->forbidden();
                }
            }
            $form = $this->form('BonusRequestCancel');
            $form->setData($bonus);
            if ($this->request->is('POST')){
                $form->handle($this->request->post);
                if (!$form->validate()) {
                    return [
                        'errors' => $form->getErrors(),
                    ];
                }
                if ($form->isSubmitted()) {
                    $bonus = $form->save();
                    return [
                        'bonus' => $bonus,
                    ];
                }
            }
        }else{
            return $this->notFound();
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function removemainAction($id) // отменить бонус
    {
        $mbonus = $this->model('Bonus');
        $bonus = $mbonus->getById($id);
        if($bonus){
            if($bonus['manager_id'] == $this->user->id){
                if($bonus['is_approved'] == Bonus::STATUS_NEW){
                    $data = ['id' => $id,
                             'is_approved' => Bonus::STATUS_CANCEL,
                         ];
                    $mbonus->upsert($data);
                    return ['bonus' => $mbonus->getById($id),
                            ];
                }
            }
            return $this->forbidden();
        }else{
            return $this->notFound();
        }
    }

    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function removemainrequestAction($id) // отменить свой запрос бонус
    {
        $mbonus = $this->model('Bonus');
        $bonus = $mbonus->getById($id);
        if($bonus){
            if($bonus['manager_id'] == $this->user->id){
                $data = ['id' => $id,
                    'request' => Bonus::REQUEST_NO,
                ];
                $mbonus->upsert($data);
                return ['bonus' => $mbonus->getById($id),
                ];
            }
            return $this->forbidden();
        }else{
            return $this->notFound();
        }
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function approvedchiefAction($id) // утвердить
    {
        $mbonus = $this->model('Bonus');
        $bonus = $mbonus->getById($id);
        if($bonus){
            if(! $this->allow(false, $bonus['manager_id'])){
                return $this->forbidden();
            }
            $data = ['id' => $id,
                    'approved_id' => $this->user->id,
                 ];
            $mbonus->upsert($data);
            $query = $this->request->query->all();
            return ['bonus' => $mbonus->getById($id),
                    ];
        }else{
            return $this->notFound();
        }
    }
    
    
    
    protected function allow($id = false, $user_id = NULL)
    {
        if(! $this->is_allow){
            if($this->is_chief){
                if(! $user_id){
                    $bonus = $this->model('Bonus')->getById($id);
                    if(null == $bonus){
                        return false;
                    }else{
                        $user_id = \Arr::get($bonus, 'manager_id', null);
                        if(! $user_id){
                            return false;
                        }
                    }
                }
                if($user_id != $this->user->id ){
                    if($this->model('EmployeeData')->hasChildren($this->is_chief, $user_id)){
                        return true;
                    }
                }
            }
        }else{
            return true;
        }
        return false;
    }
    
}