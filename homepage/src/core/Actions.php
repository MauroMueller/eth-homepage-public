<?php
/**
 * Finds actions from routes to execute them.
 */
class Actions {
    private $actions_dir;

    public function __construct($actions_dir) {
        $this->actions_dir = $actions_dir;
    }

    public function find($route) {
        $filepath = $this->actions_dir . '/' . $route . '.php';
        if (!file_exists($filepath)) return null;
        return new Action($filepath);
    }
}

class Action {
    private $filepath;

    public function __construct($filepath) {
        $this->filepath = $filepath;
    }

    public function execute() {
        include($this->filepath);
    }
}
?>
