<?php
/**
 * Object to hold validation results.
 * 
 * Validation can return (or add to existing) ValidationResult to tell the
 * application what went wrong (if anything) during validation. The success
 * attribute is set on construction, but is only valid as long as there are no
 * issues.
 */
class ValidationResult {
    private $success;
    private $issues;

    public function __construct($action) {
        $this->success = new ValidationSuccess($action);
    }

    private function contains($error, $field) {
        foreach ($this->issues ?? [] as $issue) {
            if ($issue->error === $error && $issue->field === $field)
                return true;
        }
        return false;
    }

    public function add($error, $field, $data = []) {
        if (!$this->contains($error, $field))
            $this->issues[] = new ValidationIssue($error, $field, $data);
    }

    public function no_issues() {
        return empty($this->issues);
    }

    public function messages() {
        $messages = [];
        foreach ($this->issues ?? [] as $issue)
            $messages[] = $issue->message();
        if ($messages == [])
            $messages[] = $this->success->message();
        return $messages;
    }
}

class ValidationSuccess {
    public $action;

    public function __construct($action) {
        $this->action = $action;
    }

    public function flash_message() {
        return match ($this->action) {
            ValidationAction::REGISTER              => new FlashMessage('success_register'),
            ValidationAction::ATTEMPT               => new FlashMessage('success_attempt'),
            ValidationAction::CHANGE_USERNAME       => new FlashMessage('success_change_username'),
            ValidationAction::CHANGE_EMAIL          => new FlashMessage('success_change_email'),
            ValidationAction::CHANGE_PASSWORD       => new FlashMessage('success_change_password'),
            ValidationAction::INITIATE_EMAIL_VERIFY => new FlashMessage('success_initiate_email_verify'),
            ValidationAction::VERIFY_EMAIL          => new FlashMessage('success_verify_email'),
            ValidationAction::INITIATE_PW_RESET     => new FlashMessage('success_initiate_pw_reset'),
            ValidationAction::RESET_PW              => new FlashMessage('success_reset_pw'),
            ValidationAction::PREFERENCES           => new FlashMessage('success_preferences'),
            ValidationAction::FEEDBACK              => new FlashMessage('success_feedback'),
        };
    }

    public function message() {
        return $this->flash_message()->to_array();
    }
}

enum ValidationAction {
    case REGISTER;
    case ATTEMPT;
    case CHANGE_USERNAME;
    case CHANGE_EMAIL;
    case CHANGE_PASSWORD;
    case INITIATE_EMAIL_VERIFY;
    case VERIFY_EMAIL;
    case INITIATE_PW_RESET;
    case RESET_PW;
    case PREFERENCES;
    case FEEDBACK;
}

class ValidationIssue {
    public $error;
    public $field;
    public $data;

    public function __construct($error, $field, $data) {
        $this->error = $error;
        $this->field = $field;
        $this->data = $data;
    }

    public function flash_message() {
        return match ($this->error) {
            ValidationError::REQUIRED =>
                match ($this->field) {
                    ValidationField::USERNAME   => new FlashMessage('required_username'),
                    ValidationField::EMAIL      => new FlashMessage('required_email'),
                    ValidationField::PASSWORD   => new FlashMessage('required_password'),
                    ValidationField::PREFERENCE => new FlashMessage('required_preference'),
                    ValidationField::FEEDBACK   => new FlashMessage('required_feedback'),
                },
            ValidationError::INVALID =>
                match ($this->field) {
                    ValidationField::EMAIL      => new FlashMessage('invalid_email'),
                    ValidationField::USER,
                    ValidationField::PASSWORD   => new FlashMessage('invalid_user_password'),
                    ValidationField::TOKEN      => new FlashMessage('invalid_token'),
                    ValidationField::USER_TOKEN => new FlashMessage('invalid_usertoken'),
                    ValidationField::PREFERENCE => new FlashMessage('invalid_preference'),
                },
            ValidationError::INVALID_CHARACTERS =>
                match ($this->field) {
                    ValidationField::USERNAME   => new FlashMessage('invalidchars_username'),
                },
            ValidationError::TOO_SHORT =>
                match ($this->field) {
                    ValidationField::USERNAME   => new FlashMessage('tooshort_username', data: $this->data),
                    ValidationField::PASSWORD   => new FlashMessage('tooshort_password', data: $this->data),
                },
            ValidationError::TOO_LONG =>
                match ($this->field) {
                    ValidationField::USERNAME   => new FlashMessage('toolong_username', data: $this->data),
                    ValidationField::EMAIL      => new FlashMessage('toolong_email', data: $this->data),
                    ValidationField::PASSWORD   => new FlashMessage('toolong_password', data: $this->data),
                },
            ValidationError::RESERVED =>
                match ($this->field) {
                    ValidationField::USERNAME   => new FlashMessage('reserved_username'),
                },
            ValidationError::TAKEN =>
                match ($this->field) {
                    ValidationField::USERNAME   => new FlashMessage('taken_username'),
                    ValidationField::EMAIL      => new FlashMessage('taken_email'),
                },
            ValidationError::DONT_MATCH =>
                match ($this->field) {
                    ValidationField::PASSWORD   => new FlashMessage('dontmatch_password'),
                },
            ValidationError::NOT_IN_WHITELIST   =>
                match ($this->field) {
                    ValidationField::LANGUAGE   => new FlashMessage('whitelist_language'),
                    ValidationField::TIMEZONE   => new FlashMessage('whitelist_timezone'),
                    ValidationField::LOCALE     => new FlashMessage('whitelist_locale'),
                },
            ValidationError::ALREADY_SIGNED_IN =>
                match ($this->field) {
                    ValidationField::STATE      => new FlashMessage('signedin_state'),
                },
            ValidationError::NOT_SIGNED_IN =>
                match ($this->field) {
                    ValidationField::STATE      => new FlashMessage('notsignedin_state'),
                },
            ValidationError::ALREADY_VERIFIED =>
                match ($this->field) {
                    ValidationField::EMAIL      => new FlashMessage('alreadyverified_email'),
                },
            ValidationError::RECENT_REQUEST =>
                match ($this->field) {
                    ValidationField::EMAIL      => new FlashMessage('recentrequest_email'),
                    ValidationField::PASSWORD   => new FlashMessage('recentrequest_pw'),
                },
            ValidationError::MISC =>
                match ($this->field) {
                    ValidationField::DATABASE   => new FlashMessage('misc_database'),
                },
        };
    }

    public function message() {
        return $this->flash_message()->to_array();
    }
}

enum ValidationError {
    case REQUIRED;
    case INVALID;
    case INVALID_CHARACTERS;
    case TOO_SHORT;
    case TOO_LONG;
    case RESERVED;
    case TAKEN;
    case DONT_MATCH;
    case NOT_IN_WHITELIST;
    case ALREADY_SIGNED_IN;
    case NOT_SIGNED_IN;
    case ALREADY_VERIFIED;
    case RECENT_REQUEST;
    case MISC;
}

enum ValidationField {
    case STATE;
    case USERNAME;
    case EMAIL;
    case USER;
    case PASSWORD;
    case TOKEN;
    case USER_TOKEN;
    case DATABASE;
    case PREFERENCE;
    case LANGUAGE;
    case TIMEZONE;
    case LOCALE;
    case FEEDBACK;
}
?>
