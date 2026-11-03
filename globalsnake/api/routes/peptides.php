<?php
$q = trim($_GET['q'] ?? '');
$venom = trim($_GET['venom'] ?? '');
$cancer = trim($_GET['cancer'] ?? '');
$limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
$sql = "SELECT p.peptide_id,p.public_id,p.peptide_name,p.sequence,p.sequence_length,p.sequence_source_status,p.curation_status,p.venom_verification_status,p.biological_nature,
COUNT(DISTINCT aa.activity_id) activity_records,COUNT(DISTINCT pm.peptide_mechanism_id) mechanism_records,COUNT(DISTINCT s.structure_id) structures,GROUP_CONCAT(DISTINCT s.pdb_id ORDER BY s.pdb_id SEPARATOR ',') pdb_ids,
GROUP_CONCAT(DISTINCT so.venom_group ORDER BY so.venom_group SEPARATOR ', ') venom_groups,
GROUP_CONCAT(DISTINCT CONCAT(COALESCE(so.common_name,so.scientific_name),' (',so.scientific_name,')') ORDER BY so.scientific_name SEPARATOR '; ') animal_sources,
MIN(pub.publication_year) first_evidence_year,MAX(pub.publication_year) latest_evidence_year
FROM peptides p
LEFT JOIN activity_assays aa ON p.peptide_id=aa.peptide_id
LEFT JOIN peptide_mechanisms pm ON p.peptide_id=pm.peptide_id
LEFT JOIN structures s ON p.peptide_id=s.peptide_id
LEFT JOIN peptide_sources ps ON p.peptide_id=ps.peptide_id
LEFT JOIN source_organisms so ON ps.organism_id=so.organism_id
LEFT JOIN publications psrc ON ps.publication_id=psrc.publication_id
LEFT JOIN publications pub ON pub.publication_id IN (SELECT aa2.publication_id FROM activity_assays aa2 WHERE aa2.peptide_id=p.peptide_id UNION SELECT pm2.publication_id FROM peptide_mechanisms pm2 WHERE pm2.peptide_id=p.peptide_id)
LEFT JOIN cell_lines cl ON aa.cell_line_id=cl.cell_line_id
LEFT JOIN cancer_types ct ON cl.cancer_type_id=ct.cancer_type_id
WHERE 1=1";
$params=[];
if($q!==''){$sql.=" AND (p.peptide_name LIKE :q OR p.public_id LIKE :q2 OR p.sequence LIKE :q3 OR so.scientific_name LIKE :q4 OR so.common_name LIKE :q5 OR so.venom_group LIKE :q6 OR ct.cancer_name LIKE :q7 OR cl.cell_line_name LIKE :q8)"; foreach(['q','q2','q3','q4','q5','q6','q7','q8'] as $k)$params[':'.$k]="%$q%";}
if($venom!==''){$sql.=' AND so.venom_group=:venom';$params[':venom']=$venom;}
if($cancer!==''){$sql.=' AND ct.cancer_name=:cancer';$params[':cancer']=$cancer;}
$sql.=' GROUP BY p.peptide_id ORDER BY p.peptide_name LIMIT '.$limit;
$stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
$related=[];
if(!$rows){$parts=[];$rp=[]; if($q!==''){$parts[]='(p.peptide_name LIKE :rq OR p.public_id LIKE :rq2 OR p.sequence LIKE :rq3 OR so.venom_group LIKE :rq4 OR so.scientific_name LIKE :rq5 OR so.common_name LIKE :rq6)'; foreach(['rq','rq2','rq3','rq4','rq5','rq6'] as $k)$rp[':'.$k]="%$q%";} if($venom!==''){$parts[]='so.venom_group=:rvenom';$rp[':rvenom']=$venom;} if($cancer!==''){$parts[]='ct.cancer_name=:rcancer';$rp[':rcancer']=$cancer;} if($parts){$sqlr="SELECT DISTINCT p.public_id,p.peptide_name,p.sequence_length,so.venom_group FROM peptides p LEFT JOIN peptide_sources ps ON p.peptide_id=ps.peptide_id LEFT JOIN source_organisms so ON ps.organism_id=so.organism_id LEFT JOIN activity_assays aa ON p.peptide_id=aa.peptide_id LEFT JOIN cell_lines cl ON aa.cell_line_id=cl.cell_line_id LEFT JOIN cancer_types ct ON cl.cancer_type_id=ct.cancer_type_id WHERE ".implode(' OR ',$parts)." ORDER BY p.peptide_name LIMIT 10";$stmt=$pdo->prepare($sqlr);$stmt->execute($rp);$related=$stmt->fetchAll();}}
$dbCount=(int)$pdo->query("SELECT COUNT(*) FROM peptides")->fetchColumn();
echo json_encode(['success'=>true,'data'=>$rows,'count'=>count($rows),'related'=>$related,'database_empty'=>$dbCount===0,'total_records'=>$dbCount]);
