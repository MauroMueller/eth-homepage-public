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

/**
 * Some color helpers that can:
 *  - convert a color in binary (stored in database) to a color string that can
 *    be used in HTML/CSS (color_str_from_bin)
 *  - compute whether black or white would have the better contrast to a given
 *    rgb color (text_color)
 *  - adjust a color's lightness to simplify creating small palettes from
 *    single colors (adjust_color)
 */
function color_str_from_bin($bin) {
    return is_null($bin) ? null : '#' . bin2hex($bin);
}

function text_color($rgb) {
    $brightness = 0.299 * ord($rgb[0])
                + 0.587 * ord($rgb[1])
                + 0.114 * ord($rgb[2]);
    return ($brightness >= 128) ? "\x00\x00\x00" : "\xFF\xFF\xFF";
}

function adjust_color($rgb, $amount) {
    [$h, $s, $l] = rgb_to_hsl($rgb);
    $l = max(0.0, min(1.0, $l + $amount / 100));
    return hsl_to_rgb($h, $s, $l);
}

function rgb_to_hsl($rgb) {
    $r = ord($rgb[0]) / 255;
    $g = ord($rgb[1]) / 255;
    $b = ord($rgb[2]) / 255;

    $max = max($r, $g, $b);
    $min = min($r, $g, $b);

    $l = ($max + $min) / 2;

    if ($max === $min)
        return [0.0, 0.0, $l];

    $d = $max - $min;
    $s = $l > 0.5
        ? $d / (2 - $max - $min)
        : $d / ($max + $min);

    if ($max === $r) {
        $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
    } else if ($max === $g) {
        $h = ($b - $r) / $d + 2;
    } else {
        $h = ($r - $g) / $d + 4;
    }

    return [$h / 6, $s, $l];
}

function hsl_to_rgb($h, $s, $l) {
    if ($s === 0.0) {
        $r = $g = $b = $l;
    } else {
        $q = $l < 0.5
            ? $l * (1 + $s)
            : $l + $s - $l * $s;

        $p = 2 * $l - $q;

        $r = hue_to_rgb($p, $q, $h + 1 / 3);
        $g = hue_to_rgb($p, $q, $h);
        $b = hue_to_rgb($p, $q, $h - 1 / 3);
    }

    return chr((int) round($r * 255))
         . chr((int) round($g * 255))
         . chr((int) round($b * 255));
}

function hue_to_rgb($p, $q, $t) {
    if ($t < 0) $t += 1;

    if ($t > 1) $t -= 1;

    if ($t < 1 / 6)
        return $p + ($q - $p) * 6 * $t;

    if ($t < 1 / 2)
        return $q;

    if ($t < 2 / 3)
        return $p + ($q - $p) * (2 / 3 - $t) * 6;

    return $p;
}
?>
