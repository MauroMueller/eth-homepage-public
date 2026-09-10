<?php
$array = App::file_service()->download($_POST['file_uuid']);

if ($array['success']) {
    header('Content-Type: ' . $array['mime_type']);
    header('Content-Length: ' . $array['size']);

    $filename = basename($array['name']);
    $filename = preg_replace('/[\r\n"]+/', '', $filename);
    $filename = trim($filename);
    header('Content-Disposition: attachment; ' .
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
