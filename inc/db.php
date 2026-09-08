<?php
// Yapılandırma: önce webroot DIŞINDAKİ dosya (canlı sunucu), yoksa proje içindeki (yerel geliştirme)
(function () {
    $__yollar = [
        __DIR__ . '/../../private/tyk-config.php',  // HestiaCP (open_basedir'e açık, web'den erişilemez)
        __DIR__ . '/../../tyk-config.php',          // genel: webroot'un bir üstü
        __DIR__ . '/../config.php',                 // yerel geliştirme
    ];
    foreach ($__yollar as $__c) {
        if (is_file($__c)) { require_once $__c; return; }
    }
    http_response_code(500);
    exit('Yapılandırma dosyası bulunamadı (tyk-config.php / config.php).');
})();
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
function q(string $sql, array $p = []): PDOStatement { $s = db()->prepare($sql); $s->execute($p); return $s; }
function rows(string $sql, array $p = []): array { return q($sql, $p)->fetchAll(); }
function row(string $sql, array $p = []) { return q($sql, $p)->fetch(); }
function val(string $sql, array $p = []) { return q($sql, $p)->fetchColumn(); }
