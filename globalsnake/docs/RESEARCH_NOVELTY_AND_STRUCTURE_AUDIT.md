# Research novelty and structure-audit protocol

## Defensible project contribution

VenomOncoDB should be evaluated as a provenance-aware evidence-integration workflow, not as a new anticancer peptide repository and not as a clinical predictor. CancerPPD2 already provides a large anticancer peptide/protein resource and programmatic access. The project contribution is the explicit joining of venom-source provenance, peptide identity, cell-line-specific assay evidence, mechanism evidence, structure provenance and publication traceability, with missingness and uncertainty retained.

## Why structure verification is a research problem

A peptide record can refer to a mature peptide, a precursor, a fragment, a chemically modified sequence or a construct used for structural experiments. A structure link by itself does not prove that the deposited polymer entity is identical to the mature venom peptide. For every proposed match, preserve: query sequence, PDB ID, polymer entity ID, entity sequence, percent identity, sequence coverage, construct/termini notes, experimental method, source URL and verification date. Predicted models should be labelled separately from experimental structures.

## Batch audit protocol

1. Obtain the sequence from a traceable source (accession or primary paper) and retain the source URL/identifier.
2. Normalize the sequence for comparison while retaining modifications/termini as separate metadata. Do not silently convert unusual residues or post-translational modifications into canonical amino acids.
3. Search RCSB PDB by sequence.
4. Retrieve candidate polymer-entity sequences and calculate identity and length coverage.
5. Inspect the entry's experimental method, entity/chain, mature peptide versus precursor/construct, missing residues, modifications and biological context.
6. Record the result as `Exact full-length sequence match candidate`, `Identical aligned region; length/coverage differs`, `Partial or non-identical sequence match`, `Candidate hit(s); entity check failed`, `Live lookup unavailable`, or `No hit returned by this query`.
7. Only after manual review mark a structure verified in the project database. Record who checked it and when. A structure match is not evidence of anticancer activity.

## Evidence status definitions

- **Experimental anticancer evidence reported:** cite a primary study and retain cell line, assay endpoint, numeric value and units only when explicitly reported.
- **Structure experimentally reported:** source entry and experimental method are identified; sequence/construct match is reviewed.
- **Predicted structure:** a model exists but is not represented as an experimental PDB structure.
- **Ongoing research:** use only when a dated active study, recent paper or registry record supports it; otherwise use `research status not established` or `follow-up needed`.
- **No linked evidence in this database:** a curation statement, not a claim of biological absence.

## Data sources and intended roles

- CancerPPD2: anticancer peptide/protein records and API.
- RCSB PDB: experimentally deposited structures and polymer-entity sequences.
- AlphaFold Protein Structure Database: predicted structural models.
- UniProtKB/Tox-Prot: sequence, organism and toxin annotation.
- PubMed and Europe PMC: literature discovery and publication metadata/full-text links.
- NCBI BLAST: sequence similarity discovery; similarity does not prove equivalent function.
- DBAASP and DRAMP: antimicrobial/cytotoxic peptide evidence; not substitutes for cancer-specific assays.
- ChEMBL: bioactivity and assay metadata where applicable.
- ClinicalTrials.gov: registry records; inspect status and dates rather than inferring ongoing status from search results.

## Literature basis for the project rationale

1. Chauhan M, Gupta A, Tomer R, Raghava GPS. *CancerPPD2: an updated repository of anticancer peptides and proteins*. Database. 2025;2025:baaf030. DOI: 10.1093/database/baaf030. PubMed: https://pubmed.ncbi.nlm.nih.gov/40338521/ . The article reports 6,521 entries and describes mixed structure provenance including PDB and prediction tools.
2. Tyagi A, Tuknait A, Anand P, et al. *CancerPPD: a database of anticancer peptides and proteins*. Nucleic Acids Research. 2015;43(D1):D837–D843. DOI: 10.1093/nar/gku892. https://academic.oup.com/nar/article/43/D1/D837/2439477 .
3. Pirtskhalava M, Amstrong AA, Grigolava M, et al. *DBAASP v3: database of antimicrobial/cytotoxic activity and structure of peptides as a resource for development of new therapeutics*. Nucleic Acids Research. 2021;49(D1):D288–D297. DOI: 10.1093/nar/gkaa991. https://dbaasp.org/about .
4. Europe PMC REST API documentation: https://europepmc.org/RestfulWebService .
5. AlphaFold Protein Structure Database: https://alphafold.ebi.ac.uk/ .

## Viva-ready novelty statement

“The novelty is not that VenomOncoDB lists venom peptides. It is that the platform makes evidence lineage inspectable across venom source, sequence identity, cell-line-specific anticancer assays, mechanisms, structures and publications, and introduces an explicit structure-to-sequence audit trail so a PDB hit is not mistaken for a verified mature-peptide structure. Missing evidence is represented as missing, not as a negative biological result.”

## Evaluation plan for the report

- Measure the fraction of curated peptide records with traceable primary sources, explicit venom-origin evidence, sequence provenance, cell-line/assay context, structure-match review and publication links.
- Manually review a stratified sample of structure-audit outputs and report false-match/construct-mismatch cases.
- Test missing-data behavior and verify the app never reports a missing IC50 or structure as zero/inactive.
- Compare the integration workflow with the stated scope of existing resources; do not claim feature exclusivity without a systematic comparison.
- Report the actual number of verified records after importing and curating them. Do not call the 107-species catalogue 107 verified peptides.
