<?php
/**
 * Handles password hashing and verification.
 * 
 * Uses a custom pepper before the php password_hash() to securely hash user
 * passwords. Allows Auth to check if a hash is outdated (no longer matches the
 * parameters in config) and rehash, for both the custom peppering part as well
 * as the password_hash()/password_verify() part.
 */
class PasswordHasher {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    private function pepper_password($pepper_version, $pw) {
        $pepper_algo = $this->config['pepper_algo'];
        $pepper = $this->config['peppers'][$pepper_version];
        $pw_peppered = hash_hmac($pepper_algo, $pw, $pepper);
        return $pw_peppered;
    }

    public function verify($user, $pw) {
        $pepper_version = $user->pepper_version();
        $pw_hash = $user->password_hash();
        $pw_peppered = $this->pepper_password($pepper_version, $pw);
        return password_verify($pw_peppered, $pw_hash);
    }

    public function dummy_verify($pw) {
        // Dummy function taking about the same time as verify() to prevent
        // timing attacks
        $pepper_version = $this->config['current_pepper_version'];
        $dummy_hash = $this->config['dummy_hash'];
        $pw_peppered = $this->pepper_password($pepper_version, $pw);
        return password_verify($pw_peppered, $dummy_hash);
    }

    public function hash($pw) {
        $pepper_version = $this->config['current_pepper_version'];
        $pw_peppered = $this->pepper_password($pepper_version, $pw);
        $pw_algo = $this->config['pw_algo'];
        $pw_options = $this->config['pw_options'];
        $pw_hash = password_hash($pw_peppered, $pw_algo, $pw_options);
        return ['pepper_version' => $pepper_version, 'pw_hash' => $pw_hash];
    }

    public function needs_rehash($user) {
        $user_pepper_version = $user->pepper_version();
        $current_pepper_version = $this->config['current_pepper_version'];
        $pw_hash = $user->password_hash();
        $pw_algo = $this->config['pw_algo'];
        $pw_options = $this->config['pw_options'];
        if ($user_pepper_version != $current_pepper_version) return true;
        if (password_needs_rehash($pw_hash, $pw_algo, $pw_options)) return true;
        return false;
    }
}
?>
