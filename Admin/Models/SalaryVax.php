<?
namespace Modules\Employee\Admin\Models;

class SalaryVax extends \Classes\Base\Model
{
    const READY_NO = 0;
    const READY = 1;
    
    protected $table = 'salary_vax';
    
    protected function init_sql(){
        "
        CREATE TABLE IF NOT EXISTS `salary_vax` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `vax` int(11) NOT NULL,
        `ready` tinyint(1) default '0' NOT NULL,
        `date` date,
        `time` datetime,
        INDEX user_id (`user_id`),
        PRIMARY KEY (`id`)
      ) ENGINE=Aria DEFAULT CHARSET=utf8;";
    }
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                v.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM salary_vax v
            LEFT JOIN user u ON u.id=v.user_id
            WHERE v.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function getByList(array $filter = [])
    {
        $vaxes = $params = $criteria = [];
        foreach($filter as $key => $value){
            if(! is_array($value)){
                $first = substr($key, 0, 1);
                if('!' == $first){
                    $key = substr($key, 1);
                    $criteria[$key] = "v.".$key." != :".$key.":";    
                }else{
                    $criteria[$key] = "v.".$key." = :".$key.":";
                }
                
            }else{
                $criteria[$key] = "v.".$key." IN (:".$key.":)";
            }
            $params[$key] = $value;
        }
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                v.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM salary_vax v
            LEFT JOIN user u ON u.id=v.user_id
            {$where}
            ORDER BY v.date, u.lastname
        ");
        $query->bind($params);
        foreach($query->getAllRecords() as $vax){
            $vaxes[$vax['id']] = $vax;
        }
        return $vaxes;
    }
}