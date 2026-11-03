# VenomOncoDB — Final Semester Project Brief

## Core novelty
VenomOncoDB is a provenance-aware evidence exploration platform connecting venom source, peptide sequence, cancer-cell-line experimental evidence, mechanism evidence, structures and publication provenance in one relational workflow.

## Research gap
CancerPPD2 provides a large manually curated anticancer peptide repository, but venom-focused project workflows still need source filtering, evidence-layer separation, provenance tracking and a transparent way to distinguish missing evidence from negative evidence.

## 30-mark presentation structure
- **Novelty (~9 marks):** Evidence Completeness Profile, venom-source filtering, provenance/audit trail, 3D structural linkage and context-preserving evidence model.
- **Methodology + technical (~27/30 target):** MySQL relational design, PHP REST API, JavaScript frontend, sequence analysis, data validation, source synchronization, interactive dashboard, 3Dmol.js structure viewer and testing.
- **Research / clinical relevance:** separates preclinical cell-line evidence from clinical evidence and avoids converting experimental observations into treatment recommendations.

## Demonstration
1. Home page: live counters and 3D medical-tech visual design.
2. Explorer: search `Chlorotoxin`, `scorpion`, `A-549`, or a sequence.
3. Open a peptide record: sequence analysis + evidence completeness.
4. Open a linked PDB structure where documented.
5. Insights: venom-group, cancer-type and publication-year views.
6. Methodology: research gap, data provenance, technical workflow and clinical-context limitation.
7. Admin source sync: build the local verified venom evidence layer from documented CancerPPD2 venom-associated rows without numeric padding.

## Scientific rule
Do not invent missing values. If the source does not report an activity value, mechanism, structure or clinical outcome, the application displays `Not reported` or leaves the field NULL.

## Assignment-to-platform mapping
1. **Collect venom-derived peptides:** source organisms are classified as Snake, Scorpion, Spider and Cone snail when supported by source evidence; unclassified candidates remain explicitly marked for review.
2. **Cell lines and IC50:** activity_assays stores cell line, cancer type, assay, exposure time, endpoint, reported value, unit, paper location and curation status. Missing IC50 values remain Not reported.
3. **Mechanism:** peptide_mechanisms stores mechanism name, cell line, evidence method, evidence statement, publication and curation status.
4. **Sequences and structures:** peptides stores sequence and provenance; structures stores PDB identifier, chain, method, resolution and RCSB URL; the frontend provides an interactive 3D viewer.
5. **Cancer-type search:** Explorer supports cancer type, cell line, sequence, peptide and venom-group search through the PHP REST API.
6. **3Dmol.js:** the 3D Lab and peptide records use 3Dmol.js for embedded molecular visualization.
7. **BLAST:** Sequence Analysis provides local sequence composition checks and an external NCBI BLASTP workflow for similarity exploration.
