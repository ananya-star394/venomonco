import requests, time
from db import get_connection
from validators import clean_sequence, sequence_length
from provenance import add_audit_record

BASE = 'https://webs.iiitd.edu.in/raghava/cancerppd2/api/api.php'
CANCER_TYPES = ['Breast Cancer','Lung Cancer','Ovarian Cancer','Cervical Cancer','Colon Cancer','Skin Cancer','Blood Cancer','Prostate Cancer','Liver Cancer']

def get_data(cancer):
    r = requests.get(BASE, params={'dataType':'cancer_type','dataValue':cancer}, timeout=30)
    r.raise_for_status()
    return r.json().get('data', [])

def run():
    db = get_connection(); cur = db.cursor()
    cur.execute("INSERT INTO ingestion_runs(source_name,started_at,status) VALUES('CancerPPD2',NOW(),'running')")
    run_id = cur.lastrowid; seen = imported = skipped = 0
    for cancer in CANCER_TYPES:
        try: rows = get_data(cancer)
        except Exception as e: print('ERROR', cancer, e); continue
        for row in rows:
            seen += 1
            name = str(row.get('Name') or '').strip()
            ext = str(row.get('CancerPPD2 ID') or '').strip()
            if not name and not ext: skipped += 1; continue
            public_id = 'CPPD2-' + (ext or str(seen))
            cur.execute('SELECT peptide_id FROM peptides WHERE public_id=%s', (public_id,))
            if cur.fetchone(): continue
            pmid = str(row.get('PMID') or '').strip() or None
            title = row.get('Title') or ('CancerPPD2 record ' + ext)
            pub_id = None
            if pmid:
                cur.execute('SELECT publication_id FROM publications WHERE pmid=%s', (pmid,)); p = cur.fetchone(); pub_id = p[0] if p else None
            if pub_id is None:
                cur.execute("INSERT INTO publications(pmid,title,publication_year,source_type,source_url) VALUES(%s,%s,%s,'Database',%s)", (pmid,title,row.get('Year'),'https://webs.iiitd.edu.in/raghava/cancerppd2/'))
                pub_id = cur.lastrowid
            seq = clean_sequence(row.get('Sequence'))
            cur.execute("INSERT INTO peptides(public_id,peptide_name,sequence,sequence_length,sequence_source_status,curation_status) VALUES(%s,%s,%s,%s,'External database verified','Needs review')", (public_id,name,seq,sequence_length(seq)))
            pid = cur.lastrowid
            add_audit_record(cur,'peptides',pid,'external_record_id','CancerPPD2',ext,'https://webs.iiitd.edu.in/raghava/cancerppd2/','VenomOncoDB','Imported','Candidate discovery only; venom classification and biological interpretation require manual verification.')
            imported += 1
        time.sleep(.2)
    cur.execute("UPDATE ingestion_runs SET finished_at=NOW(),records_seen=%s,records_imported=%s,records_skipped=%s,status='completed' WHERE run_id=%s", (seen,imported,skipped,run_id))
    db.commit(); cur.close(); db.close(); print(f'CancerPPD2 sync complete: {seen} seen, {imported} imported, {skipped} skipped.')

if __name__ == '__main__': run()
