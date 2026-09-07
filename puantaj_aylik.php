<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$ay = get('ay', date('Y-m')); $sid = (int)get('santiye_id'); [$y, $m] = explode('-', $ay); $gun = (int)date('t', strtotime("$ay-01"));
$w = $sid ? " AND p.santiye_id=?" : ''; $pp = $sid ? [$ay, $sid] : [$ay];
$kay = rows("SELECT p.*, DAY(p.tarih) g FROM puantaj p WHERE DATE_FORMAT(p.tarih,'%Y-%m')=? $w", $pp);
$mat = []; foreach ($kay as $k) $mat[$k['personel_id']][$k['g']] = $k;
$personel = rows("SELECT * FROM personel WHERE id IN (SELECT personel_id FROM puantaj p WHERE DATE_FORMAT(p.tarih,'%Y-%m')=? $w) OR aktif=1 ORDER BY ad_soyad", $pp);
$baslik = 'Aylık Puantaj'; $geri = 'puantaj.php';
include 'inc/layout_top.php'; ?>
<div class="araclar"><input type="month" value="<?= e($ay) ?>" onchange="location='?santiye_id=<?= $sid ?>&ay='+this.value">
<select onchange="location='?ay=<?= $ay ?>&santiye_id='+this.value"><option value="0">Tüm şantiyeler</option><?php foreach (santiyelerim() as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$sid?'selected':'' ?>><?= e($s['ad']) ?></option><?php endforeach; ?></select>
<a class="btn btn-cizgi btn-kucuk" href="export.php?tip=puantaj&ay=<?= $ay ?>&santiye_id=<?= $sid ?>"><svg class="ic" aria-hidden="true"><use href="#i-download-simple"></use></svg> Excel</a><button class="btn btn-cizgi btn-kucuk" onclick="print()"><svg class="ic" aria-hidden="true"><use href="#i-printer"></use></svg> PDF</button></div>
<div class="tablo-kutu"><table class="ay-tablo"><thead><tr><th>Personel</th><?php for ($d=1;$d<=$gun;$d++): ?><th><?= $d ?></th><?php endfor; ?><th>Tam</th><th>Yarım</th><th>Mesai</th><th class="num">Ödenecek</th></tr></thead><tbody>
<?php $gt = 0; foreach ($personel as $p): $tam=$yar=$mes=0; $tut=0; ?>
<tr><td><?= e($p['ad_soyad']) ?></td><?php for ($d=1;$d<=$gun;$d++): $k = $mat[$p['id']][$d] ?? null; $c = $k ? ['tam'=>'t','yarim'=>'y','yok'=>'x'][$k['durum']] : '';
  if ($k) { if ($k['durum']==='tam') { $tam++; $tut += $k['yevmiye']; } elseif ($k['durum']==='yarim') { $yar++; $tut += $k['yevmiye']/2; } $mes += $k['mesai_saat']; $tut += $k['mesai_saat']*$k['yevmiye']/9; } ?>
  <td class="<?= $c ?>"><?= $k ? ['tam'=>'✓','yarim'=>'½','yok'=>'−'][$k['durum']] : '' ?></td><?php endfor; $gt += $tut; ?>
  <td><?= $tam ?></td><td><?= $yar ?></td><td><?= $mes ?: '' ?></td><td class="num b"><?= para($tut) ?></td></tr>
<?php endforeach; ?></tbody><tfoot><tr><td colspan="<?= $gun+4 ?>">Toplam ödenecek yevmiye</td><td class="num"><?= para($gt) ?></td></tr></tfoot></table></div>
<?php include 'inc/layout_bottom.php';
