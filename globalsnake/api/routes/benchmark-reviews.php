<?php
// Independent annotations for an existing benchmark record. Reviewer codes are labels, not authenticated identities.
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$labels = ['positive','negative','unclear'];
function br_json($payload, $status=200) { http_response_code($status); echo json_encode($payload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); }
function br_input() { $d=json_decode(file_get_contents('php://input'),true); return is_array($d)?$d:[]; }
function br_clean($v,$max=10000) { $s=trim((string)($v ?? '')); return function_exists('mb_substr')?mb_substr($s,0,$max,'UTF-8'):substr($s,0,$max); }
try {
 if ($method==='GET') {
  $q=$pdo->query("SELECT review_id AS id, benchmark_id AS benchmarkId, reviewer_code AS reviewer, review_label AS label, review_rationale AS rationale, evidence_location AS location, protocol_version AS protocolVersion, DATE_FORMAT(reviewed_at,'%Y-%m-%dT%H:%i:%sZ') AS reviewedAt FROM benchmark_reviews ORDER BY benchmark_id, reviewer_code");
  br_json(['success'=>true,'reviews'=>$q->fetchAll()]); exit;
 }
 if ($method!=='POST') { header('Allow: GET, POST'); br_json(['success'=>false,'error'=>'Method not allowed.'],405); exit; }
 $d=br_input(); $id=(int)($d['benchmarkId']??0); $reviewer=br_clean($d['reviewer']??'',120); $label=$d['label']??''; $rationale=br_clean($d['rationale']??'',10000); $location=br_clean($d['location']??'',500);
 if ($id<1 || $reviewer==='' || !in_array($label,$labels,true) || $rationale==='') { br_json(['success'=>false,'error'=>'Existing benchmark ID, reviewer code, valid label, and rationale are required.'],400); exit; }
 $check=$pdo->prepare('SELECT benchmark_id FROM benchmark_records WHERE benchmark_id=?'); $check->execute([$id]); if (!$check->fetch()) { br_json(['success'=>false,'error'=>'Benchmark record not found. Create the benchmark record first.'],404); exit; }
 $pdo->beginTransaction();
 $old=$pdo->prepare('SELECT review_id FROM benchmark_reviews WHERE benchmark_id=? AND reviewer_code=?'); $old->execute([$id,$reviewer]); $existing=$old->fetchColumn();
 if ($existing) {
  $s=$pdo->prepare("UPDATE benchmark_reviews SET review_label=?,review_rationale=?,evidence_location=?,protocol_version='v2.7' WHERE review_id=?"); $s->execute([$label,$rationale,$location?:null,$existing]); $rid=(int)$existing; $action='UPDATE';
 } else {
  $s=$pdo->prepare("INSERT INTO benchmark_reviews (benchmark_id,reviewer_code,review_label,review_rationale,evidence_location,protocol_version) VALUES (?,?,?,?,?,'v2.7')"); $s->execute([$id,$reviewer,$label,$rationale,$location?:null]); $rid=(int)$pdo->lastInsertId(); $action='CREATE';
 }
 $a=$pdo->prepare('INSERT INTO benchmark_review_audit (review_id,benchmark_id,reviewer_code,action_type,event_payload) VALUES (?,?,?,?,?)'); $a->execute([$rid,$id,$reviewer,$action,json_encode(['label'=>$label,'location'=>$location?:null])]);
 $pdo->commit(); br_json(['success'=>true,'reviewId'=>$rid,'action'=>$action],$action==='CREATE'?201:200);
} catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); br_json(['success'=>false,'error'=>'Review operation failed. Confirm that migrate_v27_independent_reviews.sql was imported and the benchmark record exists.'],500); }
