"""Import up to 200 reviewed animal-toxin records from the official UniProtKB REST API.
Source annotations are preserved as annotations, not converted into toxicity scores.
Run from the project root: python ingestion/sync_uniprot_toxins.py
"""
import requests
import mysql.connector
from datetime import datetime

API = "https://rest.uniprot.org/uniprotkb/search"
QUERY = '(taxonomy_id:33208) AND (cc_tissue_specificity:venom OR keyword:KW-0800) AND (reviewed:true)'
LIMIT = 200

def first_name(entry):
    desc = entry.get("proteinDescription", {})
    for key in ("recommendedName", "submittedName"):
        items = desc.get(key, [])
        if isinstance(items, list) and items:
            full = items[0].get("fullName", {})
            if full.get("value"):
                return full["value"]
        if isinstance(items, dict):
            full = items.get("fullName", {})
            if full.get("value"):
                return full["value"]
    return entry.get("uniProtkbId") or entry.get("primaryAccession", "Unnamed toxin protein")

def comments(entry, wanted):
    values = []
    for item in entry.get("comments", []):
        if item.get("commentType", "").upper() == wanted.upper():
            for text in item.get("texts", []):
                value = text.get("value")
                if value and value not in values:
                    values.append(value)
    return " | ".join(values)[:60000] or None

def main():
    params = {"query": QUERY, "format": "json", "size": LIMIT}
    response = requests.get(API, params=params, timeout=90,
                            headers={"User-Agent": "VenomOncoDB academic research importer"})
    response.raise_for_status()
    payload = response.json()
    entries = payload.get("results", [])
    if not entries:
        raise RuntimeError("UniProt returned no results. Check the API query or network access.")
    conn = mysql.connector.connect(host="localhost", user="root", password="", database="venomoncodb")
    cur = conn.cursor()
    inserted = updated = skipped = 0
    try:
        release = response.headers.get("x-uniprot-release", "not provided")
        for e in entries:
            accession = (e.get("primaryAccession") or "").strip()
            seq = (e.get("sequence") or {}).get("value", "")
            organism = e.get("organism") or {}
            name = first_name(e)
            if not accession or not seq or not organism.get("scientificName"):
                skipped += 1
                continue
            url = "https://www.uniprot.org/uniprotkb/" + accession + "/entry"
            review = e.get("entryType", "UniProtKB record")
            args = (accession, name, organism.get("scientificName"),
                    organism.get("taxonId"), seq, len(seq), review,
                    comments(e, "FUNCTION"), comments(e, "TISSUE SPECIFICITY"),
                    url, "UniProtKB/Tox-Prot", release)
            cur.execute("SELECT toxin_id FROM toxin_catalog WHERE accession=%s", (accession,))
            exists = cur.fetchone()
            cur.execute("""INSERT INTO toxin_catalog
              (accession,toxin_name,organism_name,taxonomy_id,sequence,sequence_length,
               entry_review_status,function_text,tissue_specificity,source_url,source_database,source_release)
              VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
              ON DUPLICATE KEY UPDATE toxin_name=VALUES(toxin_name),
               organism_name=VALUES(organism_name),taxonomy_id=VALUES(taxonomy_id),
               sequence=VALUES(sequence),sequence_length=VALUES(sequence_length),
               entry_review_status=VALUES(entry_review_status),function_text=VALUES(function_text),
               tissue_specificity=VALUES(tissue_specificity),source_url=VALUES(source_url),
               source_database=VALUES(source_database),source_release=VALUES(source_release)""", args)
            if exists: updated += 1
            else: inserted += 1
        conn.commit()
        cur.execute("SELECT COUNT(*) FROM toxin_catalog")
        total = cur.fetchone()[0]
        print(f"UniProt release: {release}")
        print(f"Source entries returned: {len(entries)}; inserted: {inserted}; refreshed: {updated}; skipped: {skipped}")
        print(f"Total unique toxin catalog records in local database: {total}")
        if total < 100:
            print("NOTE: Fewer than 100 records are currently stored. Re-run after checking UniProt connectivity/query and confirm total before claiming the dataset target is met.")
    finally:
        cur.close()
        conn.close()

if __name__ == "__main__":
    main()
