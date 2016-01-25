<?php

namespace Modules\Employee\Admin\Forms;

class TimeManagerEdit extends \Classes\Base\Form
{
    public function defaults()
    {
        $days = range(0,6);
        $defaults = [];
        if(! $this->getData('id', 0)){
            /*foreach($days as $day){
                if(! in_array($day, [0,6])){
                    $defaults['s'.$day] = '09:00';
                    $defaults['e'.$day] = '18:00';
                }
            }*/
        }
        return $defaults;
    }
    
    public function rules()
    {
        return [
            'since' => [
                'NotEmpty' => 'Введите день с которого будет дейcтвовать',
            ],
            'user_id' => [
                'NotEmpty' => 'Выберите пользователя',
            ],
            'since_new' => function($value){
                if(! empty($value)){
                    $month = strtotime(date('Y-m').'-01');
                    if(strtotime($value) < $month){
                        return 'Нельзя выставить график за прошедший месяц';
                    }
                }
            },
        ];
    }
    
    public function init()
    {
        
    }
    
    public function adapters()
    {
        $se = [
            'since' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'since_new' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'till' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
        ];
        $days = range(0,6);
        foreach($days as $day){
                $se['s'.$day] = [
                    'date.formater',
                    ['input' => 'H:i:s', 'output' => 'H:i'],
                ];
                $se['e'.$day] = [
                    'date.formater',
                    ['input' => 'H:i:s', 'output' => 'H:i'],
                ];
        }
        
        return $se;
    }

    public function save(){
        $data = $this->getData();
        unset($data['name']);
        if($id = $this->getData('id')){
            $data['id'] = $id;    
        }
        if($new = \Arr::get($data, 'since_new', null)){
            if($id){
                $_date = new \DateTime($new);
                $_date->sub(new \DateInterval('P1D'));
                $close = ['id' => $id];
                $close['till'] = $_date->format('Y-m-d');
                $this->model('ManagerTimeSheet')->upsert($close);
                $id = 0;
                $data['since'] = $new;
                unset($data['id']);
            }
        }
        unset($data['since_new']);
        unset($data['till']);
        if(empty($data['id'])){
            if(isset($data['id'])) unset($data['id']);
        }
        $_id = $this->model('ManagerTimeSheet')->upsert($data);
        $id = ($id ? $id : $_id);
        return $this->model('ManagerTimeSheet')->getById($id);
    }
}
?>