<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Classe para gerenciamento de conexão com banco de dados
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct()
    {
        $this->config = require __DIR__ . '/../config/database.php';
        $this->connect();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void
    {
        try {
            $defaultConnection = $this->config['default'];
            $connectionConfig = $this->config['connections'][$defaultConnection];

            $dsn = $this->buildDsn($connectionConfig);
            
            $this->connection = new PDO(
                $dsn,
                $connectionConfig['username'] ?? null,
                $connectionConfig['password'] ?? null,
                $connectionConfig['options'] ?? []
            );

        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new \Exception("Erro de conexão com o banco de dados");
        }
    }

    private function buildDsn(array $config): string
    {
        switch ($config['driver']) {
            case 'mysql':
                return sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    $config['host'],
                    $config['port'],
                    $config['database'],
                    $config['charset'] ?? 'utf8mb4'
                );

            case 'pgsql':
                return sprintf(
                    "pgsql:host=%s;port=%s;dbname=%s",
                    $config['host'],
                    $config['port'],
                    $config['database']
                );

            case 'sqlite':
                return "sqlite:" . $config['database'];

            default:
                throw new \Exception("Driver de banco não suportado: " . $config['driver']);
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage() . " SQL: " . $sql);
            throw new \Exception("Erro na consulta ao banco de dados");
        }
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        
        return (int) $this->connection->lastInsertId();
    }

    public function update(string $table, array $data, array $where): int
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        
        $whereClause = [];
        foreach (array_keys($where) as $column) {
            $whereClause[] = "{$column} = :where_{$column}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . 
               " WHERE " . implode(' AND ', $whereClause);
        
        // Combinar parâmetros
        $params = $data;
        foreach ($where as $key => $value) {
            $params["where_{$key}"] = $value;
        }
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete(string $table, array $where): int
    {
        $whereClause = [];
        foreach (array_keys($where) as $column) {
            $whereClause[] = "{$column} = :{$column}";
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->query($sql, $where);
        return $stmt->rowCount();
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    public function getLastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
}