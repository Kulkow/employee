<?
namespace Modules\Employee\Admin\Models;

class Rule extends \Classes\Base\Model
{
    protected $table = 'rule';
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT 
            r.*,
            rp.name as g_name 
            FROM rule as r
            LEFT JOIN rule_group rp ON r.rule_group_id=rp.id
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
                r.*,
                rp.name as g_name
            FROM rule as r
            LEFT JOIN rule_group rp ON r.rule_group_id=rp.id
            {$where}
            ORDER BY r.rule_group_id, r.text
        ");
        $query->bind($params);
        return $query->getAllRecords();
        foreach($query->getAllRecords() as $rule){
            $avanses[$rules['id']] = $rule;
        }
        return $rules;
    }
}

