<?php
/**
 * Configuração da Base de Dados - Monitoramento de Criptomoedas
 */

class Database {
    private $connection;
    private $host;
    private $user;
    private $password;
    private $database;
    private $driver;

    public function __construct() {
        $this->driver = getenv('DB_DRIVER') ?: 'mysql';
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->database = getenv('DB_NAME') ?: 'crypto_monitor';

        $this->connect();
    }

    private function connect() {
        try {
            if ($this->driver === 'mysql') {
                $dsn = "mysql:host={$this->host};dbname={$this->database}";
                $this->connection = new PDO($dsn, $this->user, $this->password);
            } elseif ($this->driver === 'pgsql') {
                $dsn = "pgsql:host={$this->host};dbname={$this->database};user={$this->user};password={$this->password}";
                $this->connection = new PDO($dsn);
            }

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erro de Conexão: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function closeConnection() {
        $this->connection = null;
    }
}
?>
