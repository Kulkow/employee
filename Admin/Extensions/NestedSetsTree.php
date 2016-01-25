<?php

namespace Modules\Employee\Admin\Extensions;

class NestedSetsTree
{
    /**
     * Name of the table where tree is stored.
     *
     * @var string
     */
    public $table = '';
    
    public $pk = 'id';

    /**
     * Unique number of node.
     *
     * @var string
     */
    public $tableId = '';

    /**
     * Level of nesting.
     *
     * @var string
     */
    public $tableLevel = '';

    /**
     * Database layer object.
     *
     * @var db
     */
    protected $db;

    /**
     * @var string
     */
    public $tableLeft = '';

    /**
     * @var string
     */
    public $tableRight = '';


    public function __construct($fields, \Classes\DB\Connection $db)
    {
        $this->db = $db;
        $this->table = $fields['table'];
        $this->tableId = isset ($fields['id']) ? $fields['id'] : 'id';
        $this->tableLeft = isset ($fields['left']) ? $fields['left'] : 'lkey';
        $this->tableRight = isset ($fields['right']) ? $fields['right'] : 'rkey';
        $this->tableLevel = isset ($fields['level']) ? $fields['level'] : 'level';
        $this->tableTree = $this->db->table($this->table, $this->pk);
    }
    
    public function errors($key = NULL){
        $errors = ['DBTREE_INTERNAL_ERROR_U' => 'Внутренняя ошибка сервера, свяжитесь с администратором.',
                'DBTREE_NO_ELEMENT' => 'В дереве нет такого элемента.',
                'DBTREE_CANT_MOVE' => 'Немогу переместить ветку.',
                'DBTREE_CANT_CHANGE_POSITION' => 'Немогу поменять позицию.',
                'DBTREE_INCORRECT_POSITION' => 'Неправильная позиция. Вы можете выбрать: "after" of "before"',
                'DBTREE_NO_DATA_TABLE' => 'Нет дополнительной таблицы для данных.',
                ];
        return \Arr::get($errors,  $key, NULL);
    }

    /**
     * Converts array of selected fields into part of SELECT query.
     *
     * @param string|array $fields Fields to be selected
     * @param string $table - Table or alias to select form
     * @return string - Part of SELECT query
     */
    protected function PrepareSelectFields($fields = '*', $table = null)
    {
        if (!empty($table)) {
            $table .= '.';
        }

        if (is_array($fields)) {
            $fields = $table . implode(', ' . $table, $fields);
        } else {
            $fields = $table . $fields;
        }

        return $fields;
    }

    /**
     * Receive all data for node with number $nodeId.
     *
     * @param int $nodeId Unique node id
     * @param string|array $fields Fields to be selected
     * @return array All node data
     * @throws USER_Exception
     */
    public function GetNode($nodeId = NULL, $fields = '*')
    {
        if(! $nodeId){
            return $this->GetRoot();
        }
        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT ' . $fields . ' FROM ' . $this->table . ' AS A WHERE A.' . $this->tableId . ' = ' . (int)$nodeId;
        $query = $this->db->newStatement($sql);
        $result = $query->getFirstRecord();
        if (false === $result) {
            throw new \Exception($this->errors('DBTREE_NO_ELEMENT'));
        }
        return $result;
    }
    
    public function GetNodePrev($nodeId = NULL, $fields = '*')
    {
        $node = $this->GetNode($nodeId, '*');
        $parent = $this->GetParent($nodeId, '*');
        $lkeyparent = \Arr::get($parent, $this->tableLeft, 0);
        $lkey = \Arr::get($node, $this->tableLeft, 0);
        $rkey_prev = $lkey -1;
        if($rkey_prev == $lkeyparent){
            //prev parent
            $key = $this->tableLeft;
            return null;
        }else{
            $key = $this->tableRight;
        }
        $fields = $this->PrepareSelectFields($fields, 'A');
        $sql = 'SELECT ' . $fields . ' FROM ' . $this->table . ' AS A WHERE A.' . $key . ' = ' . $rkey_prev;
        $query = $this->db->newStatement($sql);
        $result = $query->getFirstRecord();
        if (false === $result) {
            throw new \Exception($this->errors('DBTREE_NO_ELEMENT'));
        }
        return $result;
    }
    
    public function GetNodeNext($nodeId = NULL, $fields = '*')
    {
        $parent = $this->GetParent($nodeId, '*');
        if($parent){
            $parent = \Arr::get($parent, $this->tableId, 0);
        }
        $branch = $this->Branch($parent);
        $last = array_pop($branch);
        if($last_id = \Arr::get($last, $this->tableId, 0)){
            if($last_id == $nodeId){
                return null;
            }
        }
        $node = $this->GetNode($nodeId, '*');
        $rkey_prev = \Arr::get($node, $this->tableRight, 0);
        $rkey_prev++;
        $fields = $this->PrepareSelectFields($fields, 'A');
        $sql = 'SELECT ' . $fields . ' FROM ' . $this->table . ' AS A WHERE A.' . $this->tableLeft . ' = ' . $rkey_prev;
        $query = $this->db->newStatement($sql);
        $result = $query->getFirstRecord();
        if (false === $result) {
            throw new \Exception($this->errors('DBTREE_NO_ELEMENT'));
        }
        return $result;
    }
    
    public function GetRoot($fields = '*')
    {
        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT ' . $fields . ' FROM ' . $this->table . ' AS A WHERE A.' . $this->tableLevel . ' = 0';
        $query = $this->db->newStatement($sql);
        $result = $query->getFirstRecord();
        return $result;
    }
    
    public function addRoot($name = 'Root'){
        if($this->GetRoot('id') === NULL){
            $data = [];
            $data[$this->tableLeft] = 1;
            $data[$this->tableRight] = 2;
            $data[$this->tableLevel] = 0;
            $data['name'] = $name;
        }
        return $this->tableTree->insert($data);
    }
    

    /**
     * Receive data of closest parent for node with number $nodeId.
     *
     * @param int $nodeId
     * @param string|array $fields Fields to be selected
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return array All node data
     * @throws USER_Exception
     */
    public function GetParent($nodeId, $fields = '*', $condition = '')
    {
        $condition = $this->PrepareCondition($condition, false, 'A.');
        $fields = $this->PrepareSelectFields($fields, 'A');

        $node_info = $this->GetNode($nodeId);

        $left_id = $node_info[$this->tableLeft];
        $right_id = $node_info[$this->tableRight];
        $level = $node_info[$this->tableLevel];
        $level--;

        $sql = 'SELECT ' . $fields . ' FROM ' . $this->table . ' AS A';
        $sql .= ' WHERE ' . $this->tableLeft . ' < ' . $left_id . ' AND ' . $this->tableRight . ' > ' . $right_id . ' AND ' . $this->tableLevel . ' = ' . $level . ' ';
        $sql .= $condition . ' ORDER BY ' . $this->tableLeft;
        $query = $this->db->newStatement($sql);
        $result = $query->getFirstRecord();

        if (empty($result)) {
            throw new \Exception($this->errors('DBTREE_NO_ELEMENT'));
        }

        return $result;
    }

    /**
     * Add new child element to node with number $parentId.
     *
     * @param int $parentId Id of a parental element
     * @param array $data Contains parameters for additional fields of a tree (if is): 'filed name' => 'importance'
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return int Inserted element id
     */
    public function Insert($parentId = 0, $data = array(), $condition = '')
    {
        $node_info = $this->GetNode($parentId);

        $right_id = $node_info[$this->tableRight];
        $level = $node_info[$this->tableLevel];

        $condition = $this->PrepareCondition($condition);

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLeft . '=CASE WHEN ' . $this->tableLeft . '>' . $right_id . ' THEN ' . $this->tableLeft . '+2 ELSE ' . $this->tableLeft . ' END, ';
        $sql .= $this->tableRight . '=CASE WHEN ' . $this->tableRight . '>=' . $right_id . ' THEN ' . $this->tableRight . '+2 ELSE ' . $this->tableRight . ' END ';
        $sql .= 'WHERE ' . $this->tableRight . '>=' . $right_id;
        $sql .= $condition;
        $query = $this->db->newStatement($sql);
        $query->execute();

        $data[$this->tableLeft] = $right_id;
        $data[$this->tableRight] = $right_id + 1;
        $data[$this->tableLevel] = $level + 1;
        
        return $this->tableTree->insert($data);
    }

    /**
     * Add a new element into the tree near node with number $nodeId.
     *
     * @param int $nodeId Id of a node after which new node will be inserted (new node will have same level of nesting)
     * @param array $data Contains parameters for additional fields of a tree (if is): 'filed name' => 'importance'
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return int Inserted element id
     */
    public function InsertNear($nodeId= 0, $data = array(), $condition = '')
    {
        $node_info = $this->GetNode($nodeId);

        $right_id = $node_info[$this->tableRight];
        $level = $node_info[$this->tableLevel];

        $condition = $this->PrepareCondition($condition);

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' > ' . $right_id . ' THEN ' . $this->tableLeft . ' + 2 ELSE ' . $this->tableLeft . ' END, ';
        $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . '> ' . $right_id . ' THEN ' . $this->tableRight . ' + 2 ELSE ' . $this->tableRight . ' END ';
        $sql .= 'WHERE ' . $this->tableRight . ' > ' . $right_id;
        $sql .= $condition;
        $query = $this->db->newStatement($sql);
        $query->execute();

        $data[$this->tableLeft] = $right_id + 1;
        $data[$this->tableRight] = $right_id + 2;
        $data[$this->tableLevel] = $level;
        
        return $this->tableTree->insert($data);
    }

    /**
     * Assigns another parent ($parentId) to a node ($nodeId) with all its children.
     *
     * @param int $nodeId Movable node id
     * @param int $parentId Id of a new parent node
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return bool True if successful, false otherwise.
     * @throws USER_Exception
     */
    public function MoveAll($nodeId, $parentId, $condition = '')
    {
        $node_info = $this->GetNode($nodeId);

        $left_id = $node_info[$this->tableLeft];
        $right_id = $node_info[$this->tableRight];
        $level = $node_info[$this->tableLevel];

        $node_info = $this->GetNode($parentId);

        $left_idp = $node_info[$this->tableLeft];
        $right_idp = $node_info[$this->tableRight];
        $levelp = $node_info[$this->tableLevel];

        if ($nodeId == $parentId || $left_id == $left_idp || ($left_idp >= $left_id && $left_idp <= $right_id) || ($level == $levelp + 1 && $left_id > $left_idp && $right_id < $right_idp)) {
            throw new \Exception($nodeId.'-'.$parentId.$this->errors('DBTREE_CANT_MOVE'));
        }

        $condition = $this->PrepareCondition($condition);

        $sql = 'UPDATE ' . $this->table . ' SET ';
        if ($left_idp < $left_id && $right_idp > $right_id && $levelp < $level - 1) {
            $sql .= $this->tableLevel . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLevel . sprintf('%+d', -($level - 1) + $levelp) . ' ELSE ' . $this->tableLevel . ' END, ';
            $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . ' BETWEEN ' . ($right_id + 1) . ' AND ' . ($right_idp - 1) . ' THEN ' . $this->tableRight . '-' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableRight . '+' . ((($right_idp - $right_id - $level + $levelp) / 2) * 2 + $level - $levelp - 1) . ' ELSE ' . $this->tableRight . ' END, ';
            $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id + 1) . ' AND ' . ($right_idp - 1) . ' THEN ' . $this->tableLeft . '-' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLeft . '+' . ((($right_idp - $right_id - $level + $levelp) / 2) * 2 + $level - $levelp - 1) . ' ELSE ' . $this->tableLeft . ' END ';
            $sql .= 'WHERE ' . $this->tableLeft . ' BETWEEN ' . ($left_idp + 1) . ' AND ' . ($right_idp - 1);
        } elseif ($left_idp < $left_id) {
            $sql .= $this->tableLevel . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLevel . sprintf('%+d', -($level - 1) + $levelp) . ' ELSE ' . $this->tableLevel . ' END, ';
            $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $right_idp . ' AND ' . ($left_id - 1) . ' THEN ' . $this->tableLeft . '+' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLeft . '-' . ($left_id - $right_idp) . ' ELSE ' . $this->tableLeft . ' END, ';
            $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . ' BETWEEN ' . $right_idp . ' AND ' . $left_id . ' THEN ' . $this->tableRight . '+' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableRight . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableRight . '-' . ($left_id - $right_idp) . ' ELSE ' . $this->tableRight . ' END ';
            $sql .= 'WHERE (' . $this->tableLeft . ' BETWEEN ' . $left_idp . ' AND ' . $right_id . ' ';
            $sql .= 'OR ' . $this->tableRight . ' BETWEEN ' . $left_idp . ' AND ' . $right_id . ')';
        } else {
            $sql .= $this->tableLevel . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLevel . sprintf('%+d', -($level - 1) + $levelp) . ' ELSE ' . $this->tableLevel . ' END, ';
            $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $right_id . ' AND ' . $right_idp . ' THEN ' . $this->tableLeft . '-' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLeft . '+' . ($right_idp - 1 - $right_id) . ' ELSE ' . $this->tableLeft . ' END, ';
            $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . ' BETWEEN ' . ($right_id + 1) . ' AND ' . ($right_idp - 1) . ' THEN ' . $this->tableRight . '-' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableRight . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableRight . '+' . ($right_idp - 1 - $right_id) . ' ELSE ' . $this->tableRight . ' END ';
            $sql .= 'WHERE (' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_idp . ' ';
            $sql .= 'OR ' . $this->tableRight . ' BETWEEN ' . $left_id . ' AND ' . $right_idp . ')';
        }
        $sql .= $condition;
        //$this->db->query($sql);
        $query = $this->db->newStatement($sql);
        $query->execute();

        return true;
    }

    /**
     * Change position of nodes. Nodes have to have same parent and same level of nesting.
     *
     * @param integer $nodeId1 first node id
     * @param integer $nodeId2 second node id
     * @return bool true if successful, false otherwise.
     */
    public function ChangePosition($nodeId1, $nodeId2)
    {
        $node_info = $this->GetNode($nodeId1);

        $left_id1 = $node_info[$this->tableLeft];
        $right_id1 = $node_info[$this->tableRight];
        $level1 = $node_info[$this->tableLevel];

        $node_info = $this->GetNode($nodeId2);

        $left_id2 = $node_info[$this->tableLeft];
        $right_id2 = $node_info[$this->tableRight];
        $level2 = $node_info[$this->tableLevel];

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLeft . ' = ' . $left_id2 . ', ';
        $sql .= $this->tableRight . ' = ' . $right_id2 . ', ';
        $sql .= $this->tableLevel . ' = ' . $level2 . ' ';
        $sql .= 'WHERE ' . $this->tableId . ' = ' . $nodeId1;
        $query = $this->db->newStatement($sql);
        $query->execute();

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLeft . ' = ' . $left_id1 . ', ';
        $sql .= $this->tableRight . ' = ' . $right_id1 . ', ';
        $sql .= $this->tableLevel . ' = ' . $level1 . ' ';
        $sql .= 'WHERE ' . $this->tableId . ' = ' . $nodeId2;
        $query = $this->db->newStatement($sql);
        $query->execute();

        return true;
    }

    /**
     * Swapping nodes with it's children. Nodes have to have same parent and same level of nesting.
     * $nodeId1 can be placed "before" or "after" $nodeId2.
     *
     * @param int $nodeId1 first node id
     * @param int $nodeId2 second node id
     * @param string $position 'before' or 'after' (default) $nodeId2
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return bool true if successful, false otherwise.
     * @throws USER_Exception
     */
    public function ChangePositionAll($nodeId1, $nodeId2, $position = 'after', $condition = '')
    {
        if ($position != 'after' && $position != 'before') {
            throw new \Exception($this->error('DBTREE_INCORRECT_POSITION'));
        }

        $node_info = $this->GetNode($nodeId1);

        $left_id1 = $node_info[$this->tableLeft];
        $right_id1 = $node_info[$this->tableRight];
        $level1 = $node_info[$this->tableLevel];

        $node_info = $this->GetNode($nodeId2);

        $left_id2 = $node_info[$this->tableLeft];
        $right_id2 = $node_info[$this->tableRight];
        $level2 = $node_info[$this->tableLevel];

        if ($level1 <> $level2) {
            throw new \Exception($nodeId1.'-'.$nodeId2.$this->errors('DBTREE_CANT_CHANGE_POSITION'));
        }

        $sql = 'UPDATE ' . $this->table . ' SET ';
        if ('before' == $position) {
            if ($left_id1 > $left_id2) {
                $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableRight . ' - ' . ($left_id1 - $left_id2) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id2 . ' AND ' . ($left_id1 - 1) . ' THEN ' . $this->tableRight . ' +  ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableRight . ' END, ';
                $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableLeft . ' - ' . ($left_id1 - $left_id2) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id2 . ' AND ' . ($left_id1 - 1) . ' THEN ' . $this->tableLeft . ' + ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableLeft . ' END ';
                $sql .= 'WHERE ' . $this->tableLeft . ' BETWEEN ' . $left_id2 . ' AND ' . $right_id1;
            } else {
                $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableRight . ' + ' . (($left_id2 - $left_id1) - ($right_id1 - $left_id1 + 1)) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id1 + 1) . ' AND ' . ($left_id2 - 1) . ' THEN ' . $this->tableRight . ' - ' . (($right_id1 - $left_id1 + 1)) . ' ELSE ' . $this->tableRight . ' END, ';
                $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableLeft . ' + ' . (($left_id2 - $left_id1) - ($right_id1 - $left_id1 + 1)) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id1 + 1) . ' AND ' . ($left_id2 - 1) . ' THEN ' . $this->tableLeft . ' - ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableLeft . ' END ';
                $sql .= 'WHERE ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . ($left_id2 - 1);
            }
        }

        if ('after' == $position) {
            if ($left_id1 > $left_id2) {
                $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableRight . ' - ' . ($left_id1 - $left_id2 - ($right_id2 - $left_id2 + 1)) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id2 + 1) . ' AND ' . ($left_id1 - 1) . ' THEN ' . $this->tableRight . ' +  ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableRight . ' END, ';
                $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableLeft . ' - ' . ($left_id1 - $left_id2 - ($right_id2 - $left_id2 + 1)) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id2 + 1) . ' AND ' . ($left_id1 - 1) . ' THEN ' . $this->tableLeft . ' + ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableLeft . ' END ';
                $sql .= 'WHERE ' . $this->tableLeft . ' BETWEEN ' . ($right_id2 + 1) . ' AND ' . $right_id1;
            } else {
                $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableRight . ' + ' . ($right_id2 - $right_id1) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id1 + 1) . ' AND ' . $right_id2 . ' THEN ' . $this->tableRight . ' - ' . (($right_id1 - $left_id1 + 1)) . ' ELSE ' . $this->tableRight . ' END, ';
                $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableLeft . ' + ' . ($right_id2 - $right_id1) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id1 + 1) . ' AND ' . $right_id2 . ' THEN ' . $this->tableLeft . ' - ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableLeft . ' END ';
                $sql .= 'WHERE ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id2;
            }
        }

        $condition = $this->PrepareCondition($condition);

        $sql .= $condition;
        $query = $this->db->newStatement($sql);
        $query->execute();
        return true;
    }

    /**
     * Deletes element with number $nodeId from the tree without deleting it's children
     * All it's children will move up one level.
     *
     * @param integer $nodeId Node id
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return bool true if successful, false otherwise.
     */
    public function Delete($nodeId, $condition = '')
    {
        $node_info = $this->GetNode($nodeId);

        $condition = $this->PrepareCondition($condition);

        $left_id = $node_info[$this->tableLeft];
        $right_id = $node_info[$this->tableRight];

        $sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->tableId . ' = ' . $nodeId;
        $query = $this->db->newStatement($sql);
        $query->execute();

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLevel . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLevel . ' - 1 ELSE ' . $this->tableLevel . ' END, ';
        $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableRight . ' - 1 ';
        $sql .= 'WHEN ' . $this->tableRight . ' > ' . $right_id . ' THEN ' . $this->tableRight . ' - 2 ELSE ' . $this->tableRight . ' END, ';
        $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLeft . ' - 1 ';
        $sql .= 'WHEN ' . $this->tableLeft . ' > ' . $right_id . ' THEN ' . $this->tableLeft . ' - 2 ELSE ' . $this->tableLeft . ' END ';
        $sql .= 'WHERE ' . $this->tableRight . ' > ' . $left_id;
        $sql .= $condition;
        $query = $this->db->newStatement($sql);
        $query->execute();

        return true;
    }

    /**
     * Deletes element with number $nodeId from the tree and all it children.
     *
     * @param integer $nodeId Node id
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return bool true if successful, false otherwise.
     */
    public function DeleteAll($nodeId, $condition = '')
    {
        $node_info = $this->GetNode($nodeId);

        $left_id = $node_info[$this->tableLeft];
        $right_id = $node_info[$this->tableRight];

        $condition = $this->PrepareCondition($condition);

        $sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id;
        $sql .= $condition;
        $this->db->query($sql);

        $delta_id = (($right_id - $left_id) + 1);
        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' > ' . $left_id . ' THEN ' . $this->tableLeft . ' - ' . $delta_id . ' ELSE ' . $this->tableLeft . ' END, ';
        $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . ' > ' . $left_id . ' THEN ' . $this->tableRight . ' - ' . $delta_id . ' ELSE ' . $this->tableRight . ' END ';
        $sql .= 'WHERE ' . $this->tableRight . ' > ' . $right_id;
        $sql .= $condition;
        //$this->db->query($sql);
        $query = $this->db->newStatement($sql);
        $query->execute();
        return true;
    }

    /**
     * Transforms array with conditions to SQL query
     * Array structure:
     * array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc
     * where array key - condition (AND, OR, etc), value - condition string.
     *
     * @param array $condition
     * @param string $prefix
     * @param bool $where - true - yes, false (dafault) - not
     * @return string
     */
    protected function PrepareCondition($condition, $where = false, $prefix = '')
    {
        if (empty ($condition)) {
            return '';
        }

        if (!is_array($condition)) {
            return $condition;
        }

        $sql = ' ';

        if (true === $where) {
            $sql .= 'WHERE ' . $prefix;
        }

        $keys = array_keys($condition);

        for ($counter = count($keys), $i = 0; $i < $counter; $i++) {
            if (false === $where || (true === $where && $i > 0)) {
                $sql .= ' ' . strtoupper($keys[$i]) . ' ' . $prefix;
            }

            $sql .= implode(' ' . strtoupper($keys[$i]) . ' ' . $prefix, $condition[$keys[$i]]);
        }

        return $sql;
    }
    
    /**
     * Returns all elements of the tree sorted by "left".
     *
     * @param string|array $fields Fields to be selected
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return array Needed fields
     */
    function Full($fields = '*', $condition = '', $join = '')
    {
        $condition = $this->PrepareCondition($condition, true, 'A.');
        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT ' . $fields . ' FROM ' . $this->table . ' AS A';
        $sql .= $condition;
        $sql .= $join;
        $sql .= ' ORDER BY ' . $this->tableLeft;
        //$result = $this->db->getInd($this->tableId, $sql);
        $query = $this->db->newStatement($sql);
        $result = $query->getAllRecords();
        return $result;
    }

    /**
     * Returns all elements of a branch starting from an element with number $nodeId.
     *
     * @param integer $nodeId Node unique id
     * @param string|array $fields Fields to be selected
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return array Needed fields
     */
    function Branch($nodeId, $fields = '*', $condition = '')
    {

        $condition = $this->PrepareCondition($condition, false, 'A.');
        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT A.' . $this->tableId . ', A.' . $this->tableLeft . ', A.' . $this->tableRight . ', A.' . $this->tableLevel . ', ' . $fields . ', CASE WHEN A.' . $this->tableLeft . ' + 1 < A.' . $this->tableRight . ' THEN 1 ELSE 0 END AS nflag ';
        $sql .= 'FROM ' . $this->table . ' B, ' . $this->table . ' A ';
        $sql .= 'WHERE B.' . $this->tableId . ' = ' . (int)$nodeId . ' AND A.' . $this->tableLeft . ' >= B.' . $this->tableLeft . ' AND A.' . $this->tableRight . ' <= B.' . $this->tableRight;
        $sql .= $condition;
        $sql .= ' ORDER BY A.' . $this->tableLeft;
        $query = $this->db->newStatement($sql);
        return $query->getAllRecords();
    }

    /**
     * Returns all parents of element with number $nodeId.
     *
     * @param integer $nodeId Node unique id
     * @param string|array $fields Fields to be selected
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return array Needed fields
     */
    function Parents($nodeId = NULl, $fields = '*', $condition = '')
    {
        $condition = $this->PrepareCondition($condition, false, 'A.');
        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT A.' . $this->tableId . ', A.' . $this->tableLeft . ', A.' . $this->tableRight . ', A.' . $this->tableLevel . ', ' . $fields . ', CASE WHEN A.' . $this->tableLeft . ' + 1 < A.' . $this->tableRight . ' THEN 1 ELSE 0 END AS nflag ';
        $sql .= 'FROM ' . $this->table . ' B, ' . $this->table . ' A ';
        $sql .= 'WHERE B.' . $this->tableId . ' = ' . (int)$nodeId . ' AND B.' . $this->tableLeft . ' BETWEEN A.' . $this->tableLeft . ' AND A.' . $this->tableRight;
        $sql .= $condition;
        $sql .= ' ORDER BY A.' . $this->tableLeft;
        $query = $this->db->newStatement($sql);
        return $query->getAllRecords();
    }

    /**
     * Returns a slightly opened tree from an element with number $nodeId.
     *
     * @param integer $nodeId Node unique id
     * @param string|array $fields Fields to be selected
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return array Needed fields
     * @throws USER_Exception
     */
    function Ajar($nodeId, $fields = '*', $condition = '')
    {
        $condition = $this->PrepareCondition($condition, false, 'A.');

        $sql = 'SELECT A.' . $this->tableLeft . ', A.' . $this->tableRight . ', A.' . $this->tableLevel . ' ';
        $sql .= 'FROM ' . $this->table . ' A, ' . $this->table . ' B ';
        $sql .= 'WHERE B.' . $this->tableId . ' = ' . $nodeId . ' ';
        $sql .= 'AND B.' . $this->tableLeft . ' BETWEEN A.' . $this->tableLeft . ' ';
        $sql .= 'AND A.' . $this->tableRight;
        $sql .= $condition;
        $sql .= ' ORDER BY A.' . $this->tableLeft;
        $res = $this->db->query($sql);

        if (0 == $this->db->numRows($res)) {
            throw new USER_Exception(DBTREE_NO_ELEMENT, 0);
        }

        $alen = $this->db->numRows($res);
        $i = 0;

        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT A.' . $this->tableId . ', A.' . $this->tableLeft . ', A.' . $this->tableRight . ', A.' . $this->tableLevel . ', ' . $fields . ' ';
        $sql .= 'FROM ' . $this->table . ' A ';
        $sql .= 'WHERE (' . $this->tableLevel . ' = 1';
        while ($row = $this->db->fetch($res)) {
            if ((++$i == $alen) && ($row[$this->tableLeft] + 1) == $row[$this->tableRight]) {
                break;
            }
            $sql .= ' OR (' . $this->tableLevel . ' = ' . ($row[$this->tableLevel] + 1) . ' AND ' . $this->tableLeft . ' > ' . $row[$this->tableLeft] . ' AND ' . $this->tableRight . ' < ' . $row[$this->tableRight] . ')';
        }
        $sql .= ') ' . $condition;
        $sql .= ' ORDER BY ' . $this->tableLeft;

        return $this->db->getInd($this->tableId, $sql);
    }

    /**
     * Sort children in a tree for $orderField in alphabetical order.
     *
     * @param integer $id - Parent's ID.
     * @param string $orderField - the name of the field on which sorting will go
     */
    public function SortChildren($id, $orderField)
    {
        $node = $this->GetNode($id);
        $data = $this->Branch(
            $id,
            array(
                $this->tableId
            ), array(
                'and' => array(
                    $this->tableLevel . ' = ' . ($node[$this->tableLevel] + 1)
                )
            )
        );

        if (!empty($data)) {
            $sql = 'SELECT ' . $this->tableId . ' FROM ' . $this->table . ' WHERE ' . $this->tableId . ' IN(?a) ORDER BY ' . $orderField;
            $sorted_data = $this->db->getAll($sql, array_keys($data));

            $data = array_values($data);

            $last_coincidence = true;
            foreach ($sorted_data as $key => $value) {
                if ($data[$key][$this->tableId] == $value[$this->tableId] && $last_coincidence !== false) {
                    continue;
                } else {
                    $last_coincidence = false;

                    if ($key == 0) {
                        $this->ChangePositionAll($value[$this->tableId], $data[$key][$this->tableId], 'before');
                    } else {
                        $this->ChangePositionAll($sorted_data[($key)][$this->tableId], $sorted_data[($key - 1)][$this->tableId], 'after');
                    }
                }
            }
        }
    }

    /**
     * Makes UL/LI html from nested sets tree with links (if needed). UL id named as table_name + _tree.
     *
     * @param array $tree - nested sets tree array
     * @param string $nameField - name of field that contains title of URL
     * @param array $linkField - name of field that contains URL (if needed)
     * @param null|string $linkPrefix - URL prefix (if needed)
     * @param string $delimiter - linkField delimiter
     * @return string - UL/LI html code
     */
    public function MakeUlList($tree, $nameField, $linkField = array(), $linkPrefix = null, $delimiter = '')
    {
        $current_depth = 0;
        $node_depth = 0;
        $counter = 0;

        $result = '<ul id="' . $this->table . '_tree">';

        foreach ($tree as $node) {
            $node_depth = $node[$this->tableLevel];
            $node_name = $node[$nameField];

            if ($node_depth == $current_depth) {
                if ($counter > 0) $result .= '</li>';
            } elseif ($node_depth > $current_depth) {
                $result .= '<ul>';
                $current_depth = $current_depth + ($node_depth - $current_depth);
            } elseif ($node_depth < $current_depth) {
                $result .= str_repeat('</li></ul>', $current_depth - $node_depth) . '</li>';
                $current_depth = $current_depth - ($current_depth - $node_depth);
            }

            $result .= '<li>';

            if (!empty($linkField)) {
                $link_data = array();
                $linkField = !is_array($linkField) ? array($linkField) : $linkField;
                foreach($linkField as $field) {
                    $link_data[] = $node[$field];
                }

                $link = !is_null($linkPrefix) ? $linkPrefix . implode($delimiter, $link_data) : implode($delimiter, $link_data);

                $result .= '<a href="' . $link . '">' . $node_name . '</a>';
            } else {
                $result .= $node_name;
            }
            ++$counter;
        }

        $result .= str_repeat('</li></ul>', $node_depth) . '</li>';

        $result .= '</ul>';

        return $result;
    }
}

?>