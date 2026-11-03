<?php
$q = trim($_GET['q'] ?? '');
$limit = min(150, max(1, (int)($_GET['limit'] ?? 120)));
// Generic searches should open the catalogue rather than fail because the word "snake" is not part of every species name.
$broad = ($q === '' || preg_match('/^(snake|snakes|venom|venomous|all|all snakes|snake venom|toxin|toxins|healthcare|healthcare use|medicine|medical|drug|drug discovery)$/i', $q));
$sql = "SELECT scientific_name, common_name, record_type, anticancer_evidence_status, evidence_note, taxonomy_reference,
CASE
 WHEN scientific_name='Bothrops jararaca' THEN 'Drug-discovery source: bradykinin-potentiating peptides inspired ACE-inhibitor drugs such as captopril; the drug is synthetic and this is not an anticancer treatment.'
 WHEN scientific_name='Echis carinatus' THEN 'Drug-discovery source: echistatin inspired the antiplatelet drug tirofiban; this is cardiovascular/antiplatelet research, not anticancer treatment.'
 WHEN scientific_name IN ('Sistrurus miliarius barbouri','Sistrurus miliarius') THEN 'Drug-discovery source: barbourin inspired the antiplatelet drug eptifibatide; this is cardiovascular/antiplatelet research, not anticancer treatment.'
 WHEN scientific_name IN ('Bothrops atrox','Bothrops moojeni') THEN 'Venom-derived enzyme batroxobin has been investigated/used in some regions for fibrinogen/thrombotic indications; regulatory status varies by country. Not an anticancer treatment.'
 WHEN scientific_name='Naja naja atra' OR scientific_name='Naja atra' THEN 'Cobrotoxin/cobratide has been reported in analgesic research and some regional use; verify jurisdiction and source before making a clinical claim. Not an anticancer treatment.'
 ELSE 'No species-specific healthcare application is curated in this catalogue yet. Venom components are studied as biochemical tools and drug-discovery leads; do not infer a treatment indication for this species.'
END AS healthcare_research_use,
CASE
 WHEN scientific_name IN ('Bothrops jararaca','Echis carinatus','Sistrurus miliarius barbouri','Sistrurus miliarius') THEN 'Published drug-design lineage; current development/trial status not inferred from this source.'
 WHEN scientific_name IN ('Bothrops atrox','Bothrops moojeni') THEN 'Reported regional use/research; regulatory status varies by jurisdiction and needs current checking.'
 WHEN scientific_name IN ('Naja naja atra','Naja atra') THEN 'Analgesic research/use has been reported in literature; current regulatory status requires jurisdiction-specific verification.'
 ELSE 'Species-specific healthcare application not curated here; ongoing research status unknown until a dated source is reviewed.'
END AS healthcare_research_status,
CASE
 WHEN scientific_name='Bothrops jararaca' THEN 'https://pubmed.ncbi.nlm.nih.gov/21447352/'
 WHEN scientific_name='Echis carinatus' THEN 'https://pubmed.ncbi.nlm.nih.gov/31137917/'
 WHEN scientific_name IN ('Sistrurus miliarius barbouri','Sistrurus miliarius') THEN 'https://pubmed.ncbi.nlm.nih.gov/31137917/'
 WHEN scientific_name IN ('Bothrops atrox','Bothrops moojeni') THEN 'https://pmc.ncbi.nlm.nih.gov/articles/PMC10605524/'
 WHEN scientific_name='Naja naja atra' OR scientific_name='Naja atra' THEN 'https://www.frontiersin.org/journals/bioengineering-and-biotechnology/articles/10.3389/fbioe.2021.811905/full'
 ELSE CONCAT('https://pubmed.ncbi.nlm.nih.gov/?term=',REPLACE(scientific_name,' ','+'),'+venom+therapeutic+research')
END AS healthcare_source
FROM snake_venom_catalog";
$params = [];
if ($q !== '' && !$broad) { $sql .= " WHERE scientific_name LIKE :q OR common_name LIKE :q2 OR (:q4 LIKE '%captopril%' AND scientific_name='Bothrops jararaca') OR (:q5 LIKE '%tirofiban%' AND scientific_name='Echis carinatus') OR (:q6 LIKE '%eptifibatide%' AND scientific_name LIKE 'Sistrurus miliarius%') OR (:q7 LIKE '%batroxobin%' AND scientific_name IN ('Bothrops atrox','Bothrops moojeni')) OR (:q8 LIKE '%cobrotoxin%' AND scientific_name IN ('Naja atra','Naja naja atra'))"; $params[':q'] = "%$q%"; $params[':q2'] = "%$q%"; foreach ([':q4',':q5',':q6',':q7',':q8'] as $k) $params[$k] = strtolower($q); }
$sql .= " ORDER BY scientific_name LIMIT " . $limit;
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
echo json_encode(['success'=>true,'data'=>$rows,'count'=>count($rows),'total_catalog_records'=>(int)$pdo->query("SELECT COUNT(*) FROM snake_venom_catalog")->fetchColumn(),'query_mode'=>$broad?'catalogue':'filtered']);
