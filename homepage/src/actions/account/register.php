<?php
$return = safe_relative_url($_POST['return']);

$result = App::auth()->register(
    $_POST['username'], $_POST['email'],
    $_POST['password'], $_POST['password_confirm']
);

if ($result->no_issues()) {
    header('Location: ' . $return);
} else {
    $_SESSION['validation_messages'] = $result->messages();
    header('Location: /account/register?return=' . urlencode($return));
}
?>
