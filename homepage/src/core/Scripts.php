<?php
/**
 * Finds scripts based on their name to execute them.
 */
class Scripts {
    private $scripts_dir;

    public function __construct($scripts_dir) {
        $this->scripts_dir = $scripts_dir;
    }

    public function find($script_name) {
        $filepath = $this->scripts_dir . '/' . $script_name . '.php';
        if (!file_exists($filepath)) return null;
        return new Script($filepath);
    }
}

class Script {
    private $filepath;

    public function __construct($filepath) {
        $this->filepath = $filepath;
    }

    public function execute() {
        include($this->filepath);
    }
}
?>
