# Project Novelty and High-Marks Positioning

VenomOncoDB should be presented as a **provenance-aware evidence integration platform**, not as another list of anticancer peptides. CancerPPD2 already provides a large anticancer peptide/protein repository and API; therefore the novelty claim is not the existence of peptide search itself. CancerPPD2 documents peptide sequence, origin, cell line, cancer type, assay and structure information and provides programmatic access.

## Distinctive project features
1. **Venom-specific verification boundary** — only explicit venom-origin evidence enters the verified venom layer.
2. **Evidence graph** — source organism, peptide, cell line, assay, mechanism, structure and publication are separate linked entities.
3. **Evidence completeness profile** — missing evidence is visible instead of being converted into scores or assumptions.
4. **Cell-line-specific evidence** — activity remains attached to the tested cell line and assay rather than collapsing into a peptide-wide efficacy score.
5. **Structure provenance** — PDB IDs and sequence/construct matching status are retained; a structural model is not treated as proof of anticancer activity.
6. **Audit trail** — source identifier, URL, curation status and notes are stored.
7. **Sequence-analysis layer** — composition, cysteine count, hydrophobic fraction and BLASTP similarity are exposed as exploratory analyses, while explicitly separating similarity from biological efficacy.
8. **Research-safe interpretation** — the interface states that preclinical cell-line evidence is not clinical efficacy and that missing evidence is not negative evidence.

## How to defend novelty
A strong viva statement is: “The novelty is not that VenomOncoDB contains venom peptides; the novelty is that it connects venom provenance, sequence identity, cancer-cell-line evidence, mechanism, structure and publication provenance while preserving evidence status and missingness.”

This positioning is intentionally comparative and does not claim that VenomOncoDB replaces CancerPPD2.
