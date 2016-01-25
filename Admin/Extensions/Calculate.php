<?php
namespace Modules\Employee\Admin\Extensions;

class Calculate extends \Classes\Base\Model
{
    protected $table = 'plan';
    
    /**
    * Рассчитать планы на выбранный месяц
    */
    public function init($mount = NULL, $year = NULL, $date = NULL, $userId = NULL){
        
    }
}


?>