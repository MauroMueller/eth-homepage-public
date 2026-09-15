<?php
/**
 * Object to hold validation results.
 * 
 * Validation can return (or add to existing) ValidationResult to tell the
 * application what went wrong (if anything) during validation.
 */
class ValidationResult {
    private $issues;

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

    public function issues() {
        return $this->issues;
    }

    public function messages() {
        $messages = [];
        foreach ($this->issues ?? [] as $issue)
            $messages[] = $issue->message();
        return $messages;
    }
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

    public function message() {
        $t = App::lang()->validation_messages();
        return match ($this->error) {
            ValidationError::REQUIRED =>
                match ($this->field) {
                    ValidationField::USERNAME   => $t['required_username'],
                    ValidationField::EMAIL      => $t['required_email'],
                    ValidationField::PASSWORD   => $t['required_password'],
                    ValidationField::PREFERENCE => $t['required_preference'],
                },
            ValidationError::INVALID =>
                match ($this->field) {
                    ValidationField::EMAIL      => $t['invalid_email'],
                    ValidationField::USER,
                    ValidationField::PASSWORD   => $t['invalid_user_password'],
                    ValidationField::TOKEN      => $t['invalid_token'],
                    ValidationField::USER_TOKEN => $t['invalid_usertoken'],
                    ValidationField::PREFERENCE => $t['invalid_preference'],
                },
            ValidationError::INVALID_CHARACTERS =>
                match ($this->field) {
                    ValidationField::USERNAME   => $t['invalidchars_username'],
                },
            ValidationError::TOO_SHORT =>
                match ($this->field) {
                    ValidationField::USERNAME   => ($t['tooshort_username_1']
                        . $this->data['min'] . $t['tooshort_username_2']),
                    ValidationField::PASSWORD   => ($t['tooshort_password_1']
                        . $this->data['min'] . $t['tooshort_password_2']),
                },
            ValidationError::TOO_LONG =>
                match ($this->field) {
                    ValidationField::USERNAME   => ($t['toolong_username_1']
                        . $this->data['max'] . $t['toolong_username_2']),
                    ValidationField::EMAIL      => ($t['toolong_email_1']
                        . $this->data['max'] . $t['toolong_email_2']),
                    ValidationField::PASSWORD   => ($t['toolong_password_1']
                        . $this->data['max'] . $t['toolong_password_2']),
                },
            ValidationError::RESERVED =>
                match ($this->field) {
                    ValidationField::USERNAME   => $t['reserved_username'],
                },
            ValidationError::TAKEN =>
                match ($this->field) {
                    ValidationField::USERNAME   => $t['taken_username'],
                    ValidationField::EMAIL      => $t['taken_email'],
                },
            ValidationError::DONT_MATCH =>
                match ($this->field) {
                    ValidationField::PASSWORD   => $t['dontmatch_password'],
                },
            ValidationError::NOT_IN_WHITELIST   =>
                match ($this->field) {
                    ValidationField::LANGUAGE   => $t['whitelist_language'],
                    ValidationField::TIMEZONE   => $t['whitelist_timezone'],
                    ValidationField::LOCALE     => $t['whitelist_locale'],
                },
            ValidationError::ALREADY_SIGNED_IN =>
                match ($this->field) {
                    ValidationField::STATE      => $t['signedin_state'],
                },
            ValidationError::NOT_SIGNED_IN =>
                match ($this->field) {
                    ValidationField::STATE      => $t['notsignedin_state'],
                },
            ValidationError::ALREADY_VERIFIED =>
                match ($this->field) {
                    ValidationField::EMAIL      => $t['alreadyverified_email'],
                },
            ValidationError::RECENT_REQUEST =>
                match ($this->field) {
                    ValidationField::EMAIL      => $t['recentrequest_email'],
                    ValidationField::PASSWORD   => $t['recentrequest_pw'],
                },
            ValidationError::MISC =>
                match ($this->field) {
                    ValidationField::DATABASE   => $t['misc_database'],
                },
        };
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
}
?>
