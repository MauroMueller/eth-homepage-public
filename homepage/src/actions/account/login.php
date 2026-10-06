<?php
$return = safe_relative_url($_POST['return']);

$result = App::auth()->attempt($_POST['identifier'], $_POST['password']);

if ($result->no_issues()) {
    header('Location: ' . $return);
} else {
    $_SESSION['validation_messages'] = $result->messages();
    header('Location: /account/login?return=' . urlencode($return));
}
?>
