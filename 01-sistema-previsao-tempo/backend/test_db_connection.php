<?php
// Load environment variables from .env file
$envFile = __DIR__ . '/src/config/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0 && strpos($line, ';') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Force SQLite for development
putenv('DB_DRIVER=sqlite');
putenv('DB_NAME=weather_system');
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_NAME'] = 'weather_system';

require_once __DIR__ . '/src/config/database.php';

try {
    echo "Testing database connection...\n";
    $db = new Database();
    echo "Database connection successful!\n";
    
    // Test if password_reset_tokens table exists
    $connection = $db->getConnection();
    
    // For SQLite, use different query
    $stmt = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='password_reset_tokens'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "password_reset_tokens table exists\n";
    } else {
        echo "password_reset_tokens table does not exist - creating it now\n";
        
        // Create password_reset_tokens table
        $connection->exec("
            CREATE TABLE password_reset_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token TEXT UNIQUE NOT NULL,
                expires_at TEXT NOT NULL,
                used_at TEXT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        $connection->exec("CREATE INDEX idx_token ON password_reset_tokens(token)");
        $connection->exec("CREATE INDEX idx_expires_at ON password_reset_tokens(expires_at)");
        $connection->exec("CREATE INDEX idx_user_id ON password_reset_tokens(user_id)");
        
        echo "password_reset_tokens table created\n";
    }
    
    // Check if users table has new columns
    $stmt = $connection->query("PRAGMA table_info(users)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    $requiredColumns = ['failed_login_attempts', 'locked_until', 'email_verified'];
    $missingColumns = array_diff($requiredColumns, $columnNames);
    
    if (empty($missingColumns)) {
        echo "Users table has all required columns\n";
    } else {
        echo "Users table missing columns: " . implode(', ', $missingColumns) . " - adding them now\n";
        
        foreach ($missingColumns as $column) {
            switch ($column) {
                case 'failed_login_attempts':
                    $connection->exec("ALTER TABLE users ADD COLUMN failed_login_attempts INTEGER DEFAULT 0");
                    break;
                case 'locked_until':
                    $connection->exec("ALTER TABLE users ADD COLUMN locked_until TEXT NULL");
                    break;
                case 'email_verified':
                    $connection->exec("ALTER TABLE users ADD COLUMN email_verified INTEGER DEFAULT 0");
                    break;
            }
        }
        
        echo "Missing columns added\n";
    }
    
    // Create activity_logs table if it doesn't exist
    $stmt = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='activity_logs'");
    $result = $stmt->fetch();
    
    if (!$result) {
        echo "Creating activity_logs table\n";
        $connection->exec("
            CREATE TABLE activity_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                action TEXT NOT NULL,
                description TEXT NOT NULL,
                metadata TEXT NULL,
                ip_address TEXT NULL,
                user_agent TEXT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        $connection->exec("CREATE INDEX idx_activity_user_id ON activity_logs(user_id)");
        $connection->exec("CREATE INDEX idx_activity_action ON activity_logs(action)");
        $connection->exec("CREATE INDEX idx_activity_created_at ON activity_logs(created_at)");
        
        echo "activity_logs table created\n";
    }
    
    echo "Database setup complete!\n";
    
} catch (Exception $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
}
?>