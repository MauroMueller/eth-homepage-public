<?php
/**
 * Handles routing.
 * 
 * This file expects that the application has been set up with bootstrap.php,
 * then takes over for the routing. It checks if the requested url exists (this
 * can work independently of the file system) and if the user has permissions
 * to access it, then shows the correct page by way of including it. If
 * something goes wrong, it shows an error page.
 */
function handle_page($route) {
    $page = App::pages()->find($route);
    if (is_null($page)) error(404);
    if (!App::authorization()->authorize_page($route)) error(403);
    $page->initialize();
    $page->render();
    exit;
}

function handle_action($route) {
    $action = App::actions()->find($route);
    if (is_null($action)) error(404);
    if (!App::authorization()->authorize_action($route)) error(404);
    $action->execute();
    exit;
}

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
        handle_page($route);
        break;

    case 'POST':
        handle_action($route);
        break;

    default:
        error(405);
}
?>
