<?php
$queries=[
 'peptides'=>'SELECT COUNT(*) n FROM peptides',
 'verified_venom_peptides'=>"SELECT COUNT(*) n FROM peptides WHERE venom_verification_status='Verified' AND biological_nature='Natural'",
 'review_peptides'=>"SELECT COUNT(*) n FROM peptides WHERE venom_verification_status<>'Verified'",
 'organisms'=>'SELECT COUNT(DISTINCT organism_id) n FROM peptide_sources',
 'activity_records'=>"SELECT COUNT(*) n FROM activity_assays a JOIN peptides p ON p.peptide_id=a.peptide_id WHERE p.venom_verification_status='Verified' AND p.biological_nature='Natural'",
 'mechanism_records'=>"SELECT COUNT(*) n FROM peptide_mechanisms m JOIN peptides p ON p.peptide_id=m.peptide_id WHERE p.venom_verification_status='Verified' AND p.biological_nature='Natural'",
 'structures'=>"SELECT COUNT(*) n FROM structures s JOIN peptides p ON p.peptide_id=s.peptide_id WHERE p.venom_verification_status='Verified' AND p.biological_nature='Natural'",
 'publications'=>"SELECT COUNT(DISTINCT pub.publication_id) n FROM publications pub JOIN (SELECT publication_id FROM activity_assays a JOIN peptides p ON p.peptide_id=a.peptide_id WHERE p.venom_verification_status='Verified' AND p.biological_nature='Natural' UNION SELECT publication_id FROM peptide_mechanisms m JOIN peptides p ON p.peptide_id=m.peptide_id WHERE p.venom_verification_status='Verified' AND p.biological_nature='Natural') x ON x.publication_id=pub.publication_id",
 'cell_lines'=>'SELECT COUNT(*) n FROM cell_lines',
 'cancer_types'=>'SELECT COUNT(*) n FROM cancer_types'
];
$out=[]; foreach($queries as $k=>$sql){$out[$k]=(int)$pdo->query($sql)->fetch()['n'];}
echo json_encode(['success'=>true,'data'=>$out]);
