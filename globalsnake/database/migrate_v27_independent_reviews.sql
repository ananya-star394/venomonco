-- VenomOncoDB v2.7: independent reviewer annotation layer
-- Apply after schema.sql and migrate_v26_benchmark.sql. No records are seeded.
USE venomoncodb;
CREATE TABLE IF NOT EXISTS benchmark_reviews (
 review_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 benchmark_id BIGINT UNSIGNED NOT NULL,
 reviewer_code VARCHAR(120) NOT NULL,
 review_label ENUM('positive','negative','unclear') NOT NULL,
 review_rationale TEXT NOT NULL,
 evidence_location VARCHAR(500) NULL,
 protocol_version VARCHAR(50) NOT NULL DEFAULT 'v2.7',
 reviewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_record_reviewer (benchmark_id, reviewer_code),
 INDEX idx_review_record (benchmark_id),
 INDEX idx_review_label (review_label),
 CONSTRAINT fk_benchmark_review_record FOREIGN KEY (benchmark_id)
   REFERENCES benchmark_records(benchmark_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS benchmark_review_audit (
 audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 review_id BIGINT UNSIGNED NULL,
 benchmark_id BIGINT UNSIGNED NOT NULL,
 reviewer_code VARCHAR(120) NOT NULL,
 action_type ENUM('CREATE','UPDATE') NOT NULL,
 event_payload JSON NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_review_audit_record (benchmark_id),
 INDEX idx_review_audit_time (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
