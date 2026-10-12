<?php
function process_poll_selection() {
    $result = App::poll_repository()->update_selection(
        $_POST['poll_uuid'],
        $_POST['clicked_option'] ?? null,
    );
    return $result;
}

$result = process_poll_selection();
header('Content-Type: application/json');
echo json_encode($result);
?>
