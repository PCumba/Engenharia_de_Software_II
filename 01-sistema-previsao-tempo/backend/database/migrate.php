<?php
/**
 * Database Migration Runner
 * Executes migration scripts for MySQL and PostgreSQL
 */

require_once __DIR__ . '/../src/config/database.php';

class MigrationRunner {
    private $db;
    private $driver;
    
    public function __construct() {
        $this->db = new Database();
        $this->driver = getenv('DB_DRIVER') ?: 'mysql';
    }
    
    public function run() {
        echo "Starting database migrations for {$this->driver}...\n";
        
        // Create migrations table if it doesn't exist
        $this->createMigrationsTable();
        
        // Get list of executed migrations
        $executedMigrations = $this->getExecutedMigrations();
        
        // Get migration files
        $migrationFiles = $this->getMigrationFiles();
        
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.sql');
            
            if (in_array($migrationName, $executedMigrations)) {
                echo "Skipping migration: {$migrationName} (already executed)\n";
                continue;
            }
            
            echo "Executing migration: {$migrationName}\n";
            
            try {
                $sql = file_get_contents($file);
                $this->executeMigration($sql);
                $this->recordMigration($migrationName);
                echo "✓ Migration {$migrationName} executed successfully\n";
            } catch (Exception $e) {
                echo "✗ Migration {$migrationName} failed: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
        
        echo "All migrations completed successfully!\n";
    }
    
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT PRIMARY KEY AUTO_INCREMENT,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($this->driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        
        $this->db->getConnection()->exec($sql);
    }
    
    private function getExecutedMigrations() {
        try {
            $stmt = $this->db->getConnection()->query("SELECT migration FROM migrations");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getMigrationFiles() {
        $migrationDir = __DIR__ . '/migrations';
        
        if ($this->driver === 'pgsql') {
            $migrationDir .= '/postgresql';
        }
        
        $files = glob($migrationDir . '/*.sql');
        sort($files);
        
        return $files;
    }
    
    private function executeMigration($sql) {
        // Split SQL by semicolons and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $this->db->getConnection()->exec($statement);
            }
        }
    }
    
    private function recordMigration($migrationName) {
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO migrations (migration) VALUES (?)"
        );
        $stmt->execute([$migrationName]);
    }
}

// Run migrations if script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    try {
        $runner = new MigrationRunner();
        $runner->run();
    } catch (Exception $e) {
        echo "Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>