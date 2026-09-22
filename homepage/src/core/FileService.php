<?php
/**
 * Interface for up-/downloading files.
 * 
 * Allows the user to up-/download files. Uses both FileRepository and
 * FileStorage to allow this.
 */
require_once(SRC_DIR.'/core/FileRepository.php');
require_once(SRC_DIR.'/core/FileStorage.php');

class FileService {
    private $config;
    private $repository;
    private $storage;

    public function __construct($db, $config) {
        $this->config = $config;
        $this->repository = new FileRepository($db);
        $this->storage = new FileStorage($config['storage_location']);
    }

    public function add_link($name, $url, $collection_uuid, $options) {
        $collection_uuid = UUID::to_bytes($collection_uuid);

        if (!App::authorization()->has_permission('upload_files'))
            return ['success' => false];

        $uuid = UUID::v4();

        $now = new DateTimeImmutable('now');
        $filelink_array = [
            'file_uuid' => $uuid,
            'name' => $name,
            'mime_type' => 'application/x-file-link',
            'url' => $url,
            'size' => 0,
            'public' => (int) ($options['public'] ?? false),
            'created_at' => $now->format('Y-m-d H:i:s'),
            'modified_at' => $now->format('Y-m-d H:i:s'),
        ];
        $this->repository->insert($filelink_array, $collection_uuid);

        $filelink_array['file_uuid'] = UUID::to_string($uuid);
        return [
            'success' => true,
            'file_link' => $filelink_array,
        ];
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
            'url' => null,
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

    private function modify_visibility_permission($file_uuid) {
        return App::authorization()->has_permission('modify_file_visibility');
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

    public function toggle_visibility($file_uuid_string) {
        $uuid_bytes = UUID::to_bytes($file_uuid_string);

        if (!$this->modify_visibility_permission($uuid_bytes))
            return ['success' => false];

        $this->repository->toggle_visibility($uuid_bytes);
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
    public $url;
    public $size;
    public $public;
    public $modified_at;

    public function __construct($array) {
        $this->file_uuid_string = UUID::to_string($array['file_uuid']);
        $this->name = $array['name'];
        $this->mime_type = $array['mime_type'];
        $this->url = $array['url'];
        $this->size = $array['size'];
        $this->public = (bool) $array['public'];
        $this->modified_at = new DateTimeImmutable(
            $array['modified_at'],
            new DateTimeZone('UTC'),
        );
    }

    public function is_link() {
        return $this->mime_type === 'application/x-file-link';
    }

    public function get_fa_icon() {
        $icons = [
            'application/x-file-link' => 'fa-solid fa-link',

            'application/pdf' => 'fa-regular fa-file-pdf',

            'application/msword' => 'fa-regular fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' =>
                'fa-regular fa-file-word',
            'application/vnd.ms-excel' => 'fa-regular fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' =>
                'fa-regular fa-file-excel',
            'application/vnd.ms-powerpoint' => 'fa-regular fa-file-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' =>
                'fa-regular fa-file-powerpoint',

            'application/zip' => 'fa-regular fa-file-zipper',
            'application/x-rar-compressed' => 'fa-regular fa-file-zipper',
            'application/vnd.rar' => 'fa-regular fa-file-zipper',
            'application/x-7z-compressed' => 'fa-regular fa-file-zipper',
            'application/gzip' => 'fa-regular fa-file-zipper',
            'application/x-tar' => 'fa-regular fa-file-zipper',

            'text/csv' => 'fa-regular fa-file-csv',

            'text/markdown' => 'fa-regular fa-file-code',
            'text/html' => 'fa-regular fa-file-code',
            'text/css' => 'fa-regular fa-file-code',
            'text/javascript' => 'fa-regular fa-file-code',
            'application/javascript' => 'fa-regular fa-file-code',
            'application/json' => 'fa-regular fa-file-code',
            'application/xml' => 'fa-regular fa-file-code',
            'text/xml' => 'fa-regular fa-file-code',
            'text/x-php' => 'fa-regular fa-file-code',
            'text/x-python' => 'fa-regular fa-file-code',
            'text/x-java-source' => 'fa-regular fa-file-code',
            'text/x-c' => 'fa-regular fa-file-code',
            'text/x-c++' => 'fa-regular fa-file-code',
            'text/x-csharp' => 'fa-regular fa-file-code',
            'text/x-shellscript' => 'fa-regular fa-file-code',
            'application/x-sh' => 'fa-regular fa-file-code',
            'text/x-sql' => 'fa-regular fa-file-code',
            'application/sql' => 'fa-regular fa-file-code',
            'text/yaml' => 'fa-regular fa-file-code',
            'application/yaml' => 'fa-regular fa-file-code',
        ];
        if (isset($icons[$this->mime_type])) return $icons[$this->mime_type];

        if (str_starts_with($this->mime_type, 'image/'))
            return 'fa-regular fa-file-image';
        if (str_starts_with($this->mime_type, 'audio/'))
            return 'fa-regular fa-file-audio';
        if (str_starts_with($this->mime_type, 'video/'))
            return 'fa-regular fa-file-video';

        $extension = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));

        $code_extensions = [
            'php', 'php3', 'php4', 'php5', 'phtml',
            'js', 'jsx', 'ts', 'tsx',
            'html', 'htm', 'css', 'scss', 'sass', 'less',
            'py', 'pyw',
            'java', 'kt', 'kts',
            'c', 'h', 'cpp', 'cc', 'cxx', 'hpp',
            'cs',
            'go',
            'rs',
            'swift',
            'rb',
            'pl', 'pm',
            'sh', 'bash', 'zsh', 'fish',
            'sql',
            'json', 'json5',
            'xml', 'xsl', 'xslt',
            'yaml', 'yml',
            'toml',
            'md', 'markdown',
            'vue',
            'svelte',
            'graphql', 'gql',
        ];
        if (in_array($extension, $code_extensions, true))
            return 'fa-regular fa-file-code';

        $text_extensions = [
            'txt', 'log', 'ini', 'conf', 'cfg',
            'env', 'properties',
        ];
        if (in_array($extension, $text_extensions, true))
            return 'fa-regular fa-file-lines';

        if (str_starts_with($this->mime_type, 'text/'))
            return 'fa-regular fa-file-lines';

        return 'fa-regular fa-file';
    }
}
?>
