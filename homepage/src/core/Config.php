<?php
/**
 * Stores global configuration.
 * 
 * The actual configuration is inputted in config.php at the root level, this
 * is just a class that stores it.
 */
class Config {
    private $config_arr;

    public function __construct($config_arr) {
        $this->config_arr = $config_arr;
    }

    public function base_url() {
        return $this->config_arr['base_url'];
    }

    public function absolute_base_url() {
        return $this->config_arr['absolute_base_url'];
    }

    public function database() {
        return $this->config_arr['database'];
    }

    public function mail() {
        return $this->config_arr['mail'];
    }

    public function pw_hashing() {
        return $this->config_arr['pw_hashing'];
    }

    public function tokens() {
        return $this->config_arr['tokens'];
    }

    public function supported_preferences() {
        return $this->config_arr['supported_preferences'];
    }

    public function session_defaults() {
        return $this->config_arr['session_defaults'];
    }

    public function user_defaults() {
        return $this->config_arr['user_defaults'];
    }

    public function reserved_usernames() {
        return $this->config_arr['reserved_usernames'];
    }

    public function roles() {
        return $this->config_arr['roles'];
    }

    public function route_permissions() {
        return $this->config_arr['route_permissions'];
    }

    public function testing_mode() {
        return $this->config_arr['testing_mode'];
    }

    public function navigation() {
        return $this->config_arr['navigation'];
    }

    public function user_menu() {
        return $this->config_arr['user_menu'];
    }

    public function file_service() {
        return $this->config_arr['file_service'];
    }

    public function icons() {
        return $this->config_arr['icons'];
    }
}
?>
