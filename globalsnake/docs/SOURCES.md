# Authoritative source register

These are the external resources used by the VenomOncoDB methodology. The application does not treat a source as proof beyond the fields that source actually documents.

## CancerPPD2

Purpose: anticancer peptide/protein candidate discovery and experimental context.

- Website: https://webs.iiitd.edu.in/raghava/cancerppd2/
- REST API documentation: https://webs.iiitd.edu.in/raghava/cancerppd2/api/rest.html
- Download sequences: https://webs.iiitd.edu.in/raghava/cancerppd2/down_seq.html
- Browse by length: https://webs.iiitd.edu.in/raghava/cancerppd2/above100.php
- Publication: Chauhan M, Gupta A, Tomer R, Raghava GPS. CancerPPD2: an updated repository of anticancer peptides and proteins. Database. 2025;baaf030. DOI: https://doi.org/10.1093/database/baaf030

## UniProt / Tox-Prot

Purpose: authoritative venom-protein annotation and sequence cross-checking.

- Tox-Prot: https://www.uniprot.org/help/Toxins/contributors
- UniProtKB: https://www.uniprot.org/UniProt

## PubMed

Purpose: primary publication and experimental-mechanism verification.

- https://pubmed.ncbi.nlm.nih.gov/

## RCSB Protein Data Bank

Purpose: experimentally determined structure verification and structure metadata.

- https://www.rcsb.org/
- File services: https://www.rcsb.org/docs/programmatic-access/file-download-services

Example of a verified scorpion-toxin structure used for testing the viewer (not inserted into the dataset unless its peptide linkage is separately curated):

- PDB 7X41: https://www.rcsb.org/structure/7X41

## Data-use rule

The platform stores only values supported by the source record being cited. Derived sequence statistics such as length, amino-acid composition, cysteine count and hydrophobic fraction are labelled as calculations from the stored sequence. They are not presented as experimentally measured biological properties.

## Primary literature used in the starter layer

- Lycosin-I: Shen et al., 2018, PMID 29763387, DOI 10.1177/1535370218772802.
- Smp24: Nguyen et al., 2022, PMID 36287985, DOI 10.3390/toxins14100717.
- BmK AGAP: Kampo et al., 2019, PMID 30740360, DOI 10.3389/fonc.2019.00021.
- Crotamine: Pereira et al., 2011, PMID 21834748, DOI 10.1517/13543784.2011.602064; additional mechanistic evidence DOI 10.1021/mp2000605.
- Chlorotoxin: Soroceanu et al., 1998, PMID 9809993; mechanistic evidence DOI 10.1074/jbc.M205662200; structure example PDB 7X41.
