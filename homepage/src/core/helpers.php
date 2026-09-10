<?php
/**
 * Provides some simple, globally useful helpers.
 */

/**
 * Displays an HTTP error page and stops execution.
 */
function error($no) {
    global $SRC_DIR;
    $path = $SRC_DIR . '/errors/' . $no;
    $filetypes = ['.php', '.html'];

    http_response_code($no);
    foreach ($filetypes as $filetype) {
        if (file_exists($path.$filetype)) {
            include($path.$filetype);
            exit;
        }
    }
    echo htmlspecialchars('Error ' . $no);
    exit;
}

/**
 * Makes sure the URL passed in is relative. Returns the default otherwise.
 */
function safe_relative_url($url, $default = null) {
    if (is_null($default)) $default = App::config()->base_url() . '/';

    if (is_null($url)) return $default;
    if ($url == '') return $default;
    if (!str_starts_with($url, '/')) return $default;
    if (str_starts_with($url, '//')) return $default;
    return $url;
}

/**
 * Gets items in a directory on disk.
 */
function get_dir_items($dir, $ignore_prefixes = ['.', '_'],
                       $drop_extensions = false, $full_paths = false) {
    $items = scandir($dir);
    $items = array_filter($items, function ($v) use ($ignore_prefixes) {
        foreach ($ignore_prefixes as $prefix)
            if (str_starts_with($v, $prefix)) return false;
        return true;
    });
    if ($drop_extensions) {
        $items = array_map(function ($v) use ($dir) {
            $path = $dir . '/' . $v;
            return (is_file($path)) ? pathinfo($path, PATHINFO_FILENAME) : $v;
        }, $items);
    }
    if ($full_paths) {
        $items = array_map(function ($v) use ($dir) {
            return $dir . '/' . $v;
        }, $items);
    }
    return array_values($items);
}
?>
