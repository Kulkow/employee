<?
namespace Modules\Employee\Admin\Models;

class Codex extends \Classes\Base\Model
{
    protected $table = 'codex';
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT 
            r.*
            FROM role as r
            WHERE r.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function getByList(array $filter = [])
    {
        $rules = [];
        $params = [];
        $criteria = [];
        foreach($filter as $key => $value){
            if(! is_array($value)){
                $first = substr($key, 0, 1);
                if('!' == $first){
                    $key = substr($key, 1);
                    $criteria[$key] = "r.".$key." != :".$key.":";    
                }else{
                    $criteria[$key] = "r.".$key." = :".$key.":";
                }
                
            }else{
                $criteria[$key] = "r.".$key." IN (:".$key.":)";
            }
            $params[$key] = $value;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT 
                r.*
            FROM role as r
            {$where}
            ORDER BY r.position
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function getByAllowList($disallow = [])
    {
        $params = [];
        $criteria = [];
        if(! empty($disallow)){
            $criteria['code'] = "r.code NOT IN (:code:)";
            $params['code'] = $disallow;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT 
                r.*
            FROM role as r
            {$where}
            ORDER BY r.position
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
    
    public function getByUser($userid = NULL)
    {
        if(! $userid){
            return null;
        }
        $query = $this->db->newStatement("
            SELECT ru.text as name,
                   c1.is_allowed group_rule,
                   c2.is_allowed personal_rule,
                   (c1.is_allowed OR c2.is_allowed) is_allowed,
                   ru.id,
                   c2.id as codex_id,
                   ru.rule_group_id,
                   gr.name groupname
            FROM rule ru
            LEFT OUTER JOIN codex c1 ON ru.id=c1.rule_id AND c1.role_id=(SELECT role_id FROM user WHERE id=:id:)
            LEFT OUTER JOIN codex c2 ON ru.id=c2.rule_id AND c2.user_id=:id:
            INNER JOIN rule_group gr ON gr.id=ru.rule_group_id
            ORDER BY gr.id, ru.text
        ");
        $query->setInteger('id', $userid);
        return $query->getAllRecords();
    }
    
    public function asArrayGroup($rules = [])
    {
        $groups = [];
        foreach($rules as $rule){
            $group_id = \Arr::get($rule, 'rule_group_id', 0);
            if(! isset($groups[$group_id])) $groups[$group_id] = ['id' => $group_id, 'name' => $rule['groupname'], 'rules' => []];
            $groups[$group_id]['rules'][] = $rule;
        }
        return $groups;
    }
}
/*
    SELECT 
    c.*,
    TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name,
    ru.text,
    ro.name
    FROM `codex` as c
    LEFT JOIN user u ON c.user_id=u.id
    LEFT JOIN role ro ON c.role_id=ro.id
    LEFT JOIN rule ru ON c.rule_id=ru.id
    WHERE c.role_id=1
*/