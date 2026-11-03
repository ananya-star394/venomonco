<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/config.php';
$route = $_GET['route'] ?? '';
try {
    switch ($route) {
        case 'summary': require __DIR__.'/routes/summary.php'; break;
        case 'peptides': require __DIR__.'/routes/peptides.php'; break;
        case 'snake-catalog': require __DIR__.'/routes/snake-catalog.php'; break;
        case 'peptide': require __DIR__.'/routes/peptide.php'; break;
        case 'dashboard': require __DIR__.'/routes/dashboard.php'; break;
        case 'methods': require __DIR__.'/routes/methods.php'; break;
        case 'dataset': require __DIR__.'/routes/dataset.php'; break;
        case 'cancers': require __DIR__.'/routes/cancers.php'; break;
        case 'toxins': require __DIR__.'/routes/toxins.php'; break;
        case 'benchmark': require __DIR__.'/routes/benchmark.php'; break;
        case 'benchmark-reviews': require __DIR__.'/routes/benchmark-reviews.php'; break;
        default: http_response_code(404); echo json_encode(['success'=>false,'error'=>'Invalid API route']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'API error','detail'=>$e->getMessage()]);
}
