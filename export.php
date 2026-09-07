<?php
// CSV (Excel uyumlu, UTF-8 BOM, ; ayraçlı) dışa aktarma
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$tip = get('tip'); $ay = get('ay', date('Y-m')); $sid = (int)get('santiye_id');
$n = fn($v) => number_format((float)$v, 2, ',', '');
switch ($tip) {
  case 'kar': $bas = ['Şantiye','Müşteri','Durum','Anlaşma','Tahsilat','Kalan','Yevmiye','Makine','Malzeme','Maliyet','Kâr/Zarar'];
    foreach (rows("SELECT id FROM santiyeler ORDER BY ad") as $x) { $o = santiye_ozet($x['id']); $s = $o['s']; $veri[] = [$s['ad'],$s['musteri'] ?? '',$s['durum'],$n($s['anlasma_tutari']),$n($o['tahsil']),$n($o['kalan']),$n($o['yevmiye']),$n($o['makine']),$n($o['malzeme']),$n($o['maliyet']),$n($o['kar'])]; } break;
  case 'hakedis': case 'puantaj': $bas = ['Personel','Görev','Tam','Yarım','Mesai','Hakediş','Ödenen','Kalan']; $w = $sid ? " AND pu.santiye_id=?" : ''; $p = $sid ? [$ay, $sid] : [$ay];
    foreach (rows("SELECT p.*, SUM(pu.durum='tam') tam, SUM(pu.durum='yarim') yarim, SUM(pu.mesai_saat) mesai, SUM(CASE pu.durum WHEN 'tam' THEN pu.yevmiye WHEN 'yarim' THEN pu.yevmiye/2 ELSE 0 END + pu.mesai_saat*pu.yevmiye/9) hak FROM personel p JOIN puantaj pu ON pu.personel_id=p.id WHERE DATE_FORMAT(pu.tarih,'%Y-%m')=? $w GROUP BY p.id ORDER BY p.ad_soyad", $p) as $r) { $od = (float)val("SELECT COALESCE(SUM(tutar),0) FROM personel_odeme WHERE personel_id=? AND DATE_FORMAT(tarih,'%Y-%m')=?", [$r['id'], $ay]); $veri[] = [$r['ad_soyad'], gorev_adi($r['gorev']), $r['tam'], $r['yarim'], $r['mesai'], $n($r['hak']), $n($od), $n($r['hak']-$od)]; } break;
  case 'musteri': $bas = ['Müşteri','Yetkili','Telefon','Şantiye sayısı','Sözleşme','Tahsilat','Kalan alacak','Portföydeki çek'];
    foreach (rows("SELECT * FROM musteriler ORDER BY ad") as $mu) { $o = musteri_ozet($mu['id']); $veri[] = [$mu['ad'],$mu['yetkili'],$mu['telefon'],$o['santiye'],$n($o['anlasma']),$n($o['tahsil']),$n($o['kalan']),$n($o['portfoy_cek'])]; } break;
  case 'borc': case 'tedarikci': $bas = ['Firma','Kategori','Yetkili','Telefon','Alım','Ödeme','Borç'];
    foreach (rows("SELECT * FROM tedarikciler ORDER BY firma") as $t) { $b = tedarikci_bakiye($t['id']); $veri[] = [$t['firma'],$t['kategori'],$t['yetkili'],$t['telefon'],$n($b['alim']),$n($b['odeme']),$n($b['bakiye'])]; } break;
  case 'cek': $bas = ['Vade','Yön','Kimden/Kime','Banka','Çek No','Durum','Tutar'];
    foreach (rows("SELECT c.*, s.ad santiye, t.firma FROM cekler c LEFT JOIN santiyeler s ON s.id=c.santiye_id LEFT JOIN tedarikciler t ON t.id=c.tedarikci_id WHERE DATE_FORMAT(c.vade,'%Y-%m')=? ORDER BY c.vade", [$ay]) as $c) $veri[] = [tarih_tr($c['vade']), $c['yon'], $c['yon']==='alinan' ? $c['santiye'] : $c['firma'], $c['banka'], $c['cek_no'], $c['durum'], $n($c['tutar'])]; break;
  case 'gider': $bas = ['Tarih','Şantiye','Kategori','Açıklama','Tedarikçi','Fiş No','Durum','Tutar']; $w = $sid ? " AND g.santiye_id=?" : ''; $p = $sid ? [$ay, $sid] : [$ay];
    foreach (rows("SELECT g.*, s.ad santiye, k.ad kat, t.firma FROM giderler g JOIN santiyeler s ON s.id=g.santiye_id LEFT JOIN gider_kategorileri k ON k.id=g.kategori_id LEFT JOIN tedarikciler t ON t.id=g.tedarikci_id WHERE DATE_FORMAT(g.tarih,'%Y-%m')=? $w ORDER BY g.tarih", $p) as $g) $veri[] = [tarih_tr($g['tarih']),$g['santiye'],$g['kat'],$g['aciklama'],$g['firma'],$g['fis_no'],$g['odeme_durumu'],$n($g['tutar'])]; break;
  default: $bas = ['Nakit akışı']; $veri = [['Bu rapor için ekran görünümünü yazdırın.']];
}
header('Content-Type: text/csv; charset=utf-8'); header("Content-Disposition: attachment; filename=\"{$tip}_{$ay}.csv\"");
$o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF"); fputcsv($o, $bas, ';', '"', '\\'); foreach ($veri ?? [] as $v) fputcsv($o, $v, ';', '"', '\\'); fclose($o);
