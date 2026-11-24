<?php
switch ($_POST['token_type']) {
    case 'email_verification':
        $result = App::auth()->verify_email($_POST['token']);
        break;
    
    case 'password_reset':
        $result = App::auth()->reset_password(
            $_POST['token'], $_POST['identifier'],
            $_POST['password'], $_POST['password_confirm'],
        );
        break;
}

$_SESSION['validation_messages'] = $result->messages();
if ($result->no_issues()) {
    header('Location: /account');
} else {
    $_SESSION['pending_token_type'] = $_POST['token_type'];
    $_SESSION['pending_token'] = $_POST['token'];
    header('Location: /account/verify');
}
?>
