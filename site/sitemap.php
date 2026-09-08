<?php
require __DIR__ . '/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$kok = site_adres();
$bugun = date('Y-m-d');
$sayfalar = [['', '1.0', 'weekly']];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($sayfalar as [$yol, $oncelik, $siklik]): ?>
  <url>
    <loc><?= htmlspecialchars($kok . '/' . $yol, ENT_XML1) ?></loc>
    <lastmod><?= $bugun ?></lastmod>
    <changefreq><?= $siklik ?></changefreq>
    <priority><?= $oncelik ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
