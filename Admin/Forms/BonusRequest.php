<?php

namespace Modules\Employee\Admin\Forms;

class BonusRequest extends \Classes\Base\Form
{
        
    public function defaults()
    {
        return [];
    }
    
    public function filters()
    {
        return [
            'comment_user' => 'trim',
            'type_request' => 'intval'
        ];
    }
    
    public function rules()
    {
        $mBonus = $this->model('Bonus');
        $type_request = $this->getData('type_request', 0);
        return [
            'comment_user' => [
                function($value) use ($mBonus, $type_request){
                    if($type_request == $mBonus::REQUEST_TYPE_OUTER){
                        if(empty($value)){
                            return 'Не указан комментарий';
                        }
                    }
                }
            ],
            'type_request'=> [
                function($value) use ($mBonus){
                    if(empty($value)){
                        return 'Выберите причину';
                    }else{
                        $tRequest = $mBonus->getSelectRequest();
                        if(! \Arr::get($tRequest, $value, null)){
                            return 'Неизвестная причина';
                        }
                    }
                }
            ],
            'working' => [
                function($value) use ($mBonus, $type_request){
                    if($type_request == $mBonus::REQUEST_TYPE_CHANGE){
                        if(empty($value)){
                            return 'Не указана дата отработки';
                        }
                    }
                }
            ],
            'employee_id' => [
                function($value) use ($mBonus, $type_request){
                    if($type_request == $mBonus::REQUEST_TYPE_CHANGE){
                        if(empty($value)){
                            return 'Не указана сотрудник с кем вы менялись';
                        }else{
                            $employee = $this->model('EmployeeData')->getById($value);
                            if(null == $employee){
                                return 'Не верно указан сотрудник с кем вы менялись';
                            }
                        }
                    }
                }
            ],
            'id' => [
                'NotEmpty' => 'Нет депремирования',
            ],
        ];
    }

    public function adapters()
    {
        return [
            'working' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'from' => [
                'date.formater',
                ['input' => 'H:i', 'output' => 'H:i'],
            ],
            'to' => [
                'date.formater',
                ['input' => 'H:i', 'output' => 'H:i'],
            ],
        ];
    }

    public function save()
    {
        $mBonus = $this->model('Bonus');
        $tRequest = $mBonus->getSelectRequest();
        $data = $this->getData(['comment_user', 'id']);
        $id = $this->getData('id');
        $type_request = $this->getData('type_request', 0);
        $bonus = $mBonus->getById($id);
        switch($type_request){
            case $mBonus::REQUEST_TYPE_OUTER :
            break;

            case $mBonus::REQUEST_TYPE_CHANGE :
                $data['comment_user'] = \Arr::get($tRequest, $type_request, 'не известная причина');
                $e_id = $this->getData('employee_id');
                $employee = $this->model('EmployeeData')->getById($e_id);
                $w = $this->getData('working');
                $comment = $data['comment_user'];
                $comment = str_replace('...(выбрать сотрудника)', $employee['name'], $comment);
                $comment = str_replace('...(число отработки)', $w, $comment);
                $data['comment_user'] = $comment;
                //менялся с ...(выбрать сотрудника) на ...(число отработки
            break;

            case $mBonus::REQUEST_TYPE_GRAFIC :
                $_date = \Arr::get($bonus,'date', null);
                $_user_id = \Arr::get($bonus,'manager_id', null);
                $day = intval(date('w',strtotime($_date)));
                $date = new \DateTime($_date);
                $s = $date->format('Y-m-d');
                $date->add(new \DateInterval('P1D'));
                $e = $date->format('Y-m-d');
                $filter = ['start' => $s, 'end' => $e, 'user_id' => $_user_id];
                $managerTime = $this->model('ManagerTimeSheet')->getByList($filter);
                if(! empty($managerTime) and is_array($managerTime)){
                    $managerTime = \Arr::get($managerTime, 0, null);
                }
                //работал не по графику с ... (со скольки) до ... (до скольки) вместо (текущий график на день)
                $from = $this->getData('from');
                $to = $this->getData('to');
                $comment = \Arr::get($tRequest, $type_request, 'не известная причина');
                $comment = str_replace('... (со скольки)', $from, $comment);
                $comment = str_replace('... (до скольки)', $to, $comment);
                if(empty($managerTime)){
                    $tTime = ' график не задан';
                }else{
                    $t_s = \Arr::get($managerTime, 's'.$day, 0);
                    $t_e = \Arr::get($managerTime, 'e'.$day, 0);
                    $t_s = str_replace(':00:00', ':00', $t_s);
                    $t_e = str_replace(':00:00', ':00', $t_e);
                    $tTime = $t_s.'-'.$t_e;
                    //$tTime = $managerTime[''];
                }
                $comment = str_replace('(текущий график на день)', $tTime, $comment);
                $data['comment_user'] = $comment;
            break;

            default :
                $data['comment_user'] = \Arr::get($tRequest, $type_request, 'не известная причина');
            break;
        }
        $data['request'] = $mBonus::REQUEST_NEW;
        $mBonus->upsert($data);
        return  $mBonus->getById($id);
    }
}