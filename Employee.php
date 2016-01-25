<?php

namespace Modules\Employee;

class Employee extends \Classes\Base\Module
{
    public $timeout = 50; // время показа страницы
    
    public function init()
    {
        $this->router->add(
             'admin.employee',
             'admin/employee/?(:controller)?/?(:action)?/?(:id)?', [
                 'controller' => 1,
                 'action' => 2,
                 'id' => 3,
             ])->defaults([
                'admin' => 1,
                'module' => 'employee',
                'controller' => 'salary',
                'action' => 'index',
             ]);
    }
    
    public function setTimeout()
    {
        if ($this->container->get('user')->isGranted('SALARY_SHEET')) {
            $this->timeout = 1800;
        }
        $this->container->get('session')->set('cabinet_timeout', time() + $this->timeout);
    }

    public function clearTimeout()
    {
        $this->container->get('session')->remove('cabinet_timeout');
    }
}