<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$sid = (int)get('santiye_id'); $ay = get('ay', date('Y-m')); $kat = (int)get('kat');
$w = "DATE_FORMAT(g.tarih,'%Y-%m')=?"; $p = [$ay]; if ($sid) { $w .= " AND g.santiye_id=?"; $p[] = $sid; } if ($kat) { $w .= " AND g.kategori_id=?"; $p[] = $kat; }
$liste = rows("SELECT g.*, k.ad kat, k.ikon, t.firma, s.ad santiye FROM giderler g LEFT JOIN gider_kategorileri k ON k.id=g.kategori_id LEFT JOIN tedarikciler t ON t.id=g.tedarikci_id JOIN santiyeler s ON s.id=g.santiye_id WHERE $w ORDER BY g.tarih DESC, g.id DESC", $p);
$baslik = 'Giderler'; $fab = 'gider_form.php'; include 'inc/layout_top.php'; ?>
<form class="araclar"><input type="month" name="ay" value="<?= e($ay) ?>"><select name="santiye_id"><option value="0">Tüm şantiyeler</option><?php foreach (santiyelerim() as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$sid?'selected':'' ?>><?= e($s['ad']) ?></option><?php endforeach; ?></select>
<select name="kat"><option value="0">Tüm kategoriler</option><?php foreach (rows("SELECT * FROM gider_kategorileri ORDER BY sira") as $k): ?><option value="<?= $k['id'] ?>" <?= $k['id']==$kat?'selected':'' ?>><?= e($k['ad']) ?></option><?php endforeach; ?></select><button class="btn btn-kucuk">Filtrele</button><a class="btn btn-cizgi btn-kucuk" href="export.php?tip=gider&ay=<?= $ay ?>&santiye_id=<?= $sid ?>"><svg class="ic" aria-hidden="true"><use href="#i-download-simple"></use></svg> Excel</a></form>
<div class="grid" style="grid-template-columns:repeat(3,1fr)"><div class="ozet ozet-kirmizi"><div class="baslik">Toplam</div><div class="rakam"><?= para(array_sum(array_column($liste, 'tutar'))) ?></div></div>
<div class="ozet ozet-yesil"><div class="baslik">Ödenen</div><div class="rakam"><?= para(array_sum(array_map(fn($r) => $r['odeme_durumu']==='odendi' ? $r['tutar'] : 0, $liste))) ?></div></div>
<div class="ozet ozet-sari"><div class="baslik">Borç</div><div class="rakam"><?= para(array_sum(array_map(fn($r) => $r['odeme_durumu']==='borc' ? $r['tutar'] : 0, $liste))) ?></div></div></div>
<div class="tablo-kutu"><table><tr><th>Tarih</th><th>Şantiye</th><th>Kategori</th><th>Açıklama</th><th>Tedarikçi</th><th>Durum</th><th class="num">Tutar</th></tr>
<?php foreach ($liste as $r): ?><tr onclick="location='gider_form.php?id=<?= $r['id'] ?>'" style="cursor:pointer"><td><?= tarih_tr($r['tarih']) ?></td><td><?= e($r['santiye']) ?></td><td><?= kat_ikon($r['ikon']) ?> <?= e($r['kat']) ?></td><td><?= e($r['aciklama']) ?><?= fotolar($r['fotolar']) ? ' <svg class="ic" aria-hidden="true"><use href="#i-camera"></use></svg>' : '' ?></td><td><?= e($r['firma'] ?: '-') ?></td><td><?= durum_etiketi($r['odeme_durumu']) ?></td><td class="num kirmizi b"><?= para($r['tutar']) ?></td></tr><?php endforeach; ?>
<?php if (!$liste): ?><tr><td colspan="7" class="bos">Bu dönemde gider yok.</td></tr><?php endif; ?></table></div>
<?php include 'inc/layout_bottom.php';
