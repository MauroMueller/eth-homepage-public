<?php
/**
 * Toggles between testing and non-testing mode.
 * 
 * Executing this file toggles between testing and non-testing mode. In testing
 * mode, only I have access to the entire homepage, in non-testing mode, the
 * homepage is completely public (at least the root directory, some directories
 * might also still be protected; in particular the 'private' directory).
 * 
 * This is achieved by automatically adding or removing hashes in front of each
 * relevant line in the .htaccess.n file, in order to comment them out or re-
 * enable them.
 */
$b = __DIR__; while (!file_exists($b.'/.root')) $b = dirname($b); $BASE_DIR = $b;
require_once($BASE_DIR.'/config.php');

$lang = "en";
$translations = require_once($SRC_DIR."/lang/{$lang}.php");

$file = $BASE_DIR.'/.htaccess.n';

$block_start = '# BEGIN AUTO-TOGGLE TESTING MODE';
$block_end = '# END AUTO-TOGGLE TESTING MODE';

$lines = file($file, FILE_IGNORE_NEW_LINES);

$inside_block = false;
$commenting = null;

foreach ($lines as $index => $line) {
    if (trim($line) === $block_start) {
        $inside_block = true;
        continue;
    }

    if (trim($line) === $block_end) {
        $inside_block = false;
        continue;
    }

    if ($inside_block) {
        if ($commenting === null) {
            // first relevant line
            $commenting = strpos(trim($line), '#') !== 0;
        }

        $lines[$index] = $commenting ? "#" . $line : ltrim($line, "#");
    }
}

file_put_contents($file, implode("\n", $lines));

$result = null;
if ($commenting) $result = $translations['private']['toggle-testing']['success-public'];
elseif ($commenting === false) $result = $translations['private']['toggle-testing']['success-private'];
else $result = $translations['private']['toggle-testing']['fail'];

echo $result;
?>
