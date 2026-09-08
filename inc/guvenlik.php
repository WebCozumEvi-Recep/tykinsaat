<?php
// Adres birleştirme: güvensiz bağlantıyı ve ikinci alan adını tek adrese toplar.
// Sunucu nginx (Hestia) olduğu için .htaccess çalışmaz; yönlendirme burada yapılır.
// Site Yönetimi > SEO sekmesinden kapatılabilir.

/** İstek HTTPS üzerinden mi geldi? Vekil sunucu başlıkları da dikkate alınır. */
function baglanti_guvenli(): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
    if (($_SERVER['SERVER_PORT'] ?? '') == 443) return true;
    $p = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['HTTP_X_FORWARDED_SCHEME'] ?? '');
    if ($p === 'https') return true;
    if (strtolower($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on') return true;
    return false;
}

/**
 * Gerekiyorsa kalıcı (301) yönlendirme yapar.
 * $zorla_https: http -> https
 * $tercih: '' (dokunma), 'www' (www ekle), 'cikar' (www kaldır)
 */
function adres_birlestir(bool $zorla_https = true, string $tercih = 'cikar'): void {
    if (PHP_SAPI === 'cli') return;
    // POST/PUT gibi isteklerde yönlendirme gövdeyi düşürür; sadece okuma isteklerinde çalış.
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) return;

    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    if ($host === '' || str_starts_with($host, 'localhost') || filter_var(explode(':', $host)[0], FILTER_VALIDATE_IP)) return;

    $guvenli = baglanti_guvenli();
    $hedef_host = $host;
    if ($tercih === 'cikar' && str_starts_with($host, 'www.')) $hedef_host = substr($host, 4);
    if ($tercih === 'www' && !str_starts_with($host, 'www.'))  $hedef_host = 'www.' . $host;

    $sema_degisecek = $zorla_https && !$guvenli;
    if (!$sema_degisecek && $hedef_host === $host) return;

    // Sonsuz döngü koruması: sunucu şemayı yanlış bildiriyorsa bir kereden fazla deneme.
    if (!empty($_COOKIE['ssl_yonlendirme'])) { setcookie('ssl_yonlendirme', '', ['expires' => time() - 3600, 'path' => '/']); return; }
    if ($sema_degisecek) setcookie('ssl_yonlendirme', '1', ['expires' => time() + 60, 'path' => '/', 'samesite' => 'Lax']);

    $sema = ($zorla_https || $guvenli) ? 'https' : 'http';
    header('Location: ' . $sema . '://' . $hedef_host . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}
