<?php
// Site içerik katmanını yükler. Panel/veritabanı erişilemezse site varsayılan
// içeriklerle yayında kalır; hiçbir durumda beyaz sayfa vermez.
$__kok = dirname(__DIR__);
$__cfg = [$__kok . '/../private/tyk-config.php', $__kok . '/../tyk-config.php', $__kok . '/config.php'];
$__db_var = false;
foreach ($__cfg as $__c) { if (is_file($__c)) { $__db_var = true; break; } }

if ($__db_var) {
    require_once $__kok . '/inc/db.php';
    require_once $__kok . '/inc/helpers.php';
} else {
    function e($s): string { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
    function rows(string $sql, array $p = []): array { throw new RuntimeException('db yok'); }
    function row(string $sql, array $p = []) { throw new RuntimeException('db yok'); }
    function q(string $sql, array $p = []) { throw new RuntimeException('db yok'); }
    function val(string $sql, array $p = []) { throw new RuntimeException('db yok'); }
    if (!defined('UPLOAD_URL')) define('UPLOAD_URL', 'uploads/');
}
require_once $__kok . '/inc/site.php';

/* Varlık yolları: sayfa hem /site/ altından hem kökten (kök index.php
   ziyaretçiye siteyi dahil eder) servis edilebildiği için, yollar
   SCRIPT_NAME tahminine değil dosya sistemi konumuna göre hesaplanır. */
(function () use (&$__kok) {
    $taban = '';
    $dr = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $dr = $dr ? realpath($dr) : '';
    $kok = realpath($__kok);            // public_html (site/ dizininin üstü)
    if ($dr && $kok && str_starts_with($kok, $dr)) {
        $taban = rtrim(str_replace('\\', '/', substr($kok, strlen($dr))), '/');
    }
    define('PANEL',  $taban . '/');           // login.php, uploads/ gibi panel yolları
    define('VARLIK', $taban . '/site/');      // site.css gibi site dosyaları
})();

/** Sitenin kendi adresi (canonical, sitemap, JSON-LD için). */
function site_adres(): string {
    $u = sa('site_url');
    if ($u) return rtrim($u, '/');
    $sema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $sema . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}
