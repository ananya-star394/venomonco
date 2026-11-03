# Rubric-ready project architecture

This document is a presentation guide, not a promise of marks.

## Research novelty

1. Venom-focused evidence model rather than a generic peptide table.
2. Explicit linkage of source, sequence, cell line, activity, mechanism, structure and publication.
3. Evidence Completeness Profile that exposes missing evidence instead of assigning an efficacy score.
4. Provenance and audit trail for imported records.
5. Clear distinction between source-backed facts, calculated sequence statistics and missing evidence.

## Technical implementation

- PHP + PDO backend
- MySQL/MariaDB relational schema
- JSON REST API
- Prepared SQL statements
- Responsive HTML/CSS/JavaScript frontend
- 3Dmol.js/RCSB structure viewing
- BLASTP workflow
- PWA-ready structure for later Android packaging

## Data integrity

The final package intentionally contains no fabricated biological rows. The source synchronizer imports only explicit venom-origin rows from official CancerPPD2 pages and never pads the dataset to a numeric target. This creates a smaller but auditable verified layer when source coverage is limited.

## Evidence interpretation

- Cell-line activity is preclinical evidence.
- IC50 is reported only when present in the source evidence.
- Mechanism is not inferred from sequence alone.
- PDB IDs are not generated.
- Missing evidence is not interpreted as negative evidence.
- The platform does not provide clinical treatment recommendations.
