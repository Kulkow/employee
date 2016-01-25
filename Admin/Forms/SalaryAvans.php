<?php

namespace Modules\Employee\Admin\Forms;
use Modules\Employee\Admin\Models\Salary;

class SalaryAvans extends \Classes\Base\Form
{
    public $salary = null; //после фиксации
    
    public $avans = null; //до фиксации
    
    public $calculate = null; //расчет

    
    public function init(){
        $id = $this->getData('id', 0);
        if($id){
            $this->salary = $this->model('Salary')->getById($id);
            //$this->setData($this->salary);
        }else{
            $avans_id = $this->getData('avans_id');
            $this->avans = $this->model('SalaryAvans')->getById($avans_id);
            //$this->setData($this->avans);
        }
    }
    
    public function filters(){
        return [
                'mount' => 'intval',
                'year' => 'intval',
                ];
    }    
    
    public function rules(){
        /*
        * Calculate
        **/
        $calculate = $this->model('Calculate');
        $user_id = $this->getData('user_id');
        $_date = $this->getData('date');
        $month = date('m', strtotime($_date));
        $year = date('Y', strtotime($_date));
        $calculate->init($month, $year, NULL, $user_id);
        $this->calculate = $calculate->calculate($user_id);
        
        return ['avans' => function($value){
                    if(0 > $value){
                        return 'Аванс не может отрицательным';  
                    }
                    $_data = $this->getData(['avans','id','user_id']);
                    $avans = \Arr::get($_data, 'avans', 0);
                    $salary = $this->salary;
                    if($salary){
                        if(\Arr::get($salary, 'balance', 0) < $avans){
                            return 'Аванс не может быть больше зарплаты';  
                        }
                    }else{
                        if($this->calculate){
                            if(\Arr::get($this->calculate, 'total', 0) < $avans){
                                return 'Аванс не может быть больше зарплаты';  
                            }    
                        }
                    }
                },
                'avans_id' => function($value){
                    if(! $this->salary){
                        $_date = $this->getData('date');
                        if(empty($_date)){
                            return 'Не указана дата аванса';
                        }
                        $user_id = $this->getData('user_id', null);
                        if(! $user_id){
                            return 'Не указан сотрудник';
                        }
                    }
                }
            ];
    } 
    
    public function adapters()
    {
        return [
                'avans' => ["price", [],
                            ],
                'date' => [
                    'date.formater',
                    ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
                ],
            ];
    }
    
    

    public function save()
    {
        $_data = $this->getData(['avans','id']);
        $id = \Arr::get($_data, 'id');
        $avans = \Arr::get($_data, 'avans', 0);
        $salary = $this->salary;
        if($salary){
            if($salary['avans'] > 0){
                $balance = $salary['balance'] + $salary['avans'] - $avans;
            }else{
                $balance = $salary['balance'] - $avans;
            }
            $ready = ($salary['ready'] == Salary::READY_NO ? Salary::READY_AVANS : $salary['ready']);
            $_data = ['id' => $salary['id'],
                      'avans' => $avans,
                      'updater' => $this->getData('updater'),
                      'updated' => $this->getData('updated'),
                      'balance' => $balance,
                      'ready' => $ready,
                      ];
            $this->model('Salary')->upsert($_data);
            return $this->model('Salary')->getById($id);
        }else{
            $mAvans = $this->model('SalaryAvans');
            $_avans = $this->avans;
            $_data = ['avans' => $avans,
                      'ready' => $mAvans::READY,
                      'user_id' => $this->getData('user_id'),
                      'date' => $this->getData('date'),
                      'time' => date('Y-m-d H:i:s'),
                      ];
            if($_avans){
                $_data['id'] = $_avans['id'];
                $mAvans->upsert($_data);
                $avans_id = $_data['id'];
            }else{
                $avans_id = $mAvans->insert($_data);    
            }
            
            $balance = \Arr::get($this->calculate, 'total', 0) - $avans;
            return ['balance' => $balance,
                    'avans' => $avans,
                    'avans_id' => $avans_id,
                    ];
        }
    }
}