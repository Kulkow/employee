<?
namespace Modules\Employee\Admin\Models;

class Role extends \Classes\Base\Model
{
    const CLIENT = 3;

    protected $table = 'role';
    
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

    /**
     * @param array $filter
     * @return mixed
     */
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
}

