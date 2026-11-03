USE venomoncodb;
CREATE TABLE IF NOT EXISTS toxin_catalog (
 toxin_id INT AUTO_INCREMENT PRIMARY KEY,
 accession VARCHAR(30) NOT NULL UNIQUE,
 toxin_name VARCHAR(500) NOT NULL,
 organism_name VARCHAR(255),
 taxonomy_id INT,
 sequence LONGTEXT,
 sequence_length INT,
 entry_review_status VARCHAR(80),
 function_text TEXT,
 tissue_specificity TEXT,
 source_url VARCHAR(500) NOT NULL,
 source_database VARCHAR(80) NOT NULL DEFAULT 'UniProtKB/Tox-Prot',
 source_release VARCHAR(80),
 imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_toxin_name(toxin_name(150)),
 INDEX idx_toxin_organism(organism_name),
 INDEX idx_toxin_taxonomy(taxonomy_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
