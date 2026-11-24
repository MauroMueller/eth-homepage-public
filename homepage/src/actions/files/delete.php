<?php
$return = safe_relative_url($_POST['return']);

App::file_service()->delete($_POST['file_uuid']);

header('Location: ' . $return);
?>
