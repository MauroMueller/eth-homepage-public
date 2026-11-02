<?php
$return = safe_relative_url($_POST['return']);

$result = App::auth()->initiate_password_reset($_POST['identifier']);

if (!$result->no_issues())
    $_SESSION['validation_messages'] = $result->messages();

header('Location: ' . $return);
?>
