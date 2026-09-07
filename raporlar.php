<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$r = get('r', 'kar'); $ay = get('ay', date('Y-m'));
$sek = ['kar'=>'Şantiye Kâr/Zarar','musteri'=>'Müşteri Alacak','hakedis'=>'Personel Hakediş','borc'=>'Tedarikçi Borç','cek'=>'Çek Takvimi','nakit'=>'Nakit Akışı'];
$aciklama = ['kar'=>'Şantiye bazında anlaşma, maliyet ve kâr marjı','musteri'=>'Müşteri bazında açık alacak ve yaşlandırma',
  'hakedis'=>'Seçili ayın puantajından doğan hakediş ve ödemeler','borc'=>'Tedarikçi bazında alım, ödeme ve kalan borç',
  'cek'=>'Seçili ayın çek vadeleri','nakit'=>'30 / 60 günlük nakit projeksiyonu ve açık pozisyonlar'];
$ayAd = function(string $a){ $m=['01'=>'Ocak','02'=>'Şubat','03'=>'Mart','04'=>'Nisan','05'=>'Mayıs','06'=>'Haziran','07'=>'Temmuz','08'=>'Ağustos','09'=>'Eylül','10'=>'Ekim','11'=>'Kasım','12'=>'Aralık'];
  [$y,$mm]=explode('-',$a); return ($m[$mm]??$mm).' '.$y; };
$baslik = 'Raporlar'; include 'inc/layout_top.php'; ?>
<div class="sekmeler"><?php foreach ($sek as $k=>$v): ?><a href="?r=<?= $k ?>&ay=<?= $ay ?>" class="<?= $r===$k?'aktif':'' ?>"><?= $v ?></a><?php endforeach; ?></div>
<div class="rapor-bas">
  <div><h2><?= $sek[$r] ?></h2><p><?= $aciklama[$r] ?><?= in_array($r,['hakedis','cek']) ? ' · '.$ayAd($ay) : '' ?></p></div>
  <div class="araclar">
    <?php if (in_array($r, ['hakedis','cek'])): ?><input type="month" value="<?= e($ay) ?>" onchange="location='?r=<?= $r ?>&ay='+this.value"><?php endif; ?>
    <a class="btn btn-cizgi btn-kucuk" href="export.php?tip=<?= $r ?>&ay=<?= $ay ?>"><svg class="ic" aria-hidden="true"><use href="#i-download-simple"></use></svg> Excel</a>
    <button class="btn btn-cizgi btn-kucuk" onclick="print()"><svg class="ic" aria-hidden="true"><use href="#i-printer"></use></svg> Yazdır</button>
  </div>
</div>

<?php if ($r === 'kar'):
  $sat = []; $tk = $tm = $tt = $tTah = 0;
  foreach (rows("SELECT id FROM santiyeler ORDER BY durum='aktif' DESC, ad") as $x) {
    $o = santiye_ozet($x['id']); $sat[] = $o;
    $tk += $o['kar']; $tm += $o['maliyet']; $tt += $o['s']['anlasma_tutari']; $tTah += $o['tahsil'];
  }
  $marj = $tt > 0 ? round($tk / $tt * 100) : 0; ?>
<div class="rapor-ozet">
  <div class="ozet ozet-mavi"><div class="baslik">Toplam anlaşma</div><div class="rakam"><?= para($tt) ?></div></div>
  <div class="ozet ozet-yesil"><div class="baslik">Tahsil edilen</div><div class="rakam"><?= para($tTah) ?></div></div>
  <div class="ozet ozet-kirmizi"><div class="baslik">Toplam maliyet</div><div class="rakam"><?= para($tm) ?></div></div>
  <div class="ozet <?= $tk>=0?'ozet-yesil':'ozet-kirmizi' ?>"><div class="baslik">Kâr / Zarar</div><div class="rakam"><?= para($tk) ?></div><div class="alt">marj %<?= $marj ?></div></div>
</div>
<div class="tablo-kutu rtablo"><table>
<thead><tr><th>Şantiye</th><th>Durum</th><th class="num">Anlaşma</th><th class="num">Tahsilat</th><th class="num detay">Yevmiye</th><th class="num detay">Makine</th><th class="num detay">Malzeme</th><th class="num">Maliyet</th><th class="num">Kâr/Zarar</th><th class="num">Marj</th></tr></thead>
<tbody><?php foreach ($sat as $o): $s = $o['s']; ?>
<tr><td class="bas"><b><?= e($s['ad']) ?></b><br><small><?= e($s['musteri']) ?></small></td>
<td data-l="Durum"><?= durum_etiketi($s['durum']) ?></td>
<td class="num" data-l="Anlaşma"><?= para($s['anlasma_tutari']) ?></td>
<td class="num yesil" data-l="Tahsilat"><?= para($o['tahsil']) ?></td>
<td class="num detay" data-l="Yevmiye"><?= para($o['yevmiye']) ?></td>
<td class="num detay" data-l="Makine"><?= para($o['makine']) ?></td>
<td class="num detay" data-l="Malzeme"><?= para($o['malzeme']) ?></td>
<td class="num kirmizi" data-l="Maliyet"><?= para($o['maliyet']) ?></td>
<td class="num b <?= $o['kar']>=0?'yesil':'kirmizi' ?>" data-l="Kâr/Zarar"><?= para($o['kar']) ?></td>
<td class="num" data-l="Marj"><?= $s['anlasma_tutari']>0 ? round($o['kar']/$s['anlasma_tutari']*100) . '%' : '-' ?></td></tr>
<?php endforeach; ?></tbody>
<tfoot><tr><td class="bas" colspan="2">Toplam</td><td class="num" data-l="Anlaşma"><?= para($tt) ?></td><td class="num" data-l="Tahsilat"><?= para($tTah) ?></td><td class="detay"></td><td class="detay"></td><td class="detay"></td><td class="num" data-l="Maliyet"><?= para($tm) ?></td><td class="num" data-l="Kâr/Zarar"><?= para($tk) ?></td><td class="num" data-l="Marj"><?= $marj ?>%</td></tr></tfoot>
</table></div>

<?php elseif ($r === 'musteri'):
  $sat = []; $tA = $tT = $tK = 0; $kv = [0,0,0];
  foreach (rows("SELECT * FROM musteriler ORDER BY ad") as $mu) {
    $o = musteri_ozet($mu['id']); if (!$o['santiye']) continue;
    $tA += $o['anlasma']; $tT += $o['tahsil']; $tK += $o['kalan'];
    $k = [0,0,0];
    foreach (rows("SELECT id, anlasma_tutari FROM santiyeler WHERE musteri_id=?", [$mu['id']]) as $sx) {
      $kal = (float)$sx['anlasma_tutari'] - (float)val("SELECT COALESCE(SUM(tutar),0) FROM tahsilatlar WHERE santiye_id=?", [$sx['id']]);
      if ($kal <= 0) continue; $g = alacak_yas_gun((int)$sx['id']); $k[$g <= 30 ? 0 : ($g <= 60 ? 1 : 2)] += $kal;
    }
    foreach ($k as $i => $v) $kv[$i] += $v;
    $sat[] = [$mu, $o, $k];
  } ?>
<div class="rapor-ozet">
  <div class="ozet ozet-mavi"><div class="baslik">Toplam sözleşme</div><div class="rakam"><?= para($tA) ?></div></div>
  <div class="ozet ozet-yesil"><div class="baslik">Tahsil edilen</div><div class="rakam"><?= para($tT) ?></div></div>
  <div class="ozet ozet-sari"><div class="baslik">Kalan alacak</div><div class="rakam"><?= para($tK) ?></div></div>
  <div class="ozet ozet-kirmizi"><div class="baslik">60+ gün geciken</div><div class="rakam"><?= para($kv[2]) ?></div></div>
</div>
<div class="tablo-kutu rtablo"><table>
<thead><tr><th>Müşteri</th><th class="detay">Yetkili · Telefon</th><th class="num">Şantiye</th><th class="num">Sözleşme</th><th class="num">Tahsilat</th><th class="num">Kalan alacak</th><th class="num">0-30</th><th class="num">30-60</th><th class="num">60+</th></tr></thead>
<tbody><?php foreach ($sat as [$mu, $o, $k]): ?>
<tr><td class="bas"><a href="musteri.php?id=<?= $mu['id'] ?>"><b><?= e($mu['ad']) ?></b></a></td>
<td class="detay" data-l="Yetkili"><?php $yt = trim($mu['yetkili'].' '.$mu['telefon']); ?><?php if ($yt): ?><small><?= e($yt) ?></small><?php endif; ?></td>
<td class="num" data-l="Şantiye"><?= $o['santiye'] ?></td>
<td class="num" data-l="Sözleşme"><?= para($o['anlasma']) ?></td>
<td class="num yesil" data-l="Tahsilat"><?= para($o['tahsil']) ?></td>
<td class="num b <?= $o['kalan']>0?'sari':'gri' ?>" data-l="Kalan alacak"><?= para($o['kalan']) ?></td>
<td class="num yesil" data-l="0-30 gün"><?= $k[0] ? para($k[0]) : '' ?></td>
<td class="num sari" data-l="30-60 gün"><?= $k[1] ? para($k[1]) : '' ?></td>
<td class="num kirmizi" data-l="60+ gün"><?= $k[2] ? para($k[2]) : '' ?></td></tr>
<?php endforeach; if (!$sat): ?><tr><td colspan="9" class="bos">Kayıt yok.</td></tr><?php endif; ?></tbody>
<tfoot><tr><td class="bas" colspan="2">Toplam</td><td class="detay"></td><td class="num" data-l="Sözleşme"><?= para($tA) ?></td><td class="num" data-l="Tahsilat"><?= para($tT) ?></td><td class="num" data-l="Kalan alacak"><?= para($tK) ?></td><td class="num" data-l="0-30 gün"><?= para($kv[0]) ?></td><td class="num" data-l="30-60 gün"><?= para($kv[1]) ?></td><td class="num" data-l="60+ gün"><?= para($kv[2]) ?></td></tr></tfoot>
</table></div>

<?php elseif ($r === 'hakedis'): $gt = $go = 0;
  $liste = rows("SELECT p.id, p.ad_soyad, p.gorev, SUM(pu.durum='tam') tam, SUM(pu.durum='yarim') yarim, SUM(pu.mesai_saat) mesai, SUM(CASE pu.durum WHEN 'tam' THEN pu.yevmiye WHEN 'yarim' THEN pu.yevmiye/2 ELSE 0 END + pu.mesai_saat*pu.yevmiye/9) hak,
    (SELECT COALESCE(SUM(tutar),0) FROM personel_odeme o WHERE o.personel_id=p.id AND DATE_FORMAT(o.tarih,'%Y-%m')=?) odenen FROM personel p JOIN puantaj pu ON pu.personel_id=p.id WHERE DATE_FORMAT(pu.tarih,'%Y-%m')=? GROUP BY p.id ORDER BY p.ad_soyad", [$ay, $ay]);
  foreach ($liste as $p) { $gt += $p['hak']; $go += $p['odenen']; } ?>
<div class="rapor-ozet">
  <div class="ozet ozet-mavi"><div class="baslik">Personel</div><div class="rakam"><?= count($liste) ?></div><div class="alt"><?= $ayAd($ay) ?> puantajı</div></div>
  <div class="ozet ozet-turuncu"><div class="baslik">Toplam hakediş</div><div class="rakam"><?= para($gt) ?></div></div>
  <div class="ozet ozet-yesil"><div class="baslik">Ödenen</div><div class="rakam"><?= para($go) ?></div></div>
  <div class="ozet <?= $gt-$go>0?'ozet-kirmizi':'ozet-gri' ?>"><div class="baslik">Kalan borç</div><div class="rakam"><?= para($gt-$go) ?></div></div>
</div>
<div class="tablo-kutu rtablo"><table>
<thead><tr><th>Personel</th><th>Görev</th><th class="num">Tam</th><th class="num">Yarım</th><th class="num">Mesai (sa)</th><th class="num">Hakediş</th><th class="num">Ödenen (ay)</th><th class="num">Kalan</th></tr></thead>
<tbody><?php foreach ($liste as $p): ?>
<tr><td class="bas"><a href="personel.php?id=<?= $p['id'] ?>"><b><?= e($p['ad_soyad']) ?></b></a></td>
<td data-l="Görev"><?= gorev_adi($p['gorev']) ?></td>
<td class="num" data-l="Tam gün"><?= $p['tam'] ?></td>
<td class="num" data-l="Yarım gün"><?= $p['yarim'] ?></td>
<td class="num" data-l="Mesai (sa)"><?= $p['mesai'] ?: '-' ?></td>
<td class="num b" data-l="Hakediş"><?= para($p['hak']) ?></td>
<td class="num yesil" data-l="Ödenen"><?= para($p['odenen']) ?></td>
<td class="num <?= $p['hak']-$p['odenen']>0?'kirmizi':'gri' ?>" data-l="Kalan"><?= para($p['hak']-$p['odenen']) ?></td></tr>
<?php endforeach; if (!$liste): ?><tr><td colspan="8" class="bos">Bu ay puantaj yok.</td></tr><?php endif; ?></tbody>
<tfoot><tr><td class="bas" colspan="5">Toplam</td><td class="num" data-l="Hakediş"><?= para($gt) ?></td><td class="num" data-l="Ödenen"><?= para($go) ?></td><td class="num" data-l="Kalan"><?= para($gt-$go) ?></td></tr></tfoot>
</table></div>

<?php elseif ($r === 'borc'):
  $sat = []; $top = $topAlim = $topOdeme = $topCek = 0;
  foreach (rows("SELECT * FROM tedarikciler ORDER BY firma") as $t) {
    $b = tedarikci_bakiye($t['id']); if ($b['alim'] == 0 && $b['odeme'] == 0) continue;
    $cek = (float)val("SELECT COALESCE(SUM(tutar),0) FROM cekler WHERE tedarikci_id=? AND yon='verilen' AND durum='portfoy'", [$t['id']]);
    $top += max(0, $b['bakiye']); $topAlim += $b['alim']; $topOdeme += $b['odeme']; $topCek += $cek;
    $sat[] = [$t, $b, $cek];
  } ?>
<div class="rapor-ozet">
  <div class="ozet ozet-mavi"><div class="baslik">Tedarikçi</div><div class="rakam"><?= count($sat) ?></div><div class="alt">hareketli hesap</div></div>
  <div class="ozet ozet-gri"><div class="baslik">Toplam alım</div><div class="rakam"><?= para($topAlim) ?></div></div>
  <div class="ozet ozet-yesil"><div class="baslik">Ödenen</div><div class="rakam"><?= para($topOdeme) ?></div></div>
  <div class="ozet ozet-kirmizi"><div class="baslik">Kalan borç</div><div class="rakam"><?= para($top) ?></div><div class="alt">çek portföyü <?= para($topCek) ?></div></div>
</div>
<div class="tablo-kutu rtablo"><table>
<thead><tr><th>Tedarikçi</th><th>Kategori</th><th class="num">Alım</th><th class="num">Ödeme</th><th class="num">Borç</th><th class="num detay">Verilen çek (portföy)</th></tr></thead>
<tbody><?php foreach ($sat as [$t, $b, $cek]): ?>
<tr><td class="bas"><a href="tedarikci.php?id=<?= $t['id'] ?>"><b><?= e($t['firma']) ?></b></a></td>
<td data-l="Kategori"><?= e($t['kategori']) ?></td>
<td class="num" data-l="Alım"><?= para($b['alim']) ?></td>
<td class="num yesil" data-l="Ödeme"><?= para($b['odeme']) ?></td>
<td class="num b <?= $b['bakiye']>0?'kirmizi':'gri' ?>" data-l="Borç"><?= para($b['bakiye']) ?></td>
<td class="num sari detay" data-l="Verilen çek"><?= para($cek) ?></td></tr>
<?php endforeach; if (!$sat): ?><tr><td colspan="6" class="bos">Kayıt yok.</td></tr><?php endif; ?></tbody>
<tfoot><tr><td class="bas" colspan="2">Toplam</td><td class="num" data-l="Alım"><?= para($topAlim) ?></td><td class="num" data-l="Ödeme"><?= para($topOdeme) ?></td><td class="num" data-l="Borç"><?= para($top) ?></td><td class="num detay" data-l="Verilen çek"><?= para($topCek) ?></td></tr></tfoot>
</table></div>

<?php elseif ($r === 'cek'): $ilk = strtotime("$ay-01"); $gun = (int)date('t', $ilk); $hafta = (int)date('N', $ilk);
  $cek = rows("SELECT c.*, s.ad santiye, t.firma FROM cekler c LEFT JOIN santiyeler s ON s.id=c.santiye_id LEFT JOIN tedarikciler t ON t.id=c.tedarikci_id WHERE DATE_FORMAT(c.vade,'%Y-%m')=? ORDER BY c.vade", [$ay]);
  $byGun = []; foreach ($cek as $c) $byGun[(int)date('j', strtotime($c['vade']))][] = $c;
  $al = array_sum(array_map(fn($c) => $c['yon']==='alinan' && $c['durum']==='portfoy' ? $c['tutar'] : 0, $cek));
  $ve = array_sum(array_map(fn($c) => $c['yon']==='verilen' && $c['durum']==='portfoy' ? $c['tutar'] : 0, $cek)); ?>
<div class="rapor-ozet iki">
  <div class="ozet ozet-yesil"><div class="baslik">Bu ay alınacak</div><div class="rakam"><?= para($al) ?></div><div class="alt">portföydeki alınan çekler</div></div>
  <div class="ozet ozet-kirmizi"><div class="baslik">Bu ay ödenecek</div><div class="rakam"><?= para($ve) ?></div><div class="alt">portföydeki verilen çekler</div></div>
</div>
<div class="takvim"><?php foreach (['Pt','Sa','Ça','Pe','Cu','Ct','Pz'] as $h): ?><div class="bas"><?= $h ?></div><?php endforeach;
for ($i=1;$i<$hafta;$i++) echo '<div></div>';
for ($d=1;$d<=$gun;$d++): $bugun = date('Y-m-d') === "$ay-" . sprintf('%02d', $d); ?>
  <div class="gun <?= $bugun?'bugun':'' ?>"><b><?= $d ?></b><?php foreach ($byGun[$d] ?? [] as $c): ?><a class="c" href="cek_durum.php?id=<?= $c['id'] ?>&geri=<?= urlencode("raporlar.php?r=cek&ay=$ay") ?>" style="background:<?= $c['durum']!=='portfoy' ? '#f0f1f4;color:#6b7280;text-decoration:line-through' : ($c['yon']==='alinan' ? '#e4f5ec;color:#12694a' : '#fdeaea;color:#991b1b') ?>" title="<?= e($c['yon']==='alinan' ? $c['santiye'] : $c['firma']) ?> · <?= e($c['banka']) ?> <?= e($c['cek_no']) ?>"><?= $c['yon']==='alinan'?'<svg class="ic" aria-hidden="true"><use href="#i-arrow-circle-down"></use></svg>':'<svg class="ic" aria-hidden="true"><use href="#i-arrow-circle-up"></use></svg>' ?> <?= para($c['tutar']) ?></a><?php endforeach; ?></div>
<?php endfor; ?></div>
<div class="tablo-kutu rtablo" style="margin-top:1.25rem"><table>
<thead><tr><th>Vade</th><th>Yön</th><th>Kimden / kime</th><th class="detay">Banka · No</th><th>Durum</th><th class="num">Tutar</th></tr></thead>
<tbody><?php foreach ($cek as $c): ?>
<tr><td class="bas"><b><?= tarih_tr($c['vade']) ?></b><span class="sadece-mobil"> · <?= e($c['yon']==='alinan' ? $c['santiye'] : $c['firma']) ?></span></td>
<td data-l="Yön"><?= $c['yon']==='alinan' ? etiket('Alınan','yesil') : etiket('Verilen','kirmizi') ?></td>
<td class="gizle-mobil" data-l="Kimden / kime"><?= e($c['yon']==='alinan' ? $c['santiye'] : $c['firma']) ?></td>
<td class="detay" data-l="Banka · No"><?= e($c['banka']) ?> · <?= e($c['cek_no']) ?></td>
<td data-l="Durum"><?= durum_etiketi($c['durum']) ?></td>
<td class="num b <?= $c['yon']==='alinan'?'yesil':'kirmizi' ?>" data-l="Tutar"><?= para($c['tutar']) ?></td></tr>
<?php endforeach; if (!$cek): ?><tr><td colspan="6" class="bos">Bu ay vadesi gelen çek yok.</td></tr><?php endif; ?></tbody>
</table></div>

<?php elseif ($r === 'nakit'):
  foreach ([30, 60] as $g) { ${"in$g"} = (float)val("SELECT COALESCE(SUM(tutar),0) FROM cekler WHERE yon='alinan' AND durum='portfoy' AND vade BETWEEN CURDATE() AND CURDATE()+INTERVAL ? DAY", [$g]); ${"out$g"} = (float)val("SELECT COALESCE(SUM(tutar),0) FROM cekler WHERE yon='verilen' AND durum='portfoy' AND vade BETWEEN CURDATE() AND CURDATE()+INTERVAL ? DAY", [$g]); }
  $borc = 0; foreach (rows("SELECT id FROM tedarikciler") as $t) $borc += max(0, tedarikci_bakiye($t['id'])['bakiye']);
  $yev = 0; foreach (rows("SELECT id FROM personel") as $p) $yev += max(0, personel_hakedis($p['id'])['kalan']);
  $alacak = (float)val("SELECT COALESCE(SUM(anlasma_tutari),0) FROM santiyeler WHERE durum<>'tamamlandi'") - (float)val("SELECT COALESCE(SUM(t.tutar),0) FROM tahsilatlar t JOIN santiyeler s ON s.id=t.santiye_id WHERE s.durum<>'tamamlandi'");
  $vadesiGecen = (float)val("SELECT COALESCE(SUM(tutar),0) FROM cekler WHERE durum='portfoy' AND vade<CURDATE() AND yon='verilen'"); ?>
<div class="rapor-ozet iki">
  <div class="ozet <?= $in30-$out30>=0?'ozet-yesil':'ozet-kirmizi' ?>"><div class="baslik">30 günlük net akış</div><div class="rakam"><?= para($in30-$out30) ?></div><div class="alt">giriş <?= para($in30) ?> · çıkış <?= para($out30) ?></div></div>
  <div class="ozet <?= $in60-$out60>=0?'ozet-yesil':'ozet-kirmizi' ?>"><div class="baslik">60 günlük net akış</div><div class="rakam"><?= para($in60-$out60) ?></div><div class="alt">giriş <?= para($in60) ?> · çıkış <?= para($out60) ?></div></div>
</div>
<div class="tablo-kutu rtablo"><table>
<thead><tr><th>Kalem</th><th class="num">30 gün</th><th class="num">60 gün</th></tr></thead>
<tbody>
<tr><td class="bas"><svg class="ic" aria-hidden="true"><use href="#i-arrow-circle-down"></use></svg> Beklenen giriş (alınan çekler)</td><td class="num yesil" data-l="30 gün"><?= para($in30) ?></td><td class="num yesil" data-l="60 gün"><?= para($in60) ?></td></tr>
<tr><td class="bas"><svg class="ic" aria-hidden="true"><use href="#i-arrow-circle-up"></use></svg> Beklenen çıkış (verilen çekler)</td><td class="num kirmizi" data-l="30 gün"><?= para($out30) ?></td><td class="num kirmizi" data-l="60 gün"><?= para($out60) ?></td></tr>
</tbody>
<tfoot><tr><td class="bas">Net çek akışı</td><td class="num <?= $in30-$out30>=0?'yesil':'kirmizi' ?>" data-l="30 gün"><?= para($in30-$out30) ?></td><td class="num <?= $in60-$out60>=0?'yesil':'kirmizi' ?>" data-l="60 gün"><?= para($in60-$out60) ?></td></tr></tfoot>
</table></div>
<h2>Açık pozisyonlar</h2>
<div class="rapor-ozet">
  <div class="ozet ozet-yesil"><div class="baslik">Müşteri alacağı</div><div class="rakam"><?= para($alacak) ?></div></div>
  <div class="ozet ozet-kirmizi"><div class="baslik">Tedarikçi borcu</div><div class="rakam"><?= para($borc) ?></div></div>
  <div class="ozet ozet-sari"><div class="baslik">Ödenmemiş yevmiye</div><div class="rakam"><?= para($yev) ?></div></div>
  <div class="ozet ozet-kirmizi"><div class="baslik">Vadesi geçmiş çek</div><div class="rakam"><?= para($vadesiGecen) ?></div></div>
</div>
<?php endif; include 'inc/layout_bottom.php';
