<?php
/**
 * Handles application setup & routing.
 * 
 * This file is where every request goes through. It sets up the application
 * with everything necessary, then checks if the requested url exists (this can
 * work independently of the file system) and if the user has permissions to
 * access it, then shows the correct page by way of including it. If something
 * goes wrong, it shows an error page.
 */
$BASE_DIR = __DIR__;
$SRC_DIR = $BASE_DIR . '/src';

date_default_timezone_set('UTC');

session_start();

// Load application code
require_once($SRC_DIR.'/core/helpers.php');
require_once($SRC_DIR.'/core/App.php');
require_once($SRC_DIR.'/core/Config.php');
require_once($SRC_DIR.'/core/AssetRegistry.php');
require_once($SRC_DIR.'/core/DB.php');
require_once($SRC_DIR.'/core/Mailer.php');
require_once($SRC_DIR.'/core/SessionRepository.php');
require_once($SRC_DIR.'/core/UserRepository.php');
require_once($SRC_DIR.'/core/TokenRepository.php');
require_once($SRC_DIR.'/core/Auth.php');
require_once($SRC_DIR.'/core/PasswordHasher.php');
require_once($SRC_DIR.'/core/Preferences.php');
require_once($SRC_DIR.'/core/Authorization.php');
require_once($SRC_DIR.'/core/Lang.php');
require_once($SRC_DIR.'/core/Navigation.php');
require_once($SRC_DIR.'/core/FileService.php');
require_once($SRC_DIR.'/core/normalization/InputNormalizer.php');
require_once($SRC_DIR.'/core/validation/AuthValidator.php');
require_once($SRC_DIR.'/core/validation/PreferencesValidator.php');
require_once($SRC_DIR.'/core/validation/ValidationResult.php');
require_once($SRC_DIR.'/core/UUID.php');

// Initialize application
App::set('config', new Config(require($BASE_DIR . '/config.php')));
App::set('asset_registry', new AssetRegistry());
App::set('db', new DB(App::config()->database()));
App::set('mailer', new Mailer(App::config()->mail()));
App::set('session_repository', new SessionRepository(
    App::db(),
    App::config()->session_defaults(),
));
App::set('user_repository', new UserRepository(
    App::db(),
    App::config()->user_defaults(),
));
App::set('token_repository', new TokenRepository(
    App::db(),
    App::config()->tokens(),
));
App::set('auth', new Auth(
    new InputNormalizer(),
    new AuthValidator(App::config()->reserved_usernames()),
    new PasswordHasher(App::config()->pw_hashing()),
));
App::auth()->initialize();
App::set('preferences', new Preferences(
    new PreferencesValidator(App::config()->supported_preferences()),
    App::config()->session_defaults(),
));
App::set('authorization', new Authorization(
    App::config()->roles(),
    App::config()->route_permissions(),
    App::config()->testing_mode(),
));
App::set('lang', new Lang(
    App::preferences()->resolve_language(),
    $SRC_DIR . '/lang',
));
App::set('breadcrumbs', new Breadcrumbs(
    App::config()->navigation(),
    $SRC_DIR . '/pages',
));
App::set('sidebar', new Sidebar(
    App::config()->navigation(),
    $SRC_DIR . '/pages',
));
App::set('file_service', new FileService(
    App::db(),
    App::config()->file_service(),
));

// Redirect to correct page
$request = substr(urldecode($_SERVER['REQUEST_URI']),
                  strlen(App::config()->base_url()));
[$route, $query] = array_pad(explode('?', $request, 2), 2, '');

$route = trim($route, '/');

if (!preg_match('~^[a-zA-Z0-9_/-]*$~', $route))
    error(400);

if (preg_match('~(?:^|/)_~', $route))
    error(404);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $path = $SRC_DIR . '/pages/' . $route;
        if (is_dir($path)) {
            $route = trim($route . '/_page', '/');
            $path = $SRC_DIR . '/pages/' . $route;
        }

        if (!App::authorization()->authorize_page($route))
            error(403);

        $filetypes = ['', '.php', '.html'];
        break;

    case 'POST':
        $path = $SRC_DIR . '/actions/' . $route;

        if (!App::authorization()->authorize_action($route))
            error(403);
        
        $filetypes = ['', '.php'];
        break;

    default:
        error(405);
}

foreach ($filetypes as $filetype) {
    if (file_exists($path.$filetype)) {
        // Correct file found -> show file
        include($path.$filetype);
        exit;
    }
}

// No correct file found -> show 404
error(404);
?>
