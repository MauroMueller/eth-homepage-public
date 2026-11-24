<?php
/**
 * Validates authentication input.
 * 
 * Ensures that authentication parameters meet their requirements. Does not
 * check if username/email are already taken (Auth has to do that itself).
 */
class AuthValidator {
    private $reserved_usernames;

    public function __construct($reserved_usernames) {
        $this->reserved_usernames = $reserved_usernames;
    }

    public function username($username, $result) {
        $field = ValidationField::USERNAME;
        if (!preg_match('/\S/u', $username)) {
            $result->add(ValidationError::REQUIRED, $field);
            return;
        }
        $length = mb_strlen($username);
        if ($length < 3)
            $result->add(ValidationError::TOO_SHORT, $field, ['min' => 3]);
        if ($length > 50)
            $result->add(ValidationError::TOO_LONG, $field, ['max' => 50]);
        if (preg_match('/[\p{C}]/u', $username))
            $result->add(ValidationError::INVALID_CHARACTERS, $field);
        if (str_contains($username, '@'))
            $result->add(ValidationError::INVALID_CHARACTERS, $field);
        if (in_array(mb_strtolower($username),
                     $this->reserved_usernames, true))
            $result->add(ValidationError::RESERVED, $field);
    }

    public function email($email, $result) {
        $field = ValidationField::EMAIL;
        if ($email == '') {
            $result->add(ValidationError::REQUIRED, $field);
            return;
        }
        if (mb_strlen($email) > 254)
            $result->add(ValidationError::TOO_LONG, $field, ['max' => 254]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $result->add(ValidationError::INVALID, $field);
        if (!str_contains($email, '@'))
            // Probably already covered by the rule above
            $result->add(ValidationError::INVALID, $field);
    }

    public function password($password, $result) {
        $field = ValidationField::PASSWORD;
        if ($password == '') {
            $result->add(ValidationError::REQUIRED, $field);
            return;
        }
        $length = strlen($password);
        if ($length < 12)
            $result->add(ValidationError::TOO_SHORT, $field, ['min' => 12]);
        if ($length > 1000)
            $result->add(ValidationError::TOO_LONG, $field, ['max' => 1000]);
    }
}
?>
