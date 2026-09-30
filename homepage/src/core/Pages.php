<?php
/**
 * Finds pages from routes to initialize and render them.
 */
class Pages {
    private $pages_dir;
    private $layout_file;

    public function __construct($pages_dir, $layout_file) {
        $this->pages_dir = $pages_dir;
        $this->layout_file = $layout_file;
    }

    public function find($route) {
        $path = rtrim($this->pages_dir . '/' . $route, '/');
        if (!is_dir($path)) return null;
        return new Page($path, $this->layout_file);
    }
}

class Page {
    private $path;
    private $meta;
    private $init_file;
    private $layout_file;

    public function __construct($path, $layout_file) {
        $this->path = $path;
        $this->meta = require($this->path . '/_meta.php');
        if (!isset($this->meta['layout']['path']))
            $this->meta['layout']['path'] = $path;
        $this->init_file = $this->path . '/_init.php';
        $this->layout_file = $layout_file;
    }

    public function initialize() {
        $meta = $this->meta;
        if (file_exists($this->init_file))
            require($this->init_file);
    }

    public function render() {
        $meta = $this->meta;
        $page = $meta['layout'];
        include($this->layout_file);
    }
}
?>
