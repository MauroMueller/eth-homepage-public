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

    public function __construct($lang, $db) {
        $this->lang = $lang;
        $sql = 'SELECT translation_key, value FROM translations WHERE lang = :lang';
        $array = $db->all($sql, ['lang' => $lang]);
        if ($array) {
            $this->error = false;
            $this->translations = array_column($array, 'value', 'translation_key');
        } else {
            $this->error = true;
            $this->translations = [];
        }
    }

    public function lang() {
        return $this->lang;
    }

    private function get($key, $null_on_error = false) {
        if ($this->error)
            return $null_on_error ? null
                : "Error loading language {$this->lang}";
        $value = $this->translations[$key] ?? null;
        if (is_null($value) && !$null_on_error)
            return "No such key exists for language {$this->lang}";
        return $value;
    }

    private function set_nested(&$target, $remaining_parts, $value) {
        $part = array_shift($remaining_parts);
        if (empty($remaining_parts)) {
            $target[$part] = $value;
            return;
        }
        if (!isset($target[$part]) || !is_array($target[$part]))
            $target[$part] = [];
        $this->set_nested($target[$part], $remaining_parts, $value);
    }

    private function get_array($prefix, $null_on_error = false) {
        if ($this->error)
            return $null_on_error ? null
                : ["Error loading language {$this->lang}"];
        $prefix .= '.';
        $result = [];
        foreach ($this->translations as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $remaining = substr($key, strlen($prefix));
                $remaining_parts = explode('.', $remaining);
                $this->set_nested($result, $remaining_parts, $value);
            }
        }
        return $result;
    }

    public function page($page) {
        $page = str_replace('/', '.', $page);
        return $this->get_array('pages.'.$page);
    }

    public function component($component) {
        return $this->get_array('components.'.$component);
    }

    public function breadcrumb_text($key) {
        $key = str_replace('/', '.', $key);
        return $this->get('breadcrumbs.'.$key, true);
    }

    public function sidebar_text($key) {
        $key = str_replace('/', '.', $key);
        return $this->get('sidebar.'.$key, true);
    }

    public function page_overview_text($key) {
        $key = str_replace('/', '.', $key);
        return $this->get('page_overview.'.$key, true);
    }

    public function validation_messages() {
        return $this->get_array('validation_messages');
    }

    public function mail_text($mail) {
        return $this->get_array('mails.'.$mail);
    }
}
?>
