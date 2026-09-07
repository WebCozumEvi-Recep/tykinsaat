<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$id = (int)get('id'); $t = $id ? row("SELECT * FROM tedarikciler WHERE id=?", [$id]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [post('firma'), post('kategori'), post('vergi_no'), post('yetkili'), post('telefon'), post('notlar')];
    if ($id) q("UPDATE tedarikciler SET firma=?, kategori=?, vergi_no=?, yetkili=?, telefon=?, notlar=? WHERE id=?", [...$d, $id]);
    else { q("INSERT INTO tedarikciler (firma, kategori, vergi_no, yetkili, telefon, notlar) VALUES (?,?,?,?,?,?)", $d); $id = db()->lastInsertId(); }
    flash('Tedarikçi kaydedildi.'); redirect("tedarikci.php?id=$id");
}
$baslik = $id ? 'Tedarikçi Kartı' : 'Yeni Tedarikçi'; $geri = 'tedarikciler.php'; include 'inc/layout_top.php'; ?>
<form method="post" class="kart"><?= csrf_field() ?>
  <label class="alan"><span>Firma adı *</span><input type="text" name="firma" required value="<?= e($t['firma'] ?? '') ?>"></label>
  <label class="alan"><span>Kategori</span><input type="text" name="kategori" list="katList" value="<?= e($t['kategori'] ?? '') ?>"><datalist id="katList"><?php foreach (rows("SELECT ad FROM gider_kategorileri ORDER BY sira") as $k): ?><option value="<?= e($k['ad']) ?>"><?php endforeach; ?></datalist></label>
  <div class="satir-form"><label class="alan"><span>Vergi no</span><input type="text" name="vergi_no" value="<?= e($t['vergi_no'] ?? '') ?>"></label><label class="alan"><span>Telefon</span><input type="tel" name="telefon" value="<?= e($t['telefon'] ?? '') ?>"></label></div>
  <label class="alan"><span>Yetkili</span><input type="text" name="yetkili" value="<?= e($t['yetkili'] ?? '') ?>"></label>
  <label class="alan"><span>Notlar</span><textarea name="notlar"><?= e($t['notlar'] ?? '') ?></textarea></label>
  <button class="btn btn-turuncu btn-blok">Kaydet</button>
  <?php if ($id && patron()): ?><a class="btn btn-gri btn-blok" style="margin-top:.5rem" href="sil.php?t=tedarikciler&id=<?= $id ?>&geri=tedarikciler.php" data-onay="Tedarikçi ve ödeme geçmişi silinecek. Emin misiniz?"><svg class="ic" aria-hidden="true"><use href="#i-trash"></use></svg> Sil</a><?php endif; ?>
</form>
<?php include 'inc/layout_bottom.php';
