<?php

namespace Modules\Employee\Admin\Forms;
use Modules\Employee\Admin\Extensions\Calendar;

class BonusPlus extends \Classes\Base\Form
{
        
    public function filters()
    {
        return [
                'date' => 'trim',
                'hour' => 'intval',
                'max' => function($max){
                    $max = str_replace(',','.',$max);
                    $max = floatval($max);
                    return $max;
                }
        ];
    }
    
    public function rules()
    {
        return [
            'date' => [
                'NotEmpty' => 'Введите дату',
            ],
            'hour' => [
                'NotEmpty' => 'Не работали в этот день',
                function($hour){
                    $minute = $this->getData('minute', 0);
                    $hour = $hour + ($minute/60);
                    if($hour == 0){
                        return 'Не указали время';
                    }
                    $max = $this->getData('max', 0);
                    $hour = floatval($hour);
                    if($hour > $max){
                        return 'Нельзя указать часов ('.$hour.') больше чем отработано '.$max;
                    }
                }
            ],
            'user_id' => [
                'NotEmpty' => 'Не указан сотрудник',
            ],
        ];
    }
    
    public function adapters()
    {
        return [
            'date' => [
                        'date.formater',
                        ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
                    ],
        ];
    }
    
    public function save()
    {
        $data = $this->getData(['date', 'hour','minute', 'user_id']);
        $user_id = \Arr::get($data, 'user_id', 0);
        $_date = date('Y-m', strtotime($data['date']));
        $period_month = Calendar::getPeriodMonth($_date.'-01');
        
        $oklad = $this->model('EmployeeSalary')->getByOkladUserId($user_id, $period_month);
        $price_hour = intval($oklad/(25*8));
        
        $bonus = $this->model('Bonus')->plus($data, $price_hour);
        return $bonus;
        /*
        $id = $this->model('Bonus')->insert($data);
        $bonus = $this->model('Bonus')->getById($id);
        if(null != $bonus){
            $bonus['date'] = date('d.m.Y',strtotime($bonus['date'])); 
        }
        return $bonus;*/
    }
}