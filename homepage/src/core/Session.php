<?php
/**
 * Stores information about the current session.
 */
class Session {
    private $session_array;
    
    public function __construct($session_array) {
        $this->session_array = $session_array;
    }

    public function session_array() {
        return $this->session_array;
    }

    public function id() {
        return $this->session_array['session_id'];
    }

    public function user_uuid() {
        return $this->session_array['user_uuid'];
    }

    public function created_at() {
        return new DateTimeImmutable($this->session_array['created_at']);
    }

    public function expires_at() {
        return new DateTimeImmutable($this->session_array['expires_at']);
    }

    public function expired() {
        return ($this->expires_at() < new DateTimeImmutable('now'));
    }

    public function language() {
        return $this->session_array['language'];
    }

    public function timezone() {
        return $this->session_array['timezone'];
    }

    public function locale() {
        return $this->session_array['locale'];
    }

    public function set_cookie() {
        setcookie(
            "session",
            $this->id(),
            [
                'expires' => $this->expires_at()->getTimestamp(),
                'path' => '/',
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    public function clear_cookie() {
        setcookie(
            "session",
            "",
            [
                'expires' => time() - 60*60,
                'path' => '/',
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Lax',
            ]
        );
    }
}
?>
