-- Allow NULL values for email in trainees and users tables
-- This enables optional email during admin enrollment

-- Update trainees table to allow NULL email
ALTER TABLE trainees MODIFY COLUMN email VARCHAR(255) NULL;

-- Update users table to allow NULL email
ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NULL;

-- Add comment
-- Email is now optional during admin enrollment
-- Trainees without email will be prompted to provide it on first login
