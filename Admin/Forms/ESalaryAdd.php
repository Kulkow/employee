<?php

namespace Modules\Employee\Admin\Forms;

class ESalaryAdd extends \Classes\Base\Form
{
    
    public function rules()
    {
        return [
            'user_id' => [
                'NotEmpty' => 'Выберите сотрудника',
            ],
            /*
            'new_start' => function(){
                //Не может быть раньше предыдущей ставки
                $user_id = $this->getData('user_id');
                $value = $this->getData('new_start');
                if(! empty($value)){
                    if($user_id){
                        $esalary = $this->model('EmployeeSalary')->getByUserId($user_id);
                        $start = \Arr::get($esalary, 'start');
                        if(strtotime($value) < strtotime($start)){
                            return 'Время начала действия не может быть раньше начала предыдущей';
                        }
                    }
                }
            },*/
            'base' => function($value){
                /*if(intval($value) <= 0){
                    return 'Выставьте ставку';
                }*/
            },
            'start' => [
                'NotEmpty' => 'Выставьте дату',
                function($start){
                    if(date('d', strtotime($start)) != 1){
                        return 'Дата начала действия показателя не 1 число месяца';
                    }
                },
            ],
        ];
    }
    
    public function adapters()
    {
        return [
            'start' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'end' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'new_start' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'base' => [
                'price',
                [],
            ],
        ];
    }

    public function save()
    {
        $data = $this->getData();
        $id = \Arr::get($data, 'id', NULL);
        $user_id = \Arr::get($data, 'user_id', NULL);
        $base_new = \Arr::get($data, 'base', NULL);
        $start = \Arr::get($data, 'new_start', NULL);
        $mEsalary = $this->model('EmployeeSalary');
        if($id){
            $esalary = $mEsalary->getById($id);
            $base = \Arr::get($esalary, 'base', NULL);
            if(! empty($start)){
            // Новая ставка начинаю с 
                if($base != $base_new){
                    if(! $start){
                        $data['start'] = "Y-m-d"; //по умолчанию действует с сегоднешнего дня
                    }
                    $date = new \DateTime($start);
                    $date->sub(new \DateInterval('P1D'));
                    $end = $date->format('Y-m-d');// Предыдущим днем
                    //close prev
                    $close = [
                              'id' => $data['id'],
                              'end' => $end
                              ];
                    $data['start'] = $data['new_start'];
                    unset($data['new_start']);
                    unset($data['id']);
                    $mEsalary->upsert($close);
                    $_id = $mEsalary->insert($data);
                    $esalary = $mEsalary->getById($_id);
                    return $esalary;
                }else{
                    return $esalary;
                }
            }else{
                unset($data['new_start']);
                if(empty($data['end'])){
                    unset($data['end']);    
                }
                $mEsalary->upsert($data);
                $mEsalary->pull($user_id, $esalary['start'], $data['start']);
            }
        }else{
            unset($data['new_start']);
            unset($data['id']);
            $mEsalary = $this->model('EmployeeSalary');
            $start = \Arr::get($data, 'start', NULL);
            
            $next = $mEsalary->getByNextStart($user_id, $start);
            $prev = $mEsalary->getByPrevStart($user_id, $start);
            
            $_id = $mEsalary->insert($data);
            if($next){
                $up = ['id' => $_id,
                       'end' => $mEsalary->getPrevDay($next['start']),
                       ];
                $mEplan->upsert($up);
            }
            if($prev){
                $up = ['id' => $prev['id'],
                       'end' => $mEsalary->getPrevDay($start),
                       ];
                $mEsalary->upsert($up);
            }
            
            return $mEsalary->getById($_id);
        }
        return;
    }
}