<?php
function process_upload() {
    if (!isset($_FILES['file'])) {
        return [
            'success' => false,
            'error' => 'No file found. Maybe it was too large (above 20MB)?',
        ];
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'error' => 'PHP file error: ' . $_FILES['file']['error'],
        ];
    }
    
    $result = App::file_service()->upload(
        $_FILES['file'],
        $_POST['collection_uuid'],
        [
            'public' => isset($_POST['public']),
        ],
    );
    return $result;
}

$result = process_upload();
header('Content-Type: application/json');
echo json_encode($result);
?>
