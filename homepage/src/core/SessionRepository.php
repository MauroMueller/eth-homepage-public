<?php
/**
 * Loads and stores sessions from/to the database.
 * 
 * Is responsible for both storing session information in the db as well as
 * constructing Session objects to store the information in the application
 * when requested by Auth.
 */
require_once($SRC_DIR.'/core/Session.php');

class SessionRepository {
    private $db;
    private $defaults;

    public function __construct($db, $defaults) {
        $this->db = $db;
        $this->defaults = $defaults;
    }

    private function session_from_array($session_array) {
        if (is_null($session_array)) return null;
        $session = new Session($session_array);
        return ($session->expired()) ? null : $session;
    }

    public function get($id) {
        $sql = 'SELECT * FROM sessions WHERE session_id = :session_id';
        $session_array = $this->db->one($sql, ['session_id' => $id]);
        return $this->session_from_array($session_array);
    }

    public function create_anonymous() {
        $id = bin2hex(random_bytes(32));
        $now = new DateTimeImmutable('now');
        $row = [
            'session_id' => $id,
            'user_uuid' => null,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'expires_at' => $now->add(new DateInterval('P30D'))
                                ->format('Y-m-d H:i:s'),
            'language' => $this->defaults['language'],
            'timezone' => $this->defaults['timezone'],
            'locale' => $this->defaults['locale'],
        ];
        $this->db->insert_row('sessions', $row);
        return $this->get($id);
    }

    public function create_for_user($user) {
        $id = bin2hex(random_bytes(32));
        $now = new DateTimeImmutable('now');
        $row = [
            'session_id' => $id,
            'user_uuid' => $user->uuid(),
            'created_at' => $now->format('Y-m-d H:i:s'),
            'expires_at' => $now->add(new DateInterval('P30D'))
                                ->format('Y-m-d H:i:s'),
            'language' => $user->language(),
            'timezone' => $user->timezone(),
            'locale' => $user->locale(),
        ];
        $this->db->insert_row('sessions', $row);
        return $this->get($id);
    }

    public function refresh_and_get($id) {
        $expires_at = (new DateTimeImmutable('now'))
                        ->add(new DateInterval('P30D'))->format('Y-m-d H:i:s');
        $sql = 'UPDATE sessions SET expires_at = :expires_at
                WHERE session_id = :session_id';
        $this->db->execute($sql, ['session_id' => $id,
                                  'expires_at' => $expires_at]);
        return $this->get($id);
    }

    public function update_session($session) {
        $this->db->update_row('sessions', $session->session_array(), 'session_id');
    }

    public function delete($id) {
        $sql = 'DELETE FROM sessions WHERE session_id = :session_id';
        $this->db->execute($sql, ['session_id' => $id]);
    }

    public function update_session_preferences($id, $array) {
        $this->db->update_fields('sessions', $array, 'session_id', $id);
    }

    public function update_user_preferences($user_uuid, $array) {
        $this->db->update_fields('sessions', $array, 'user_uuid', $user_uuid);
    }
}
?>
