<?php

namespace Modules\Employee\Admin\Controllers;

/**
 * @acesss (PRIVATE_OFFICE)
 */
class InitController extends \Classes\Base\Controller
{
    public $menu = 'employee';
    public $timeout = 500; // время показа страницы
    public $closetime = 3000; // недоступности страницы
    
    public $is_allow = false;
    
    public $is_chief = false;
    
    public $is_bookkeper = false;
    
    public function init(){
        if ($this->container->get('user')->isGranted('SALARY_SHEET')) {
            $this->timeout = 1800;
            $this->is_allow = true;
        }else{
            //
            $user =  $this->container->get('user')->offsetGet('employee_data');
            $department_id =  \Arr::get($user, 'department_id', null);
            $status =  \Arr::get($user, 'status', null);
            $mEmployee = $this->model('Employee');
            if($mEmployee::STATUS_CHIEF == $status){
                $this->is_chief = $department_id;
                $this->timeout = 90;
            }
        }
        if ($this->container->get('user')->isRole('USER_BOOKKEEPER')) {
            $this->is_bookkeper = true;
            $this->timeout = 60;
        }
        if ($this->container->get('user')->isGranted('EMPLOYEE_CONTACT_EDIT')) {
            $this->is_bookkeper = true;
        }
        $cabinet_timeout = $this->container->get('session')->get('cabinet_timeout', 0);
        if(! $cabinet_timeout){
            $this->setbackurl();
            $session = $this->container->get('session')->get('back_url', null);
            $this->redirect('/admin/employee/cabinet');
        }
        else {
            /*
            if($cabinet_timeout - time() < 0){
                $this->clearTimeout();
                $this->redirect('/admin/employee/cabinet');    
            }*/
            $this->setTimeout();
        }
    }
    
    public function setTimeout()
    {
        $this->container->get('session')->set('cabinet_timeout', time() + $this->timeout);
    }

    public function clearTimeout()
    {
        $this->container->get('session')->remove('cabinet_timeout');
    }
    
    protected function setbackurl(){
        $uri = $this->request->server->get('REQUEST_URI');
        $session = $this->container->get('session')->set('back_url', $uri);
    }
}