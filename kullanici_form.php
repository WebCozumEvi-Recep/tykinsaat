<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); patron_gerekli();
$id = (int)get('id'); $k = $id ? row("SELECT * FROM kullanicilar WHERE id=?", [$id]) : null;
$atanan = $id ? array_column(rows("SELECT santiye_id FROM kullanici_santiye WHERE kullanici_id=?", [$id]), 'santiye_id') : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rol = in_array(post('rol'), ['patron','santiye','muhasebe']) ? post('rol') : 'santiye';
    if ($id) { q("UPDATE kullanicilar SET kullanici_adi=?, ad_soyad=?, rol=?, aktif=? WHERE id=?", [post('kullanici_adi'), post('ad_soyad'), $rol, (int)!empty($_POST['aktif']), $id]); if (post('sifre')) q("UPDATE kullanicilar SET sifre_hash=? WHERE id=?", [password_hash(post('sifre'), PASSWORD_DEFAULT), $id]); }
    else { if (!post('sifre')) { flash('Şifre zorunlu.', 'hata'); redirect('kullanici_form.php'); } q("INSERT INTO kullanicilar (kullanici_adi, sifre_hash, ad_soyad, rol, aktif) VALUES (?,?,?,?,?)", [post('kullanici_adi'), password_hash(post('sifre'), PASSWORD_DEFAULT), post('ad_soyad'), $rol, (int)!empty($_POST['aktif'])]); $id = (int)db()->lastInsertId(); }
    q("DELETE FROM kullanici_santiye WHERE kullanici_id=?", [$id]);
    foreach ((array)($_POST['santiye'] ?? []) as $s) q("INSERT INTO kullanici_santiye VALUES (?,?)", [$id, (int)$s]);
    flash('Kullanıcı kaydedildi.'); redirect('ayarlar.php');
}
$baslik = $id ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı'; $geri = 'ayarlar.php'; include 'inc/layout_top.php'; ?>
<form method="post" class="kart"><?= csrf_field() ?>
  <label class="alan"><span>Ad Soyad *</span><input type="text" name="ad_soyad" required value="<?= e($k['ad_soyad'] ?? '') ?>"></label>
  <div class="satir-form"><label class="alan"><span>Kullanıcı adı *</span><input type="text" name="kullanici_adi" required value="<?= e($k['kullanici_adi'] ?? '') ?>" autocomplete="off"></label><label class="alan"><span>Şifre <?= $id ? '(boş = değişmez)' : '*' ?></span><input type="password" name="sifre" autocomplete="new-password" <?= $id ? '' : 'required' ?>></label></div>
  <label class="alan"><span>Rol</span><div class="secim"><?php foreach (['patron'=>'<svg class="ic" aria-hidden="true"><use href="#i-briefcase"></use></svg> Patron','santiye'=>'<svg class="ic" aria-hidden="true"><use href="#i-hard-hat"></use></svg> Şantiye Yön.','muhasebe'=>'<svg class="ic" aria-hidden="true"><use href="#i-calculator"></use></svg> Muhasebe'] as $r=>$v): ?><input type="radio" name="rol" id="r<?= $r ?>" value="<?= $r ?>" <?= ($k['rol'] ?? 'santiye')===$r?'checked':'' ?>><label for="r<?= $r ?>"><?= $v ?></label><?php endforeach; ?></div></label>
  <label class="alan"><span>Atanan şantiyeler (şantiye yöneticisi için)</span><div class="secim"><?php foreach (rows("SELECT id, ad FROM santiyeler WHERE durum<>'tamamlandi' ORDER BY ad") as $s): ?><input type="checkbox" name="santiye[]" id="s<?= $s['id'] ?>" value="<?= $s['id'] ?>" <?= in_array($s['id'], $atanan)?'checked':'' ?>><label for="s<?= $s['id'] ?>"><?= e($s['ad']) ?></label><?php endforeach; ?></div></label>
  <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem"><input type="checkbox" name="aktif" value="1" style="width:22px;height:22px" <?= ($k['aktif'] ?? 1) ? 'checked' : '' ?>> Aktif</label>
  <button class="btn btn-turuncu btn-blok">Kaydet</button>
</form>
<?php include 'inc/layout_bottom.php';
