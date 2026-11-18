-- Apply once to an existing EcoLeaf database before uploading the upgraded PHP files.
-- This migration preserves all customer accounts, orders, services, and reviews.
CREATE TABLE IF NOT EXISTS security_limits (
    bucket_key CHAR(64) NOT NULL PRIMARY KEY,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    window_start BIGINT NOT NULL,
    INDEX security_limits_window (window_start)
) ENGINE=InnoDB;
