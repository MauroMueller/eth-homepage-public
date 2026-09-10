<?php
/**
 * Interface for up-/downloading files.
 * 
 * Allows the user to up-/download files. Uses both FileRepository and
 * FileStorage to allow this.
 */
require_once($SRC_DIR.'/core/FileRepository.php');
require_once($SRC_DIR.'/core/FileStorage.php');

class FileService {
    private $config;
    private $repository;
    private $storage;

    public function __construct($db, $config) {
        $this->config = $config;
        $this->repository = new FileRepository($db);
        $this->storage = new FileStorage($config['storage_location']);
    }

    public function upload($file, $collection_uuid, $options) {
        $collection_uuid = UUID::to_bytes($collection_uuid);

        if (!App::authorization()->has_permission('upload_files'))
            return ['success' => false];

        if (!is_uploaded_file($file['tmp_name'])) return ['success' => false];

        $uuid = UUID::v4();

        $target_path = $this->storage->put(UUID::to_string($uuid), $file);
        if (is_null($target_path)) return ['success' => false];

        $now = new DateTimeImmutable('now');
        $file_array = [
            'file_uuid' => $uuid,
            'name' => basename($file['name']),
            'mime_type' => mime_content_type($target_path),
            'size' => $file['size'],
            'public' => (int) ($options['public'] ?? false),
            'created_at' => $now->format('Y-m-d H:i:s'),
            'modified_at' => $now->format('Y-m-d H:i:s'),
        ];
        $this->repository->insert($file_array, $collection_uuid);

        $file_array['file_uuid'] = UUID::to_string($uuid);
        return [
            'success' => true,
            'file' => $file_array,
        ];
    }

    private function download_permission($file_uuid, $public = null) {
        if (App::authorization()->has_permission('download_private_files'))
            return true;

        if (is_null($public))
            $public = $this->repository->is_public($file_uuid);

        if (App::authorization()->has_permission('download_public_files')
            && $public)
            return true;

        return false;
    }

    private function delete_permission($file_uuid) {
        return App::authorization()->has_permission('delete_files');
    }

    public function download($file_uuid_string) {
        $uuid_bytes = UUID::to_bytes($file_uuid_string);

        if (!$this->download_permission($uuid_bytes))
            return ['success' => false, 'error' => 'permssions'];

        if (!$this->repository->exists($uuid_bytes))
            return ['success' => false, 'error' => 'inexistent'];
        
        $file_array = $this->repository->get($uuid_bytes);

        $path = $this->storage->get_path($file_uuid_string);

        return [
            'success' => true,
            'path' => $path,
            'name' => $file_array['name'],
            'mime_type' => $file_array['mime_type'],
            'size' => $file_array['size'],
        ];
    }

    public function delete($file_uuid_string) {
        $uuid_bytes = UUID::to_bytes($file_uuid_string);

        if (!$this->delete_permission($uuid_bytes))
            return ['success' => false];

        $this->repository->delete($uuid_bytes);
        $this->storage->delete($file_uuid_string);
    }

    public function collection_files($collection_uuid) {
        $uuid_bytes = UUID::to_bytes($collection_uuid);

        $collection = $this->repository->get_collection($uuid_bytes);
        
        $result = [];
        foreach ($collection as $e) {
            if (!$this->download_permission($e['file_uuid'], $e['public']))
                continue;
            $result[] = new FileElement($e);
        }
        return $result;
    }
}

class FileElement {
    public $file_uuid_string;
    public $name;
    public $mime_type;
    public $size;
    public $public;

    public function __construct($array) {
        $this->file_uuid_string = UUID::to_string($array['file_uuid']);
        $this->name = $array['name'];
        $this->mime_type = $array['mime_type'];
        $this->size = $array['size'];
        $this->public = (bool) $array['public'];
    }
}
?>
