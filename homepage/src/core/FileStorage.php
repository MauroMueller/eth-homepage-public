<?php
/**
 * Handles files on disk.
 * 
 * Interface for FileService to interact with the files stored on disk.
 */

class FileStorage {
    private $storage_location;

    public function __construct($storage_location) {
        $this->storage_location = $storage_location;
    }

    public function get_path($file_uuid_string) {
        return $this->storage_location . '/' . $file_uuid_string;
    }

    public function put($file_uuid_string, $file) {
        $target = $this->get_path($file_uuid_string);
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $target;
        } else {
            return null;
        }
    }

    public function delete($file_uuid_string) {
        $target = $this->get_path($file_uuid_string);
        if (file_exists($target))
            unlink($target);
    }
}
?>
