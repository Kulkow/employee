<?php

namespace Modules\Employee\Admin\Forms\Employee;

class Account extends \Classes\Base\Form
{
    
    public function filters()
    {
        return ['email' => 'trim',
                'phone' => 'trim',
                'user_id' => 'intval',
                ];
    }
    
    public function rules()
    {
        return [
            'user_id' => function(){
                
            },
            'q' => function(){
                $user_id = $this->getData('user_id', null);
                if($user_id){
                    $employee = $this->model('EmployeeData')->getById($user_id);
                    if(null !== $employee){
                        return 'Уже работает';
                    }
                }
            },
            'email' => function($value){
                $user_id = $this->getData('user_id', null);
                if(! $user_id){
                    $email = $this->getData('email', null);
                    $phone = $this->getData('phone', null);
                    if (! $email AND ! $phone){
                        return 'Не введен ни email, ни телефон';
                    }
                    if ($email AND ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return 'Не верно введен E-mail';
                    }
                    if ($email and \Model::factory('User')->has($email)) {
                        return 'Пользователь с таким email\'ом уже существует';
                    }
                }
                
            },
            'phone' => function($value){
                $phone = $this->getData('phone', null);
                if ($phone and \Model::factory('User')->hasPhone($phone)) {
                    return 'Пользователь с таким телефоном уже существует';
                }
            },
            'name' => function($value){
                $user_id = $this->getData('user_id', null);
                if(! $user_id){
                    if(empty($value)){
                        return 'Введите имя';
                    }
                }
            }
        ];
    }
    
    public function adapters()
    {
        return [
        ];
    }
    
    protected function preparename($name){
        $lastname = $firstname = $secondname = '';
        $name = preg_replace("/\s{2,}/",' ',$name);
        $arr = explode(' ',$name);
        array_walk($arr, function(&$str){
            $str = trim($str);
        });
        $lastname = \Arr::get($arr, 0, $lastname);
        $firstname = \Arr::get($arr, 1, $firstname);
        $secondname = \Arr::get($arr, 2, $secondname);
        return ['lastname' => $lastname,
                'firstname' => $firstname,
                'secondname' => $secondname,    
        ];
    }

    public function save()
    {
        $user_id = $this->getData('user_id', null);
        if(! $user_id){
            $data = $this->getData(['email', 'phone','password', 'name']);
            if(empty($data['password'])){
                $data['password'] = \Text::random(6);
            }
            $names = $this->preparename($data['name']);
            unset($data['name']);
            $data = array_merge($data, $names);
            $user_id = $this->model('User')->registration($data);
            if($user_id){
                $data = ['user_id' => $user_id,
                         'salary_password'  => $data['password'],
                         'phone' => $data['phone'],
                         'email' => $data['email'],
                ];
                $this->model('EmployeeData')->recruit($data);
            }
            return $this->model('EmployeeData')->getById($user_id);
        }else{
            $data = $this->getData(['email', 'phone','password']);
            $user = $this->model('User')->getById($user_id);
            $data = ['user_id' => $user_id,
                     'salary_password'  => \Arr::get($data, 'password',$user['password']),
                     'phone' => \Arr::get($data, 'phone', $user['phone']),
                     'email' => \Arr::get($data, 'email', $user['email']),
            ];
            $this->model('EmployeeData')->recruit($data);
            return $this->model('EmployeeData')->getById($user_id);
        }
        return;
    }
}