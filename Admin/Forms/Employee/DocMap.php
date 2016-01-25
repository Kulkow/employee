<?php

namespace Modules\Employee\Admin\Forms\Employee;

class DocMap extends \Classes\Base\Form
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
        $mDocMap = $this->model('EmployeeDocMap');
        $docs = $mDoc->getDocs();
        $keys = array_keys($docs);
        $keys[] = 'user_id'; 
        $data = $this->getData($keys);
        $_maps = $data;
        
        foreach($data as $field => $value){
            if(empty($value)){
                unset($_maps[$field]);
            }
        }
        unset($_maps['user_id']);
        
        $map = [];
        $user_id = $this->getData('user_id');
        $_docmap = $mDocMap->getByUserId($user_id);
        foreach($_docmap as $item){
            if(! empty($item['field'])){
                $map[$item['field']] = $item;
            }
        }
        
        $add = array_diff_key($_maps, $map);
        $remove = array_diff_key($map, $_maps);
        if(! empty($add)){
            foreach($add as $field => $_r){
                $data = ['user_id' => $user_id,
                         'field' => $field, 'notrequired' => 1];
                $mDocMap->insert($data);
            }
        }
        if(! empty($remove)){
            $ids = [];
            foreach($remove as $row){
                $ids[] = $row['id'];
            }
            $delete = ['id' => $ids];
            $mDocMap->delete($delete);
        }
        $_docmap = $mDocMap->getByUserId($user_id);
        return $mDocMap->getMapUser($_docmap);
    }
}