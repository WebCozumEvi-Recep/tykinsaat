<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli();
$bugun = date('Y-m-d');
if (!finans_gorur()) {
    // ---- SAHA MODU ----
    $baslik = 'Ana Sayfa'; $fab = 'gider_form.php';
    $sant = santiyelerim(true);
    include 'inc/layout_top.php'; ?>
    <div class="kart"><h3>Merhaba, <?= e(user()['ad_soyad']) ?> </h3><small><?= date('d.m.Y') ?> · <?= count($sant) ?> aktif şantiye</small></div>
    <div class="grid">
      <a class="ozet ozet-yesil" href="puantaj.php"><div class="baslik">Puantaj</div><div class="rakam"><svg class="ic" aria-hidden="true"><use href="#i-check-circle"></use></svg></div><div class="alt">Bugünün yoklaması</div></a>
      <a class="ozet ozet-kirmizi" href="gider_form.php"><div class="baslik">Gider Ekle</div><div class="rakam"><svg class="ic" aria-hidden="true"><use href="#i-receipt"></use></svg></div><div class="alt">Fiş / malzeme</div></a>
      <a class="ozet ozet-mavi" href="is_gunlugu_form.php"><div class="baslik">İş Kaydı</div><div class="rakam"><svg class="ic" aria-hidden="true"><use href="#i-notebook"></use></svg></div><div class="alt">Fotoğraflı günlük</div></a>
      <a class="ozet ozet-turuncu" href="makineler.php"><div class="baslik">Makineler</div><div class="rakam"><svg class="ic" aria-hidden="true"><use href="#i-tractor"></use></svg></div><div class="alt">Sahadaki makineler</div></a>
    </div>
    <h2>Şantiyelerim</h2>
    <?php if (!$sant): ?><div class="kart bos"><div class="b-ico"><svg class="ic" aria-hidden="true"><use href="#i-crane-tower"></use></svg></div>Size atanmış şantiye yok.</div><?php endif; ?>
    <?php foreach ($sant as $s):
      $bugunSay = val("SELECT COUNT(*) FROM puantaj WHERE santiye_id=? AND tarih=? AND durum<>'yok'", [$s['id'], $bugun]);
      $girildi = val("SELECT COUNT(*) FROM puantaj WHERE santiye_id=? AND tarih=?", [$s['id'], $bugun]); ?>
      <div class="kart"><div style="display:flex;justify-content:space-between;align-items:center"><div><b><?= e($s['ad']) ?></b><br><small><?= e($s['musteri']) ?></small></div>
        <?= $girildi ? etiket("Bugün $bugunSay kişi", 'yesil') : etiket('Puantaj girilmedi', 'sari') ?></div>
        <div class="hizli"><a class="btn btn-cizgi btn-kucuk" href="puantaj.php?santiye_id=<?= $s['id'] ?>"><svg class="ic" aria-hidden="true"><use href="#i-check-circle"></use></svg> Puantaj</a><a class="btn btn-cizgi btn-kucuk" href="gider_form.php?santiye_id=<?= $s['id'] ?>"><svg class="ic" aria-hidden="true"><use href="#i-receipt"></use></svg> Gider</a><a class="btn btn-cizgi btn-kucuk" href="santiye.php?id=<?= $s['id'] ?>">Detay ›</a></div></div>
    <?php endforeach;
    include 'inc/layout_bottom.php'; exit;
}
// ---- PATRON MODU ----
$baslik = 'Panel'; $fab = 'gider_form.php';
$aktif = val("SELECT COUNT(*) FROM santiyeler WHERE durum='aktif'");
$alacak = (float)val("SELECT COALESCE(SUM(anlasma_tutari),0) FROM santiyeler WHERE durum<>'tamamlandi'") - (float)val("SELECT COALESCE(SUM(t.tutar),0) FROM tahsilatlar t JOIN santiyeler s ON s.id=t.santiye_id WHERE s.durum<>'tamamlandi'");
$borc = 0; foreach (rows("SELECT id FROM tedarikciler") as $t) { $b = tedarikci_bakiye($t['id']); if ($b['bakiye'] > 0) $borc += $b['bakiye']; }
$bekleyenYevmiye = 0; foreach (rows("SELECT id FROM personel") as $p) { $h = personel_hakedis($p['id']); if ($h['kalan'] > 0) $bekleyenYevmiye += $h['kalan']; }
$net = $alacak - $borc - $bekleyenYevmiye;
$sahada = rows("SELECT s.id, s.ad, (SELECT COUNT(*) FROM puantaj p WHERE p.santiye_id=s.id AND p.tarih=? AND p.durum<>'yok') AS kisi,
   (SELECT GROUP_CONCAT(m.ad SEPARATOR ', ') FROM makineler m WHERE m.santiye_id=s.id) AS makine FROM santiyeler s WHERE s.durum='aktif' ORDER BY s.ad", [$bugun]);
$cekler = rows("SELECT c.*, s.ad AS santiye, t.firma FROM cekler c LEFT JOIN santiyeler s ON s.id=c.santiye_id LEFT JOIN tedarikciler t ON t.id=c.tedarikci_id WHERE c.durum='portfoy' AND c.vade>=CURDATE()-INTERVAL 30 DAY ORDER BY c.vade LIMIT 10");
$hareket = rows("(SELECT 'gider' tip, g.tarih, g.tutar, COALESCE(g.aciklama,k.ad) aciklama, s.ad santiye FROM giderler g LEFT JOIN gider_kategorileri k ON k.id=g.kategori_id JOIN santiyeler s ON s.id=g.santiye_id)
 UNION ALL (SELECT 'tahsilat', t.tarih, t.tutar, CONCAT('Tahsilat (', t.tip, ')'), s.ad FROM tahsilatlar t JOIN santiyeler s ON s.id=t.santiye_id)
 UNION ALL (SELECT 'odeme', o.tarih, o.tutar, CONCAT(td.firma, ' ödeme'), COALESCE(s.ad,'-') FROM tedarikci_odemeleri o JOIN tedarikciler td ON td.id=o.tedarikci_id LEFT JOIN santiyeler s ON s.id=o.santiye_id)
 ORDER BY tarih DESC LIMIT 10");
include 'inc/layout_top.php'; ?>
<div class="grid">
  <div class="ozet ozet-mavi"><div class="baslik">Aktif Şantiye</div><div class="rakam"><?= $aktif ?></div><div class="alt">devam eden iş</div></div>
  <div class="ozet ozet-yesil"><div class="baslik">Toplam Alacak</div><div class="rakam"><?= para($alacak) ?></div><div class="alt">müşterilerden</div></div>
  <div class="ozet ozet-kirmizi"><div class="baslik">Tedarikçi Borcu</div><div class="rakam"><?= para($borc) ?></div><div class="alt">ödenmemiş</div></div>
  <div class="ozet <?= $net >= 0 ? 'ozet-yesil' : 'ozet-kirmizi' ?>"><div class="baslik">Net Nakit Pozisyonu</div><div class="rakam"><?= para($net) ?></div><div class="alt">alacak − borç − bekleyen yevmiye (<?= para($bekleyenYevmiye) ?>)</div></div>
</div>
<div class="grid grid-2" style="grid-template-columns:1fr">
<div>
<h2>Bugün sahada</h2>
<div class="kart liste"><?php if (!$sahada): ?><div class="bos">Aktif şantiye yok.</div><?php endif; ?>
<?php foreach ($sahada as $s): ?><a class="satir" href="santiye.php?id=<?= $s['id'] ?>"><div class="ico-b"><svg class="ic" aria-hidden="true"><use href="#i-crane-tower"></use></svg></div><div class="govde"><div class="ad"><?= e($s['ad']) ?></div><small><svg class="ic" aria-hidden="true"><use href="#i-tractor"></use></svg> <?= e($s['makine'] ?: 'Makine yok') ?></small></div><div class="tutar"><?= $s['kisi'] ?> <svg class="ic" aria-hidden="true"><use href="#i-hard-hat"></use></svg></div></a><?php endforeach; ?></div>

<h2>Şantiyeler</h2>
<?php foreach (rows("SELECT id FROM santiyeler WHERE durum<>'tamamlandi' ORDER BY durum='aktif' DESC, ad") as $r): $o = santiye_ozet($r['id']); $s = $o['s'];
  $yuzde = $s['anlasma_tutari'] > 0 ? min(100, round($o['tahsil'] / $s['anlasma_tutari'] * 100)) : 0; ?>
  <a class="kart" style="display:block" href="santiye.php?id=<?= $s['id'] ?>">
    <div style="display:flex;justify-content:space-between;gap:.5rem"><div><b><?= e($s['ad']) ?></b><br><small><?= e($s['musteri']) ?></small></div><?= durum_etiketi($s['durum']) ?></div>
    <div class="bar"><i style="width:<?= $yuzde ?>%"></i></div>
    <div style="display:flex;flex-wrap:wrap;gap:.5rem 1.2rem;font-size:.85rem">
      <span>Anlaşma <b><?= para($s['anlasma_tutari']) ?></b></span><span class="yesil">Tahsil <b><?= para($o['tahsil']) ?></b></span><span>Kalan <b><?= para($o['kalan']) ?></b></span>
      <span class="kirmizi">Maliyet <b><?= para($o['maliyet']) ?></b></span><span class="<?= $o['kar'] >= 0 ? 'yesil' : 'kirmizi' ?>">Kâr/Zarar <b><?= para($o['kar']) ?></b></span></div>
  </a>
<?php endforeach; ?>
</div>
<div>
<h2>Yaklaşan çekler</h2>
<div class="kart liste"><?php if (!$cekler): ?><div class="bos">Portföyde çek yok.</div><?php endif; ?>
<?php foreach ($cekler as $c): $gun = (strtotime($c['vade']) - strtotime($bugun)) / 86400; $r = $c['yon'] === 'alinan' ? 'yesil' : 'kirmizi'; ?>
  <div class="satir" style="<?= $gun <= 7 ? 'background:#fff8ec;margin:0 -1rem;padding:.8rem 1rem' : '' ?>"><div class="ico-b"><?= $c['yon'] === 'alinan' ? '<svg class="ic" aria-hidden="true"><use href="#i-arrow-circle-down"></use></svg>' : '<svg class="ic" aria-hidden="true"><use href="#i-arrow-circle-up"></use></svg>' ?></div>
    <div class="govde"><div class="ad"><?= e($c['yon'] === 'alinan' ? ($c['santiye'] ?: 'Müşteri') : ($c['firma'] ?: 'Tedarikçi')) ?></div><small><?= e($c['banka']) ?> · <?= e($c['cek_no']) ?> · Vade <?= tarih_tr($c['vade']) ?> <?= $gun < 0 ? etiket('Vadesi geçti', 'kirmizi') : ($gun <= 7 ? etiket($gun . ' gün', 'sari') : '') ?></small></div>
    <div class="tutar <?= $r ?>"><?= para($c['tutar']) ?></div></div>
<?php endforeach; ?></div>
<h2>Son hareketler</h2>
<div class="kart liste"><?php foreach ($hareket as $h): $r = $h['tip'] === 'tahsilat' ? 'yesil' : 'kirmizi'; ?>
  <div class="satir"><div class="ico-b"><?= ikon(['gider'=>'receipt','tahsilat'=>'coins','odeme'=>'factory'][$h['tip']]) ?></div><div class="govde"><div class="ad"><?= e($h['aciklama']) ?></div><small><?= e($h['santiye']) ?> · <?= tarih_tr($h['tarih']) ?></small></div><div class="tutar <?= $r ?>"><?= $h['tip'] === 'tahsilat' ? '+' : '−' ?><?= para($h['tutar']) ?></div></div>
<?php endforeach; if (!$hareket): ?><div class="bos">Henüz hareket yok.</div><?php endif; ?></div>
</div></div>
<?php include 'inc/layout_bottom.php';
