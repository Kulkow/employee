<?php

namespace Modules\Employee\Admin\Forms;
use Modules\Employee\Admin\Models\Role;

class EmployeeClose extends \Classes\Base\Form
{
    public function defaults()
    {
        return [
            'end' => date('Y-m-d', strtotime('last day of this month')),
        ];
    }

    public function rules()
    {
        return [
            'id' => ['NotEmpty' => 'Выберите Период'],
            'end' => function($value){
                $id = $this->getData('id');
                $pm_employee = $this->model('Employee')->getById($id);
                if(null == $pm_employee){
                    return 'Нет такого периода';
                }
                $stamp = strtotime($value);
                $day = cal_days_in_month(CAL_GREGORIAN, date('m',$stamp), date('Y',$stamp));
                if(date('d', strtotime($value)) != $day){
                    return 'Дата окончания привязки должна быть последним числом месяца';
                }
                if(strtotime($value) < strtotime($pm_employee['start'])){
                    return 'Дата окончания привязки не может быть раньше начала';
                }
            }
        ];
    }

    public function adapters()
    {
        return [
            'end' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
        ];
    }

    public function save()
    {
        $data = $this->getData(['end','id']);
        $this->model('Employee')->upsert($data);
        $id = $this->getData('id');
        return $this->model('Employee')->getById($id);
    }
}