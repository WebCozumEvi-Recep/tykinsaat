<?php
require __DIR__ . '/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
$kok = site_adres();
echo "User-agent: *\n";
echo "Allow: /\n";
foreach (['/inc/', '/uploads/', '/login.php', '/ayarlar.php', '/site_yonetimi.php', '/index.php', '/raporlar.php', '/site/index.php'] as $y) echo "Disallow: $y\n";
echo "\nSitemap: $kok/sitemap.php\n";
