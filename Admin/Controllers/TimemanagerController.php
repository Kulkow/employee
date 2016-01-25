<?php
namespace Modules\Employee\Admin\Controllers;
use Modules\Employee\Admin\Models\Bonus;
use Modules\Employee\Admin\Extensions\Calendar;


class TimemanagerController extends InitController
{
    public $menu = 'salary';

    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function viewAction($id)
    {
        $tmanager = $this->model('ManagerTimeSheet')->getByUserId($id);
        $days = [];
        foreach(range(0,6) as $_d){
            $days[$_d] = Calendar::weekdays($_d);
        }
        unset($days[0]);
        $days['0'] = Calendar::weekdays(0);
        return $this->renderPartial('tmanager/view', [
                'tmanager' => $tmanager,
                'days' => $days,
                'is_rule' => $this->is_allow
            ]);
    }
    
    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function addAction($id) 
    {
        $form = $this->form('TimeManagerEdit');
        $form->setData('user_id', $id);
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $tmanager = $form->save();
                return [
                    'tmanager' => $tmanager,
                ];
            }
            
        }
        else {
            $days = [];
            foreach(range(0,6) as $_d){
                $days[$_d] = Calendar::weekdays($_d);
            }
            unset($days[0]);
            $days['0'] = Calendar::weekdays(0);
            $tmanager = $this->model('ManagerTimeSheet')->getByUserId($id);
            $tmanager_progress = $this->model('ManagerTimeSheet')->progress($tmanager);
            return $this->renderPartial('tmanager/edit', [
                'form' => $form->createBuilder(),
                'tmanager' => $tmanager,
                'tmanager_progress' => $tmanager_progress,
                'days' => $days,
                'is_rule' => $this->is_allow
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
        $form = $this->form('TimeManagerEdit');
        $tmanager = $this->model('ManagerTimeSheet')->getByUserId($id);
        if($tmanager){
            $form->setData($tmanager);
        }else{
            $form->setData('user_id', $id);
        }
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $tmanager = $form->save();
                return [
                    'tmanager' => $tmanager,
                ];
            }
        }
        else {
            $days = [];
            foreach(range(0,6) as $_d){
                $days[$_d] = Calendar::weekdays($_d);
            }
            unset($days[0]);
            $days['0'] = Calendar::weekdays(0);
            $tmanager_progress = $this->model('ManagerTimeSheet')->progress($tmanager);
            return $this->renderPartial('tmanager/edit', [
                'form' => $form->createBuilder(),
                'tmanager' => $tmanager,
                'days' => $days,
                'tmanager_progress' => $tmanager_progress,
                'is_rule' => $this->is_allow
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
        $form = $this->form('TimeManagerEdit');
        $tmanager = $this->model('ManagerTimeSheet')->getById($id);
        if($tmanager){
            $form->setData($tmanager);
        }else{
            $form->setData('user_id', $id);
        }
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $tmanager = $form->save();
                $tmanager_progress = $this->model('ManagerTimeSheet')->progress($tmanager);
                return [
                    'tmanager' => $tmanager,
                    'progress' => $tmanager_progress,
                ];
            }
        }
        else {
            $days = [];
            foreach(range(0,6) as $_d){
                $days[$_d] = Calendar::weekdays($_d);
            }
            unset($days[0]);
            $days['0'] = Calendar::weekdays(0);
            $tmanager_progress = $this->model('ManagerTimeSheet')->progress($tmanager);
            return $this->renderPartial('tmanager/edit', [
                'form' => $form->createBuilder(),
                'tmanager' => $tmanager,
                'days' => $days,
                'tmanager_progress' => $tmanager_progress,
                'is_rule' => $this->is_allow
            ]);
        }
    }

    
}