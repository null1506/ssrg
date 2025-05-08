<?php
if (!isset($_GET['url'])) {
    http_response_code(400);
    echo "Missing url parameter";
    exit;
}

$url = escapeshellarg($_GET['url']);
$output = 'screenshot_' . md5($_GET['url']) . '.png';

// Lấy cookie phiên hiện tại
$sessionId = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
$sessionIdArg = escapeshellarg($sessionId);

// Gọi script Node.js, truyền thêm sessionId
$cmd = $cmd = "\"F:\\nodejs\\node.exe\" puppeteer_screenshot.js $url $output 2>&1";
exec($cmd, $outputArr, $returnVar);

if (!file_exists($output)) {
    http_response_code(500);
    echo "Failed to get screenshot!<br>";
    echo "CMD: $cmd<br>";
    echo "Output:<br><pre>";
    print_r($outputArr);
    echo "</pre>Return code: $returnVar";
    exit;
}

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename=\"invoice_server.png\"');
readfile($output);
@unlink($output);