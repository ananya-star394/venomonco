<?php
$peptides=(int)$pdo->query("SELECT COUNT(*) FROM peptides")->fetchColumn();
$seqs=(int)$pdo->query("SELECT COUNT(*) FROM peptides WHERE sequence IS NOT NULL AND sequence<>''")->fetchColumn();
$unclassified=(int)$pdo->query("SELECT COUNT(*) FROM peptides p JOIN peptide_sources ps ON p.peptide_id=ps.peptide_id JOIN source_organisms so ON ps.organism_id=so.organism_id WHERE so.venom_group='Unclassified'")->fetchColumn();
$activities=(int)$pdo->query("SELECT COUNT(*) FROM activity_assays")->fetchColumn();
$structures=(int)$pdo->query("SELECT COUNT(*) FROM structures")->fetchColumn();
echo json_encode(['success'=>true,'dataset'=>['records'=>$peptides,'sequence_records'=>$seqs,'unclassified_records'=>$unclassified,'activity_records'=>$activities,'structure_records'=>$structures,'source'=>'CancerPPD2 natural sequence dataset plus verified enrichment when synced','source_url'=>'https://webs.iiitd.edu.in/raghava/cancerppd2/']]);
