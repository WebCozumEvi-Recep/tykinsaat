<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli();
$id = (int)get('id'); santiye_erisim($id);
$s = row("SELECT s.*, m.ad AS musteri FROM santiyeler s LEFT JOIN musteriler m ON m.id=s.musteri_id WHERE s.id=?", [$id]); if (!$s) redirect('santiyeler.php');
$sek = get('sek', 'ozet'); $fin = finans_gorur();
if (!$fin && in_array($sek, ['ozet','tahsilat','borc'])) $sek = 'puantaj';
$baslik = $s['ad']; $geri = 'santiyeler.php'; $fab = "gider_form.php?santiye_id=$id";
$sekmeler = $fin ? ['ozet'=>'Özet','puantaj'=>'Puantaj','gider'=>'Giderler','makine'=>'Makineler','tahsilat'=>'Tahsilat','borc'=>'Tedarikçi Borç','gunluk'=>'İş Günlüğü'] : ['puantaj'=>'Puantaj','gider'=>'Giderler','makine'=>'Makineler','gunluk'=>'İş Günlüğü'];
include 'inc/layout_top.php'; ?>
<div class="kart" style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;flex-wrap:wrap"><div><small><svg class="ic" aria-hidden="true"><use href="#i-user-circle"></use></svg> <?= $fin ? '<a href="musteri.php?id=' . (int)$s['musteri_id'] . '"><b>' . e($s['musteri']) . '</b></a>' : e($s['musteri']) ?><?= $s['adres'] ? ' · <svg class="ic" aria-hidden="true"><use href="#i-map-pin"></use></svg> ' . e($s['adres']) : '' ?></small><br><small><?= tarih_tr($s['baslangic']) ?> → <?= tarih_tr($s['bitis']) ?></small></div>
<div style="display:flex;gap:.5rem;align-items:center"><?= durum_etiketi($s['durum']) ?><?php if (patron()): ?><a class="btn btn-cizgi btn-kucuk" href="santiye_form.php?id=<?= $id ?>"><svg class="ic" aria-hidden="true"><use href="#i-pencil-simple"></use></svg> Düzenle</a><?php endif; ?></div></div>
<div class="sekmeler"><?php foreach ($sekmeler as $k=>$v): ?><a href="?id=<?= $id ?>&sek=<?= $k ?>" class="<?= $sek===$k?'aktif':'' ?>"><?= $v ?></a><?php endforeach; ?></div>

<?php if ($sek === 'ozet'): $o = santiye_ozet($id);
  $top = max($o['maliyet'], 0.01); $py = $o['yevmiye']/$top*100; $pm = $o['makine']/$top*100; $pz = $o['malzeme']/$top*100; ?>
<div class="grid">
  <div class="ozet ozet-mavi"><div class="baslik">Anlaşma</div><div class="rakam"><?= para($s['anlasma_tutari']) ?></div></div>
  <div class="ozet ozet-yesil"><div class="baslik">Tahsil edilen</div><div class="rakam"><?= para($o['tahsil']) ?></div></div>
  <div class="ozet ozet-sari"><div class="baslik">Kalan alacak</div><div class="rakam"><?= para($o['kalan']) ?></div></div>
  <div class="ozet <?= $o['kar']>=0?'ozet-yesil':'ozet-kirmizi' ?>"><div class="baslik">Kâr / Zarar</div><div class="rakam"><?= para($o['kar']) ?></div><div class="alt">Maliyet <?= para($o['maliyet']) ?></div></div>
</div>
<div class="kart"><h3>Maliyet dağılımı</h3><div class="pasta">
  <div class="daire" style="background:conic-gradient(#1e2a44 0 <?= $py ?>%, #f2801e <?= $py ?>% <?= $py+$pm ?>%, #d63a3a <?= $py+$pm ?>% <?= $py+$pm+$pz ?>%, #e8eaef <?= $py+$pm+$pz ?>% 100%)"></div>
  <ul><li><i style="background:#1e2a44"></i>Yevmiye <b><?= para($o['yevmiye']) ?></b></li><li><i style="background:#f2801e"></i>Makine / kiralama <b><?= para($o['makine']) ?></b></li><li><i style="background:#d63a3a"></i>Malzeme & diğer <b><?= para($o['malzeme']) ?></b></li></ul></div>
<?php if ($s['notlar']): ?><p class="muted" style="margin-top:1rem"><?= nl2br(e($s['notlar'])) ?></p><?php endif; ?></div>

<?php elseif ($sek === 'puantaj'): $ay = get('ay', date('Y-m'));
  $liste = rows("SELECT p.tarih, COUNT(*) n, SUM(p.durum='tam') tam, SUM(p.durum='yarim') yarim, SUM(CASE p.durum WHEN 'tam' THEN p.yevmiye WHEN 'yarim' THEN p.yevmiye/2 ELSE 0 END + p.mesai_saat*p.yevmiye/9) tutar FROM puantaj p WHERE p.santiye_id=? AND DATE_FORMAT(p.tarih,'%Y-%m')=? GROUP BY p.tarih ORDER BY p.tarih DESC", [$id, $ay]); ?>
<div class="araclar"><input type="month" value="<?= e($ay) ?>" onchange="location='?id=<?= $id ?>&sek=puantaj&ay='+this.value"><a class="btn btn-turuncu btn-kucuk" href="puantaj.php?santiye_id=<?= $id ?>">+ Puantaj gir</a><?php if ($fin): ?><a class="btn btn-cizgi btn-kucuk" href="puantaj_aylik.php?santiye_id=<?= $id ?>&ay=<?= $ay ?>">Aylık tablo</a><?php endif; ?></div>
<div class="kart liste"><?php if (!$liste): ?><div class="bos">Bu ay puantaj yok.</div><?php endif; foreach ($liste as $r): ?>
  <a class="satir" href="puantaj.php?santiye_id=<?= $id ?>&tarih=<?= $r['tarih'] ?>"><div class="ico-b"><svg class="ic" aria-hidden="true"><use href="#i-calendar-blank"></use></svg></div><div class="govde"><div class="ad"><?= tarih_tr($r['tarih']) ?></div><small><?= $r['tam'] ?> tam · <?= $r['yarim'] ?> yarım · <?= $r['n']-$r['tam']-$r['yarim'] ?> gelmedi</small></div><?php if ($fin): ?><div class="tutar"><?= para($r['tutar']) ?></div><?php endif; ?></a>
<?php endforeach; ?></div>

<?php elseif ($sek === 'gider'):
  $g = rows("SELECT g.*, k.ad kat, k.ikon, t.firma FROM giderler g LEFT JOIN gider_kategorileri k ON k.id=g.kategori_id LEFT JOIN tedarikciler t ON t.id=g.tedarikci_id WHERE g.santiye_id=? ORDER BY g.tarih DESC, g.id DESC LIMIT 200", [$id]); ?>
<div class="araclar"><a class="btn btn-turuncu btn-kucuk" href="gider_form.php?santiye_id=<?= $id ?>">+ Gider ekle</a><?php if ($fin): ?><span class="muted">Toplam: <b><?= para(array_sum(array_column($g, 'tutar'))) ?></b></span><?php endif; ?></div>
<div class="kart liste"><?php if (!$g): ?><div class="bos">Gider kaydı yok.</div><?php endif; foreach ($g as $r): ?>
  <a class="satir" href="gider_form.php?id=<?= $r['id'] ?>"><div class="ico-b"><?= kat_ikon($r['ikon']) ?></div><div class="govde"><div class="ad"><?= e($r['aciklama'] ?: $r['kat']) ?></div><small><?= tarih_tr($r['tarih']) ?> · <?= e($r['firma'] ?: 'Tedarikçi yok') ?> <?= $fin ? durum_etiketi($r['odeme_durumu']) : '' ?><?= fotolar($r['fotolar']) ? ' <svg class="ic" aria-hidden="true"><use href="#i-camera"></use></svg>' : '' ?></small></div><?php if ($fin): ?><div class="tutar kirmizi"><?= para($r['tutar']) ?></div><?php endif; ?></a>
<?php endforeach; ?></div>

<?php elseif ($sek === 'makine'):
  $m = rows("SELECT * FROM makineler WHERE santiye_id=? ORDER BY ad", [$id]);
  $k = rows("SELECT mk.*, t.firma FROM makine_kiralama mk JOIN tedarikciler t ON t.id=mk.tedarikci_id WHERE mk.santiye_id=? ORDER BY mk.baslangic DESC", [$id]); ?>
<h3>Kendi makinelerimiz</h3><div class="kart liste"><?php if (!$m): ?><div class="bos">Bu şantiyede makine yok.</div><?php endif; foreach ($m as $r): ?>
  <div class="satir"><div class="ico-b"><svg class="ic" aria-hidden="true"><use href="#i-tractor"></use></svg></div><div class="govde"><div class="ad"><?= e($r['ad']) ?></div><small><?= tip_adi($r['tip']) ?> · <?= e($r['plaka']) ?></small></div><?= durum_etiketi($r['durum']) ?></div><?php endforeach; ?></div>
<h3>Dış kiralama</h3><div class="kart liste"><?php if (!$k): ?><div class="bos">Kiralama kaydı yok.</div><?php endif; foreach ($k as $r): ?>
  <div class="satir"><div class="ico-b"><svg class="ic" aria-hidden="true"><use href="#i-factory"></use></svg></div><div class="govde"><div class="ad"><?= e($r['makine_adi']) ?></div><small><?= e($r['firma']) ?> · <?= tarih_tr($r['baslangic']) ?> → <?= tarih_tr($r['bitis']) ?> · <?= tip_adi($r['tip']) ?></small></div><?php if ($fin): ?><div class="tutar"><?= para($r['toplam']) ?></div><?php endif; ?></div><?php endforeach; ?></div>

<?php elseif ($sek === 'tahsilat'):
  $t = rows("SELECT t.*, c.cek_no, c.vade, c.durum cdurum FROM tahsilatlar t LEFT JOIN cekler c ON c.id=t.cek_id WHERE t.santiye_id=? ORDER BY t.tarih DESC", [$id]); ?>
<div class="araclar"><a class="btn btn-yesil btn-kucuk" href="tahsilat_form.php?santiye_id=<?= $id ?>">+ Tahsilat ekle</a><span class="muted">Toplam: <b class="yesil"><?= para(array_sum(array_column($t, 'tutar'))) ?></b></span></div>
<div class="kart liste"><?php if (!$t): ?><div class="bos">Tahsilat yok.</div><?php endif; foreach ($t as $r): ?>
  <div class="satir"><div class="ico-b"><svg class="ic" aria-hidden="true"><use href="#i-coins"></use></svg></div><div class="govde"><div class="ad"><?= tip_adi($r['tip']) ?><?= $r['cek_no'] ? ' · Çek ' . e($r['cek_no']) : '' ?></div><small><?= tarih_tr($r['tarih']) ?> <?= $r['vade'] ? '· Vade ' . tarih_tr($r['vade']) . ' ' . durum_etiketi($r['cdurum']) : '' ?> <?= e($r['aciklama']) ?></small></div><div class="tutar yesil">+<?= para($r['tutar']) ?></div></div><?php endforeach; ?></div>

<?php elseif ($sek === 'borc'):
  $b = rows("SELECT t.id, t.firma, SUM(g.tutar) alim, SUM(CASE WHEN g.odeme_durumu='borc' THEN g.tutar ELSE 0 END) borc FROM giderler g JOIN tedarikciler t ON t.id=g.tedarikci_id WHERE g.santiye_id=? GROUP BY t.id ORDER BY borc DESC", [$id]); ?>
<div class="kart liste"><?php if (!$b): ?><div class="bos">Tedarikçi borcu yok.</div><?php endif; foreach ($b as $r): ?>
  <a class="satir" href="tedarikci.php?id=<?= $r['id'] ?>&santiye_id=<?= $id ?>"><div class="ico-b"><svg class="ic" aria-hidden="true"><use href="#i-factory"></use></svg></div><div class="govde"><div class="ad"><?= e($r['firma']) ?></div><small>Toplam alım <?= para($r['alim']) ?></small></div><div class="tutar kirmizi"><?= para($r['borc']) ?></div></a><?php endforeach; ?></div>
<div class="bilgi">Bu liste yalnızca bu şantiyeye kesilen ve "borç" olarak kaydedilen alımları gösterir. Genel bakiye için Tedarikçiler sayfasına bakın.</div>

<?php elseif ($sek === 'gunluk'):
  $g = rows("SELECT * FROM is_gunlugu WHERE santiye_id=? ORDER BY tarih DESC, id DESC LIMIT 100", [$id]); ?>
<div class="araclar"><a class="btn btn-turuncu btn-kucuk" href="is_gunlugu_form.php?santiye_id=<?= $id ?>">+ İş kaydı</a></div>
<?php if (!$g): ?><div class="kart bos">İş kaydı yok.</div><?php endif; foreach ($g as $r): ?>
  <div class="kart"><div style="display:flex;justify-content:space-between"><b><?= hava_ikon($r['hava']) ?> <?= tarih_tr($r['tarih']) ?></b><?php if ($r['metraj']): ?><span class="tag tag-mavi"><?= number_format($r['metraj'], 2, ',', '.') ?> <?= $r['birim'] ?></span><?php endif; ?></div>
  <p style="margin:.5rem 0"><?= nl2br(e($r['aciklama'])) ?></p>
  <?php if ($f = fotolar($r['fotolar'])): ?><div class="foto-galeri"><?php foreach ($f as $ff): ?><a href="<?= UPLOAD_URL . e($ff) ?>" target="_blank"><img src="<?= UPLOAD_URL . e($ff) ?>" alt=""></a><?php endforeach; ?></div><?php endif; ?></div>
<?php endforeach; endif;
include 'inc/layout_bottom.php';
