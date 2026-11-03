<?php
$q = trim($_GET['q'] ?? '');
$limit = min(100, max(1, (int)($_GET['limit'] ?? 25)));
$count = (int)$pdo->query("SELECT COUNT(*) FROM toxin_catalog")->fetchColumn();
$sql = "SELECT accession,toxin_name,organism_name,taxonomy_id,sequence_length,entry_review_status,function_text,tissue_specificity,source_url FROM toxin_catalog";
$params = [];
if ($q !== '') {
  $sql .= " WHERE accession LIKE :q1 OR toxin_name LIKE :q2 OR organism_name LIKE :q3 OR function_text LIKE :q4 OR tissue_specificity LIKE :q5";
  foreach (['q1','q2','q3','q4','q5'] as $k) $params[':'.$k] = '%'.$q.'%';
}
$sql .= " ORDER BY toxin_name LIMIT ".$limit;
$stmt=$pdo->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll();
echo json_encode(['success'=>true,'data'=>$rows,'count'=>count($rows),'total_records'=>$count]);
