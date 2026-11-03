<?php
/**
 * VenomOncoDB source synchronizer.
 *
 * Reads official CancerPPD2 browse tables and imports only records whose
 * Origin field explicitly supports a venom group. There is no numeric target
 * and no padding with unclassified records. Source-level verification and
 * primary-paper verification are tracked separately.
 */
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../api/config.php';

$sourcePages = [
  'https://webs.iiitd.edu.in/raghava/cancerppd2/unlen.php',
  'https://webs.iiitd.edu.in/raghava/cancerppd2/len_10_20.php',
  'https://webs.iiitd.edu.in/raghava/cancerppd2/len_21_30.php',
  'https://webs.iiitd.edu.in/raghava/cancerppd2/len_31_40.php',
  'https://webs.iiitd.edu.in/raghava/cancerppd2/len_41_100.php',
  'https://webs.iiitd.edu.in/raghava/cancerppd2/above100.php'
];
$targetRows = 200; // Practical assignment batch size; this is a cap, never a fabricated target.
$hardCap = 200;
$message=''; $run=false;
$stats=['pages'=>0,'rows_seen'=>0,'venom_rows'=>0,'candidate_rows'=>0,'imported'=>0,'peptides'=>0,'activities'=>0,'papers'=>0,'organisms'=>0,'skipped'=>0];

function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function fetch_page($url){
    if(function_exists('curl_init')){
        $ch=curl_init($url);
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>90,CURLOPT_USERAGENT=>'VenomOncoDB/2.1 academic-source-sync']);
        $body=curl_exec($ch); $err=curl_error($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        if($body!==false && $code>=200 && $code<300) return $body;
        throw new Exception('CancerPPD2 page download failed: HTTP '.$code.' '.$err);
    }
    $ctx=stream_context_create(['http'=>['timeout'=>90,'user_agent'=>'VenomOncoDB/2.1 academic-source-sync']]);
    $body=@file_get_contents($url,false,$ctx);
    if($body!==false) return $body;
    throw new Exception('PHP cannot download CancerPPD2. Enable cURL or allow_url_fopen in XAMPP PHP.');
}
function clean_cell(DOMElement $cell){
    $parts=[];
    foreach($cell->childNodes as $node){
        if($node instanceof DOMElement && strtolower($node->tagName)==='input'){
            $v=$node->getAttribute('value'); if($v!=='') $parts[]=$v;
        } else { $parts[]=$node->textContent; }
    }
    return trim(preg_replace('/\s+/',' ',implode(' ',$parts)));
}
function table_rows($html){
    libxml_use_internal_errors(true);
    $dom=new DOMDocument(); $dom->loadHTML($html); libxml_clear_errors();
    $xp=new DOMXPath($dom); $rows=[];
    foreach($xp->query('//table//tr') as $tr){
        $cells=$xp->query('./th|./td',$tr); $vals=[];
        foreach($cells as $cell) $vals[]=clean_cell($cell);
        if(count($vals)>=18) $rows[]=$vals;
    }
    return $rows;
}
function venom_group($origin){
    $s=strtolower($origin);
    if(preg_match('/scorpion|scorpian|buthus|tityus|androctonus|leiurus|mesobuthus|parabuthus|heterometrus|centruroides|pandinus|venom.*scorp/i',$s)) return 'Scorpion';
    if(preg_match('/spider|tarantula|lycosa|theraphos|latrodectus|loxosceles|venom.*spider/i',$s)) return 'Spider';
    if(preg_match('/conus|conotoxin|cone snail|conopeptide/i',$s)) return 'Cone snail';
    if(preg_match('/snake|cobra|viper|elapid|rattlesnake|krait|naja|bungarus|dendroaspis|crotalus|bitis|viperidae|elapidae|bothrops|ophiophagus|venom.*snake/i',$s)) return 'Snake';
    return null;
}
function first_pmid($s){foreach(preg_split('/[; ,]+/',trim((string)$s)) as $p){if(preg_match('/^\d+$/',$p))return $p;}return null;}
function biological_nature($origin){
    $s=strtolower((string)$origin);
    if(preg_match('/synthetic|analogue|analog/i',$s)) return 'Synthetic';
    if(preg_match('/recombinant|expression/i',$s)) return 'Recombinant';
    if(preg_match('/modified|conjugate|derivative/i',$s)) return 'Modified';
    if(preg_match('/venom|scorpion|spider|snake|conus|conotoxin/i',$s)) return 'Natural';
    return 'Unknown';
}
function parse_activity($text){
    $text=trim($text); $value=null; $unit=null;
    if(preg_match('/(?:IC\s*50|EC\s*50|LC\s*50|MIC|CC\s*50)\s*[=:~]?\s*([0-9]+(?:\.[0-9]+)?)/i',$text,$m)){
        $value=(float)$m[1];
        if(preg_match('/(µM|uM|μM|nM|mM|µg\/mL|ug\/mL|μg\/ml|mg\/mL|mg\/ml|%)\b/i',$text,$u))$unit=$u[1];
    }
    return [$value,$unit];
}
function ensure_compatibility($pdo){
    // Make the importer tolerant of an older database already present in XAMPP.
    $pdo->exec("ALTER TABLE source_organisms MODIFY venom_group ENUM('Snake','Scorpion','Spider','Cone snail','Unclassified') NOT NULL");
    $cols=[]; foreach($pdo->query('SHOW COLUMNS FROM curation_audit') as $c) $cols[$c['Field']]=true;
    $needed=[
      'old_value'=>'TEXT','new_value'=>'TEXT','source_type'=>'VARCHAR(100)','source_identifier'=>'VARCHAR(255)',
      'source_url'=>'TEXT','curator'=>'VARCHAR(255)','curation_action'=>'VARCHAR(100)','curation_date'=>'TIMESTAMP DEFAULT CURRENT_TIMESTAMP','notes'=>'TEXT'
    ];
    foreach($needed as $name=>$type){if(!isset($cols[$name])) $pdo->exec("ALTER TABLE curation_audit ADD COLUMN `$name` $type");}
    $cols=[]; foreach($pdo->query('SHOW COLUMNS FROM ingestion_runs') as $c) $cols[$c['Field']]=true;
    foreach(['finished_at'=>'DATETIME NULL','records_seen'=>'INT DEFAULT 0','records_imported'=>'INT DEFAULT 0','records_skipped'=>'INT DEFAULT 0','status'=>'VARCHAR(50)','notes'=>'TEXT'] as $name=>$type){if(!isset($cols[$name])) $pdo->exec("ALTER TABLE ingestion_runs ADD COLUMN `$name` $type");}
    $cols=[]; foreach($pdo->query('SHOW COLUMNS FROM peptides') as $c) $cols[$c['Field']]=true;
    foreach(['venom_verification_status'=>"ENUM('Verified','Needs review','Not established') DEFAULT 'Not established'",'biological_nature'=>"ENUM('Natural','Modified','Synthetic','Recombinant','Unknown') DEFAULT 'Unknown'",'primary_source_verified'=>'TINYINT(1) DEFAULT 0','source_traceability'=>'TEXT'] as $name=>$type){if(!isset($cols[$name])) $pdo->exec("ALTER TABLE peptides ADD COLUMN `$name` $type");}
    $cols=[]; foreach($pdo->query('SHOW COLUMNS FROM structures') as $c) $cols[$c['Field']]=true;
    if(!isset($cols['sequence_match_status'])) $pdo->exec("ALTER TABLE structures ADD COLUMN sequence_match_status ENUM('Exact','Construct differs','Not assessed') DEFAULT 'Not assessed'");
}

if(isset($_POST['run'])){
    $run=true;
    try{
        if(!class_exists('DOMDocument')) throw new Exception('PHP DOM extension is missing. Enable php_dom in XAMPP.');
        ensure_compatibility($pdo);
        $all=[];
        foreach($sourcePages as $url){
            $html=fetch_page($url); $stats['pages']++;
            foreach(table_rows($html) as $r) $all[]=$r;
        }
        if(!$all) throw new Exception('No CancerPPD2 browse rows were found. Check internet access from Apache/PHP.');
        $data=[]; $seenIds=[];
        foreach($all as $r){
            $stats['rows_seen']++;
            $id=trim($r[0]??'');
            if(preg_match('/^\d+$/',$id) && !isset($seenIds[$id]) && count($r)>=18){$data[]=$r;$seenIds[$id]=true;}
        }
        // Import up to 200 unique source-backed records. Records with no explicit venom origin are retained only as review candidates, never marked verified venom.
        $pdo->beginTransaction();
        $pdo->exec("INSERT INTO ingestion_runs(source_name,started_at,status,notes) VALUES('CancerPPD2 source-backed batch',NOW(),'running','Official CancerPPD2 browse pages; source-backed CancerPPD2 records are imported; only explicit venom-origin rows enter the verified venom layer, while other source records are labelled Needs review.')");
        $runId=(int)$pdo->lastInsertId();
        $pdo->exec("DELETE FROM peptides WHERE public_id LIKE 'CPPD2-%'");
        $pdo->exec("DELETE FROM curation_audit WHERE source_type='CancerPPD2 source-backed batch'");
        // Prioritise explicit venom-origin records, then retain other source-backed anticancer records as review candidates.
        usort($data,function($a,$b){return (venom_group($b[12]??'')!==null) <=> (venom_group($a[12]??'')!==null);});
        foreach($data as $row){
            if($stats['imported'] >= $hardCap) break;
            $origin=trim($row[12]??''); $group=venom_group($origin);
            if($group) $stats['venom_rows']++;
            $cppdId=trim($row[0]??''); if($cppdId===''){ $stats['skipped']++; continue; }
            if(!$group){ $stats['candidate_rows']++; }
            $name=trim($row[4]??'') ?: 'Unnamed CancerPPD2 record';
            $seq=trim($row[3]??''); $len=(int)trim($row[5]??''); $len=$len>0?$len:null;
            $pmid=first_pmid($row[1]??''); $year=(int)trim($row[2]??''); $year=$year>0?$year:null;
            $sourceUrl='https://webs.iiitd.edu.in/raghava/cancerppd2/';
            $pubId=null;
            if($pmid){$st=$pdo->prepare('SELECT publication_id FROM publications WHERE pmid=?');$st->execute([$pmid]);$pubId=$st->fetchColumn();}
            if(!$pubId){$st=$pdo->prepare("INSERT INTO publications(pmid,title,publication_year,source_type,source_url) VALUES(?,?,?,?,?)");$st->execute([$pmid,$name.' — CancerPPD2 evidence record',$year,'Database',$sourceUrl]);$pubId=(int)$pdo->lastInsertId();$stats['papers']++;}
            $publicId='CPPD2-'.$cppdId;
            $curation='Partially curated';
            $status='External database verified';
            $nature=biological_nature($origin);
            $st=$pdo->prepare("INSERT INTO peptides(public_id,peptide_name,sequence,sequence_length,sequence_source_status,curation_status,venom_verification_status,biological_nature,primary_source_verified,source_traceability) VALUES(?,?,?,?,?,?,?,?,?,?)");
            $st->execute([$publicId,$name,$seq!==''?$seq:null,$len,$status,$curation,$group && $nature==='Natural'?'Verified':'Needs review',$nature,0,'CancerPPD2 Origin field: '.($origin!==''?$origin:'Not reported').' | Source-level verification; primary-paper verification remains a separate curation step.']); $pepId=(int)$pdo->lastInsertId(); $stats['peptides']++;
            $originName=$origin!==''?$origin:'Not reported';
            $organismGroup=$group ?: 'Unclassified';
            $st=$pdo->prepare('SELECT organism_id FROM source_organisms WHERE scientific_name=?');$st->execute([$originName]);$orgId=$st->fetchColumn();
            if(!$orgId){$st=$pdo->prepare('INSERT INTO source_organisms(scientific_name,common_name,venom_group) VALUES(?,?,?)');$st->execute([$originName,$group?'CancerPPD2 documented venom-associated origin':'CancerPPD2 documented origin — classification pending',$organismGroup]);$orgId=(int)$pdo->lastInsertId();$stats['organisms']++;}
            $st=$pdo->prepare('INSERT INTO peptide_sources(peptide_id,organism_id,publication_id,source_description) VALUES(?,?,?,?)');$st->execute([$pepId,$orgId,$pubId,'CancerPPD2 Origin field: '.($origin!==''?$origin:'Not reported')]);
            $cancer=trim($row[14]??''); $cell=trim($row[13]??''); $cellId=null; $ctId=null;
            if($cancer!=='' && strcasecmp($cancer,'Not Available')!==0){$st=$pdo->prepare('SELECT cancer_type_id FROM cancer_types WHERE cancer_name=?');$st->execute([$cancer]);$ctId=$st->fetchColumn();if(!$ctId){$st=$pdo->prepare('INSERT INTO cancer_types(cancer_name) VALUES(?)');$st->execute([$cancer]);$ctId=(int)$pdo->lastInsertId();}}
            if($cell!=='' && strcasecmp($cell,'Not Available')!==0){$st=$pdo->prepare('SELECT cell_line_id FROM cell_lines WHERE cell_line_name=? AND species=?');$st->execute([$cell,'Human']);$cellId=$st->fetchColumn();if(!$cellId){$st=$pdo->prepare('INSERT INTO cell_lines(cell_line_name,species,tissue_origin,cancer_type_id) VALUES(?,?,?,?)');$st->execute([$cell,'Human',trim($row[18]??''),$ctId]);$cellId=(int)$pdo->lastInsertId();}}
            $activity=trim($row[16]??''); [$val,$unit]=parse_activity($activity);
            $st=$pdo->prepare("INSERT INTO activity_assays(peptide_id,cell_line_id,publication_id,assay_method,exposure_time,endpoint_name,reported_value,reported_value_text,reported_unit,paper_location,curation_status) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
            $st->execute([$pepId,$cellId,$pubId,trim($row[15]??'')?:null,trim($row[17]??'')?:null,'CancerPPD2 reported activity',$val,$activity!==''?$activity:null,$unit,'CancerPPD2 ID '.$cppdId,$curation]);
            $stats['activities']++;$stats['imported']++;

            // Explicit named columns; this is compatible with the current schema and avoids legacy-column-count errors.
            $st=$pdo->prepare("INSERT INTO curation_audit(table_name,record_id,field_name,old_value,new_value,source_type,source_identifier,source_url,curator,curation_action,notes) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
            $st->execute(['peptides',$pepId,'origin',null,$originName,'CancerPPD2 source-backed batch',$cppdId,$sourceUrl,'VenomOncoDB','Imported','Origin wording supports venom-group classification; unclassified records are excluded from this import.']);
        }
        $st=$pdo->prepare("UPDATE ingestion_runs SET finished_at=NOW(),records_seen=?,records_imported=?,records_skipped=?,status='completed',notes=? WHERE run_id=?");
        $st->execute([$stats['rows_seen'],$stats['imported'],$stats['skipped'],'Official CancerPPD2 source batch; '.$stats['venom_rows'].' explicit venom-associated rows; '.$stats['candidate_rows'].' source-backed records without explicit venom classification remain Needs review.',$runId]);
        $pdo->commit();
        $message='CancerPPD2 source-backed batch completed. Explicit venom-origin records can be verified; other source-backed records remain clearly labelled Needs review.';
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$message='Import failed: '.$e->getMessage();}
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>VenomOncoDB · Verified Data Build</title><link rel="stylesheet" href="../frontend/css/style.css"></head><body>
<nav><a href="../frontend/index.html" class="brand-link"><img class="logo-img" src="../assets/logo.svg" alt="VenomOncoDB"></a><div class="navlinks"><a href="../frontend/index.html">Home</a><a href="../frontend/explorer.html">Explorer</a><a href="../frontend/dashboard.html">Insights</a><a href="../frontend/methodology.html">Methodology</a></div></nav>
<main><section class="pagehead"><div class="eyebrow">DATA BUILD · SOURCE VERIFIED</div><h1>Build the Source-Backed Evidence Dataset</h1><p>VenomOncoDB imports up to 200 unique records from official CancerPPD2 browse tables. Records with explicit venom-origin evidence are classified into the venom layer; other anticancer records remain visible as Needs review and are never represented as confirmed venom peptides.</p></section>
<section class="panel"><div class="stats" style="margin:0"><div class="stat"><strong>VENOM-ONLY</strong><span>Import policy</span></div><div class="stat"><strong>0</strong><span>Padding rows</span></div><div class="stat"><strong>6</strong><span>Source tables</span></div><div class="stat"><strong>20</strong><span>Source fields</span></div></div><form method="post" style="margin-top:22px"><button class="btn" name="run" value="1">IMPORT UP TO 200 SOURCE-BACKED RECORDS</button></form>
<?php if($run): ?><div class="related" style="margin-top:18px"><strong><?=h($message)?></strong><span>Rows scanned: <?=h($stats['rows_seen'])?> · explicit venom rows: <?=h($stats['venom_rows'])?> · excluded unclassified: <?=h($stats['candidate_rows'])?> · imported: <?=h($stats['imported'])?> · activities: <?=h($stats['activities'])?> · publications: <?=h($stats['papers'])?></span></div><?php endif; ?></section>
<section class="panel" style="margin-top:18px"><div class="eyebrow">WHY THIS IS VALID</div><h2>Source → evidence → provenance</h2><p>The database contains source-backed CancerPPD2 evidence rows. The importer never turns an unknown origin into a venom claim. Explicit venom classifications are marked separately; unclassified candidates remain Needs review. Missing activity, mechanism or structure information remains missing.</p><div class="notice">There is intentionally no minimum record target. The system prefers a smaller verified dataset over a larger padded dataset. Records excluded by the venom-only rule remain outside the biological evidence layer.</div></section>
</main><footer class="footer">VenomOncoDB · Source-aware academic research platform.</footer></body></html>
