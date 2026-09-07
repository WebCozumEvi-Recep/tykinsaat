<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); patron_gerekli();
$id = (int)get('id'); $p = $id ? row("SELECT * FROM personel WHERE id=?", [$id]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [post('ad_soyad'), post('gorev', 'isci'), tutar_parse(post('yevmiye')), post('telefon'), (int)!empty($_POST['aktif'])];
    if ($id) q("UPDATE personel SET ad_soyad=?, gorev=?, yevmiye=?, telefon=?, aktif=? WHERE id=?", [...$d, $id]);
    else { q("INSERT INTO personel (ad_soyad, gorev, yevmiye, telefon, aktif) VALUES (?,?,?,?,?)", $d); $id = db()->lastInsertId(); }
    flash('Personel kaydedildi.'); redirect("personel.php?id=$id");
}
$baslik = $id ? 'Personel Düzenle' : 'Yeni Personel'; $geri = 'personel.php'; include 'inc/layout_top.php'; ?>
<form method="post" class="kart"><?= csrf_field() ?>
  <label class="alan"><span>Ad Soyad *</span><input type="text" name="ad_soyad" required value="<?= e($p['ad_soyad'] ?? '') ?>"></label>
  <label class="alan"><span>Görev</span><div class="secim"><?php foreach (['usta'=>'<svg class="ic" aria-hidden="true"><use href="#i-hammer"></use></svg> Usta','isci'=>'<svg class="ic" aria-hidden="true"><use href="#i-hard-hat"></use></svg> İşçi','operator'=>'<svg class="ic" aria-hidden="true"><use href="#i-tractor"></use></svg> Operatör','sofor'=>'<svg class="ic" aria-hidden="true"><use href="#i-truck"></use></svg> Şoför','diger'=>'Diğer'] as $k=>$v): ?><input type="radio" name="gorev" id="g<?= $k ?>" value="<?= $k ?>" <?= ($p['gorev'] ?? 'isci')===$k?'checked':'' ?>><label for="g<?= $k ?>"><?= $v ?></label><?php endforeach; ?></div></label>
  <label class="alan"><span>Günlük yevmiye (₺)</span><input type="text" inputmode="decimal" class="tutar-input" name="yevmiye" value="<?= $p ? number_format($p['yevmiye'], 2, ',', '.') : '' ?>"></label>
  <label class="alan"><span>Telefon</span><input type="tel" name="telefon" value="<?= e($p['telefon'] ?? '') ?>"></label>
  <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem"><input type="checkbox" name="aktif" value="1" style="width:22px;height:22px" <?= ($p['aktif'] ?? 1) ? 'checked' : '' ?>> Aktif</label>
  <button class="btn btn-turuncu btn-blok">Kaydet</button>
  <?php if ($id): ?><a class="btn btn-gri btn-blok" style="margin-top:.5rem" href="sil.php?t=personel&id=<?= $id ?>&geri=personel.php" data-onay="Personel ve tüm puantajı silinecek. Emin misiniz?"><svg class="ic" aria-hidden="true"><use href="#i-trash"></use></svg> Sil</a><?php endif; ?>
</form>
<?php include 'inc/layout_bottom.php';
