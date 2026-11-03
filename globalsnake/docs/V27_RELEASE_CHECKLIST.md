# v2.7 release and reproducibility checklist

## Install
1. Back up the local `venomoncodb` database.
2. Confirm base `database/schema.sql` and v2.6 benchmark migration have already been imported.
3. In phpMyAdmin, select `venomoncodb` and import `database/migrate_v27_independent_reviews.sql`.
4. Start Apache and MySQL in XAMPP. Open the site through `http://localhost/<your-htdocs-folder>/frontend/benchmark-lab.html` and `reviewer-lab.html`; do not use `file://`.
5. Confirm the PHP API can connect to MySQL before entering records.

## Functional smoke test
- Create one real, source-traceable benchmark record in Benchmark Lab.
- Open Reviewer Lab; confirm the record appears by ID.
- Submit reviewer A's label/rationale; confirm it appears in the review list.
- Submit reviewer B using a distinct reviewer code; confirm both labels persist after page refresh.
- Submit a second review from reviewer A for the same record; confirm it updates that review rather than creating a duplicate.
- Verify the matching rows in `benchmark_reviews` and events in `benchmark_review_audit` using phpMyAdmin.
- Test invalid record IDs and missing rationale; the API should return a validation error.
- Export benchmark data and save a copy alongside the versioned protocol and source manifest.

## What this release does not establish
This release adds a structured independent-annotation workflow and templates. It does not include a populated gold-standard dataset, authenticated accounts, guaranteed blinding, independent reviewer results, validated evidence-score weights, confidence intervals, or a completed competitor comparison. These require real source review and/or additional implementation. Do not report a novelty score or rubric score as guaranteed by the software package.
