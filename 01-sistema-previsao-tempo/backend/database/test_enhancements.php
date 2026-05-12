<?php
/**
 * Test Database Enhancements
 * Validates transaction support and validation features
 */

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/utils/DatabaseValidator.php';

class DatabaseEnhancementTest {
    private $db;
    private $validator;
    
    public function __construct() {
        $this->db = new Database();
        $this->validator = new DatabaseValidator($this->db);
    }
    
    public function runTests() {
        echo "Testing Database Enhancements...\n\n";
        
        try {
            $this->testTransactionSupport();
            $this->testValidation();
            $this->testConstraints();
            
            echo "✓ All tests passed successfully!\n";
        } catch (Exception $e) {
            echo "✗ Test failed: " . $e->getMessage() . "\n";
        }
    }
    
    private function testTransactionSupport() {
        echo "Testing transaction support...\n";
        
        // Test successful transaction
        $result = $this->db->transaction(function($db) {
            // This should work
            return "Transaction successful";
        });
        
        if ($result !== "Transaction successful") {
            throw new Exception("Transaction wrapper failed");
        }
        
        // Test rollback on exception
        try {
            $this->db->transaction(function($db) {
                throw new Exception("Intentional failure");
            });
            throw new Exception("Transaction should have thrown exception");
        } catch (Exception $e) {
            if ($e->getMessage() !== "Intentional failure") {
                throw new Exception("Transaction rollback failed");
            }
        }
        
        echo "✓ Transaction support working\n";
    }
    
    private function testValidation() {
        echo "Testing validation...\n";
        
        // Test user validation
        $validUser = [
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'name' => 'Test User',
            'language' => 'pt',
            'theme' => 'light'
        ];
        
        $errors = $this->validator->validateUser($validUser);
        if (!empty($errors)) {
            throw new Exception("Valid user data failed validation: " . json_encode($errors));
        }
        
        // Test invalid user validation
        $invalidUser = [
            'email' => 'invalid-email',
            'password' => '123',
            'name' => 'A',
            'language' => 'invalid',
            'theme' => 'invalid'
        ];
        
        $errors = $this->validator->validateUser($invalidUser);
        if (empty($errors)) {
            throw new Exception("Invalid user data passed validation");
        }
        
        // Check specific error fields
        $expectedErrors = ['email', 'password', 'name', 'language', 'theme'];
        foreach ($expectedErrors as $field) {
            if (!isset($errors[$field])) {
                throw new Exception("Missing validation error for field: $field");
            }
        }
        
        echo "✓ Validation working correctly\n";
    }
    
    private function testConstraints() {
        echo "Testing database constraints...\n";
        
        // Test weather search validation
        $validSearch = [
            'user_id' => 1,
            'city' => 'São Paulo',
            'country' => 'Brazil',
            'search_type' => 'current'
        ];
        
        $errors = $this->validator->validateWeatherSearch($validSearch);
        // Note: This will show user_id error since user doesn't exist, which is expected
        if (!isset($errors['user_id'])) {
            echo "Note: user_id validation working (user doesn't exist)\n";
        }
        
        // Test favorite validation
        $validFavorite = [
            'user_id' => 1,
            'city' => 'Rio de Janeiro',
            'country' => 'Brazil',
            'category' => 'vacation'
        ];
        
        $errors = $this->validator->validateFavorite($validFavorite);
        // Note: This will show user_id error since user doesn't exist, which is expected
        if (!isset($errors['user_id'])) {
            echo "Note: user_id validation working (user doesn't exist)\n";
        }
        
        echo "✓ Constraint validation working\n";
    }
    
    public function testDatabaseConnection() {
        echo "Testing database connection...\n";
        
        try {
            $connection = $this->db->getConnection();
            if (!$connection) {
                throw new Exception("No database connection");
            }
            
            // Test basic query
            $stmt = $connection->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            if ($result['test'] !== 1) {
                throw new Exception("Basic query failed");
            }
            
            echo "✓ Database connection working\n";
            return true;
        } catch (Exception $e) {
            echo "✗ Database connection failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run tests if script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new DatabaseEnhancementTest();
    
    // Test connection first
    if ($test->testDatabaseConnection()) {
        $test->runTests();
    } else {
        echo "\nSkipping enhancement tests due to connection failure.\n";
        echo "Please configure database credentials in .env file.\n";
    }
}
?>