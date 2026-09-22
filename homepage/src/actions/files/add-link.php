<?php
$return = safe_relative_url($_POST['return']);

App::file_service()->add_link(
    $_POST['name'],
    $_POST['url'],
    $_POST['collection_uuid'],
    [
        'public' => isset($_POST['public']),
    ],
);

header('Location: ' . $return);
?>
