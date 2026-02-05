-- Add social media columns to trainees table if they don't exist
-- Run this migration to enable LinkedIn, GitHub, and Portfolio URL fields

ALTER TABLE trainees 
ADD COLUMN IF NOT EXISTS linkedin_url VARCHAR(255) DEFAULT NULL AFTER bio,
ADD COLUMN IF NOT EXISTS github_url VARCHAR(255) DEFAULT NULL AFTER linkedin_url,
ADD COLUMN IF NOT EXISTS portfolio_url VARCHAR(255) DEFAULT NULL AFTER github_url;
