# Evidence ontology and evidence-support score (prototype)

## Purpose and boundary
VenomOncoDB separates claim-level evidence rather than assigning a single “verified” label to a peptide. The Evidence Lab is a reviewer aid; its labels and score are not a validated scientific instrument, clinical prediction, efficacy estimate, or safety score.

## Controlled ontology
- **Anticancer activity:** Not established; In vitro activity reported; In vivo activity reported; Conflicting evidence.
- **Mechanism:** Not reported; Experimentally reported; Computationally proposed; Conflicting evidence.
- **Selectivity:** Not reported; Measured; Unclear; Conflicting evidence.
- **Toxicity:** Not reported; Measured; Unclear; Conflicting evidence.
- **Structure:** Unavailable/unverified; Experimental structure; Predicted structure; Related construct—exact match unconfirmed.
- **Provenance:** Unverified; Primary research paper; Primary abstract; Review only; Database link only.

These are separate dimensions. A structure record does not establish anticancer activity; a review citation does not automatically verify a specific assay; “not reported” is not evidence of absence.

## Evidence-support score (0–100)
The prototype adds these reviewer-selected features: traceable primary paper (20), specific assay and cancer model (15), quantitative dose-response/activity (15), relevant controls (10), selectivity test (10), documented independent replication/multiple supporting studies (15), and verified claim-to-source/peptide identity (15). Each item is binary, with points only when the reviewer confirms it from the source.

Project-defined descriptive bands: 0–24 limited documented support; 25–49 preliminary documented support; 50–74 moderate documented support; 75–100 stronger documented support. These bands and weights are heuristic and not empirically validated. Do not call the result a probability, confidence interval, clinical score, or validated ranking. Score zero means no support features were entered; it does not mean the candidate is inactive.

## Curation protocol
1. Capture DOI/PMID/URL and date checked.
2. Verify exact peptide identity and source attribution.
3. Capture each experiment separately: cancer type, cell line, assay, endpoint, value/units, exposure time, controls and replication.
4. Label mechanism, selectivity, toxicity and structure independently.
5. Keep conflicting results separate and mark missing fields “not reported” or “not verified”.
6. Where feasible, request independent review for high-priority claims.

## Storage and limitations
The current page stores comparison rows in the user's browser localStorage. Rows are not shared with other users or automatically written to MySQL. Production deployment should add authentication, server-side persistence, audit logs, reviewer identity, record versioning and validated scoring rules.


## Validation Lab extension (v2.4)
The Validation Lab provides study-level extraction, completeness checks, potential-discrepancy triage, evidence-gap summaries, and a descriptive two-reviewer Cohen's kappa calculation. It is a manual curation aid, not a validated biological classifier. Review the full protocol in `VALIDATION_LAB_PROTOCOL.md`.
