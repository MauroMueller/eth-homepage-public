<?php
/**
 * Normalizes user input.
 * 
 * Passing user input through normalization ensures only one version of
 * equivalent data can exist.
 */
class InputNormalizer {
    private function trim_unicode($str) {
        return preg_replace('/^\s+|\s+$/u', '', $str);
    }

    public function username($username) {
        if (is_null($username)) return '';
        $username = Normalizer::normalize($username, Normalizer::FORM_C);
        return $this->trim_unicode($username);
    }

    public function email($email) {
        if (is_null($email)) return '';
        $email = mb_strtolower($email, mb_detect_encoding($email));
        return $this->trim_unicode($email);
    }

    public function password($password) {
        if (is_null($password)) return '';
        return $password;
    }
}
?>
