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
 * Gets the user's timezone as a string in parenthesis to append to a formatted
 * timestamp/event, if $show_tz is true and the user's timezone is not in
 * $implicit_tzs.
 */
function get_local_tz_str($show_tz = true, $implicit_tzs = ['Europe/Zurich']) {
    $tz_name = App::preferences()->resolve_timezone_name();
    if ($show_tz && !in_array($tz_name, $implicit_tzs, true)) {
        $language = App::preferences()->resolve_language();
        $tz = new DateTimeZone($tz_name);
        $t = new DateTimeImmutable('now');

        $tz_formatter = new IntlDateFormatter(
            $language,
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            $tz,
            IntlDateFormatter::GREGORIAN,
            'z',
        );
        return ' (' . $tz_formatter->format($t) . ')';
    }
    return '';
}

/**
 * Formats the given timestamp using the user's preferences.
 */
function format_timestamp($t, $date = 'long', $time = 'short',
                          $show_tz = true, $implicit_tzs = ['Europe/Zurich']) {
    $language = App::preferences()->resolve_language();
    $tz_name = App::preferences()->resolve_timezone_name();
    $locale = App::preferences()->resolve_locale();

    $tz = new DateTimeZone($tz_name);
    $t = DateTimeImmutable::createFromInterface($t)->setTimezone($tz);

    $pattern =
        match($date) {
            'none'      => '',
            'short'     => 'dM',
            'medium'    => 'dMy',
            'weekday'   => 'EEEE',
            'long'      => 'EEEEdM',
            'full'      => 'EEEEdMy',
        }
        .
        match($time) {
            'none'      => '',
            'short'     => 'Hm',
            'long'      => 'Hms',
        };
    if ($pattern == '') return '';

    $pattern = (new IntlDatePatternGenerator($locale))->getBestPattern($pattern);
    
    $formatter = new IntlDateFormatter(
        $language,
        IntlDateFormatter::NONE,
        IntlDateFormatter::NONE,
        $tz,
        IntlDateFormatter::GREGORIAN,
        $pattern,
    );

    return $formatter->format($t) . get_local_tz_str($show_tz, $implicit_tzs);
}

/**
 * Checks if two timestamps are on the same in the user's timezone.
 */
function is_same_local_date($a, $b) {
    $tz = App::preferences()->resolve_timezone();

    $a = DateTimeImmutable::createFromInterface($a)->setTimezone($tz);
    $b = DateTimeImmutable::createFromInterface($b)->setTimezone($tz);

    return $a->format('Y-m-d') === $b->format('Y-m-d');
}

/**
 * Formats the given timespan using the user's preferences.
 */
function format_event($start, $end, $date = 'long', $time = 'short',
                      $show_tz = true, $implicit_tzs = ['Europe/Zurich']) {
    $start_str = format_timestamp($start, $date, $time, false, []);
    $end_str = is_same_local_date($start, $end)
        ? format_timestamp($end, 'none', $time, false, [])
        : format_timestamp($end, $date, $time, false, []);

    $tz_str = get_local_tz_str($show_tz, $implicit_tzs);

    return $end_str != ''
        ? $start_str . ' – ' . $end_str . $tz_str
        : $start_str . $tz_str;
}
?>
