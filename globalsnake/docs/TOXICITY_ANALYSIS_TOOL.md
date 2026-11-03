# Toxicity Analysis Tool

## Included
- Browser-local sequence validation and descriptive calculations: length, approximate unmodified linear peptide mass (residue masses plus terminal water), amino-acid composition, hydrophobic-residue fraction, and simple counts of basic/acidic side chains.
- Measured-sample calculator: mass concentration from user-entered peptide mass and sample volume; molar concentration when a sequence-derived molecular mass is available; and mass fraction when measured toxin mass and total venom mass from the same sample are supplied.
- Source-reported toxicity result preview: manually records an endpoint, value, units, test model, assay conditions and publication identifier. It does not create or fetch experimental results automatically.
- Local search of the `toxin_catalog` table for UniProt accession, toxin name, organism, and annotated function/tissue specificity.
- Reproducible UniProtKB/Tox-Prot synchronizer (`ingestion/sync_uniprot_toxins.py`) capped at 200 reviewed animal-toxin entries per run.
- Original UniProt accession, organism, taxonomy identifier, sequence, annotation, release metadata, and source URL retained.

## Setup
1. Import `database/schema.sql`, `database/seed.sql`, and `database/migrate_toxin_catalog.sql` in phpMyAdmin.
2. Run `python -m pip install -r ingestion/requirements.txt`.
3. From the project root run `python ingestion/sync_uniprot_toxins.py`.
4. Check the printed inserted/refreshed/skipped counts and total unique rows. Do not report the 100-record requirement as met until the total shown is at least 100.
5. Open `frontend/toxin-lab.html` through the local PHP server.

## Interpretation
The query uses reviewed metazoan UniProtKB records annotated for venom tissue specificity or the Toxin keyword. This is a source-backed toxin reference set, not a clinical toxicity dataset. The importer does not fabricate assay doses, LD50 values, toxic potency scores, or effects in humans. Quantitative toxicity claims require a source reporting the exact molecule, assay, endpoint, dose, test system and conditions. A peptide sequence's calculated properties are not a toxicity prediction. Sequence-only data cannot establish how much toxin is present in venom. Quantitative abundance requires measured toxin mass and total venom mass from the same sample; sample concentration requires measured mass and volume. The interface labels these as user-entered measurements and does not invent values.
