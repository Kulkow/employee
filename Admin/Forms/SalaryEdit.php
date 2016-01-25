<?php

namespace Modules\Employee\Admin\Forms;
use Modules\Employee\Admin\Models\Salary;

class SalaryEdit extends \Classes\Base\Form
{
    protected $salary;
    
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
                /*'balance' => function($value){
                        $salary = $this->salary;
                        $_out = $this->getData('out', 0);
                        $out = $salary['out'] + $salary['balance']; // должно быть всего выдано
                        $summa = $value + $_out;
                        if($summa != $out){
                            return 'Сумма Выдано + Осталось выдать НЕ Совпадает ('.\Num::format($out-$_out).'), должно быть '.\Num::format($out);
                        }
                    },*/
                'out' => function($value){
                        $salary = $this->salary;
                        $out = $salary['out'] + $salary['balance']; // должно быть всего выдано
                        $_balance = $this->getData('balance', 0);
                        if(! empty($value)){
                            if($value > $out){
                                return 'нельзя выдать больше чем должно быть '.\Num::format($out);    
                            }
                        }
                        /*$summa = $value + $_balance;
                        if($summa != $out){
                            return 'Сумма Выдано + Осталось выдать НЕ Совпадает ('.\Num::format($out-$_balance).'), должно быть '.\Num::format($out);
                        }*/
                    },
                ];
    } 
    
    public function adapters()
    {
        return [
                'balance' => ["price", []],
                'out' => ["price", []],
            ];
    }
    

    public function save()
    {
        $_data = $this->getSafeData(['balance', 'out','id','user_id']);
        $salary = $this->salary;
        $id = \Arr::get($_data, 'id');
        $out = \Arr::get($_data, 'out', 0);
        
        $_t = $salary['balance'] + $salary['out'];
        $balance = $_t - $out;
        
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
            $_data = ['id' => $salary['id'],
                      'updater' => $this->getData('updater'),
                      'updated' => $this->getData('updated'),
                      'balance' => $balance,
                      'out' => $out,
                      'ready' => $ready,
                      ];
            $this->model('Salary')->upsert($_data);
            return $this->model('Salary')->getById($id);
        }else{
            $this->addError('avans','Не сгенерирован список зарплат на этот месяц');
            return false;
        }
    }
}