<?php

namespace Modules\Employee\Admin\Extensions\Plan;

use Classes\DB\Connection;
use Classes\DB\Table;
use Classes\Auth\User;

class Indicator
{
    /**
     * @var Classes\DB\Connection
     */
    protected $db;

    /**
     * @var Classes\Auth\User
     */
    protected $user;
    
    protected $start = NULL;
    
    protected $end = NULL;
    
    protected $users = [];
    
    protected $ids = [];
    
    protected $department_id = NULL;

    public function __construct(Connection $db, User $user, $start = NULL, $end = NULL, $users = [], $departments = [])
    {
        $this->db = $db;
        $this->user = $user;
        $this->start_day = $start;
        $this->end_day = $end;
        $this->start = date('Y-m', strtotime($start));
        $this->end = date('Y-m', strtotime($end));
        $this->users = $users;
        $this->ids = array_keys($this->users);
        $this->departments = $departments;
        return $this;
    }
    
    public function PreSetCalculate($allrecords){
        if($allrecords){
            $calculate = []; // user_id => ['cnt', 'mount', 'year']
            foreach($allrecords as $record){
                $record['mount'] = date('m', strtotime($record['date']));
                $record['year'] = date('Y', strtotime($record['date']));
                $department_id = \Arr::get($record, 'department_id', null);
                $user_id = \Arr::get($record, 'user_id', null);
                if($department_id){
                    $calculate[$department_id] = $record;
                }elseif ($user_id){
                    $calculate[$record['user_id']] = $record;
                } else {
                    $calculate = $record;
                }
            }
            return $calculate;
        }
        return [];
    }
    
}