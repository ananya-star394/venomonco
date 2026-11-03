import csv
from db import get_connection
from validators import clean_sequence, validate_sequence, sequence_length
from provenance import add_audit_record

def import_peptides(filename):
    connection = get_connection()
    cursor = connection.cursor()

    with open(filename, "r", encoding="utf-8-sig") as file:
        reader = csv.DictReader(file)

        for row in reader:
            public_id = row["public_id"].strip()
            name = row["peptide_name"].strip()
            sequence = clean_sequence(row.get("sequence"))

            if sequence and not validate_sequence(sequence):
                print("Skipped invalid amino-acid sequence:", name)
                continue

            length = sequence_length(sequence)

            sql = '''
                INSERT INTO peptides
                (public_id, peptide_name, sequence, sequence_length,
                 uniprot_accession, sequence_source_status, curation_status)
                VALUES (%s,%s,%s,%s,%s,%s,%s)
            '''

            values = (
                public_id,
                name,
                sequence if sequence else None,
                length,
                row.get("uniprot_accession") or None,
                row.get("sequence_source_status") or "Unavailable",
                row.get("curation_status") or "Needs review"
            )

            cursor.execute(sql, values)
            peptide_id = cursor.lastrowid

            add_audit_record(
                cursor, "peptides", peptide_id, "sequence",
                row.get("source_type") or "Manual curation",
                row.get("source_identifier") or "",
                row.get("source_url") or "",
                row.get("curator") or "VenomOncoDB",
                "Imported",
                row.get("notes") or None
            )

    connection.commit()
    cursor.close()
    connection.close()
    print("Peptide import completed.")

if __name__ == "__main__":
    import_peptides("peptides.csv")
