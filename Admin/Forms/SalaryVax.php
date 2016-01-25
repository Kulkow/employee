<?php

namespace Modules\Employee\Admin\Forms;

class SalaryVax extends \Classes\Base\Form
{
    public $salary = NULL;
    
    public $vax = null; //до фиксации
    
    public function init(){
        $id = $this->getData('id',null);
        if($id){
            $this->salary = $this->model('Salary')->getById($id);
            //$this->setData($this->salary);
        }else{
            $vax_id = $this->getData('vax_id');
            $this->vax = $this->model('SalaryVax')->getById($vax_id);
            //$this->setData($this->vax);
        }
    }
    
    public function filters(){
        return [
                'mount' => 'intval',
                'year' => 'intval',
                ];
    }    
    
    public function rules(){
        return [];
        /*return ['vax' => function($value){
                    $_data = $this->getSafeData(['avans','id','user_id']);
                    $id = \Arr::get($_data, 'id');
                    $va = \Arr::get($_data, 'vax', 0);
                    $salary = $this->model('Salary')->getById($id);
                    if($salary){
                        if(\Arr::get($salary, 'balance', 0) < $avans){
                            return 'Аванс не может быть больше зарплаты';
                        }
                    }
                }
        ];*/
    } 
    
    public function adapters()
    {
        return [
                'vax' => ["price", []]
            ];
    }
    
    

    public function save()
    {
        $_data = $this->getSafeData(['vax','id']);
        $id = \Arr::get($_data, 'id');
        $vax = \Arr::get($_data, 'vax', 0);
        $salary = $this->salary;
        $balance = $salary['balance'] + $salary['vax'] - $vax;
        $mVax = $this->model('SalaryVax');
        if($salary){
            $_data = ['id' => $salary['id'],
                      'vax' => $vax,
                      'balance' => $balance,
                      'updater' => $this->getData('updater'),
                      'updated' => $this->getData('updated'),
                      ];
            $this->model('Salary')->upsert($_data);
            return $this->model('Salary')->getById($id);
        }else{
            $mVax = $this->model('SalaryVax');
            $_vax = $this->vax;
            $_data = ['vax' => $vax,
                      'ready' => $mVax::READY,
                      'user_id' => $this->getData('user_id'),
                      'date' => $this->getData('date'),
                      'time' => date('Y-m-d H:i:s'),
                      ];
            if($_vax){
                $_data['id'] = $_vax['id'];
                $mVax->upsert($_data);
                $_id = $_data['id'];
            }
            else{
                $_id = $mVax->insert($_data);
            }
            
            //$balance = \Arr::get($this->calculate, 'total', 0) - $avans;
            return ['balance' => $balance,
                    'vax' => $vax,
                    'vax_id' => $_id,
                    ];
        }
        /*$this->model('Salary')->upsert($data);
        return $this->getData('avans');*/
    }
}