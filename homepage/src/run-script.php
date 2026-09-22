<?php
// Set up application state
require('bootstrap.php');

// Run script
$script_name = $argv[1] ?? null;
$script = App::scripts()->find($script_name);
if (is_null($script)) {
    echo "Script not found\n";
    exit(1);
}
$script->execute();
exit;
?>
