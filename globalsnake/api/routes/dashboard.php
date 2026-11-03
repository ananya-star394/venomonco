<?php
$cancerTypeId = isset($_GET['cancer_type_id']) ? (int)$_GET['cancer_type_id'] : 0;
$selectedCancerName = 'All cancer types';
if ($cancerTypeId > 0) {
    $stmt = $pdo->query("SELECT cancer_name FROM cancer_types WHERE cancer_type_id={$cancerTypeId} LIMIT 1");
    $selectedCancerName = $stmt->fetchColumn() ?: 'All cancer types';
    if ($selectedCancerName === 'All cancer types') $cancerTypeId = 0;
}

$base = "p.venom_verification_status='Verified' AND p.biological_nature='Natural'";
$peptideFilter = $cancerTypeId > 0
    ? "$base AND EXISTS (SELECT 1 FROM activity_assays af JOIN cell_lines clf ON af.cell_line_id=clf.cell_line_id WHERE af.peptide_id=p.peptide_id AND clf.cancer_type_id={$cancerTypeId})"
    : $base;

$venom=$pdo->query("SELECT so.venom_group,COUNT(DISTINCT p.peptide_id) count
    FROM source_organisms so
    JOIN peptide_sources ps ON so.organism_id=ps.organism_id
    JOIN peptides p ON ps.peptide_id=p.peptide_id
    WHERE {$peptideFilter}
    GROUP BY so.venom_group ORDER BY count DESC")->fetchAll();

$review=$pdo->query("SELECT p.venom_verification_status status,COUNT(*) count
    FROM peptides p WHERE p.venom_verification_status<>'Verified'
    GROUP BY p.venom_verification_status ORDER BY count DESC")->fetchAll();

$activityWhere = $cancerTypeId > 0
    ? "{$base} AND cl.cancer_type_id={$cancerTypeId}"
    : $base;
$cancer=$pdo->query("SELECT ct.cancer_name,COUNT(aa.activity_id) count
    FROM cancer_types ct
    JOIN cell_lines cl ON ct.cancer_type_id=cl.cancer_type_id
    JOIN activity_assays aa ON cl.cell_line_id=aa.cell_line_id
    JOIN peptides p ON aa.peptide_id=p.peptide_id
    WHERE {$activityWhere}
    GROUP BY ct.cancer_type_id ORDER BY count DESC,ct.cancer_name")->fetchAll();

$publicationWhere = $cancerTypeId > 0
    ? "{$base} AND cl.cancer_type_id={$cancerTypeId}"
    : $base;
$years=$pdo->query("SELECT pub.publication_year year,COUNT(DISTINCT pub.publication_id) count
    FROM publications pub
    JOIN activity_assays a ON a.publication_id=pub.publication_id
    JOIN cell_lines cl ON cl.cell_line_id=a.cell_line_id
    JOIN peptides p ON p.peptide_id=a.peptide_id
    WHERE {$publicationWhere} AND pub.publication_year IS NOT NULL
    GROUP BY pub.publication_year ORDER BY pub.publication_year")->fetchAll();

if ($cancerTypeId > 0) {
    $summary=$pdo->query("SELECT
        COUNT(DISTINCT p.peptide_id) peptides,
        COUNT(DISTINCT a.activity_id) activity_records,
        COUNT(DISTINCT a.publication_id) publications,
        COUNT(DISTINCT s.structure_id) structures
        FROM peptides p
        JOIN activity_assays a ON a.peptide_id=p.peptide_id
        JOIN cell_lines cl ON cl.cell_line_id=a.cell_line_id AND cl.cancer_type_id={$cancerTypeId}
        LEFT JOIN structures s ON s.peptide_id=p.peptide_id
        WHERE {$peptideFilter}")->fetch();
} else {
    $summary=$pdo->query("SELECT
        COUNT(DISTINCT p.peptide_id) peptides,
        COUNT(DISTINCT a.activity_id) activity_records,
        COUNT(DISTINCT a.publication_id) publications,
        COUNT(DISTINCT s.structure_id) structures
        FROM peptides p
        LEFT JOIN activity_assays a ON a.peptide_id=p.peptide_id
        LEFT JOIN structures s ON s.peptide_id=p.peptide_id
        WHERE {$peptideFilter}")->fetch();
}

$completeness=$pdo->query("SELECT
    COUNT(*) total,
    SUM(sequence_source_status<>'Unavailable') seq_verified,
    SUM(EXISTS(SELECT 1 FROM activity_assays a WHERE a.peptide_id=p.peptide_id" . ($cancerTypeId > 0 ? " AND EXISTS(SELECT 1 FROM cell_lines cl WHERE cl.cell_line_id=a.cell_line_id AND cl.cancer_type_id={$cancerTypeId})" : "") . ")) has_activity,
    SUM(EXISTS(SELECT 1 FROM structures s WHERE s.peptide_id=p.peptide_id)) has_structure,
    SUM(EXISTS(SELECT 1 FROM peptide_mechanisms m" . ($cancerTypeId > 0 ? " JOIN cell_lines clm ON clm.cell_line_id=m.cell_line_id" : "") . " WHERE m.peptide_id=p.peptide_id" . ($cancerTypeId > 0 ? " AND clm.cancer_type_id={$cancerTypeId}" : "") . ")) has_mechanism
    FROM peptides p WHERE {$peptideFilter}")->fetch();

echo json_encode([
    'success'=>true,
    'filter'=>['cancer_type_id'=>$cancerTypeId,'cancer_name'=>$selectedCancerName],
    'summary'=>[
        'peptides'=>(int)$summary['peptides'],
        'activity_records'=>(int)$summary['activity_records'],
        'publications'=>(int)$summary['publications'],
        'structures'=>(int)$summary['structures']
    ],
    'venom_groups'=>$venom,
    'review'=>$review,
    'cancer_types'=>$cancer,
    'publication_years'=>$years,
    'completeness'=>$completeness
]);
