<?
namespace Modules\Employee\Admin\Models;

class User extends \Classes\Base\Model
{
    protected $table = 'user';
    
    public function getById($id)
    {
        $query = $this->db->newStatement("
            SELECT
                u.*,
                TRIM(CONCAT_WS(' ', u.lastname, u.firstname, u.secondname)) name
            FROM user u
            WHERE u.id = :id:
            LIMIT 1
        ");
        $query->setInteger('id', $id);
        return $query->getFirstRecord();
    }
    
    public function registration($data) {
        $keys = array(
            'lastname',
            'firstname',
            'secondname',
            'phone',
            'additional_phones',
            'email',
            'password',
            'is_subscribed',
            'city_id',
            'street',
            'home',
            'housing',
            'apartment',
            'floor',
            'lift',
        );
        $data = \Arr::extract($data, $keys);
        $data['reg_date'] = date("Y-m-d H:i:s");
        $data['owner_id'] = OWNER_ID;
        if (!$data['city_id']) {
            $data['city_id'] = \Model::factory('Geo')->getCityId();
        }
        $comuser = $this->getByAttributes(['email' => $data['email']]);
        if ($data['phone']) {
            $comuser2 = $this->getByAttributes(['phone' => $data['phone']]);
            if (null !== $comuser2) {
                if (null === $comuser || $comuser['id'] == $comuser2['id']) {
                    $comuser = $comuser2;
                }
                else {
                    if ($comuser['phone'] && $comuser['phone'] != $comuser2['phone']) {
                        $additionals = [];
                        if ($data['additional_phones']) {
                            $additionals[] = $data['additional_phones'];
                        }
                        $additionals[] = '+7'.$comuser['phone'];
                        if ($comuser['additional_phones']) {
                            $additionals[] = $comuser['additional_phones'];
                        }
                        if ($comuser2['additional_phones']) {
                            $additionals[] = $comuser2['additional_phones'];
                        }
                        $data['additional_phones'] = implode(', ', $additionals);
                        $comuser['phone'] = $data['phone'];
                    }
                    $query = $this->_db->newStatement("
                        UPDATE `order`
                        SET user_id = :new_user_id:
                        WHERE user_id = :old_user_id:
                    ");
                    $query->setInteger('new_user_id', $comuser['id']);
                    $query->setInteger('old_user_id', $comuser2['id']);
                    $query->execute();
                }
            }
            if (null !== $comuser) {
                if ($comuser['phone'] && $comuser['phone'] != $data['phone']) {
                    $additionals = [];
                    if ($data['additional_phones']) {
                        $additionals[] = $data['additional_phones'];
                    }
                    $additionals[] = '+7'.$comuser['phone'];
                    if ($comuser['additional_phones']) {
                        $additionals[] = $comuser['additional_phones'];
                    }
                    $data['additional_phones'] = implode(', ', $additionals);
                }
                $this->update($data, $comuser['id']);
                return $comuser['id'];
            }
        }
        return $this->insert($data);
    }

    //Roles users
    public function getRoles($ids = []){
        $params = $criteria = $roles = [];
        $criteria['ids'] = "u.id IN (:ids:)";
        $params['ids'] = $ids;
        $where = ! empty($criteria) ? "WHERE ".implode(' AND ', $criteria) : '';
        $query = $this->db->newStatement("
            SELECT
                r.*,
                u.id as user_id,
                u.role_id
            FROM user as u
            LEFT JOIN role as r ON u.role_id=r.id
            {$where}
            ORDER BY u.id
        ");
        $query->bind($params);
        foreach($query->getAllRecords() as $r){
            $roles[$r['user_id']] = $r;
        }
        return $roles;
    }
}