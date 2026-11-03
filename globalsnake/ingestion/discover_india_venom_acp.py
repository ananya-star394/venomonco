"""Discover India-relevant venom/anticancer literature candidates from PubMed.
This creates a literature review queue, NOT verified peptide records. Every candidate
must be manually checked for animal origin, peptide sequence and experimental cancer evidence.
"""
import csv, time, requests, xml.etree.ElementTree as ET
from pathlib import Path
BASE='https://eutils.ncbi.nlm.nih.gov/entrez/eutils/'
QUERIES=[
 '((venom OR toxin OR venom-derived OR scorpion OR snake OR spider OR bee OR wasp OR cone snail) AND (anticancer OR antitumor OR tumour OR tumor OR cancer cell OR cytotoxic)) AND (India OR Indian)',
 '((snake venom OR scorpion venom OR spider venom OR bee venom) AND (cancer OR anticancer OR antitumor)) AND (India OR Indian)',
 '((venom peptide OR toxin peptide OR conotoxin OR chlorotoxin OR crotamine OR lycosin OR BmK AGAP OR Smp24) AND (cancer OR anticancer OR tumor))'
]
def get(url, params):
 r=requests.get(BASE+url,params=params,timeout=45,headers={'User-Agent':'VenomOncoDB academic literature discovery contact: local project'})
 r.raise_for_status(); return r

def main():
 ids=[]
 for q in QUERIES:
  root=ET.fromstring(get('esearch.fcgi',{'db':'pubmed','term':q,'retmax':500,'retmode':'xml'}).text)
  ids.extend(x.text for x in root.findall('.//Id') if x.text)
  time.sleep(.35)
 ids=list(dict.fromkeys(ids))
 rows=[]
 for start in range(0,len(ids),100):
  root=ET.fromstring(get('efetch.fcgi',{'db':'pubmed','id':','.join(ids[start:start+100]),'retmode':'xml'}).text)
  for a in root.findall('.//PubmedArticle'):
   pmid=a.findtext('.//PMID') or ''
   title=' '.join(''.join(a.find('.//ArticleTitle').itertext()).split()) if a.find('.//ArticleTitle') is not None else ''
   abstract=' '.join(' '.join(x.itertext()) for x in a.findall('.//Abstract/AbstractText'))
   year=a.findtext('.//PubDate/Year') or ''
   journal=a.findtext('.//Journal/Title') or ''
   doi=''
   for aid in a.findall('.//ArticleId'):
    if aid.attrib.get('IdType')=='doi': doi=aid.text or ''; break
   rows.append([pmid,year,title,journal,doi,abstract[:5000],f'https://pubmed.ncbi.nlm.nih.gov/{pmid}/','Candidate only - manual verification required'])
 out=Path(__file__).resolve().parent/'india_venom_acp_literature_candidates.csv'
 with out.open('w',newline='',encoding='utf-8-sig') as f:
  w=csv.writer(f); w.writerow(['PMID','Year','Title','Journal','DOI','Abstract_excerpt','PubMed_URL','Curation_status']); w.writerows(rows)
 print(f'Wrote {len(rows)} unique PubMed literature candidates to {out.name}.')
 print('These are papers, not peptide records. Verify sequence, venom origin, Indian geographic relevance, cancer assay, and source before adding peptide rows.')
if __name__=='__main__': main()
