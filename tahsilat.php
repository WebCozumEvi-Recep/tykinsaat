<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$liste = rows("SELECT t.*, s.ad santiye, m.ad musteri, s.musteri_id, c.cek_no, c.banka, c.vade, c.durum cdurum FROM tahsilatlar t JOIN santiyeler s ON s.id=t.santiye_id LEFT JOIN musteriler m ON m.id=s.musteri_id LEFT JOIN cekler c ON c.id=t.cek_id ORDER BY t.tarih DESC LIMIT 300");
// Alacak yaşlandırma: müşteri bazında, kalan alacağı şantiye başlangıcına göre yaşlandırıyoruz
$yas = rows("SELECT m.id musteri_id, m.ad musteri, s.id, s.ad, s.baslangic, s.anlasma_tutari - COALESCE((SELECT SUM(tutar) FROM tahsilatlar WHERE santiye_id=s.id),0) kalan, DATEDIFF(CURDATE(), COALESCE((SELECT MAX(tarih) FROM tahsilatlar WHERE santiye_id=s.id), s.baslangic, CURDATE())) gun FROM santiyeler s LEFT JOIN musteriler m ON m.id=s.musteri_id WHERE s.durum<>'tamamlandi' HAVING kalan>0 ORDER BY m.ad, s.ad");
// müşteri bazında toplama
$mus = []; foreach ($yas as $y) { $k = (int)$y['musteri_id']; $i = $y['gun'] <= 30 ? 0 : ($y['gun'] <= 60 ? 1 : 2);
  $mus[$k] ??= ['ad' => $y['musteri'], 'kova' => [0,0,0], 'santiye' => 0]; $mus[$k]['kova'][$i] += $y['kalan']; $mus[$k]['santiye']++; }
$baslik = 'Tahsilat'; $fab = 'tahsilat_form.php'; include 'inc/layout_top.php'; ?>
<h2 style="margin-top:0">Alacak yaşlandırma — müşteri bazında</h2>
<div class="tablo-kutu"><table><tr><th>Müşteri</th><th class="num">Şantiye</th><th class="num">0-30 gün</th><th class="num">30-60 gün</th><th class="num">60+ gün</th><th class="num">Toplam</th></tr>
<?php $t = [0,0,0]; foreach ($mus as $mid => $mv): foreach ($mv['kova'] as $i => $v) $t[$i] += $v; ?>
<tr onclick="location='musteri.php?id=<?= $mid ?>'" style="cursor:pointer"><td><b><?= e($mv['ad']) ?></b></td><td class="num"><?= $mv['santiye'] ?></td><td class="num yesil"><?= $mv['kova'][0] ? para($mv['kova'][0]) : '' ?></td><td class="num sari"><?= $mv['kova'][1] ? para($mv['kova'][1]) : '' ?></td><td class="num kirmizi"><?= $mv['kova'][2] ? para($mv['kova'][2]) : '' ?></td><td class="num b"><?= para(array_sum($mv['kova'])) ?></td></tr><?php endforeach; ?>
<?php if (!$mus): ?><tr><td colspan="6" class="bos">Açık alacak yok.</td></tr><?php endif; ?>
<tfoot><tr><td colspan="2">Toplam</td><td class="num"><?= para($t[0]) ?></td><td class="num"><?= para($t[1]) ?></td><td class="num"><?= para($t[2]) ?></td><td class="num"><?= para(array_sum($t)) ?></td></tr></tfoot></table></div>

<h2>Alacak yaşlandırma — şantiye bazında</h2>
<div class="tablo-kutu"><table><tr><th>Şantiye</th><th>Müşteri</th><th class="num">0-30 gün</th><th class="num">30-60 gün</th><th class="num">60+ gün</th><th class="num">Toplam</th></tr>
<?php foreach ($yas as $y): $i = $y['gun'] <= 30 ? 0 : ($y['gun'] <= 60 ? 1 : 2); ?>
<tr onclick="location='santiye.php?id=<?= $y['id'] ?>'" style="cursor:pointer"><td><b><?= e($y['ad']) ?></b></td><td><?= e($y['musteri']) ?></td><td class="num yesil"><?= $i===0 ? para($y['kalan']) : '' ?></td><td class="num sari"><?= $i===1 ? para($y['kalan']) : '' ?></td><td class="num kirmizi"><?= $i===2 ? para($y['kalan']) : '' ?></td><td class="num b"><?= para($y['kalan']) ?></td></tr><?php endforeach; ?>
</table></div>
<div class="bilgi">Gün sayısı son tahsilat tarihinden (yoksa şantiye başlangıcından) itibaren hesaplanır.</div>
<h2>Tahsilat listesi</h2>
<div class="kart liste"><?php if (!$liste): ?><div class="bos"><div class="b-ico"><svg class="ic" aria-hidden="true"><use href="#i-coins"></use></svg></div>Henüz tahsilat yok.</div><?php endif; foreach ($liste as $r): ?>
  <div class="satir"><div class="ico-b"><?= ['nakit'=>'<svg class="ic" aria-hidden="true"><use href="#i-money-wavy"></use></svg>','havale'=>'<svg class="ic" aria-hidden="true"><use href="#i-bank"></use></svg>','cek'=>'<svg class="ic" aria-hidden="true"><use href="#i-files"></use></svg>'][$r['tip']] ?></div><div class="govde"><div class="ad"><?= e($r['musteri']) ?> · <?= e($r['santiye']) ?></div><small><?= tarih_tr($r['tarih']) ?> · <?= tip_adi($r['tip']) ?><?= $r['cek_no'] ? ' ' . e($r['cek_no']) . ' · ' . e($r['banka']) . ' · vade ' . tarih_tr($r['vade']) . ' ' . durum_etiketi($r['cdurum']) : '' ?> <?= e($r['aciklama']) ?></small></div><div class="tutar yesil">+<?= para($r['tutar']) ?></div>
  <a class="btn btn-gri btn-kucuk" href="sil.php?t=tahsilatlar&id=<?= $r['id'] ?>&geri=tahsilat.php" data-onay="Tahsilat silinsin mi?"><svg class="ic" aria-hidden="true"><use href="#i-trash"></use></svg></a></div><?php endforeach; ?></div>
<?php include 'inc/layout_bottom.php';
