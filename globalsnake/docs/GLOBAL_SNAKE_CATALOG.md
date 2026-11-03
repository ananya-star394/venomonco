# Global snake-venom catalogue

This package bundles 107 snake species reference rows in `snake_venom_catalog`. It is intentionally separate from the curated `peptides` table. The rows are not presented as anticancer peptides and do not assert that anticancer activity has been established for each species.

The Explorer displays this reference catalogue beneath the peptide evidence table. The homepage is not changed.

## Import
On a fresh install, import `database/schema.sql` and then `database/seed.sql`. If the existing database is already installed, import `database/seed_global_snake_catalog.sql` in phpMyAdmin.

## Scientific interpretation
Only records in the evidence table with linked activity assays and traceable publications should be described as experimental anticancer evidence. Catalogue-only entries have status `Not established in this catalogue`; this is not a claim of inactivity. Taxonomy reference: https://reptile-database.reptarium.cz/
