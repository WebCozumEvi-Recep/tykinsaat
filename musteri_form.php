<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$id = (int)get('id'); $m = $id ? row("SELECT * FROM musteriler WHERE id=?", [$id]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [post('ad'), post('yetkili'), post('telefon'), post('email'), post('vergi_dairesi'), post('vergi_no'), post('adres'), post('notlar'), (int)!empty($_POST['aktif'])];
    try {
        if ($id) q("UPDATE musteriler SET ad=?, yetkili=?, telefon=?, email=?, vergi_dairesi=?, vergi_no=?, adres=?, notlar=?, aktif=? WHERE id=?", [...$d, $id]);
        else { q("INSERT INTO musteriler (ad, yetkili, telefon, email, vergi_dairesi, vergi_no, adres, notlar, aktif) VALUES (?,?,?,?,?,?,?,?,?)", $d); $id = (int)db()->lastInsertId(); }
    } catch (PDOException $ex) { flash('Bu isimde bir müşteri zaten var.', 'hata'); redirect('musteri_form.php' . ($id ? "?id=$id" : '')); }
    flash('Müşteri kaydedildi.'); redirect(get('donus') ? get('donus') : "musteri.php?id=$id");
}
$baslik = $id ? 'Müşteri Kartı' : 'Yeni Müşteri'; $geri = $id ? "musteri.php?id=$id" : 'musteriler.php'; include 'inc/layout_top.php'; ?>
<form method="post" class="kart"><?= csrf_field() ?>
  <label class="alan"><span>Müşteri / firma adı *</span><input type="text" name="ad" required value="<?= e($m['ad'] ?? get('ad')) ?>" autofocus></label>
  <div class="satir-form"><label class="alan"><span>Yetkili kişi</span><input type="text" name="yetkili" value="<?= e($m['yetkili'] ?? '') ?>"></label><label class="alan"><span>Telefon</span><input type="tel" name="telefon" value="<?= e($m['telefon'] ?? '') ?>"></label></div>
  <label class="alan"><span>E-posta</span><input type="email" name="email" value="<?= e($m['email'] ?? '') ?>"></label>
  <div class="satir-form"><label class="alan"><span>Vergi dairesi</span><input type="text" name="vergi_dairesi" value="<?= e($m['vergi_dairesi'] ?? '') ?>"></label><label class="alan"><span>Vergi / TC no</span><input type="text" name="vergi_no" value="<?= e($m['vergi_no'] ?? '') ?>"></label></div>
  <label class="alan"><span>Adres</span><textarea name="adres"><?= e($m['adres'] ?? '') ?></textarea></label>
  <label class="alan"><span>Notlar</span><textarea name="notlar"><?= e($m['notlar'] ?? '') ?></textarea></label>
  <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem"><input type="checkbox" name="aktif" value="1" style="width:22px;height:22px" <?= ($m['aktif'] ?? 1) ? 'checked' : '' ?>> Aktif</label>
  <button class="btn btn-turuncu btn-blok">Kaydet</button>
  <?php if ($id && patron()): $sy = (int)val("SELECT COUNT(*) FROM santiyeler WHERE musteri_id=?", [$id]);
    if ($sy): ?><div class="bilgi" style="margin-top:1rem">Bu müşteriye bağlı <?= $sy ?> şantiye var; silinemez. Önce şantiyeleri başka müşteriye taşıyın.</div>
    <?php else: ?><a class="btn btn-gri btn-blok" style="margin-top:.5rem" href="sil.php?t=musteriler&id=<?= $id ?>&geri=musteriler.php" data-onay="Müşteri silinsin mi?"><svg class="ic" aria-hidden="true"><use href="#i-trash"></use></svg> Sil</a><?php endif; endif; ?>
</form>
<?php include 'inc/layout_bottom.php';
