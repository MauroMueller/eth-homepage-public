<?php
/**
 * Handles file entries in the database.
 * 
 * Interface for FileService to interact with the file database.
 */

class FileRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function insert($file_data, $collection_uuid) {
        $this->db->insert_row('files', $file_data);

        $sql = 'INSERT INTO collection_files (collection_uuid, file_uuid, pos)
                VALUES (:collection_uuid, :file_uuid, (
                    SELECT COALESCE(MAX(pos), -1) + 1
                    FROM collection_files
                    WHERE collection_uuid = :collection_uuid
                ))';
        $this->db->execute($sql, [
            'collection_uuid' => $collection_uuid,
            'file_uuid' => $file_data['file_uuid'],
        ]);
    }

    public function exists($file_uuid) {
        $sql = 'SELECT COUNT(*) FROM files WHERE file_uuid = :file_uuid';
        return (bool) $this->db->value($sql, ['file_uuid' => $file_uuid]);
    }

    public function is_public($file_uuid) {
        if (!$this->exists($file_uuid)) return false;
        $sql = 'SELECT public FROM files WHERE file_uuid = :file_uuid';
        return (bool) $this->db->value($sql, ['file_uuid' => $file_uuid]);
    }

    public function get($file_uuid) {
        $sql = 'SELECT * FROM files WHERE file_uuid = :file_uuid';
        return $this->db->one($sql, ['file_uuid' => $file_uuid]);
    }

    public function delete($file_uuid) {
        $sql = 'DELETE FROM collection_files WHERE file_uuid = :file_uuid;
                DELETE FROM files            WHERE file_uuid = :file_uuid';
        $this->db->execute($sql, ['file_uuid' => $file_uuid]);
    }

    public function get_collection($collection_uuid) {
        $sql = 'SELECT * FROM collection_files cf, files f
                WHERE cf.collection_uuid = :collection_uuid
                  AND cf.file_uuid = f.file_uuid
                ORDER BY pos';
        return $this->db->all($sql, ['collection_uuid' => $collection_uuid]);
    }
}
?>
