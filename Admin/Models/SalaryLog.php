<?
namespace Modules\Employee\Admin\Models;

class SalaryLog extends \Classes\Base\Model
{
    protected $table = 'salary_log';
    
    protected function init_sql(){
        // ЗП за месяц
        "
        CREATE TABLE IF NOT EXISTS `salary_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `expense_id` int(11) NOT NULL,
        `avans` int(11) NOT NULL,
        `out` int(11) NOT NULL,
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
                s.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM salary_log s
            LEFT JOIN user u ON u.id=s.user_id
            WHERE s.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function getByList(array $filter = [])
    {
        $logs = [];
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
                s.*,
                e.text,
                e.creation_date,
                e.approval_date,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM salary_log s
            LEFT JOIN user u ON u.id=s.user_id
            LEFT JOIN expense e ON e.id=s.expense_id
            {$where}
            ORDER BY e.approval_date, s.date
        ");
        $query->bind($params);
        return $query->getAllRecords();
    }
}