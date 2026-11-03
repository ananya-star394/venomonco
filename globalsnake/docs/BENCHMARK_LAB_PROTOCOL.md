# VenomOncoDB v2.5 — Benchmark Lab Protocol

## Purpose
The Benchmark Lab evaluates the agreement between a system output and a human-adjudicated reference label for a clearly defined literature-evidence claim. It does not establish biological truth, clinical efficacy, or treatment utility.

## No fabricated records
The interface starts empty. Do not add placeholder or invented study rows to reported results. Each benchmark row must point to a real primary source and include an evidence location when available. Unavailable details should remain unreported/unclear rather than inferred.

## Unit of evaluation
Use one row per pre-specified claim or study unit. Before annotation, define the unit (for example, whether a claim is at the peptide × cell-line × assay × endpoint level), source-search dates, source hierarchy, inclusion/exclusion criteria, duplicate handling, and adjudication rules. Avoid mixing different assay endpoints or experimental contexts into one label.

## Labels
- `positive`: the reviewed source set reports evidence that meets the project's pre-defined criterion.
- `negative`: the reviewed source set does not establish the criterion under the stated review protocol. This is not proof of biological inactivity.
- `unclear`: insufficient, ambiguous, conflicting, or inadequately documented information prevents a confident label.

The reference label should be decided by human reviewers using the written protocol and, where feasible, independently of the system output. Record adjudication rationale and the exact paper location. Do not use database absence as a negative biological result.

## Metrics
Binary metrics use only rows where both the reference and system labels are `positive` or `negative`. Rows involving `unclear` are excluded from binary metrics and reported separately. The tool calculates TP, FP, TN, FN, accuracy, precision (positive predictive value), recall/sensitivity, specificity, F1, and Cohen's kappa where estimable. Always report the eligible sample size and class counts. Kappa may be undefined when expected agreement is 100% or no eligible rows exist; report that limitation rather than replacing it with zero.

These metrics measure agreement with the chosen reference protocol, not absolute biological truth. Small or imbalanced samples can yield unstable estimates. For formal evaluation, predefine a sampling strategy, use enough records for the question, report confidence intervals where feasible, and keep a locked test set separate from any rule development.

## Reproducibility and limitations
In v2.6, records are stored in MySQL by the PHP API. Apply `database/migrate_v26_benchmark.sql` after the base schema. The API writes create/delete/bulk-delete events to `benchmark_audit`. Reviewer IDs are self-entered labels, not authenticated identities. Export CSV files and retain a versioned copy with the project materials. Record the application version, protocol version, review date, source access date, reviewer identifiers (using appropriate privacy safeguards), and any adjudication changes in the accompanying research log. The current page does not enforce blinded review, user authentication/authorization, reviewer-role separation, confidence intervals, or external database snapshots. Do not expose this prototype API to the public internet until authentication, authorization, rate limiting and appropriate privacy controls are implemented.

## Resource comparison
The resource-comparison table is an unverified checklist. Apply the same feature definitions and test cases to the current versions of VenomOncoDB, CancerPPD2, and PeptiHub; record access date, URLs, examples and limitations. Do not claim a competitor lacks a feature unless it was checked in the specified scope and date.

## Suggested report outputs
1. Protocol and versioned benchmark manifest.
2. Number of records screened, included, excluded and unresolved, with reasons.
3. Label counts and source distribution.
4. Confusion matrix and metrics with eligible sample size; confidence intervals if calculated.
5. Independent reviewer agreement and adjudication notes, if two reviewers actually performed independent annotation.
6. Discrepancy review examples with assay/model compatibility checked by a human.
7. Resource comparison checklist with dated evidence.
