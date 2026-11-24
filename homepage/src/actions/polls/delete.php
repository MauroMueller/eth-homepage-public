<?php
$return = safe_relative_url($_POST['return']);

App::poll_repository()->delete_poll($_POST['poll_uuid']);

header('Location: ' . $return);
?>
