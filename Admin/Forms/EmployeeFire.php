<?php

namespace Modules\Employee\Admin\Forms;
use Modules\Employee\Admin\Models\Role;

class EmployeeFire extends \Classes\Base\Form
{
    public function defaults(){
        $f = new \DateTime();
        $f->add(new \DateInterval('P1M'));
        return [
            'fire_date' => date('Y-m-d'),
            'expire_date' => $f->format('Y-m-d'),
        ];
    }
    public function rules()
    {
        return [
            'user_id' => ['NotEmpty' => 'Выберите сотрудника'],
            'expire_date' => function($value){
                $fire = $this->getData('fire_date');
                if(strtotime($value) < strtotime($fire)){
                    return 'До какого дня показывать в списке Не должна быть раньше даты увольнения';
                }
            }
        ];
    }
    
    public function adapters()
    {
        return [
            'fire_date' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
            'expire_date' => [
                'date.formater',
                ['input' => 'Y-m-d', 'output' => 'd.m.Y'],
            ],
        ];
    }

    public function save()
    {
        $data = $this->getData(['expire_date','fire_date', 'user_id']);
        $id = $data['user_id'];
        $close = $this->getData('close', null);
        if($close){
            $eplans = $this->model('EmployeePlan')->getByUserId($id);
            $employee = $this->model('Employee')->getByList(['user_id' => $id]);
            foreach($eplans as $eplan){
                $_data = ['id' => $eplan['id'], 'end' => $data['fire_date']];
                $this->model('EmployeePlan')->upsert($_data);
            }
            foreach($employee as $_employee){
                $_data = ['id' => $_employee['id'], 'end' => $data['fire_date']];
                $this->model('Employee')->upsert($_data);
            }
        }
        
        $user = ['id' => $id, 'role_id' => 3]; //client
        $this->model('User')->upsert($user);
        //$data['department_id'] = 0;
        $this->model('EmployeeData')->upsert($data);
        $employee = $this->model('EmployeeData')->getById($id);
        $mUser = $this->model('User');
        $clearrole = ['id' => $id, 'role_id' => Role::CLIENT]; //3 - Client
        $mUser->upsert($clearrole);
        $mCodex = $this->model('Codex');
        $mCodex->delete(['user_id' => $id]);
        return $employee;
    }
}