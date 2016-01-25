<?php

namespace Modules\Employee\Admin\Forms;
use Modules\Employee\Admin\Models\Salary;

class SalaryRequest extends \Classes\Base\Form
{
    public $types;
    public $points;
    public $payments;
    public $sources;
    
    public function defaults(){
        return ['data' => date('Y-m-d H:i:s'),
                'source_id' => 1,
                'category_id' =>  4, // ЗП
                'coming_type' => 'out', //расход доход
                'type_id' => 7, //ЗП
                'source_id' => 3,
                'payment_type_id' => 1,
                'accomplish' => 1
                ];
    }
    
    public function filters(){
        return [];
    }
    
    public function rules(){
        return [
                 'amount' => function($value){
                    if($value <= 0) return 'Еще нет средств на расход';
                    $amount = $this->model('Salary')->getReady();
                    $amount = intval($amount);
                    $value = intval($value);
                    if($amount !== $value){
                        return 'Сумма изменилась должно быть:'.\Num::format($amount);
                    }
                 }
                ];
    } 
    
    public function adapters()
    {
        return [
                'data' => [
                    'date.formater',
                    ['input' => 'Y-m-d H:i:s', 'output' => 'd.m.Y H:i:s'],
                ],
                'amount' => ["price", []]
            ];
    }
    
    public function init(){
         $this->points = \Arr::column(\Model::factory('Representative')->getAllByAttributes(['owner_id' => OWNER_ID,
                                                                                   'is_active' => 1,
                                                                                   'is_point' => 1,
                                                                                   'has_delivery' =>1],
                                                                                  ['city_id' => 'ASC',
                                                                                   'is_central' => 'DESC']),'name','id')
                + \Arr::column(\Model::factory('Representative')->getAllByAttributes(['id' => 208]),'name','id');
        $this->payments = array_by_single_field(\Model::factory('Payment')->get(),'name');
        unset($this->payments[array_search('Кредитом', $this->payments)]);
        $sources = \Model::admin('Balance')->getSources();
        $this->sources = array_combine(
                array_by_single_field($sources,'id'),
                array_by_single_field($sources,'source')
            );
        $this->types = [7 => 'ЗП', 6 => 'Аванс'];
    }
    
    

    public function save()
    {
        $data = $this->getSafeData();
        $insert = [
            'type_id' => $data['type_id'], //аванс - ЗП
            'text' => \Arr::get($this->types, $data['type_id'], 'ЗП'),
            'amount' => -$data['amount'],
            'payment_type_id' => $data['payment_type_id'],
            'creation_date' => date('Y-m-d H:i:s'),
            'creator_id' => \Model::factory('User')->get('id'),
            'approval_date' => date('Y-m-d H:i:s'),
            'approver_id' => \Model::factory('User')->get('id'),
            'source_id' => $data['source_id'],
            'point_id' => $data['point_id'],
        ];
        $id = \Model::admin('Expense')->insert($insert); //Insert
        if($this->getData('accomplish')){
            \Model::admin('Expense')->accomplish($id, $insert['source_id']); //проведен    
        }
        $this->model('Salary')->updateReady($id);
        return ['amount' => $insert['amount']];
    }
}