USE venomoncodb;
ALTER TABLE peptides ADD COLUMN venom_verification_status ENUM('Verified','Needs review','Not established') DEFAULT 'Not established';
ALTER TABLE peptides ADD COLUMN biological_nature ENUM('Natural','Modified','Synthetic','Recombinant','Unknown') DEFAULT 'Unknown';
ALTER TABLE peptides ADD COLUMN primary_source_verified TINYINT(1) DEFAULT 0;
ALTER TABLE peptides ADD COLUMN source_traceability TEXT;
ALTER TABLE structures ADD COLUMN sequence_match_status ENUM('Exact','Construct differs','Not assessed') DEFAULT 'Not assessed';
