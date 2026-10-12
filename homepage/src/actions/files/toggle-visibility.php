<?php
$return = safe_relative_url($_POST['return']);

App::file_service()->toggle_visibility($_POST['file_uuid']);

header('Location: ' . $return);
?>
