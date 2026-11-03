-- VenomOncoDB v2.6 benchmark persistence migration
-- Run after importing schema.sql into the venomoncodb database.
USE venomoncodb;
CREATE TABLE IF NOT EXISTS benchmark_records (
 benchmark_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 claim_identifier VARCHAR(255) NOT NULL,
 source_identifier VARCHAR(500) NOT NULL,
 evidence_location VARCHAR(500) NULL,
 cancer_model VARCHAR(255) NULL,
 reference_label ENUM('positive','negative','unclear') NOT NULL,
 system_label ENUM('positive','negative','unclear') NOT NULL,
 adjudication_rationale TEXT NOT NULL,
 reviewer_id VARCHAR(120) NOT NULL,
 protocol_version VARCHAR(50) NOT NULL DEFAULT 'v2.6',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_benchmark_claim (claim_identifier), INDEX idx_benchmark_source (source_identifier(191)), INDEX idx_benchmark_labels (reference_label,system_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS benchmark_audit (
 audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 benchmark_id BIGINT UNSIGNED NULL,
 action_type ENUM('CREATE','DELETE','BULK_DELETE') NOT NULL,
 reviewer_id VARCHAR(120) NOT NULL,
 event_payload JSON NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_benchmark_audit_record (benchmark_id), INDEX idx_benchmark_audit_time (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
