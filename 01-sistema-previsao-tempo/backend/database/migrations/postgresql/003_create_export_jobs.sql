-- Migration: Create export_jobs table for async export processing (PostgreSQL)
-- Requirements: 2.3, 7.6, 7.7

CREATE TYPE export_type_enum AS ENUM ('csv', 'pdf');
CREATE TYPE export_status_enum AS ENUM ('pending', 'processing', 'completed', 'failed');

CREATE TABLE export_jobs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    export_type export_type_enum NOT NULL,
    status export_status_enum DEFAULT 'pending',
    file_path VARCHAR(500),
    parameters JSONB,
    progress INTEGER DEFAULT 0,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_export_jobs_user_id ON export_jobs(user_id);
CREATE INDEX idx_export_jobs_status ON export_jobs(status);
CREATE INDEX idx_export_jobs_created_at ON export_jobs(created_at);