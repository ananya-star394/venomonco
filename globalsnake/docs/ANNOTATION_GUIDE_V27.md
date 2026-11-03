# VenomOncoDB annotation guide — v2.7

## Purpose and unit
This is a protocol template for literature-evidence curation, not a claim that a benchmark has already been collected. Before screening, freeze a protocol version and define one unit consistently. Recommended unit: one peptide × cancer cell model × assay × endpoint × exposure condition claim. Do not combine different cell lines, assay endpoints, concentrations, or constructs into one row.

## Inclusion and exclusion
Include a record only when a primary source or a clearly traceable source record can be opened and the relevant claim can be located. Capture DOI/PMID or stable URL, paper title, access date, page/figure/table/supplement location, peptide name and sequence/construct identity status, species, cell line, assay, endpoint, units, exposure time, controls, and the exact wording supporting the claim. Exclude duplicate reports of the same experiment from the primary count, but record linked follow-up papers. Exclude reviews as the sole evidence for an experimental measurement; use them as discovery leads only.

## Label definitions
- **positive**: the pre-specified claim is explicitly supported by the reviewed source under the defined inclusion rule.
- **negative**: after applying the stated search/review protocol, the reviewed source set does not establish the claim. This is not evidence of biological inactivity, lack of efficacy, or a failed experiment unless the source explicitly reports that result.
- **unclear**: evidence is ambiguous, conflicting, insufficiently reported, or identity/model compatibility cannot be resolved.

Never label a claim negative merely because a database field is blank, a structure is unavailable, a search returned no hit, or no paper was found in a limited search.

## Independent review and adjudication
1. Two reviewers receive the same frozen source packet and label definitions. Where feasible, hide the system label and the other reviewer's label until each initial review is submitted.
2. Each reviewer records their own label, rationale, and exact evidence location. Do not copy one reviewer's decision into the other's record.
3. Record independent labels before discussion. Resolve disagreements only after preserving both original annotations; document the adjudicated outcome and reason in the research log.
4. The v2.7 page stores reviewer codes, not verified identities. Do not claim authenticated or blinded review unless access controls and the actual review procedure support that claim.

## Analysis and reporting
Report the screened, included, excluded, unclear, and adjudicated counts; class distribution; number of records with two independent labels; raw reviewer agreement; and Cohen's kappa only when estimable. For system metrics, compare the locked system output to a reference standard produced independently of the system where feasible. Report the eligible denominator, confusion matrix, metric definitions, uncertainty/limitations, and any exclusions. A small convenience sample is a pilot, not broad validation. Never describe metrics as measured until actual source-backed records have been reviewed.

## Source and provenance minimum
Record source identifier, source URL, access date, evidence location, curator/reviewer code, protocol version, review timestamp, and reason for any exclusion or correction. Preserve exported CSV and the protocol version with the report. Do not infer IC50, mechanism, sequence identity, structure, or publication linkage when not reported.
