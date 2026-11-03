<?php
$stmt=$pdo->query("SELECT cancer_type_id,cancer_name FROM cancer_types ORDER BY cancer_name");
echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
