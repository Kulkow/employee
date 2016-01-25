<?php

namespace Modules\Employee\Admin\Controllers;

class CabinetController extends \Classes\Base\Controller
{
    /**
     * @Method (!AJAX)
     * @Access (PRIVATE_OFFICE)
     */
    public function indexAction()
    {
        $this->getbackurl();
        $cabinet_timeout = $this->container->get('session')->get('cabinet_timeout', 0);
        if($cabinet_timeout){
            $this->redirect('/admin/employee/salary/cabinet');
        }
        $form = $this->form('Login');
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if ($form->validate()) {
                $this->module->setTimeout();
                $url = $this->getbackurl();
                echo $url;
                $this->redirect($url);
            }
        }

        return $this->render('login', [
            'form' => $form->createBuilder(),
        ]);
    }
    
    /**
     * @Method (!AJAX)
     * @Access (PRIVATE_OFFICE)
     */
    public function editAction()
    {
        $form = $this->form('Password');
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            $form->setData('user_id', $this->user->id);
            if ($form->validate()) {
                $form->save();
                $this->redirect('/admin/employee/cabinet');
            }
        }
        return $this->render('password', [
            'form' => $form->createBuilder(),
        ]);
    }
    
    /**
     * @Method (!AJAX)
     * @Access (PRIVATE_OFFICE)
     */
    public function logoutAction()
    {
        $this->module->clearTimeout();
        $this->redirect('/admin/employee/cabinet');
    }
    
    protected function getbackurl(){
        $default = '/admin/employee/salary/cabinet/';
        if ($this->container->get('user')->isGranted('SALARY_SHEET')) {
            $default = '/admin/employee/salary/';
        }
        return $default;
        //return $this->container->get('session')->get('back_url', $default);
    }
}