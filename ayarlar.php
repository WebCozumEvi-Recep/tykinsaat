<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); patron_gerekli();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $i = post('islem');
    if ($i === 'firma') { $f = row("SELECT * FROM firma WHERE id=1"); $logo = $f['logo']; if ($l = foto_yukle('logo')) $logo = $l[0]; q("UPDATE firma SET ad=?, vergi_no=?, adres=?, telefon=?, logo=? WHERE id=1", [post('ad'), post('vergi_no'), post('adres'), post('telefon'), $logo]); flash('Firma bilgileri güncellendi.'); }
    if ($i === 'kat_ekle' && post('ad')) { q("INSERT INTO gider_kategorileri (ad, ikon, sira) VALUES (?,?,?)", [post('ad'), post('ikon') ?: '<svg class="ic" aria-hidden="true"><use href="#i-cube"></use></svg>', (int)post('sira')]); flash('Kategori eklendi.'); }
    if ($i === 'kat_sil') { q("DELETE FROM gider_kategorileri WHERE id=?", [(int)post('id')]); flash('Kategori silindi.'); }
    if ($i === 'sifre') { if (post('sifre1') && post('sifre1') === post('sifre2')) { q("UPDATE kullanicilar SET sifre_hash=? WHERE id=?", [password_hash(post('sifre1'), PASSWORD_DEFAULT), user()['id']]); flash('Şifreniz değişti.'); } else flash('Şifreler eşleşmiyor.', 'hata'); }
    redirect('ayarlar.php');
}
$firma = row("SELECT * FROM firma WHERE id=1"); $kul = rows("SELECT k.*, (SELECT GROUP_CONCAT(s.ad SEPARATOR ', ') FROM kullanici_santiye ks JOIN santiyeler s ON s.id=ks.santiye_id WHERE ks.kullanici_id=k.id) santiyeler FROM kullanicilar k ORDER BY k.rol, k.ad_soyad");
$baslik = 'Ayarlar'; include 'inc/layout_top.php'; ?>
<h2 style="margin-top:0">Kullanıcılar <a class="btn btn-turuncu btn-kucuk" href="kullanici_form.php">+ Kullanıcı</a></h2>
<div class="kart liste"><?php foreach ($kul as $k): ?><a class="satir" href="kullanici_form.php?id=<?= $k['id'] ?>"><div class="ico-b"><?= ['patron'=>'<svg class="ic" aria-hidden="true"><use href="#i-briefcase"></use></svg>','santiye'=>'<svg class="ic" aria-hidden="true"><use href="#i-hard-hat"></use></svg>','muhasebe'=>'<svg class="ic" aria-hidden="true"><use href="#i-calculator"></use></svg>'][$k['rol']] ?></div><div class="govde"><div class="ad"><?= e($k['ad_soyad']) ?> <small style="display:inline">(<?= e($k['kullanici_adi']) ?>)</small> <?= $k['aktif'] ? '' : etiket('Pasif','gri') ?></div><small><?= ['patron'=>'Patron / Yönetici','santiye'=>'Şantiye Yöneticisi','muhasebe'=>'Muhasebe'][$k['rol']] ?><?= $k['santiyeler'] ? ' · ' . e($k['santiyeler']) : '' ?></small></div>›</a><?php endforeach; ?></div>

<h2>Gider kategorileri</h2>
<div class="kart"><?php foreach (rows("SELECT * FROM gider_kategorileri ORDER BY sira, ad") as $k): ?><form method="post" style="display:flex;align-items:center;gap:.5rem;padding:.4rem 0;border-bottom:1px solid var(--cizgi)"><?= csrf_field() ?><input type="hidden" name="islem" value="kat_sil"><input type="hidden" name="id" value="<?= $k['id'] ?>"><span class="ico-b" style="width:34px;height:34px;border-radius:10px"><?= kat_ikon($k['ikon']) ?></span><span style="flex:1"><?= e($k['ad']) ?></span><button class="btn btn-gri btn-kucuk" data-onay="Kategori silinsin mi? Bu kategorideki giderler kategorisiz kalır."><svg class="ic" aria-hidden="true"><use href="#i-trash"></use></svg></button></form><?php endforeach; ?>
<form method="post" style="display:flex;gap:.5rem;margin-top:.75rem;flex-wrap:wrap"><?= csrf_field() ?><input type="hidden" name="islem" value="kat_ekle"><input type="text" name="ikon" placeholder="Emoji" style="width:80px;text-align:center"><input type="text" name="ad" placeholder="Kategori adı" required style="flex:1;min-width:140px"><input type="number" name="sira" placeholder="Sıra" style="width:80px"><button class="btn btn-kucuk">Ekle</button></form></div>

<h2>Firma bilgileri</h2>
<form method="post" enctype="multipart/form-data" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="firma">
<label class="alan"><span>Firma adı</span><input type="text" name="ad" value="<?= e($firma['ad']) ?>"></label>
<div class="satir-form"><label class="alan"><span>Vergi no</span><input type="text" name="vergi_no" value="<?= e($firma['vergi_no']) ?>"></label><label class="alan"><span>Telefon</span><input type="tel" name="telefon" value="<?= e($firma['telefon']) ?>"></label></div>
<label class="alan"><span>Adres</span><textarea name="adres"><?= e($firma['adres']) ?></textarea></label>
<label class="foto-alan"><svg class="ic" aria-hidden="true"><use href="#i-image-square"></use></svg> Logo yükle (PNG/JPG)<input type="file" name="logo[]" accept="image/*"></label><div class="onizleme"><?php if ($firma['logo']): ?><img src="<?= UPLOAD_URL . e($firma['logo']) ?>" alt=""><?php endif; ?></div>
<button class="btn btn-turuncu btn-blok" style="margin-top:1rem">Kaydet</button></form>

<h2>Şifremi değiştir</h2>
<form method="post" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="sifre"><div class="satir-form"><label class="alan"><span>Yeni şifre</span><input type="password" name="sifre1" required minlength="6"></label><label class="alan"><span>Tekrar</span><input type="password" name="sifre2" required></label></div><button class="btn btn-blok">Şifreyi Güncelle</button></form>
<?php include 'inc/layout_bottom.php';
