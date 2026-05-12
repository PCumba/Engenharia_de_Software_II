# Database Migration System

This directory contains the enhanced database schema and migration system for the Weather System Technical Requirements Compliance project.

## Overview

The migration system supports both MySQL and PostgreSQL databases and provides:
- Incremental schema updates
- Transaction support
- Database validation and constraints
- Cross-database compatibility

## Files Structure

```
database/
├── migrations/                    # MySQL migration files
│   ├── 001_create_password_reset_tokens.sql
│   ├── 002_create_user_sessions.sql
│   ├── 003_create_export_jobs.sql
│   ├── 004_create_weather_alerts.sql
│   ├── 005_enhance_users_table.sql
│   ├── 006_enhance_weather_searches_table.sql
│   ├── 007_enhance_favorites_table.sql
│   ├── 008_enhance_activity_logs_table.sql
│   └── 009_add_database_constraints.sql
├── migrations/postgresql/         # PostgreSQL migration files
│   └── [same files as above, PostgreSQL compatible]
├── migrate.php                    # Migration runner script
├── schema_enhanced.sql            # Complete MySQL schema
├── schema_enhanced_postgresql.sql # Complete PostgreSQL schema
└── README.md                     # This file
```

## Environment Configuration

Set the following environment variables in your `.env` file:

```bash
# Database Configuration
DB_DRIVER=mysql          # or 'pgsql' for PostgreSQL
DB_HOST=localhost
DB_PORT=3306            # 5432 for PostgreSQL
DB_USER=your_username
DB_PASSWORD=your_password
DB_NAME=weather_system
```

## Running Migrations

### Method 1: Using Migration Runner (Recommended)

```bash
# Navigate to backend directory
cd 01-sistema-previsao-tempo/backend

# Run migrations
php database/migrate.php
```

The migration runner will:
1. Create a `migrations` table to track executed migrations
2. Execute only new migrations that haven't been run
3. Skip already executed migrations
4. Provide detailed output of the migration process

### Method 2: Manual Schema Creation

For a fresh installation, you can create the complete schema directly:

**MySQL:**
```bash
mysql -u username -p database_name < database/schema_enhanced.sql
```

**PostgreSQL:**
```bash
psql -U username -d database_name -f database/schema_enhanced_postgresql.sql
```

## New Database Features

### Enhanced Tables

1. **Users Table Enhancements:**
   - `failed_login_attempts` - Track failed login attempts
   - `locked_until` - Account lockout timestamp
   - `email_verified` - Email verification status
   - `email_verification_token` - Email verification token
   - `notification_preferences` - JSON preferences

2. **Weather Searches Enhancements:**
   - `search_type` - Type of search (current, forecast, historical)
   - `coordinates` - Geographic coordinates (JSON)
   - `cached` - Whether result is cached

3. **Favorites Enhancements:**
   - `category` - User-defined category
   - `alerts_enabled` - Whether alerts are enabled
   - `coordinates` - Geographic coordinates (JSON)

4. **Activity Logs Enhancements:**
   - `ip_address` - User's IP address
   - `user_agent` - User's browser/client info
   - `metadata` - Additional metadata (JSON)

### New Tables

1. **password_reset_tokens** - Secure password reset functionality
2. **user_sessions** - Session management
3. **export_jobs** - Async export processing
4. **weather_alerts** - User weather notifications

### Database Constraints

The system includes comprehensive validation constraints:
- Email format validation
- Password complexity requirements
- String length limits
- Enum value validation
- Foreign key integrity
- Check constraints for data validity

## Transaction Support

The enhanced Database class provides transaction support:

```php
// Using transaction wrapper
$db->transaction(function($db) {
    // Your database operations here
    // Automatically commits on success or rolls back on failure
});

// Manual transaction control
$db->beginTransaction();
try {
    // Your operations
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

## Validation

The system includes comprehensive validation through the `DatabaseValidator` class:

```php
$validator = new DatabaseValidator($database);

// Validate user data
$errors = $validator->validateUser($userData);
if (!empty($errors)) {
    // Handle validation errors
}

// Enforce foreign key constraints
$validator->enforceForeignKeyConstraints('users', $userData);
```

## Cross-Database Compatibility

The migration system supports both MySQL and PostgreSQL:
- Separate migration files for each database type
- Database-specific SQL syntax handling
- Automatic driver detection
- Consistent behavior across databases

## Requirements Compliance

This migration system addresses the following technical requirements:

- **2.1-2.5**: Enhanced CRUD operations across all tables
- **2.6**: Database transaction support
- **2.7**: Database validation and constraints
- **2.8**: Cross-database compatibility (MySQL/PostgreSQL)
- **8.1-8.3**: Password reset token management
- **9.1**: Enhanced user profile management
- **10.1-10.3**: Weather alerts and favorites management
- **12.1**: Email notification support

## Troubleshooting

### Common Issues

1. **Access Denied Error:**
   - Check database credentials in `.env` file
   - Ensure database user has proper permissions
   - Verify database server is running

2. **Migration Already Executed:**
   - Migrations are tracked in the `migrations` table
   - To re-run a migration, delete its entry from the table
   - Or use a fresh database

3. **Constraint Violations:**
   - Check existing data compatibility with new constraints
   - Clean up invalid data before running migrations
   - Review constraint definitions in migration files

### Manual Cleanup

To reset migrations (use with caution):

```sql
-- Drop migrations table to re-run all migrations
DROP TABLE IF EXISTS migrations;
```

## Testing

Test the migration system:

```bash
# Test with MySQL
DB_DRIVER=mysql php database/migrate.php

# Test with PostgreSQL
DB_DRIVER=pgsql php database/migrate.php
```

## Next Steps

After running migrations:
1. Update your models to use the new table columns
2. Implement the enhanced authentication features
3. Add transaction support to your business logic
4. Test the validation constraints
5. Implement the new weather alerts functionality