<?php

namespace Modules\Employee\Admin\Forms;
use Modules\Employee\Admin\Models\Salary;

class SalaryBalance extends \Classes\Base\Form
{
    public $salary;
    
    public function init(){
        $id = $this->getData('id');
        $this->salary = $this->model('Salary')->getById($id);
        $this->setData($this->salary);
    }
    
    public function filters(){
        return [
                'mount' => 'intval',
                'year' => 'intval',
                ];
    }    
    
    public function rules(){
        
        return [
                'balance' => function($value){
                        $salary = $this->salary;
                        if(! empty($value)){
                            if($value > $salary['balance']){
                                return 'нельзя выдать больше чем должно быть';    
                            }
                        }else{
                            return 'нельзя ничего не выдать';
                        }
                    },
                ];
    } 
    
    public function adapters()
    {
        return [
                'balance' => ["price", []]
            ];
    }
    
    

    public function save()
    {
        $_data = $this->getSafeData(['balance','id','user_id']);
        $id = \Arr::get($_data, 'id');
        $balance = \Arr::get($_data, 'balance', 0);
        $salary = $this->salary;
        if($salary){
            if($salary['ready'] == Salary::READY_NO){
                $ready = Salary::READY_BALANCE; // только ЗП
            }else{
                if($salary['ready'] == Salary::READY_AVANS){
                    $ready = Salary::READY_AVANS_BALANCE; //  АВАНС + ЗП
                }else{
                    $ready = Salary::READY_BALANCE;//  только ЗП
                }
            }
            //$ready = ($salary['ready'] == Salary::READY_NO ? Salary::READY_AVANS_BALANCE : $salary['ready']);
            $_data = ['id' => $salary['id'],
                      'updater' => $this->getData('updater'),
                      'updated' => $this->getData('updated'),
                      'balance' => $salary['balance'] - $balance,
                      'out' => $salary['out'] + $balance,
                      'ready' => $ready,
                      ];
            $this->model('Salary')->upsert($_data);
            return $this->model('Salary')->getById($id);
        }else{
            $this->addError('avans','Не сгенерирован список зарплат на этот месяц');
            return false;
        }
        /*$this->model('Salary')->upsert($data);
        return $this->getData('avans');*/
    }
}