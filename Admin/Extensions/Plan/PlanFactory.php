<?php

class PlanFactory {

    static function getPlanInstance() {
        $params = func_get_args();
        $planName = $params[0].'Plan';
        unset($params[0]);
        if (file_exists('req/module/admin/employees/salary_calculator/'.$planName.'.class.php')) {
            require_once 'module/admin/employees/salary_calculator/'.$planName.'.class.php';
            $reflectionObj = new ReflectionClass($planName);
            return $reflectionObj->newInstanceArgs($params);             
        }
        else return null;
        
    }

}