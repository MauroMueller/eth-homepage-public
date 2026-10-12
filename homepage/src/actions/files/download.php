<?php
$array = App::file_service()->download($_POST['file_uuid']);

if ($array['success']) {
    header('Content-Type: ' . $array['mime_type']);
    header('Content-Length: ' . $array['size']);

    $disposition = $_POST['force_download'] == 'true' ? 'attachment' : 'inline';
    $filename = basename($array['name']);
    $filename = preg_replace('/[\r\n"]+/', '', $filename);
    $filename = trim($filename);
    header('Content-Disposition: ' . $disposition . '; ' .
           'filename="' . addcslashes($filename, "\\\"") . '"; ' .
           "filename*=UTF-8''" . rawurlencode($filename));
    
    $file = fopen($array['path'], 'rb');
    while (!feof($file)) {
        echo fread($file, 8192);
        flush();
    }
    fclose($file);
} else {
    echo htmlspecialchars("something went wrong: " . $array['error']);
}

?>
