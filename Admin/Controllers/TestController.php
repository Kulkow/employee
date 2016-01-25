<?php

namespace Modules\Employee\Admin\Controllers;
use Modules\Employee\Admin\Tests\EmployeeSalaryTest;

/**
* @acesss (SALARY_SHEET)
*/
class TestController extends \Classes\Base\Controller
{
    /**
     * @param integer $id
     *
     * @Method (!AJAX)
     */
    public function esalaryAction($id)
    {
        $db = $this->container->get('db');
        $user = $this->container->get('user');
        $test = new EmployeeSalaryTest($db, $user);
        /*$intersects = $test->checkInterSectInterval($id);
         */
        $intersects = $test->checkInterSectIntervalUsers();
        //print_r($intersects);
        return $this->render('test/esalary', [
                'intersects' => $intersects,
            ]);
    }
}