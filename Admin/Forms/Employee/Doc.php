<?php

namespace Modules\Employee\Admin\Forms\Employee;

class Doc extends \Classes\Base\Form
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
        $mDoc = $this->model('EmployeeDoc');
        $docs = $mDoc->getDocs();
        $keys = array_keys($docs);
        
        $user_id = $this->getData('user_id');
        $doc = $mDoc->getById($user_id);
        
        $keys[] = 'user_id'; 
        $data = $this->getData($keys);
        array_walk($data, function(&$value){
            $value = (null === $value ? 0 : $value);
        });
        if(null != $doc){
            $data['updater'] = $this->getData('updater');
            $data['updated'] = date('Y-m-d H:i:s');
            $mDoc->upsert($data);
        }else{
            $data['updated'] = date('Y-m-d H:i:s');
            $data['created'] = date('Y-m-d H:i:s');
            $data['updater'] = $this->getData('updater');
            $data['creater'] = $this->getData('creater');
            $mDoc->insert($data);
        }
        
        return $mDoc->getById($user_id);
    }
}