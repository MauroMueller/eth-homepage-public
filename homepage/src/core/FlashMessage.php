<?php
/**
 * Message to show to the user. to_array() and from_array() allow this to be
 * stored in the $_SESSION to persist for the next request.
 */
class FlashMessage {
    private $translation_key;
    private $namespace;
    private $data;

    public function __construct($key, $namespace = FlashMessageNamespace::VALIDATION, $data = []) {
        $this->translation_key = $key;
        $this->namespace = $namespace;
        $this->data = $data;
    }

    public function message() {
        $t = App::lang()->{$this->namespace->value}();
        $message = $t[$this->translation_key];
        foreach ($this->data as $key => $value)
            $message = str_replace('{'.$key.'}', $value, $message);
        return $message;
    }

    public function to_array() {
        return [
            'key' => $this->translation_key,
            'namespace' => $this->namespace->value,
            'data' => $this->data,
        ];
    }

    public static function from_array($arr) {
        $namespace = FlashMessageNamespace::tryFrom($arr['namespace']);
        if (is_null($namespace))
            throw new InvalidArgumentException('Invalid translation namespace');
        return new self(
            $arr['key'],
            $namespace,
            $arr['data'],
        );
    }
}

enum FlashMessageNamespace: string {
    case VALIDATION = 'validation_messages';
}
?>
