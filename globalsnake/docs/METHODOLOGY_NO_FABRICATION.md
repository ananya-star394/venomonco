# Methodology — evidence-only data policy

## 1. Source discovery

Candidate anticancer peptide records are obtained from CancerPPD2. Venom-source annotation is checked against the documented **Origin** field and, for final curation, can be cross-checked with UniProt/Tox-Prot and the primary publication.

CancerPPD2 documents fields including peptide ID, PMID, sequence, peptide name, length, cell line, cancer type, assay and testing time. Its browse tables also expose Origin and Activity fields.

## 2. Data extraction

The importer downloads the official CancerPPD2 browse tables directly from the source website. Real CancerPPD2 IDs are preserved. Duplicate IDs are discarded.

## 3. Venom classification

A record is classified as Snake, Scorpion, Spider or Cone snail only when the source Origin text contains explicit evidence supporting that classification. If the source does not support a venom classification, the record is stored as `Unclassified`.

## 4. Activity / IC50

The importer stores the activity text exactly as reported by the source table. A numeric value and unit are parsed only when the source text explicitly contains a recognizable measurement. If no value is present, the field remains NULL and the original text is retained when available.

## 5. Mechanism

Mechanism is **not inferred from peptide sequence, charge, hydrophobicity, cancer type or IC50**. A mechanism is entered only after checking the primary publication or another authoritative source that explicitly reports the mechanism. Until then it remains `Not reported` / `Needs review`.

## 6. Structure

A PDB identifier is entered only after verification against RCSB PDB or an authoritative source. No PDB identifier is generated from sequence similarity.

## 7. Missing evidence

Missing evidence is represented as `NULL`, `Not reported`, `Not available`, or `Needs review`. It is never interpreted as evidence of absence or lack of biological effect.

## 8. Quality control

The system checks:

- sequence character validity;
- sequence length consistency;
- duplicate external IDs;
- publication identifier format;
- cell-line/cancer-type linkage;
- activity value/unit provenance;
- PDB identifier provenance;
- curation state;
- source URL and audit trail.

## 9. Evidence Completeness Profile

For each peptide, the interface reports coverage of sequence, source, activity, mechanism, structure and publication evidence. This is a documentation metric, not an efficacy score.

## 10. Interpretation boundary

The database supports evidence exploration and research prioritisation. Cell-line activity is preclinical evidence and is not presented as human clinical efficacy or treatment advice.
