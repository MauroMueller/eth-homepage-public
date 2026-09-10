<?php
/**
 * Loads and stores users from/to the database.
 * 
 * Interface for Auth to get users from the database as User objects or store
 * modified User objects back into the database.
 */
require_once($SRC_DIR.'/core/User.php');

class UserRepository {
    private $db;
    private $defaults;

    public function __construct($db, $defaults) {
        $this->db = $db;
        $this->defaults = $defaults;
    }

    private function user_from_array($user_array) {
        if (is_null($user_array)) return null;
        return new User($user_array);
    }

    public function get_by_uuid($user_uuid) {
        $sql = 'SELECT * FROM users WHERE user_uuid = :user_uuid';
        $user_array = $this->db->one($sql, ['user_uuid' => $user_uuid]);
        return $this->user_from_array($user_array);
    }

    public function get_by_username($username) {
        $sql = 'SELECT * FROM users WHERE username = :username';
        $user_array = $this->db->one($sql, ['username' => $username]);
        return $this->user_from_array($user_array);
    }

    public function get_by_email($email) {
        $sql = 'SELECT * FROM users WHERE email = :email';
        $user_array = $this->db->one($sql, ['email' => $email]);
        return $this->user_from_array($user_array);
    }

    public function update_user($user) {
        $this->db->update_row('users', $user->user_array(), 'user_uuid');
    }

    public function create_user($user_array) {
        foreach ($this->defaults as $key => $value) {
            if (!isset($user_array[$key]) || is_null($user_array[$key]))
                $user_array[$key] = $value;
        }
        $row_count = $this->db->insert_row('users', $user_array);
        return ($row_count == 1) ? new User($user_array) : null;
    }

    public function username_exists($username) {
        return !is_null($this->get_by_username($username));
    }

    public function email_exists($email) {
        return !is_null($this->get_by_email($email));
    }

    public function update_user_preferences($user_uuid, $array) {
        $this->db->update_fields('users', $array, 'user_uuid', $user_uuid);
    }
}
?>
