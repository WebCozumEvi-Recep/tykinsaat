<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli();
$baslik = 'Şantiyeler'; $fab = patron() ? 'santiye_form.php' : null;
$durum = get('durum'); $liste = santiyelerim();
if ($durum) $liste = array_filter($liste, fn($s) => $s['durum'] === $durum);
include 'inc/layout_top.php'; ?>
<div class="araclar"><a class="btn btn-kucuk <?= $durum===''?'':'btn-cizgi' ?>" href="santiyeler.php">Tümü</a><a class="btn btn-kucuk <?= $durum==='aktif'?'':'btn-cizgi' ?>" href="?durum=aktif">Aktif</a><a class="btn btn-kucuk <?= $durum==='beklemede'?'':'btn-cizgi' ?>" href="?durum=beklemede">Beklemede</a><a class="btn btn-kucuk <?= $durum==='tamamlandi'?'':'btn-cizgi' ?>" href="?durum=tamamlandi">Tamamlandı</a></div>
<?php if (!$liste): ?><div class="kart bos"><div class="b-ico"><svg class="ic" aria-hidden="true"><use href="#i-crane-tower"></use></svg></div>Henüz şantiye yok<?= patron() ? ' — <a href="santiye_form.php" class="b" style="color:var(--turuncu)">ilk şantiyeni ekle</a>' : '' ?>.</div><?php endif; ?>
<?php foreach ($liste as $s): $yon = rows("SELECT k.ad_soyad FROM kullanicilar k JOIN kullanici_santiye ks ON ks.kullanici_id=k.id WHERE ks.santiye_id=?", [$s['id']]);
  $kalan = finans_gorur() ? (float)$s['anlasma_tutari'] - (float)val("SELECT COALESCE(SUM(tutar),0) FROM tahsilatlar WHERE santiye_id=?", [$s['id']]) : null; ?>
  <a class="kart" style="display:block" href="santiye.php?id=<?= $s['id'] ?>">
    <div style="display:flex;justify-content:space-between;gap:.5rem;align-items:flex-start"><div><b style="font-size:1.05rem"><?= e($s['ad']) ?></b><br><small><svg class="ic" aria-hidden="true"><use href="#i-user-circle"></use></svg> <?= e($s['musteri']) ?><?= $s['adres'] ? ' · <svg class="ic" aria-hidden="true"><use href="#i-map-pin"></use></svg> ' . e(mb_strimwidth($s['adres'], 0, 40, '…')) : '' ?></small></div><?= durum_etiketi($s['durum']) ?></div>
    <div style="display:flex;justify-content:space-between;margin-top:.6rem;font-size:.85rem"><span class="muted">Yönetici: <?= e(implode(', ', array_column($yon, 'ad_soyad')) ?: '-') ?></span>
    <?php if ($kalan !== null): ?><span>Kalan alacak <b class="<?= $kalan > 0 ? 'yesil' : 'gri' ?>"><?= para($kalan) ?></b></span><?php endif; ?></div>
  </a>
<?php endforeach; include 'inc/layout_bottom.php';
