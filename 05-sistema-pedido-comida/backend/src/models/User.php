<?php
/**
 * Model - Utilizador
 */

class User {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function create($email, $password, $name, $phone, $address) {
        $query = 'INSERT INTO users (email, password, name, phone, address) VALUES (?, ?, ?, ?, ?)';
        $this->database->execute($query, [$email, $password, $name, $phone, $address]);
        return $this->database->lastInsertId();
    }

    public function findByEmail($email) {
        $query = 'SELECT * FROM users WHERE email = ?';
        $result = $this->database->query($query, [$email]);
        return $result ? $result[0] : null;
    }

    public function findById($id) {
        $query = 'SELECT * FROM users WHERE id = ?';
        $result = $this->database->query($query, [$id]);
        return $result ? $result[0] : null;
    }

    public function emailExists($email) {
        $query = 'SELECT COUNT(*) as count FROM users WHERE email = ?';
        $result = $this->database->query($query, [$email]);
        return $result[0]['count'] > 0;
    }

    public function update($id, $name, $phone, $address) {
        $query = 'UPDATE users SET name = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?';
        $this->database->execute($query, [$name, $phone, $address, $id]);
    }
}
?>
