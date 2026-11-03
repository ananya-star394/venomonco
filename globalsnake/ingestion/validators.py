import re

VALID_AMINO_ACIDS = set("ACDEFGHIKLMNPQRSTVWY")

def clean_sequence(sequence):
    if sequence is None:
        return None
    return str(sequence).upper().replace(" ", "").replace("\n", "").strip()

def validate_sequence(sequence):
    sequence = clean_sequence(sequence)
    if not sequence:
        return False
    return all(aa in VALID_AMINO_ACIDS for aa in sequence)

def sequence_length(sequence):
    sequence = clean_sequence(sequence)
    if not validate_sequence(sequence):
        return None
    return len(sequence)

def validate_pmid(pmid):
    if not pmid:
        return False
    return bool(re.fullmatch(r"\d+", str(pmid)))

def validate_pdb_id(pdb_id):
    if not pdb_id:
        return False
    return bool(re.fullmatch(r"[0-9A-Za-z]{4}", str(pdb_id)))
