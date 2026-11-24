<?php
/**
 * Handles CSS and JS assets.
 * 
 * Makes sure that no CSS or JS assets are loaded multiple times, using an
 * array-based system of storing them, and putting them into the html all at
 * once (with the css_tags()/js_tags() functions; see layout.php).
 */
class AssetRegistry {
    private $css_assets = [];
    private $js_assets = [];
    private $external_css = [];
    private $external_js = [];
    private $asset_url;
    private $asset_dir;

    public function __construct($asset_url, $asset_dir) {
        $this->asset_url = $asset_url;
        $this->asset_dir = $asset_dir;
    }

    private function add_single_css($path) {
        if ($path === null) return;
        if (!in_array($path, $this->css_assets, true))
            $this->css_assets[] = $path;
    }

    private function add_single_js($path) {
        if ($path === null) return;
        if (!in_array($path, $this->js_assets, true))
            $this->js_assets[] = $path;
    }

    public function add_css($paths) {
        if (is_array($paths)) {
            foreach ($paths as $path) {
                $this->add_single_css($path);
            }
        } else {
            $this->add_single_css($paths);
        }
    }

    public function add_js($paths) {
        if (is_array($paths)) {
            foreach ($paths as $path) {
                $this->add_single_js($path);
            }
        } else {
            $this->add_single_js($paths);
        }
    }

    public function add_external_css($url) {
        if ($url === null) return;
        if (!in_array($url, $this->external_css, true))
            $this->external_css[] = $url;
    }

    public function add_external_js($url) {
        if ($url === null) return;
        if (!in_array($url, $this->external_css, true))
            $this->external_css[] = $url;
    }

    public function css_tags() {
        foreach ($this->external_css as $css) {
            echo "<link rel='stylesheet' href='" . htmlspecialchars($css, ENT_QUOTES) . "'>\n";
        }
        foreach ($this->css_assets as $css) {
            $url = $this->asset_url . '/css/' . $css
                . '?v=' . filemtime($this->asset_dir . '/css/' . $css);
            echo "<link rel='stylesheet' href='" . htmlspecialchars($url, ENT_QUOTES) . "'>\n";
        }
    }

    public function js_tags() {
        foreach ($this->external_js as $js) {
            echo "<script src='" . htmlspecialchars($js, ENT_QUOTES) . "' defer></script>\n";
        }
        foreach ($this->js_assets as $js) {
            $url = $this->asset_url . '/js/' . $js
                . '?v=' . filemtime($this->asset_dir . '/js/' . $js);
            echo "<script src='" . htmlspecialchars($url, ENT_QUOTES) . "' defer></script>\n";
        }
    }
}
?>
