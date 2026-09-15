<?php
/**
 * Provides some simple, globally useful helpers.
 */

/**
 * Displays an HTTP error page and stops execution.
 */
function error($no) {
    $path = SRC_DIR . '/errors/' . $no;
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

/**
 * Formats the given timespan using the user's preferences.
 */
function format_event($start, $end, $show_weekday = true, $show_date = true) {
    $language = App::preferences()->resolve_language();
    $tz_name = App::preferences()->resolve_timezone_name();
    $locale = App::preferences()->resolve_locale();

    $tz = new DateTimeZone($tz_name);
    $start = $start->setTimezone($tz);
    $end = $end->setTimezone($tz);

    $pattern = match (true) {
        $show_weekday && $show_date => 'EEEE d.M.',
        $show_weekday               => 'EEEE',
        $show_date                  => 'd.M.',
        default                     => '',
    };

    $pattern = $pattern
        ? (new IntlDatePatternGenerator($locale))->getBestPattern($pattern)
        : '';

    $formatter = $pattern
        ? new IntlDateFormatter(
            $language,
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            $tz,
            IntlDateFormatter::GREGORIAN,
            $pattern,
        )
        : null;

    $start_label = '';
    if ($show_weekday) $start_label .= $start->format('d') . ' ';
    if ($show_date) $start_label .= $start->format('j.n.') . ' ';

    $end_label = '';
    if ($start->format('Y-m-d') !== $end->format('Y-m-d')) {
        if ($show_weekday) $end_label .= $end->format('d') . ' ';
        if ($show_date) $end_label .= $end->format('j.n.') . ' ';
    }

    $start_label = $formatter ? $formatter->format($start) . ' ' : '';
    
    $end_label = '';
    if ($start->format('Y-m-d') !== $end->format('Y-m-d')) {
        $end_label = $formatter ? $formatter->format($end) . ' ' : '';
    }

    $start_time = $start->format('H:i');
    $end_time = $end->format('H:i');

    $tz_label = ($tz_name !== 'Europe/Zurich') ? " ($tz_name)" : '';

    return $start_label.$start_time.' – '.$end_label.$end_time.$tz_label;
}
?>
