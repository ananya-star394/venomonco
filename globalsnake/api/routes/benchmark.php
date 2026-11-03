<?php
// Research prototype endpoint. Deploy only behind appropriate authentication/authorization for multi-user use.
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$valid = ['positive','negative','unclear'];
function benchmark_json($payload, $status=200) { http_response_code($status); echo json_encode($payload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); }
function benchmark_input() { $raw=file_get_contents('php://input'); $d=json_decode($raw,true); return is_array($d)?$d:[]; }
function benchmark_clean($v,$max=5000) { $s=trim((string)($v ?? '')); return function_exists('mb_substr') ? mb_substr($s,0,$max,'UTF-8') : substr($s,0,$max); }
try {
 if ($method === 'GET') {
  $q=$pdo->query("SELECT benchmark_id AS id, claim_identifier AS claim, source_identifier AS source, evidence_location AS location, cancer_model AS model, reference_label AS reference, system_label AS system, adjudication_rationale AS adjudication, reviewer_id AS reviewer, protocol_version AS protocolVersion, DATE_FORMAT(created_at,'%Y-%m-%dT%H:%i:%sZ') AS createdAt FROM benchmark_records ORDER BY benchmark_id ASC");
  benchmark_json(['success'=>true,'records'=>$q->fetchAll()]); exit;
 }
 $d=benchmark_input(); $reviewer=benchmark_clean($d['reviewer'] ?? '',120);
 if ($reviewer==='') { benchmark_json(['success'=>false,'error'=>'Reviewer ID is required. Use an agreed anonymous reviewer code if appropriate.'],400); exit; }
 if ($method === 'POST') {
  $claim=benchmark_clean($d['claim'] ?? '',255); $source=benchmark_clean($d['source'] ?? '',500); $location=benchmark_clean($d['location'] ?? '',500); $model=benchmark_clean($d['model'] ?? '',255); $ref=$d['reference'] ?? ''; $sys=$d['system'] ?? ''; $rationale=benchmark_clean($d['adjudication'] ?? '',10000);
  if (!$claim || !$source || !$rationale || !in_array($ref,$valid,true) || !in_array($sys,$valid,true)) { benchmark_json(['success'=>false,'error'=>'Claim, source, rationale and valid reference/system labels are required.'],400); exit; }
  $pdo->beginTransaction(); $s=$pdo->prepare('INSERT INTO benchmark_records (claim_identifier,source_identifier,evidence_location,cancer_model,reference_label,system_label,adjudication_rationale,reviewer_id) VALUES (?,?,?,?,?,?,?,?)'); $s->execute([$claim,$source,$location ?: null,$model ?: null,$ref,$sys,$rationale,$reviewer]); $id=(int)$pdo->lastInsertId();
  $a=$pdo->prepare("INSERT INTO benchmark_audit (benchmark_id,action_type,reviewer_id,event_payload) VALUES (?,'CREATE',?,?)"); $a->execute([$id,$reviewer,json_encode(['claim'=>$claim,'source'=>$source,'reference'=>$ref,'system'=>$sys])]); $pdo->commit(); benchmark_json(['success'=>true,'id'=>$id],201); exit;
 }
 if ($method === 'DELETE') {
  $id=(int)($d['id'] ?? 0);
  if ($id>0) { $s=$pdo->prepare('SELECT benchmark_id,claim_identifier,source_identifier FROM benchmark_records WHERE benchmark_id=?'); $s->execute([$id]); $old=$s->fetch(); if (!$old) { benchmark_json(['success'=>false,'error'=>'Record not found.'],404); exit; } $pdo->beginTransaction(); $a=$pdo->prepare("INSERT INTO benchmark_audit (benchmark_id,action_type,reviewer_id,event_payload) VALUES (?,'DELETE',?,?)"); $a->execute([$id,$reviewer,json_encode($old)]); $del=$pdo->prepare('DELETE FROM benchmark_records WHERE benchmark_id=?'); $del->execute([$id]); $pdo->commit(); benchmark_json(['success'=>true]); exit; }
  if (($d['confirmAll'] ?? false) === true) { $count=(int)$pdo->query('SELECT COUNT(*) FROM benchmark_records')->fetchColumn(); $pdo->beginTransaction(); $a=$pdo->prepare("INSERT INTO benchmark_audit (action_type,reviewer_id,event_payload) VALUES ('BULK_DELETE',?,?)"); $a->execute([$reviewer,json_encode(['deleted_count'=>$count])]); $pdo->exec('DELETE FROM benchmark_records'); $pdo->commit(); benchmark_json(['success'=>true,'deleted'=>$count]); exit; }
  benchmark_json(['success'=>false,'error'=>'Provide record id or explicit confirmAll.'],400); exit;
 }
 header('Allow: GET, POST, DELETE'); benchmark_json(['success'=>false,'error'=>'Method not allowed.'],405);
} catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); benchmark_json(['success'=>false,'error'=>'Benchmark database operation failed. Confirm that migrate_v26_benchmark.sql was imported.'],500); }
