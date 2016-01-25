<?
namespace Modules\Employee\Admin\Models;

class Avans extends \Classes\Base\Model
{
    protected $table = 'salary_avans';
    
    protected function init_sql(){
        "
        CREATE TABLE IF NOT EXISTS `salary_avans` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `avans` int(11) NOT NULL,
        `date` date,
        `created` datetime,
        INDEX user_id (`user_id`),
        PRIMARY KEY (`id`)
      ) ENGINE=Aria DEFAULT CHARSET=utf8;";
    }
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                a.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM salary_avans a
            LEFT JOIN user u ON u.id=s.user_id
            WHERE a.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function getByList(array $filter = [])
    {
        $avanses = [];
        $params = [];
        $criteria = [];
        foreach($filter as $key => $value){
            if(! is_array($value)){
                $first = substr($key, 0, 1);
                if('!' == $first){
                    $key = substr($key, 1);
                    $criteria[$key] = "s.".$key." != :".$key.":";    
                }else{
                    $criteria[$key] = "s.".$key." = :".$key.":";
                }
                
            }else{
                $criteria[$key] = "s.".$key." IN (:".$key.":)";
            }
            $params[$key] = $value;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                a.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM salary_avans a
            LEFT JOIN user u ON u.id=s.user_id
            {$where}
            ORDER BY a.date, u.lastname
        ");
        $query->bind($params);
        foreach($query->getAllRecords() as $avans){
            $avanses[$avans['id']] = $avans;
        }
        return $avanses;
    }
}