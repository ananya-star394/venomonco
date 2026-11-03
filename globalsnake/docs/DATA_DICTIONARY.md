# VenomOncoDB Data Dictionary

| Layer | Core fields | Purpose |
|---|---|---|
| Source organism | scientific_name, venom_group | biological source context |
| Peptide | public_id, peptide_name, sequence, sequence_length | peptide identity |
| Source link | organism_id, publication_id, source_description | provenance |
| Cancer | cancer_name | disease context |
| Cell line | cell_line_name, tissue_origin | experimental model |
| Activity | assay_method, exposure_time, endpoint_name, reported_value, reported_unit | experiment-level evidence |
| Mechanism | mechanism_name, evidence_statement, evidence_method | documented mechanism evidence |
| Structure | pdb_id, method, resolution, rcsb_url | structural evidence |
| Publication | PMID, DOI, title, journal, year | literature provenance |
| Audit | source_identifier, source_url, curator, notes | curation trace |


## Evidence Explorer display additions (2026-10-21)
- **Animal / species:** uses the source-organism common name and scientific name when recorded. Missing source names remain explicitly unreported.
- **Evidence history:** earliest/latest year among linked activity-assay and mechanism publications. This is the range in the local database, not a claim that the platform has found every global publication.
- **Structure / model status:** a linked PDB ID is displayed when present. When no structure is linked, the interface says that modelling status is not recorded; it does not invent an in-progress or completed model.
- **Venom verification:** uses the existing `venom_verification_status` field. `Needs review` and `Not established` are not treated as verified venom origin.
- **Curation progress:** uses the existing `curation_status` field. `Partially curated` is presented as curation in progress; `Needs review` is presented as awaiting verification/review.
- The existing database tables remain in place. These are UI/API additions based on existing fields, not fabricated biological records.
