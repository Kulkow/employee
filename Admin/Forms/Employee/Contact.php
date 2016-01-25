<?php

namespace Modules\Employee\Admin\Forms\Employee;

class Contact extends \Classes\Base\Form
{
    
    public function rules()
    {
        return [
            'user_id' => [
                'NotEmpty' => [
                    'message' => 'Выберите сотрудника',
                ],
            ],
        ];
    }
    
    public function adapters()
    {
        return [
        ];
    }

    public function save()
    {
        $mContact = $this->model('EmployeeContact');
        $contacts = $mContact->getContacts();
        $keys = array_keys($contacts);
        $user_id = $this->getData('user_id');
        $contact = $mContact->getById($user_id);
        $keys[] = 'user_id'; 
        $data = $this->getData($keys);
        if(null == $contact){
            $mContact->insert($data);
        }else{
            $mContact->upsert($data);    
        }
        return $mContact->getById($user_id);
    }
}