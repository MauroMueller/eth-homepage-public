<?php
$user = App::auth()->user();
$without_login = App::config()->tokens()['types_without_login'];
$token_type = $_SESSION['pending_token_type'] ?? $_GET['token_type'];
if (is_null($user) && !in_array($token_type, $without_login, true)) {
    $_SESSION['pending_token_type'] = $token_type;
    $_SESSION['pending_token'] = $_SESSION['pending_token'] ?? $_GET['token'];
    header('Location: /account/login?return=' . urlencode('/account/verify'));
    exit;
}

$page = [
    'current_page' => 'account/verify',
];

include_once($SRC_DIR.'/include/layout.php');
?>
