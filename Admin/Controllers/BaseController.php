<?php

namespace Modules\Employee\Admin\Controllers;

/**
 * @Role (USER_ADMIN)
 */
class BaseController extends \Classes\Base\Controller
{
   public $menu = 'employee';
   
   public $timeout = 900;
   
   public function init(){
      if (!$this->user->isGranted('SALARY_SHEET')) {
         $this->forbidden();
      }
   }
}