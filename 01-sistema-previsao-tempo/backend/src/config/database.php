<?php
/**
 * Configuração da Base de Dados
 * Suporta MySQL e PostgreSQL
 */

class Database {
    private $connection;
    private $host;
    private $user;
    private $password;
    private $database;
    private $driver;
    private $port;

    public function __construct() {
        // Configuração baseada em variáveis de ambiente
        $this->driver = getenv('DB_DRIVER') ?: 'mysql';
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->port = getenv('DB_PORT') ?: null;
        $this->user = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->database = getenv('DB_NAME') ?: 'weather_system';

        $this->connect();
    }

    private function connect() {
        try {
            if ($this->driver === 'mysql') {
                $dsn = "mysql:host={$this->host};dbname={$this->database}";

                if (!empty($this->port)) {
                    $dsn .= ";port={$this->port}";
                }

                $this->connection = new PDO($dsn, $this->user, $this->password);
            } elseif ($this->driver === 'pgsql') {
                $dsn = "pgsql:host={$this->host};dbname={$this->database};user={$this->user};password={$this->password}";

                if (!empty($this->port)) {
                    $dsn .= ";port={$this->port}";
                }

                $this->connection = new PDO($dsn);
            } elseif ($this->driver === 'sqlite') {
                $dbFile = $this->database;

                // Permite usar DB_NAME como nome simples (ex.: weather_system)
                if (strpos($dbFile, '/') === false) {
                    $dbFile = __DIR__ . '/../../../database/' . $dbFile . '.sqlite';
                }

                $dbDir = dirname($dbFile);

                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0777, true);
                }

                $this->connection = new PDO("sqlite:$dbFile");
            }

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            if ($this->driver === 'sqlite') {
                $this->initializeSqliteSchema();
            }
        } catch (PDOException $e) {
            throw new Exception("Erro de Conexão: " . $e->getMessage());
        }
    }

    private function initializeSqliteSchema() {
        $this->connection->exec('PRAGMA foreign_keys = ON');

        $this->connection->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                name TEXT NOT NULL,
                language TEXT DEFAULT 'pt',
                theme TEXT DEFAULT 'light',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )"
        );

        $this->connection->exec('CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)');
    }

    public function getConnection() {
        return $this->connection;
    }

    public function closeConnection() {
        $this->connection = null;
    }
    
    /**
     * Transaction Support Methods
     * Requirements: 2.6
     */
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    /**
     * Execute a callback within a transaction
     * Automatically commits on success or rolls back on failure
     * 
     * @param callable $callback Function to execute within transaction
     * @return mixed Result of the callback
     * @throws Exception If transaction fails
     */
    public function transaction(callable $callback) {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Execute multiple operations within a transaction
     * 
     * @param array $operations Array of operations to execute
     * @return array Results of all operations
     * @throws Exception If any operation fails
     */
    public function executeInTransaction(array $operations) {
        return $this->transaction(function($db) use ($operations) {
            $results = [];
            
            foreach ($operations as $operation) {
                if (is_callable($operation)) {
                    $results[] = $operation($db);
                } else {
                    throw new Exception("Invalid operation: must be callable");
                }
            }
            
            return $results;
        });
    }
}
?>
