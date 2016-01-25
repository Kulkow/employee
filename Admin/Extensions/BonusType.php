<?php
/**
 * Created by PhpStorm.
 * User: Игорёк
 * Date: 25.01.2016
 * Time: 13:59
 */
<?php
namespace Modules\Employee\Admin\Extensions;

class BonusType{

    public $name;
    public $action;
    public $amount;
    public $sign = -1;

    public function __construct($name = null, array $options = [])
    {
        $this->name = $name;
        return $this;
    }

    public function setType(){

    }

    public function asArray(){

    }
}