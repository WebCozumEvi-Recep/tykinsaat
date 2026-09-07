<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$ara = get('q');
$liste = rows("SELECT * FROM musteriler WHERE ad LIKE ? OR yetkili LIKE ? ORDER BY aktif DESC, ad", ["%$ara%", "%$ara%"]);
$baslik = 'Müşteriler'; $fab = 'musteri_form.php'; include 'inc/layout_top.php'; ?>
<form class="araclar"><input type="text" name="q" placeholder="Müşteri ara…" value="<?= e($ara) ?>"><button class="btn btn-kucuk">Ara</button><a class="btn btn-cizgi btn-kucuk" href="export.php?tip=musteri"><svg class="ic" aria-hidden="true"><use href="#i-download-simple"></use></svg> Excel</a><a class="btn btn-turuncu btn-kucuk" href="musteri_form.php">+ Müşteri</a></form>
<?php if (!$liste): ?><div class="kart bos"><div class="b-ico"><svg class="ic" aria-hidden="true"><use href="#i-handshake"></use></svg></div>Henüz müşteri yok — <a href="musteri_form.php" class="b" style="color:var(--turuncu)">ilk müşteriyi ekle</a>.</div><?php endif;
$tA = $tT = $tK = 0; foreach ($liste as $m) { $o = musteri_ozet($m['id']); $tA += $o['anlasma']; $tT += $o['tahsil']; $tK += $o['kalan']; } ?>
<?php if ($liste): ?><div class="grid" style="grid-template-columns:repeat(3,1fr)">
  <div class="ozet ozet-mavi"><div class="baslik">Toplam sözleşme</div><div class="rakam"><?= para($tA) ?></div><div class="alt"><?= count($liste) ?> müşteri</div></div>
  <div class="ozet ozet-yesil"><div class="baslik">Toplam tahsilat</div><div class="rakam"><?= para($tT) ?></div></div>
  <div class="ozet ozet-sari"><div class="baslik">Toplam alacak</div><div class="rakam"><?= para($tK) ?></div></div></div><?php endif; ?>
<div class="tablo-kutu"><table><tr><th>Müşteri</th><th class="num">Şantiye</th><th class="num">Sözleşme</th><th class="num">Tahsilat</th><th class="num">Kalan alacak</th><th class="num">Portföydeki çeki</th></tr>
<?php foreach ($liste as $m): $o = musteri_ozet($m['id']); ?>
<tr onclick="location='musteri.php?id=<?= $m['id'] ?>'" style="cursor:pointer">
  <td><b><?= e($m['ad']) ?></b><?= $m['aktif'] ? '' : ' ' . etiket('Pasif','gri') ?><br><small><?= e($m['yetkili']) ?> <?= e($m['telefon']) ?></small></td>
  <td class="num"><?= $o['santiye'] ?><?= $o['aktif'] ? ' <small>(' . $o['aktif'] . ' aktif)</small>' : '' ?></td>
  <td class="num"><?= para($o['anlasma']) ?></td><td class="num yesil"><?= para($o['tahsil']) ?></td>
  <td class="num b <?= $o['kalan'] > 0 ? 'yesil' : 'gri' ?>" style="font-size:1.05rem"><?= para($o['kalan']) ?></td>
  <td class="num sari"><?= para($o['portfoy_cek']) ?></td></tr><?php endforeach; ?>
<?php if ($liste): ?><tfoot><tr><td>Toplam</td><td></td><td class="num"><?= para($tA) ?></td><td class="num"><?= para($tT) ?></td><td class="num"><?= para($tK) ?></td><td></td></tr></tfoot><?php endif; ?></table></div>
<?php include 'inc/layout_bottom.php';
