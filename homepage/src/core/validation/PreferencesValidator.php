<?php
/**
 * Validates user preferences.
 * 
 * Makes sure user preferences to be set match the requirements (checks if they
 * are in their respective whitelist).
 */
class PreferencesValidator {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    private function validate($field, $property_str, $value, &$result) {
        if (!in_array($value, $this->config[$property_str], true))
            $result->add(ValidationError::NOT_IN_WHITELIST, $field);
    }

    public function validate_array($array) {
        $result = new ValidationResult();

        if (empty($array))
            $result->add(ValidationError::REQUIRED, ValidationField::PREFERENCE);

        $fields = [
            'language' => ValidationField::LANGUAGE,
            'timezone' => ValidationField::TIMEZONE,
            'locale'   => ValidationField::LOCALE,
        ];
        foreach ($array as $property => $value) {
            if (!isset($fields[$property])) {
                $result->add(
                    ValidationError::INVALID,
                    ValidationField::PREFERENCE,
                );
                continue;
            }
            $this->validate($fields[$property], $property, $value, $result);
        }
        return $result;
    }
}
?>
