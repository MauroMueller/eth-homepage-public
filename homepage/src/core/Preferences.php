<?php
/**
 * Resolves user preferences.
 * 
 * Resolves settings such as language, timezone and locale from current session
 * and/or user. Interface for updating such preferences.
 */
class Preferences {
    private $validator;
    private $session_defaults;

    public function __construct($preferences_validator, $session_defaults) {
        $this->validator = $preferences_validator;
        $this->session_defaults = $session_defaults;
    }

    private function resolve($property) {
        $session = App::auth()->session_if_exists();
        if (is_null($session)) return $this->session_defaults[$property];
        else return $session->$property();
    }

    public function resolve_language() {
        return $this->resolve('language');
    }

    public function resolve_timezone_name() {
        return $this->resolve('timezone');
    }

    public function resolve_timezone() {
        return new DateTimeZone($this->resolve_timezone_name());
    }

    public function resolve_locale() {
        return $this->resolve('locale');
    }

    public function update($array, $session_only) {
        $result = $this->validator->validate_array($array);
        if (!$result->no_issues()) return $result;

        if ($session_only || is_null($user = App::auth()->user())) {
            $id = App::auth()->session()->id();
            App::session_repository()->update_session_preferences($id, $array);
        } else {
            $id = $user->uuid();
            App::user_repository()->update_user_preferences($id, $array);
            App::session_repository()->update_user_preferences($id, $array);
        }
        return $result;
    }
}
?>
