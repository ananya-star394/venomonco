# VenomOncoDB Verification Standard

## Inclusion in the verified venom layer
A record is counted as a verified venom peptide only when the available source evidence supports all of the following: (1) peptide identity/sequence is traceable; (2) the source organism is explicitly associated with snake, scorpion, spider, or cone-snail venom; (3) anticancer or tumor-related experimental evidence is documented; and (4) provenance is retained through a publication/database identifier.

## Exclusions
Synthetic analogues, engineered derivatives, recombinant constructs, crude venom/fractions, proteins/enzymes that are not appropriate peptide records, predicted-only activity, and records with unestablished venom origin are not counted as verified natural venom peptides. They may be retained in a review queue when useful, but never inflate the verified count.

## Missing evidence
Missing IC50, mechanism, structure, or cell-line information is stored as missing/Not reported. Absence of a value is never converted into a negative biological conclusion.

## Structure mapping
A PDB structure is linked only when a structure identifier is traceable. If the deposited construct differs from the mature peptide sequence, the database records `Construct differs` rather than silently treating it as an exact sequence match.

## Evidence levels
- Verified: explicit venom origin + traceable sequence + experimental anticancer evidence.
- Needs review: useful source record requiring primary-paper verification.
- Not established: insufficient evidence for venom confirmation.
