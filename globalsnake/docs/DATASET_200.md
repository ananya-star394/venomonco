# Dataset policy — 200 source-backed records, with verification labels

The source importer can ingest up to 200 unique records from official CancerPPD2 browse tables. The number imported is determined by the source pages and successful parsing; it is not padded with synthetic rows.

Crucially, **200 source-backed anticancer-peptide records is not the same as 200 verified venom-derived anticancer peptides**. Records with explicit venom-origin support can enter the verified venom layer. Records without an explicit venom origin are kept as `Needs review` candidates and must not be presented as confirmed venom peptides.

Report these counts separately:
- All unique CancerPPD2 source records imported.
- Records with explicit venom-origin support.
- Records still marked `Needs review`.
- Distinct peptides with experimental anticancer evidence, with the cell line/assay/publication retained.

Do not invent sequences, IC50 values, mechanisms, structures or citations. A missing value means unreported/unknown, not a negative result.
