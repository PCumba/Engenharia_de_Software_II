<?php
/**
 * Configuração de Base de Dados com PDO
 * Suporta MySQL e PostgreSQL
 */

class Database {
    private $connection;

    public function __construct() {
        $driver = getenv('DB_DRIVER') ?: 'mysql';
        
        try {
            if ($driver === 'postgresql') {
                $dsn = 'pgsql:host=' . getenv('DB_HOST') . 
                       ';port=' . (getenv('DB_PORT') ?: 5432) . 
                       ';dbname=' . getenv('DB_NAME');
            } else {
                $dsn = 'mysql:host=' . getenv('DB_HOST') . 
                       ';port=' . (getenv('DB_PORT') ?: 3306) . 
                       ';dbname=' . getenv('DB_NAME') . 
                       ';charset=utf8mb4';
            }

            $this->connection = new PDO(
                $dsn,
                getenv('DB_USER'),
                getenv('DB_PASSWORD'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        $this->connection->beginTransaction();
    }

    public function commit() {
        $this->connection->commit();
    }

    public function rollBack() {
        $this->connection->rollBack();
    }
}
?>
