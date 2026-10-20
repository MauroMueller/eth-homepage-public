<?php
/**
 * Stores information about the user currently logged in.
 */
class User {
    private $user_array;
    
    public function __construct($user_array) {
        $this->user_array = $user_array;
    }

    public function user_array() {
        return $this->user_array;
    }

    public function uuid() {
        return $this->user_array['user_uuid'];
    }

    public function username() {
        return $this->user_array['username'];
    }

    public function email() {
        return $this->user_array['email'];
    }

    public function pepper_version() {
        return $this->user_array['pepper_version'];
    }

    public function password_hash() {
        return $this->user_array['pw_hash'];
    }

    public function verified() {
        return $this->user_array['verified'];
    }

    public function role() {
        return $this->user_array['role'];
    }

    public function language() {
        return $this->user_array['language'];
    }

    public function timezone() {
        return $this->user_array['timezone'];
    }

    public function locale() {
        return $this->user_array['locale'];
    }

    public function update_password_hash($pepper_version, $pw_hash) {
        $this->user_array['pepper_version'] = $pepper_version;
        $this->user_array['pw_hash'] = $pw_hash;
    }

    public function update_username($username) {
        $this->user_array['username'] = $username;
    }

    public function update_email($email) {
        $this->user_array['email'] = $email;
    }

    public function update_verified($verified) {
        $this->user_array['verified'] = (int) $verified;
    }
}
?>
