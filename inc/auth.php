<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// Beni hatırla çerezi
if (empty($_SESSION['uid']) && !empty($_COOKIE['remember'])) {
    $u = row("SELECT * FROM kullanicilar WHERE remember_token=? AND aktif=1", [$_COOKIE['remember']]);
    if ($u) { $_SESSION['uid'] = $u['id']; }
}
function user(): ?array {
    static $u = false;
    if ($u === false) $u = !empty($_SESSION['uid']) ? (row("SELECT * FROM kullanicilar WHERE id=? AND aktif=1", [$_SESSION['uid']]) ?: null) : null;
    return $u;
}
function login_gerekli(): void { if (!user()) redirect('login.php'); csrf_check(); }
function rol(): string { return user()['rol'] ?? ''; }
function patron(): bool { return rol() === 'patron'; }
function finans_gorur(): bool { return in_array(rol(), ['patron', 'muhasebe']); }
function patron_gerekli(): void { if (!patron()) { http_response_code(403); exit('Bu sayfaya erişim yetkiniz yok.'); } }
function finans_gerekli(): void { if (!finans_gorur()) { http_response_code(403); exit('Bu sayfaya erişim yetkiniz yok.'); } }
/** Kullanıcının görebildiği şantiyeler */
function santiyelerim(bool $sadece_aktif = false): array {
    $w = $sadece_aktif ? " AND s.durum='aktif'" : '';
    if (finans_gorur()) return rows("SELECT s.*, m.ad AS musteri FROM santiyeler s LEFT JOIN musteriler m ON m.id=s.musteri_id WHERE 1=1 $w ORDER BY s.durum='aktif' DESC, s.ad");
    return rows("SELECT s.*, m.ad AS musteri FROM santiyeler s LEFT JOIN musteriler m ON m.id=s.musteri_id JOIN kullanici_santiye ks ON ks.santiye_id=s.id WHERE ks.kullanici_id=? $w ORDER BY s.ad", [user()['id']]);
}
function santiye_erisim(int $sid): void {
    if (finans_gorur()) return;
    if (!val("SELECT 1 FROM kullanici_santiye WHERE kullanici_id=? AND santiye_id=?", [user()['id'], $sid])) { http_response_code(403); exit('Bu şantiyeye erişiminiz yok.'); }
}
function santiye_filtre(string $alias = ''): array {
    // [sql, params] — saha kullanıcıları için şantiye kısıtı
    if (finans_gorur()) return ['', []];
    $ids = array_column(santiyelerim(), 'id');
    if (!$ids) return [' AND 1=0', []];
    $col = $alias ? "$alias.santiye_id" : 'santiye_id';
    return [" AND $col IN (" . implode(',', array_fill(0, count($ids), '?')) . ")", $ids];
}
