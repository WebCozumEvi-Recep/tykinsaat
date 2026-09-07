<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$ara = get('q'); $liste = rows("SELECT * FROM tedarikciler WHERE firma LIKE ? ORDER BY firma", ["%$ara%"]);
$baslik = 'Tedarikçiler'; $fab = 'tedarikci_form.php'; include 'inc/layout_top.php'; ?>
<form class="araclar"><input type="text" name="q" placeholder="Firma ara…" value="<?= e($ara) ?>"><button class="btn btn-kucuk">Ara</button><a class="btn btn-cizgi btn-kucuk" href="export.php?tip=tedarikci"><svg class="ic" aria-hidden="true"><use href="#i-download-simple"></use></svg> Excel</a></form>
<?php if (!$liste): ?><div class="kart bos"><div class="b-ico"><svg class="ic" aria-hidden="true"><use href="#i-factory"></use></svg></div>Henüz tedarikçi yok.</div><?php endif; $top = 0; ?>
<div class="tablo-kutu"><table><tr><th>Firma</th><th>Kategori</th><th class="num">Toplam alım</th><th class="num">Toplam ödeme</th><th class="num">Borç bakiyesi</th></tr>
<?php foreach ($liste as $t): $b = tedarikci_bakiye($t['id']); $top += max(0, $b['bakiye']); ?>
<tr onclick="location='tedarikci.php?id=<?= $t['id'] ?>'" style="cursor:pointer"><td><b><?= e($t['firma']) ?></b><br><small><?= e($t['yetkili']) ?> <?= e($t['telefon']) ?></small></td><td><?= e($t['kategori'] ?: '-') ?></td><td class="num"><?= para($b['alim']) ?></td><td class="num yesil"><?= para($b['odeme']) ?></td><td class="num <?= $b['bakiye']>0?'kirmizi':'gri' ?>" style="font-size:1.1rem;font-weight:800"><?= para($b['bakiye']) ?></td></tr><?php endforeach; ?>
<?php if ($liste): ?><tfoot><tr><td colspan="4">Toplam borç</td><td class="num kirmizi"><?= para($top) ?></td></tr></tfoot><?php endif; ?></table></div>
<?php include 'inc/layout_bottom.php';
