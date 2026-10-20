<?php
/**
 * Handles the database.
 * 
 * Provides an interface to the database, allowing for easy query & transaction
 * execution.
 */
class DB {
    private $pdo;

    public function __construct($db_config) {
        $this->pdo = new PDO(
            $db_config['dsn'],
            $db_config['username'],
            $db_config['password'],
            [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC],
        );
        $this->execute("SET time_zone = '+00:00'");
    }

    public function pdo() {
        return $this->pdo;
    }

    private function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function one($sql, $params = []) {
        $row = $this->query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public function all($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function value($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }

    public function execute($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }

    public function insert_row($table, $row) {
        // $table and keys of the $row array must not be user input
        $columns = implode(', ', array_keys($row));
        $placeholders = ':' . implode(', :', array_keys($row));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        return $this->execute($sql, $row);
    }

    public function update_row($table, $row, $keyname) {
        // $table, keys of the $row array and $keyname must not be user input.
        // $row[$keyname] identifies the row of the update, so it must not be
        // modified (or the update might fail or affect a different row)
        $set = implode(', ', array_map(
            fn($col) => "$col = :$col",
            array_keys($row),
        ));
        $sql = "UPDATE $table SET $set WHERE $keyname = :$keyname";
        return $this->execute($sql, $row);
    }

    public function update_fields($table, $fields, $filtername, $filtervalue) {
        // $table, keys of the $fields array and $filtername must not be user
        // input. $filtername may or may not get a new value in $fields.
        $set = implode(', ', array_map(
            fn($col) => "$col = :$col",
            array_keys($fields),
        ));
        $sql = "UPDATE $table SET $set WHERE $filtername = :filter_value";
        var_dump($sql);
        $fields['filter_value'] = $filtervalue;
        return $this->execute($sql, $fields);
    }

    public function last_insert_ID() {
        return $this->pdo->lastInsertID();
    }

    private function transaction_begin() {
        $this->pdo->beginTransaction();
    }

    private function transaction_commit() {
        $this->pdo->commit();
    }

    private function transaction_rollback() {
        if ($this->pdo->inTransaction())
            $this->pdo->rollBack();
    }

    public function transaction($callback) {
        $this->transaction_begin();
        try {
            $result = $callback($this);
            $this->transaction_commit();
            return $result;
        } catch (Throwable $e) {
            $this->transaction_rollback();
            throw $e;
        }
    }
}
?>
