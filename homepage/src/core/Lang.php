<?php
/**
 * Handles language and translations.
 * 
 * Provides an easy interface for the language files.
 */
class Lang {
    private $lang;
    private $error;
    private $translations;

    public function __construct($lang, $lang_dir) {
        $this->lang = $lang;
        if (file_exists($lang_dir."/{$lang}.php")) {
            $this->error = false;
            $this->translations = include($lang_dir."/{$lang}.php");
        } else {
            $this->error = true;
            $this->translations = [];
        }
    }

    public function lang() {
        return $this->lang;
    }

    private function get($section, $explode_limit = PHP_INT_MAX,
                         $null_on_error = false) {
        if ($this->error)
            return $null_on_error ? null
                : ["Error loading language {$this->lang}"];
        $parts = explode('/', $section, $explode_limit);
        $value = $this->translations;
        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value))
                return $null_on_error ? null 
                    : ["No such section exists for language {$this->lang}"];
            $value = $value[$part];
        }
        return $value;
    }

    public function page($page) {
        return $this->get('pages/'.$page);
    }

    public function component($component) {
        return $this->get('components/'.$component);
    }

    public function breadcrumb_text($key) {
        return $this->get('breadcrumbs/'.$key, 2, true);
    }

    public function sidebar_text($key) {
        return $this->get('sidebar/'.$key, 2, true);
    }

    public function validation_messages() {
        return $this->get('validation_messages');
    }

    public function mail_text($mail) {
        return $this->get('mails/'.$mail);
    }

    public function text($key) {
        return $this->get($key);
    }
}
?>
