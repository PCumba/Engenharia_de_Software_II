<?php
/**
 * Configuração do Banco de Dados
 * Suporta MySQL e PostgreSQL via PDO
 */

class Database {
    private $driver;
    private $host;
    private $port;
    private $user;
    private $password;
    private $dbname;
    private $connection;

    public function __construct() {
        $this->driver = getenv('DB_DRIVER') ?: 'mysql';
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->port = getenv('DB_PORT') ?: ($this->driver === 'postgresql' ? '5432' : '3306');
        $this->user = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->dbname = getenv('DB_NAME') ?: 'gestao_financeira';

        $this->connect();
    }

    private function connect() {
        try {
            if ($this->driver === 'mysql') {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname}";
            } elseif ($this->driver === 'postgresql') {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
            } else {
                throw new Exception("Suporte para banco de dados '{$this->driver}' não implementado");
            }

            $this->connection = new PDO(
                $dsn,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function getDriver() {
        return $this->driver;
    }
}
?>
