# VenomOncoDB — Biomedical Evidence Intelligence

VenomOncoDB is an academic research platform for exploring venom-associated peptide and cancer evidence. It is intentionally designed for students, researchers, educators, clinical researchers and general science users while keeping a strict research-only boundary.

## Key project contribution
The platform connects source organism → peptide sequence → cancer cell line → experimental assay → mechanism evidence → structure → publication provenance in one relational evidence model. Its flagship contribution is a **provenance-aware evidence graph** plus an **Evidence Completeness Profile** that reports documented coverage instead of inventing a therapeutic score. A separate verification boundary prevents unclassified source rows from being counted as venom-confirmed peptides.

## Important dataset policy
The package does **not** ship a fabricated fixed-size biological snapshot. The source synchronizer can import up to 200 unique source-backed CancerPPD2 records per run. Rows with explicit venom origin are classified into the venom layer; other source-backed anticancer records are retained as Needs review, never silently treated as confirmed venom peptides. Real CancerPPD2 identifiers, sequences, source context, cell lines, cancer types, assays, testing times and reported activity text are preserved. Explicit venom-associated origins are classified only when the source Origin field supports the classification; records without explicit venom origin are excluded from the verified venom layer. No IC50, mechanism, PDB ID or cancer association is invented.

## XAMPP setup
1. Copy the `VenomOncoDB` folder to `C:\xampp\htdocs\`.
2. Start Apache and MySQL.
3. Open phpMyAdmin and import `database/schema.sql`, then `database/seed.sql`, then `database/migrate_toxin_catalog.sql`.
4. Open `http://localhost/VenomOncoDB/`.
5. Open `http://localhost/VenomOncoDB/admin/import_cancerppd2_venom.php` and run the source-backed anticancer evidence import. Review the result counts; only records with explicit venom-origin support are eligible for verified venom status.\n6. Install ingestion dependencies with `python -m pip install -r ingestion/requirements.txt`, then run `python ingestion/sync_uniprot_toxins.py` from the project root. This fetches up to 200 reviewed animal-toxin records from the official UniProtKB API and upserts them into `toxin_catalog`. Confirm the printed unique-record count; the package does not falsely claim those runtime-fetched records are already bundled.

## Main pages
- Home: professional biomedical landing page with universal search and live 3D structure
- Explorer: evidence-aware search with cancer-type, cell-line and venom-group filters
- Insights: descriptive charts
- Methodology: research gap, provenance, clinical context and technical architecture
- 3D Lab: interactive PDB structure viewer
- About: project purpose, novelty and boundaries
- API: JSON endpoints and app-ready architecture
- Guide: audience-specific workflows and future APK architecture
- Peptide record: sequence analytics, IC50/activity fields, mechanism evidence, evidence completeness and 3D structure
- Toxicity Analysis Tool: local UniProt Tox-Prot annotation search plus transparent sequence-property calculations (not a validated toxicity predictor)
- Sequence Analysis: peptide composition checks plus NCBI BLASTP workflow

## India-wide literature discovery (added)
The project scope is India-wide: records can concern venomous animals or venom-derived peptides studied in India, across snake, scorpion, spider, bee/wasp, cone-snail and other animal groups. Geographic relevance must be recorded from the source; an animal being found in India is not by itself proof that a peptide was studied in India.

Run `python ingestion/discover_india_venom_acp.py` to search PubMed for India-relevant venom/anticancer literature and create `ingestion/india_venom_acp_literature_candidates.csv`. This is a **paper-discovery queue**, not a peptide dataset. Manually curate sequence, animal origin, cancer-cell assay, measured values and citations before importing records. Do not count paper candidates as verified peptide records.

The database must report verified unique venom-derived anticancer peptides separately from publication rows, assay rows, and unverified candidates. The requested goal is at least 100 distinct verified peptide records; this package does not falsely claim that goal has already been met. CancerPPD2 contains many anticancer records from non-venom origins, so only its explicit venom-associated entries may enter the verified venom layer.

## Sources
CancerPPD2 is used for candidate discovery and source synchronization. RCSB PDB is used for structure metadata and 3D visualization. Primary publications should be used for final biological claims.

## Scope
This system supports evidence exploration and research prioritisation only. It does not diagnose disease, predict patient outcomes, recommend treatment or establish clinical efficacy from cell-line data.


## Assignment requirements covered
- Venom-derived peptide categories: snake, scorpion, spider and cone snail
- Cancer cell lines and reported activity/IC50 fields
- Mechanism evidence such as apoptosis or membrane disruption when supported by source records
- Peptide sequences and verified PDB structure metadata
- Cancer-type and cell-line search
- 3Dmol.js molecular visualization
- BLASTP sequence-similarity workflow

The local database includes a small source-traceable starter layer for demonstration and can be refreshed from CancerPPD2. The verified venom count is reported separately from the review queue. Missing IC50/mechanism/structure evidence is never fabricated.


Structure-link behavior (Explorer): the Structure column links to 3D Lab only when the database `structures` table contains a PDB ID for that peptide. The first stored PDB ID is opened via `structure.html?pdb=...`; additional linked structures are counted. A general example PDB loaded in 3D Lab is not automatically evidence that it belongs to every peptide. Add a structure mapping only after verifying the peptide/construct and sequence relationship against RCSB/primary sources.


## UI update (v3)
- Added a dedicated Research Articles tab powered by live NCBI PubMed E-utilities search.
- Updated the transparent light-themed serpent/molecular logo and homepage snake background artwork.
- Added explicit light-mode overrides for navigation, search controls, tables, panels, cards and research article results.
- PubMed results require browser internet access; this module does not fabricate articles.
\n\n## Toxicity analysis boundary (v2)\nThe Toxicity Analysis page provides (1) sequence-derived descriptors and (2) a searchable catalog of source-annotated reviewed animal toxin records. It does not calculate a validated toxicity/potency score, infer how toxic a compound is to a person, or replace experimental evidence. A source annotation, sequence match, or amino-acid property must not be reported as a quantitative toxicity measurement.\n

## Evidence Explorer provenance and status table
The Explorer retains the existing database schema and adds visible animal/species provenance, linked publication-year range, venom verification, curation progress, and structure availability. A missing structure is not labelled as modelling-in-progress unless a real status field/evidence is available. The publication-year range reflects linked local records only, not exhaustive global literature coverage. The common logo asset and light-theme setup are retained across pages.


## Research Workbench (added)

Open `frontend/research-workbench.html` through Apache to use local sequence descriptor calculations (approximate mass, estimated pI, net charge at pH 7.4, GRAVY, hydrophobic/aromatic fractions, cysteine count and composition), local toxin-catalog search, PubMed/Europe PMC/CancerPPD2/UniProt search links, RCSB PDB/AlphaFold/BLAST links, and CSV export for sequence results and a manually completed evidence-review worksheet. The worksheet is exported locally and is not automatically written to the database.

**Scientific limits:** this is not a validated toxicity predictor. No toxicity score is calculated. Sequence-derived descriptors are not toxicity measurements. A PDB ID, anticancer status, or ongoing-development status must be verified against the linked source before being entered as confirmed. Missing evidence means not curated here, not proof that no research exists.


## Research Intelligence Centre (v2 upgrade)

Open `frontend/research-center.html` from the running XAMPP site. This page adds a local-API-backed evidence snapshot, evidence-completeness charts, source-backed starter records, an RCSB PDB sequence-audit queue for up to 100 FASTA records per batch, exportable audit results, a research database directory, and a defensible novelty-comparison matrix. Navigation links to the Research Centre have been added across the front-end pages.

### Important scientific interpretation

- The batch queue is an audit tool, not a claim that 100 verified structures are already bundled. Paste the actual 100 sequences you wish to verify, run the RCSB sequence search, and review every candidate entry's entity/chain, experimental method, sequence coverage, termini/modifications and construct context.
- An exact sequence match is labelled a candidate for manual verification, not automatically an experimentally validated anticancer peptide. PDB structure evidence does not prove anticancer activity.
- A predicted AlphaFold structure is labelled as predicted, not experimental PDB evidence. “No hit returned” means the search did not return a match under those settings; it is not proof that no structure exists.
- Live charts read the local API and intentionally do not substitute invented totals if the database/API is unavailable.
- The five starter peptide records remain the bundled sample set. The 107 snake catalogue entries are species references, not 107 anticancer peptides. Use the source importer and curator workflow to add source-backed data; do not pad the scientific dataset with synthetic records.

### Additional external resources

The Research Centre links CancerPPD2, RCSB PDB, UniProt, PubMed, Europe PMC, AlphaFold DB, NCBI, Tox-Prot, DBAASP, DRAMP, ChEMBL and ClinicalTrials.gov. These resources cover different evidence types; antimicrobial activity, toxicity and anticancer activity must not be conflated.


## Search and catalogue fixes (2026-10-21)
- Explorer searches for generic terms such as `snake`, `venom`, `toxin`, `healthcare`, and `drug discovery` now open the species reference catalogue instead of only searching the peptide evidence table. Species-specific queries can fall back to matching catalogue records.
- The 107-species catalogue includes separate anticancer evidence status, healthcare/drug-discovery context, research-status qualification, source links, and taxonomy links. Known venom-inspired drug examples are explicitly distinguished from anticancer treatment claims.
- Toxicity Analysis and Research Workbench now fall back to related species references and external UniProtKB/PubMed/Europe PMC searches when the local toxin annotation table has no match or is unavailable. Species references are labelled separately from measured toxicity records.
- Workbench species results include a Review action that pre-fills the research worksheet. Export remains a local CSV worksheet; it is not silently saved to the database.
- No synthetic activity, toxicity or structure records are added. See `docs/HEALTHCARE_CONTEXT_SOURCES.md` for the specific literature references and limitations.

## v2.2 worksheet and literature candidate improvements
- The Research Workbench now appends worksheet rows to an on-page evidence table. CSV download is separate and includes all rows in the browser worksheet.
- Worksheet rows persist in browser local storage on that browser/device only; this is not shared server-side storage.
- Explorer now contains a 21-entry literature-linked candidate discovery catalogue and can surface candidate matches in the main evidence results. This discovery index is intentionally separate from fully curated database assay rows.
- The catalogue distinguishes primary-paper links from leads that still need assay-level primary-source curation. It does not treat a literature search URL as proof of a particular IC50 or mechanism.
- Chlorotoxin points to PDB 7X41 with the explicit “construct differs” status. Other candidates use RCSB search links unless a specific structure has been linked and checked.

## Evidence Lab upgrade (v2.3)
Open `frontend/evidence-lab.html` through the local PHP/XAMPP site. It includes a claim-level evidence ontology, a transparent 0–100 evidence-support checklist, a browser-local candidate comparison table, CSV export, and a documented curation protocol (`docs/EVIDENCE_ONTOLOGY_AND_SCORING.md`). The score is a project heuristic, not empirically validated, and is not a clinical efficacy or safety score. Comparison rows are local to the browser and are not automatically stored in MySQL.


## Validation Lab (v2.4 upgrade)

Open `frontend/validation-lab.html` through Apache to use study-level claim extraction, a required-field quality gate, potential-discrepancy triage, evidence-gap coverage summaries, CSV exports, and a descriptive Cohen's kappa calculator for paired reviewer labels. These features are intended to make the curation method more reproducible and testable. The discrepancy detector flags groups for human review; it does not automatically adjudicate contradictions. The workspace stores rows in browser localStorage only and does not write them to MySQL. See `docs/VALIDATION_LAB_PROTOCOL.md` for the protocol and limitations.


## v2.5 Benchmark Lab

- New `frontend/benchmark-lab.html`: manually curated benchmark register with source and evidence-location fields, reference-vs-system label comparison, confusion matrix, accuracy, precision, recall/sensitivity, specificity, F1, Cohen's kappa where estimable, and CSV export/import.
- The benchmark starts empty by design; it does not fabricate paper records or benchmark results.
- Resource comparison is a dated checklist, not a prefilled competitor ranking.
- Browser-local storage only; benchmark rows are not persisted to MySQL or shared across users.
- See `docs/BENCHMARK_LAB_PROTOCOL.md` before collecting or reporting evaluation results.


## v2.6 benchmark persistence upgrade

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin and select the `venomoncodb` database.
3. Import `database/migrate_v26_benchmark.sql` (do not re-import the destructive base schema just to add benchmark tables).
4. Open `http://localhost/venom_india_upgrade/frontend/benchmark-lab.html` using the folder name you copied into `htdocs`.
5. Enter a consistent reviewer code. Add only source-checked records. CSV export remains available.

Benchmark rows now persist in MySQL and create/delete actions are logged in `benchmark_audit`. Reviewer codes are not login credentials; this remains a local research prototype without authentication/authorization. No benchmark data or validation results are prefilled.

## v2.7 Independent Review & Reproducibility Upgrade

- Added `frontend/reviewer-lab.html` for separately recorded reviewer labels and evidence rationale against existing MySQL benchmark rows.
- Added `api/routes/benchmark-reviews.php` and API route `benchmark-reviews`.
- Added `database/migrate_v27_independent_reviews.sql` with independent review rows and review-change audit events. Apply after the base schema and v2.6 benchmark migration.
- Added `docs/ANNOTATION_GUIDE_V27.md`, a blank `docs/BENCHMARK_MANIFEST_TEMPLATE.csv`, and `docs/V27_RELEASE_CHECKLIST.md`.
- The unique `(benchmark_id, reviewer_code)` constraint allows one current annotation per code per claim; re-submission updates that annotation and records a CREATE/UPDATE event. The audit table is an application-level log, not a tamper-proof ledger.
- Reviewer codes are not authenticated identities; this does not enforce blinded review. The feature creates a workflow for collecting independent labels but does not itself provide independent reviewer results or a populated gold-standard dataset.

### v2.7 install
1. Back up the database.
2. Import `database/migrate_v27_independent_reviews.sql` into the existing `venomoncodb` database after the v2.6 migration.
3. Start Apache and MySQL in XAMPP.
4. Open `frontend/reviewer-lab.html` via `http://localhost/<folder-in-htdocs>/frontend/reviewer-lab.html`.
5. Create a source-checked benchmark claim in Benchmark Lab first, then annotate it in Reviewer Lab.

Read `docs/V27_RELEASE_CHECKLIST.md` and `docs/ANNOTATION_GUIDE_V27.md` before reporting any evaluation results.
