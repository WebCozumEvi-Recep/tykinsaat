<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$id = (int)get('id'); $m = row("SELECT * FROM musteriler WHERE id=?", [$id]); if (!$m) redirect('musteriler.php');
$o = musteri_ozet($id);
$sant = rows("SELECT * FROM santiyeler WHERE musteri_id=? ORDER BY durum='aktif' DESC, baslangic DESC", [$id]);
$tahsil = rows("SELECT t.*, s.ad santiye, c.cek_no, c.banka, c.vade, c.durum cdurum FROM tahsilatlar t JOIN santiyeler s ON s.id=t.santiye_id LEFT JOIN cekler c ON c.id=t.cek_id WHERE s.musteri_id=? ORDER BY t.tarih DESC LIMIT 100", [$id]);
$cekler = rows("SELECT c.*, s.ad santiye FROM cekler c JOIN santiyeler s ON s.id=c.santiye_id WHERE s.musteri_id=? AND c.yon='alinan' ORDER BY c.durum='portfoy' DESC, c.vade", [$id]);
// Yaşlandırma: şantiye kalanları yaş kovalarına
$kova = [0, 0, 0];
foreach ($sant as $s) { $kalan = (float)$s['anlasma_tutari'] - (float)val("SELECT COALESCE(SUM(tutar),0) FROM tahsilatlar WHERE santiye_id=?", [$s['id']]);
  if ($kalan <= 0) continue; $g = alacak_yas_gun((int)$s['id']); $kova[$g <= 30 ? 0 : ($g <= 60 ? 1 : 2)] += $kalan; }
$baslik = $m['ad']; $geri = 'musteriler.php'; include 'inc/layout_top.php'; ?>
<div class="kart" style="display:flex;justify-content:space-between;gap:.5rem;flex-wrap:wrap;align-items:center">
  <div><small><svg class="ic" aria-hidden="true"><use href="#i-user-circle"></use></svg> <?= e($m['yetkili'] ?: '-') ?> · <svg class="ic" aria-hidden="true"><use href="#i-phone"></use></svg> <?= $m['telefon'] ? '<a href="tel:' . e($m['telefon']) . '">' . e($m['telefon']) . '</a>' : '-' ?><?= $m['email'] ? ' · <svg class="ic" aria-hidden="true"><use href="#i-envelope-simple"></use></svg> ' . e($m['email']) : '' ?><br>
  <?= e($m['vergi_dairesi']) ?> <?= e($m['vergi_no']) ?><?= $m['adres'] ? ' · <svg class="ic" aria-hidden="true"><use href="#i-map-pin"></use></svg> ' . e($m['adres']) : '' ?></small></div>
  <div style="display:flex;gap:.5rem;align-items:center"><?= $m['aktif'] ? etiket('Aktif','yesil') : etiket('Pasif','gri') ?><a class="btn btn-cizgi btn-kucuk" href="musteri_form.php?id=<?= $id ?>"><svg class="ic" aria-hidden="true"><use href="#i-pencil-simple"></use></svg> Kart</a><a class="btn btn-yesil btn-kucuk" href="tahsilat_form.php?musteri_id=<?= $id ?>">+ Tahsilat</a></div></div>

<div class="grid">
  <div class="ozet ozet-mavi"><div class="baslik">Toplam sözleşme</div><div class="rakam"><?= para($o['anlasma']) ?></div><div class="alt"><?= $o['santiye'] ?> şantiye · <?= $o['aktif'] ?> aktif</div></div>
  <div class="ozet ozet-yesil"><div class="baslik">Tahsil edilen</div><div class="rakam"><?= para($o['tahsil']) ?></div><div class="alt"><?= $o['anlasma'] > 0 ? round($o['tahsil'] / $o['anlasma'] * 100) : 0 ?>% tahsilat</div></div>
  <div class="ozet ozet-sari"><div class="baslik">Kalan alacak</div><div class="rakam"><?= para($o['kalan']) ?></div></div>
  <div class="ozet ozet-turuncu"><div class="baslik">Portföydeki çeki</div><div class="rakam"><?= para($o['portfoy_cek']) ?></div><div class="alt">henüz tahsil edilmedi</div></div>
</div>

<h2>Alacak yaşlandırma</h2>
<div class="tablo-kutu"><table><tr><th class="num">0-30 gün</th><th class="num">30-60 gün</th><th class="num">60+ gün</th><th class="num">Toplam</th></tr>
<tr><td class="num yesil b"><?= para($kova[0]) ?></td><td class="num sari b"><?= para($kova[1]) ?></td><td class="num kirmizi b"><?= para($kova[2]) ?></td><td class="num b"><?= para(array_sum($kova)) ?></td></tr></table></div>
<div class="bilgi">Yaş, ilgili şantiyedeki son tahsilat tarihinden (hiç tahsilat yoksa şantiye başlangıcından) itibaren sayılır.</div>

<h2>Şantiyeleri <a class="btn btn-cizgi btn-kucuk" href="santiye_form.php?musteri_id=<?= $id ?>">+ Şantiye</a></h2>
<?php if (!$sant): ?><div class="kart bos">Bu müşteriye bağlı şantiye yok.</div><?php endif; ?>
<?php foreach ($sant as $s): $so = santiye_ozet((int)$s['id']); $yuzde = $s['anlasma_tutari'] > 0 ? min(100, round($so['tahsil'] / $s['anlasma_tutari'] * 100)) : 0; ?>
  <a class="kart" style="display:block" href="santiye.php?id=<?= $s['id'] ?>">
    <div style="display:flex;justify-content:space-between;gap:.5rem"><div><b><?= e($s['ad']) ?></b><br><small><?= tarih_tr($s['baslangic']) ?> → <?= tarih_tr($s['bitis']) ?></small></div><?= durum_etiketi($s['durum']) ?></div>
    <div class="bar"><i style="width:<?= $yuzde ?>%"></i></div>
    <div style="display:flex;flex-wrap:wrap;gap:.5rem 1.2rem;font-size:.85rem"><span>Sözleşme <b><?= para($s['anlasma_tutari']) ?></b></span><span class="yesil">Tahsil <b><?= para($so['tahsil']) ?></b></span><span class="<?= $so['kalan'] > 0 ? 'sari' : 'gri' ?>">Kalan <b><?= para($so['kalan']) ?></b></span><span class="<?= $so['kar'] >= 0 ? 'yesil' : 'kirmizi' ?>">Kâr/Zarar <b><?= para($so['kar']) ?></b></span></div></a>
<?php endforeach; ?>

<h2>Tahsilat geçmişi</h2>
<div class="kart liste"><?php if (!$tahsil): ?><div class="bos">Henüz tahsilat yok.</div><?php endif; foreach ($tahsil as $r): ?>
  <div class="satir"><div class="ico-b"><?= ['nakit'=>'<svg class="ic" aria-hidden="true"><use href="#i-money-wavy"></use></svg>','havale'=>'<svg class="ic" aria-hidden="true"><use href="#i-bank"></use></svg>','cek'=>'<svg class="ic" aria-hidden="true"><use href="#i-files"></use></svg>'][$r['tip']] ?></div><div class="govde"><div class="ad"><?= e($r['santiye']) ?></div><small><?= tarih_tr($r['tarih']) ?> · <?= tip_adi($r['tip']) ?><?= $r['cek_no'] ? ' ' . e($r['cek_no']) . ' · vade ' . tarih_tr($r['vade']) . ' ' . durum_etiketi($r['cdurum']) : '' ?> <?= e($r['aciklama']) ?></small></div><div class="tutar yesil">+<?= para($r['tutar']) ?></div></div>
<?php endforeach; ?></div>

<?php if ($cekler): ?><h2>Alınan çekler</h2>
<div class="tablo-kutu"><table><tr><th>Vade</th><th>Şantiye</th><th>Banka · No</th><th>Durum</th><th class="num">Tutar</th></tr>
<?php foreach ($cekler as $c): ?><tr><td><?= tarih_tr($c['vade']) ?></td><td><?= e($c['santiye']) ?></td><td><?= e($c['banka']) ?> · <?= e($c['cek_no']) ?></td><td><?= durum_etiketi($c['durum']) ?> <a class="btn btn-gri btn-kucuk" href="cek_durum.php?id=<?= $c['id'] ?>&geri=<?= urlencode("musteri.php?id=$id") ?>">değiştir</a></td><td class="num yesil b"><?= para($c['tutar']) ?></td></tr><?php endforeach; ?></table></div><?php endif; ?>
<?php if ($m['notlar']): ?><h2>Notlar</h2><div class="kart"><?= nl2br(e($m['notlar'])) ?></div><?php endif; ?>
<?php include 'inc/layout_bottom.php';
