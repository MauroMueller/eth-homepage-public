<?php
$return = safe_relative_url($_POST['return']);

switch ($_POST['section']) {
    case 'preferences':
        $array = array_filter([
            'language' => $_POST['language'] ?? null,
            'timezone' => $_POST['timezone'] ?? null,
            'locale' => $_POST['locale'] ?? null,
        ], fn($value) => $value !== null);
        $session_only = $_POST['scope'] == 'session';

        $result = App::preferences()->update($array, $session_only);
        break;
    
    case 'account':
        switch ($_POST['confirm']) {
            case 'username':
                $result = App::auth()->change_username($_POST['username']);
                break;
            
            case 'email':
                $result = App::auth()->change_email($_POST['email']);
                break;
            
            case 'email_verification':
                $result = App::auth()->initiate_email_verification();
                break;
            
            case 'password':
                $result = App::auth()->change_password(
                    $_POST['old_password'],
                    $_POST['password'],
                    $_POST['password_confirm'],
                );
                break;
            
            default:
                break;
        }
        break;
    
    default:
        break;
}

if (!$result->no_issues())
    $_SESSION['validation_messages'] = $result->messages();

header('Location: ' . $return);
?>
