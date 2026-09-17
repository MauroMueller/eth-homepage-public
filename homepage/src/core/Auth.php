<?php
/**
 * Manages authentication.
 * 
 * Handles logins and setting everything up so that the application can access
 * information about the currently logged in user, as well as sessions that
 * keep users logged in across requests.
 */
class Auth {
    private $session = null;
    private $user = null;
    
    private $normalizer;
    private $validator;
    private $password_hasher;

    public function __construct($input_normalizer, $auth_validator,
                                $password_hasher) {
        $this->normalizer = $input_normalizer;
        $this->validator = $auth_validator;
        $this->password_hasher = $password_hasher;
    }

    public function session_exists() {
        return !is_null($this->session);
    }

    public function session_if_exists() {
        return $this->session;
    }

    public function session() {
        if (!$this->session_exists()) $this->establish_anonymous_session();
        return $this->session;
    }
    
    public function user() {
        return $this->user;
    }

    public function initialize() {
        if (isset($_COOKIE['session']))
            $this->session = App::session_repository()
                                ->refresh_and_get($_COOKIE['session']);

        if ($this->session_exists()) $this->activate_session();
    }

    private function establish_anonymous_session() {
        $this->session = App::session_repository()->create_anonymous();
        $this->activate_session();
    }

    private function establish_user_session($user) {
        if ($this->session_exists())
            App::session_repository()->delete($this->session->id());

        $this->session = App::session_repository()->create_for_user($user);
        $this->activate_session();
    }

    private function activate_session() {
        $this->user = null;
        $this->session->set_cookie();
        $uuid = $this->session->user_uuid();
        if (!is_null($uuid))
            $this->user = App::user_repository()->get_by_uuid($uuid);
    }

    private function get_user_by_identifier($identifier) {
        $identifier = trim($identifier);
        if (str_contains($identifier, '@')) {
            $email = $this->normalizer->email($identifier);
            return App::user_repository()->get_by_email($email);
        } else {
            $username = $this->normalizer->username($identifier);
            return App::user_repository()->get_by_username($username);
        }
    }

    public function register($username, $email, $pw, $pw_confirm) {
        $validation_result = new ValidationResult();

        if (!is_null($this->user)) {
            $validation_result->add(ValidationError::ALREADY_SIGNED_IN,
                                    ValidationField::STATE);
            return $validation_result;
        }

        $username = $this->normalizer->username($username);
        $email = $this->normalizer->email($email);
        $pw = $this->normalizer->password($pw);
        $pw_confirm = $this->normalizer->password($pw_confirm);

        if ($pw !== $pw_confirm)
            $validation_result->add(ValidationError::DONT_MATCH,
                                    ValidationField::PASSWORD);

        $this->validator->username($username, $validation_result);
        $this->validator->email($email, $validation_result);
        $this->validator->password($pw, $validation_result);
        if (App::user_repository()->username_exists($username))
            $validation_result->add(ValidationError::TAKEN,
                                    ValidationField::USERNAME);
        if (App::user_repository()->email_exists($email))
            $validation_result->add(ValidationError::TAKEN,
                                    ValidationField::EMAIL);

        if (!$validation_result->no_issues()) return $validation_result;

        $hash_result = $this->password_hasher->hash($pw);

        $user = App::user_repository()->create_user([
            'user_uuid' => UUID::v7(),
            'username' => $username,
            'email' => $email,
            'pepper_version' => $hash_result['pepper_version'],
            'pw_hash' => $hash_result['pw_hash'],
            'verified' => null,
            'role' => null,
            'language' => App::preferences()->resolve_language(),
            'timezone' => App::preferences()->resolve_timezone_name(),
            'locale' => App::preferences()->resolve_locale(),
        ]);

        if (is_null($user)) {
            $validation_result->add(ValidationError::MISC,
                                    ValidationField::DATABASE);
            return $validation_result;
        }

        $this->establish_user_session($user);
        $this->initiate_email_verification();
        return $validation_result;
    }

    public function attempt($identifier, $pw) {
        $validation_result = new ValidationResult();

        if (!is_null($this->user)) {
            $validation_result->add(ValidationError::ALREADY_SIGNED_IN,
                                    ValidationField::STATE);
            return $validation_result;
        }
        
        $user = $this->get_user_by_identifier($identifier);
        if (is_null($user)) {
            // Dummy verification to prevent timing attacks
            $this->password_hasher->dummy_verify($pw);
            $validation_result->add(ValidationError::INVALID,
                                    ValidationField::USER);
            return $validation_result;
        }

        $pw = $this->normalizer->password($pw);
        if (!$this->password_hasher->verify($user, $pw)) {
            $validation_result->add(ValidationError::INVALID,
                                    ValidationField::PASSWORD);
            return $validation_result;
        }

        if ($this->password_hasher->needs_rehash($user)) {
            $result = $this->password_hasher->hash($pw);
            $user->update_password_hash(
                $result['pepper_version'],
                $result['pw_hash']
            );
            App::user_repository()->update_user($user);
        }
        
        $this->establish_user_session($user);
        return $validation_result;
    }

    public function logout() {
        if ($this->session_exists()) {
            App::session_repository()->delete($this->session->id());
            $this->session->clear_cookie();
        }

        $this->session = null;
        $this->user = null;
    }

    public function change_username($new_username) {
        $validation_result = new ValidationResult();

        if (is_null($this->user)) {
            $validation_result->add(ValidationError::NOT_SIGNED_IN,
                                    ValidationField::STATE);
            return $validation_result;
        }

        $new_username = $this->normalizer->username($new_username);
        if ($new_username == $this->user()->username()) // No change
            return $validation_result;
        $this->validator->username($new_username, $validation_result);
        if (App::user_repository()->username_exists($new_username))
            $validation_result->add(ValidationError::TAKEN,
                                    ValidationField::USERNAME);

        if (!$validation_result->no_issues()) return $validation_result;

        $this->user()->update_username($new_username);
        App::user_repository()->update_user($this->user());

        return $validation_result;
    }

    public function change_email($new_email) {
        $validation_result = new ValidationResult();

        if (is_null($this->user)) {
            $validation_result->add(ValidationError::NOT_SIGNED_IN,
                                    ValidationField::STATE);
            return $validation_result;
        }

        $new_email = $this->normalizer->email($new_email);
        if ($new_email == $this->user()->email()) // No change
            return $validation_result;
        $this->validator->email($new_email, $validation_result);
        if (App::user_repository()->email_exists($email))
            $validation_result->add(ValidationError::TAKEN,
                                    ValidationField::EMAIL);

        if (!$validation_result->no_issues()) return $validation_result;

        $this->user()->update_verified(false);
        $this->user()->update_email($new_email);
        App::user_repository()->update_user($this->user());

        $this->initiate_email_verification();

        return $validation_result;
    }

    public function change_password($old_pw, $new_pw, $new_pw_confirm) {
        $validation_result = new ValidationResult();

        if (is_null($this->user)) {
            $validation_result->add(ValidationError::NOT_SIGNED_IN,
                                    ValidationField::STATE);
            return $validation_result;
        }

        $old_pw = $this->normalizer->password($old_pw);
        if (!$this->password_hasher->verify($this->user(), $old_pw)) {
            $validation_result->add(ValidationError::INVALID,
                                    ValidationField::PASSWORD);
            return $validation_result;
        }

        $new_pw = $this->normalizer->password($new_pw);
        $new_pw_confirm = $this->normalizer->password($new_pw_confirm);
        if ($new_pw !== $new_pw_confirm)
            $validation_result->add(ValidationError::DONT_MATCH,
                                    ValidationField::PASSWORD);
        $this->validator->password($new_pw, $validation_result);

        if (!$validation_result->no_issues()) return $validation_result;

        $hash_result = $this->password_hasher->hash($new_pw);
        $this->user()->update_password_hash(
            $hash_result['pepper_version'],
            $hash_result['pw_hash'],
        );
        App::user_repository()->update_user($this->user());

        return $validation_result;
    }

    public function recent_email_verification_request() {
        return App::token_repository()->has_recent_token(
            $this->user()->uuid(),
            TokenType::EMAIL_VERIFICATION,
        );
    }

    public function initiate_email_verification() {
        $validation_result = new ValidationResult();

        if (is_null($this->user)) {
            $validation_result->add(ValidationError::NOT_SIGNED_IN,
                                    ValidationField::STATE);
            return $validation_result;
        }

        if ($this->user()->verified()) {
            $validation_result->add(ValidationError::ALREADY_VERIFIED,
                                    ValidationField::EMAIL);
            return $validation_result;
        }

        if ($this->recent_email_verification_request()) {
            $validation_result->add(ValidationError::RECENT_REQUEST,
                                    ValidationField::EMAIL);
            return $validation_result;
        }

        $token = App::token_repository()->create_token(
            $this->user()->uuid(),
            TokenType::EMAIL_VERIFICATION,
        );

        $verification_url = App::config()->absolute_base_url()
                          . '/account/verify'
                          . '?token_type=' . TokenType::EMAIL_VERIFICATION->value
                          . '&token=' . UUID::to_string($token);

        $mail_text = App::lang()->mail_text(
            TokenType::EMAIL_VERIFICATION->value
        );

        App::mailer()->send(
            $this->user()->email(),
            $mail_text['subject'],
            $mail_text['body_1']
            . '<a href="' . $verification_url . '">'
                . $mail_text['link_text']
            . '</a>'
            . $mail_text['body_2'],
        );

        return $validation_result;
    }

    public function verify_email($token_string) {
        $validation_result = new ValidationResult();
        
        if (is_null($this->user)) {
            $validation_result->add(ValidationError::NOT_SIGNED_IN,
                                    ValidationField::STATE);
            return $validation_result;
        }

        $success = App::token_repository()->use_token(
            $this->user()->uuid(),
            TokenType::EMAIL_VERIFICATION,
            UUID::to_bytes($token_string),
        );

        if (!$success) {
            $validation_result->add(ValidationError::INVALID,
                                    ValidationField::TOKEN);
            return $validation_result;
        }

        $this->user()->update_verified(true);
        App::user_repository()->update_user($this->user());

        return $validation_result;
    }

    public function recent_password_reset_request($user) {
        return App::token_repository()->has_recent_token(
            $user->uuid(),
            TokenType::PASSWORD_RESET,
        );
    }

    public function initiate_password_reset($identifier) {
        $validation_result = new ValidationResult();

        if (!is_null($this->user)) {
            $validation_result->add(ValidationError::ALREADY_SIGNED_IN,
                                    ValidationField::STATE);
            return $validation_result;
        }

        $user = $this->get_user_by_identifier($identifier);
        if (is_null($user)) return $validation_result; // No error to user

        if ($this->recent_password_reset_request($user)) {
            $validation_result->add(ValidationError::RECENT_REQUEST,
                                    ValidationField::PASSWORD);
            return $validation_result;
        }

        $token = App::token_repository()->create_token(
            $user->uuid(),
            TokenType::PASSWORD_RESET,
        );

        $verification_url = App::config()->absolute_base_url()
                          . '/account/verify'
                          . '?token_type=' . TokenType::PASSWORD_RESET->value
                          . '&token=' . UUID::to_string($token);

        $mail_text = App::lang()->mail_text(
            TokenType::PASSWORD_RESET->value
        );

        App::mailer()->send(
            $user->email(),
            $mail_text['subject'],
            $mail_text['body_1']
            . '<a href="' . $verification_url . '">'
                . $mail_text['link_text']
            . '</a>'
            . $mail_text['body_2'],
        );

        return $validation_result;
    }

    public function reset_password($token_string, $identifier, $new_pw,
                                   $new_pw_confirm) {
        $validation_result = new ValidationResult();
        
        if (!is_null($this->user)) {
            $validation_result->add(ValidationError::ALREADY_SIGNED_IN,
                                    ValidationField::STATE);
            return $validation_result;
        }

        $user = $this->get_user_by_identifier($identifier);
        if (is_null($user)) {
            $validation_result->add(ValidationError::INVALID,
                                    ValidationField::USER_TOKEN);
            return $validation_result;
        }

        $new_pw = $this->normalizer->password($new_pw);
        $new_pw_confirm = $this->normalizer->password($new_pw_confirm);
        if ($new_pw !== $new_pw_confirm)
            $validation_result->add(ValidationError::DONT_MATCH,
                                    ValidationField::PASSWORD);
        $this->validator->password($new_pw, $validation_result);

        if (!$validation_result->no_issues()) return $validation_result;

        $success = App::token_repository()->use_token(
            $user->uuid(),
            TokenType::PASSWORD_RESET,
            UUID::to_bytes($token_string),
        );

        if (!$success) {
            $validation_result->add(ValidationError::INVALID,
                                    ValidationField::TOKEN);
            return $validation_result;
        }

        $hash_result = $this->password_hasher->hash($new_pw);
        $user->update_password_hash(
            $hash_result['pepper_version'],
            $hash_result['pw_hash'],
        );
        App::user_repository()->update_user($user);

        return $validation_result;
    }
}
?>
