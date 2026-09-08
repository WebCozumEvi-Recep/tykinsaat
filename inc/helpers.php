<?php
function e($s): string { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
/** Stil/script yoluna dosya tarihini ekler; tarayıcı eski sürümü önbellekten sunmaz. */
function varlik(string $yol, ?string $tam_yol = null): string {
    $d = $tam_yol ?? (__DIR__ . '/../' . ltrim($yol, '/'));
    $t = is_file($d) ? filemtime($d) : 0;
    return $yol . ($t ? '?v=' . $t : '');
}
function para($n, bool $sembol = true): string {
    $n = (float)$n; $s = number_format(abs($n), 2, ',', '.');
    return ($n < 0 ? '-' : '') . ($sembol ? '₺' : '') . $s;
}
function tarih_tr(?string $d): string { return $d ? date('d.m.Y', strtotime($d)) : '-'; }
function tutar_parse($s): float { $s = str_replace(['₺',' ','.'], '', (string)$s); $s = str_replace(',', '.', $s); return (float)$s; }
function redirect(string $url): never { header('Location: ' . $url); exit; }
function flash(?string $msg = null, string $tip = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $tip]; return; }
    if (!empty($_SESSION['flash'])) { $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f; }
    return null;
}
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void { if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? null))) { http_response_code(403); exit('Geçersiz istek (CSRF).'); } }
function post(string $k, $d = '') { return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d; }
function get(string $k, $d = '') { return isset($_GET[$k]) ? trim((string)$_GET[$k]) : $d; }
function foto_yukle(string $alan): array {
    $out = [];
    if (empty($_FILES[$alan]['name'][0])) return $out;
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
    $izin = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic'];
    foreach ($_FILES[$alan]['tmp_name'] as $i => $tmp) {
        if (!is_uploaded_file($tmp)) continue;
        $mime = mime_content_type($tmp);
        if (!isset($izin[$mime])) continue;
        $ad = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $izin[$mime];
        if (move_uploaded_file($tmp, UPLOAD_DIR . $ad)) $out[] = $ad;
    }
    return $out;
}
function fotolar(?string $json): array { $a = json_decode($json ?: '[]', true); return is_array($a) ? $a : []; }
function etiket(string $metin, string $renk = 'gri'): string { return '<span class="tag tag-' . $renk . '">' . e($metin) . '</span>'; }
function durum_etiketi(string $d): string {
    $m = ['aktif' => ['Aktif','yesil'], 'tamamlandi' => ['Tamamlandı','gri'], 'beklemede' => ['Beklemede','sari'],
          'odendi' => ['Ödendi','yesil'], 'borc' => ['Borç','kirmizi'], 'portfoy' => ['Portföyde','sari'],
          'tahsil' => ['Tahsil Edildi','yesil'], 'ciro' => ['Ciro Edildi','gri'], 'karsiliksiz' => ['Karşılıksız','kirmizi'],
          'calisiyor' => ['Çalışıyor','yesil'], 'bosta' => ['Boşta','gri'], 'bakimda' => ['Bakımda','sari']];
    [$t, $r] = $m[$d] ?? [$d, 'gri']; return etiket($t, $r);
}
function gorev_adi(string $g): string { return ['usta'=>'Usta','isci'=>'İşçi','operator'=>'Operatör','sofor'=>'Şoför','diger'=>'Diğer'][$g] ?? $g; }
function tip_adi(string $t): string { return ['nakit'=>'Nakit','havale'=>'Havale','cek'=>'Çek','gunluk'=>'Günlük','aylik'=>'Aylık','ekskavator'=>'Ekskavatör','kepce'=>'Kepçe','kamyon'=>'Kamyon','silindir'=>'Silindir','dozer'=>'Dozer','diger'=>'Diğer'][$t] ?? $t; }
function hava_ikon(?string $h): string { return ikon(['gunesli'=>'sun','bulutlu'=>'cloud','yagmurlu'=>'cloud-rain','karli'=>'snowflake'][$h] ?? 'sun'); }
/** Veritabanında emoji olarak tutulan gider kategorisi ikonunu sprite ikonuna çevirir. */
function kat_ikon(?string $emoji): string {
    $m = ['🧱'=>'wall','🔩'=>'nut','⛽'=>'gas-pump','🚜'=>'tractor','🚚'=>'truck','🍲'=>'bowl-food','📦'=>'cube',
          '🧾'=>'receipt','🔨'=>'hammer','🛠'=>'toolbox','🏗'=>'crane-tower'];
    foreach ($m as $e => $ad) if ($emoji !== null && str_contains($emoji, $e)) return ikon($ad);
    return ikon('cube');
}

// ---- Finansal hesaplamalar ----
function santiye_ozet(int $sid): array {
    $s = row("SELECT s.*, m.ad AS musteri FROM santiyeler s LEFT JOIN musteriler m ON m.id=s.musteri_id WHERE s.id=?", [$sid]);
    $tahsil = (float)val("SELECT COALESCE(SUM(tutar),0) FROM tahsilatlar WHERE santiye_id=?", [$sid]);
    $yevmiye = (float)val("SELECT COALESCE(SUM(CASE durum WHEN 'tam' THEN yevmiye WHEN 'yarim' THEN yevmiye/2 ELSE 0 END + mesai_saat*yevmiye/9),0) FROM puantaj WHERE santiye_id=?", [$sid]);
    $malzeme = (float)val("SELECT COALESCE(SUM(g.tutar),0) FROM giderler g LEFT JOIN gider_kategorileri k ON k.id=g.kategori_id WHERE g.santiye_id=? AND (k.ad IS NULL OR k.ad NOT IN ('Kiralama'))", [$sid]);
    $kira_gider = (float)val("SELECT COALESCE(SUM(g.tutar),0) FROM giderler g JOIN gider_kategorileri k ON k.id=g.kategori_id WHERE g.santiye_id=? AND k.ad='Kiralama'", [$sid]);
    $kira = (float)val("SELECT COALESCE(SUM(toplam),0) FROM makine_kiralama WHERE santiye_id=?", [$sid]) + $kira_gider;
    $maliyet = $yevmiye + $malzeme + $kira;
    return ['s'=>$s, 'tahsil'=>$tahsil, 'kalan'=>(float)$s['anlasma_tutari'] - $tahsil, 'yevmiye'=>$yevmiye,
            'malzeme'=>$malzeme, 'makine'=>$kira, 'maliyet'=>$maliyet, 'kar'=>(float)$s['anlasma_tutari'] - $maliyet];
}
/** Müşteri bazlı alacak özeti: tüm şantiyelerinin toplamı */
function musteri_ozet(int $mid): array {
    $anlasma = (float)val("SELECT COALESCE(SUM(anlasma_tutari),0) FROM santiyeler WHERE musteri_id=?", [$mid]);
    $tahsil = (float)val("SELECT COALESCE(SUM(t.tutar),0) FROM tahsilatlar t JOIN santiyeler s ON s.id=t.santiye_id WHERE s.musteri_id=?", [$mid]);
    // Devam eden işlerdeki açık alacak (tamamlanan şantiyelerde kalan varsa o da alacaktır)
    $acik = (float)val("SELECT COALESCE(SUM(s.anlasma_tutari - COALESCE((SELECT SUM(tutar) FROM tahsilatlar WHERE santiye_id=s.id),0)),0) FROM santiyeler s WHERE s.musteri_id=?", [$mid]);
    $cek = (float)val("SELECT COALESCE(SUM(c.tutar),0) FROM cekler c JOIN santiyeler s ON s.id=c.santiye_id WHERE s.musteri_id=? AND c.yon='alinan' AND c.durum='portfoy'", [$mid]);
    return ['anlasma'=>$anlasma, 'tahsil'=>$tahsil, 'kalan'=>$acik, 'portfoy_cek'=>$cek,
            'santiye'=>(int)val("SELECT COUNT(*) FROM santiyeler WHERE musteri_id=?", [$mid]),
            'aktif'=>(int)val("SELECT COUNT(*) FROM santiyeler WHERE musteri_id=? AND durum='aktif'", [$mid])];
}
/** Bir şantiyenin kalan alacağının yaş kovası: 0=0-30, 1=30-60, 2=60+ */
function alacak_yas_gun(int $sid): int {
    $son = val("SELECT MAX(tarih) FROM tahsilatlar WHERE santiye_id=?", [$sid]) ?: val("SELECT baslangic FROM santiyeler WHERE id=?", [$sid]);
    if (!$son) return 0;
    return (int)floor((time() - strtotime($son)) / 86400);
}
function tedarikci_bakiye(int $tid): array {
    $alim = (float)val("SELECT COALESCE(SUM(tutar),0) FROM giderler WHERE tedarikci_id=?", [$tid])
          + (float)val("SELECT COALESCE(SUM(toplam),0) FROM makine_kiralama WHERE tedarikci_id=?", [$tid]);
    $pesin = (float)val("SELECT COALESCE(SUM(tutar),0) FROM giderler WHERE tedarikci_id=? AND odeme_durumu='odendi'", [$tid]);
    $odeme = (float)val("SELECT COALESCE(SUM(tutar),0) FROM tedarikci_odemeleri WHERE tedarikci_id=?", [$tid]);
    return ['alim'=>$alim, 'odeme'=>$odeme + $pesin, 'bakiye'=>$alim - $odeme - $pesin];
}
function personel_hakedis(int $pid): array {
    $hak = (float)val("SELECT COALESCE(SUM(CASE durum WHEN 'tam' THEN yevmiye WHEN 'yarim' THEN yevmiye/2 ELSE 0 END + mesai_saat*yevmiye/9),0) FROM puantaj WHERE personel_id=?", [$pid]);
    $odenen = (float)val("SELECT COALESCE(SUM(tutar),0) FROM personel_odeme WHERE personel_id=?", [$pid]);
    return ['hakedis'=>$hak, 'odenen'=>$odenen, 'kalan'=>$hak - $odenen];
}

/** Phosphor sprite'ından ikon. assets/icons.svg layout_top içinde bir kez gömülür. */
function ikon(string $ad, string $sinif = ''): string {
    return '<svg class="ic' . ($sinif ? ' ' . $sinif : '') . '" aria-hidden="true" focusable="false"><use href="#i-' . e($ad) . '"></use></svg>';
}
