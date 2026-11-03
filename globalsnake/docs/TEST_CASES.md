# VenomOncoDB Test Cases

1. Home page loads without PHP/JS error.
2. Summary counters populate from MySQL.
3. Explorer search returns a peptide by name.
4. Explorer search returns records by venom group.
5. Explorer search returns records by cancer type/cell line when present.
6. Empty exact search produces a clear no-match state and related evidence when available.
7. Peptide detail displays sequence analysis.
8. PDB-linked peptide attempts 3D structure loading.
9. Dashboard charts render descriptive counts.
10. Methodology explains research gap, provenance and clinical limitations.
11. API uses prepared statements for user-controlled filters.
12. Database rejects malformed foreign-key relationships.
13. Missing values display as Not reported / NULL rather than invented values.
14. PWA manifest and service worker load for future app packaging.
15. Importer labels venom-confirmed and unclassified source records separately.


## Validation Lab v2.4 manual test cases
1. Add a study with all required fields, verified identity, controls described, and a source ID: the quality gate should show no automated flags for the listed fields.
2. Omit the cell model, assay, endpoint, or result: the row should remain visible and the quality gate should flag missing fields.
3. Add two rows with the same candidate, cell model, assay, endpoint, and units but different result strings: the group should be flagged as a potential discrepancy, not automatically labelled contradictory.
4. Add paired reviewer labels with some agreement and some disagreement: the page should show paired count, observed agreement, expected agreement, and Cohen's kappa.
5. Export study, audit, and gap-summary CSVs; open them in a spreadsheet and check that commas/quotes are escaped.
6. Reload the page in the same browser: records should persist in localStorage. A different browser/profile should not be assumed to share them.
