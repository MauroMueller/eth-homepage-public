<?php
$return = safe_relative_url($_POST['return']);

App::auth()->logout();

header('Location: ' . $return);
?>
