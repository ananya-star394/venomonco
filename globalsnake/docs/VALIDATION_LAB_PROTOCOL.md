# Validation Lab: study-level curation and quality-control protocol

## Purpose
The Validation Lab is a browser-local quality-control workspace for manual review of primary literature. It adds study-level extraction, required-field checks, evidence-gap summaries, potential-discrepancy triage, and a descriptive Cohen's kappa calculation for two reviewer labels.

## Unit of curation
One row represents one reported experimental result for one candidate under a specified model, assay, endpoint, and condition. Do not merge different cell lines, endpoints, exposure times, peptide forms, or publications into one result row.

## Minimum extraction fields
- Candidate/peptide identity and source species/context
- DOI, PMID, or stable source URL and date checked
- Cell line or model, assay, endpoint, reported result, units, exposure/time point
- Control description and sequence/identity verification status
- Independent reviewer labels and notes

## Automated quality gate
The page flags missing candidate, source identifier, cell model, assay, endpoint, and reported result. Additional review flags cover unverified sequence identity, controls not confirmed, and missing date checked. These are completeness checks only; they do not validate a biological claim.

## Potential discrepancy triage
Rows are grouped by normalized candidate, cell model, assay, endpoint, and units. A group is flagged if it contains differing entered result strings. The flag is not a contradiction verdict. Review peptide identity, concentration units, exposure, controls, assay implementation, and study design before adjudicating any apparent difference. Numeric formatting or legitimate condition differences can trigger a flag.

## Inter-reviewer agreement
Cohen's kappa is calculated from records where both reviewers selected an assessed label. The page reports paired sample count, observed agreement, expected agreement, and kappa. Labels are treated as nominal categories. Interpret small samples and highly imbalanced categories cautiously. Kappa is not calculated from unpaired judgments and does not replace adjudication.

## Storage, exports, and limitations
Records are stored in browser localStorage. They are not shared across browsers/users and are not written to MySQL. CSV exports are local. Production use would require authentication, server-side storage, audit logs, versioning, role permissions, backup, and independent validation of the curation protocol.

## Reproducibility checklist
1. Freeze a benchmark set of primary papers and define inclusion/exclusion rules before curation.
2. Have two reviewers independently extract the same claims without seeing each other's labels.
3. Record disagreements and adjudication decisions.
4. Report sample size, category definitions, missingness, agreement statistics, and known limitations.
5. Compare against a named existing resource using the same benchmark set and predeclared evaluation criteria.
6. Never interpret missing evidence as evidence of no activity, safety, or absence of research.
