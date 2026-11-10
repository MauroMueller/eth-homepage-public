<?php
$return = safe_relative_url($_POST['return']);

App::poll_repository()->toggle_poll_visibility($_POST['poll_uuid']);

header('Location: ' . $return);
?>
