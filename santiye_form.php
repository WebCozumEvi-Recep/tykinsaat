<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); patron_gerekli();
$id = (int)get('id'); $s = $id ? row("SELECT * FROM santiyeler WHERE id=?", [$id]) : null;
$yoneticiler = rows("SELECT id, ad_soyad FROM kullanicilar WHERE rol='santiye' AND aktif=1 ORDER BY ad_soyad");
$musteriler = rows("SELECT id, ad FROM musteriler WHERE aktif=1 ORDER BY ad");
$atanan = $id ? array_column(rows("SELECT kullanici_id FROM kullanici_santiye WHERE santiye_id=?", [$id]), 'kullanici_id') : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mid = (int)post('musteri_id') ?: null;
    if (post('yeni_musteri')) { // formda yeni müşteri adı yazıldıysa kartı oluştur
        $mid = (int)val("SELECT id FROM musteriler WHERE ad=?", [post('yeni_musteri')]);
        if (!$mid) { q("INSERT INTO musteriler (ad) VALUES (?)", [post('yeni_musteri')]); $mid = (int)db()->lastInsertId(); }
    }
    if (!$mid) { flash('Müşteri seçmelisiniz.', 'hata'); redirect('santiye_form.php' . ($id ? "?id=$id" : '')); }
    $d = [post('ad'), $mid, post('adres'), tutar_parse(post('anlasma_tutari')), post('baslangic') ?: null, post('bitis') ?: null, post('durum', 'aktif'), post('notlar')];
    if ($id) { q("UPDATE santiyeler SET ad=?, musteri_id=?, adres=?, anlasma_tutari=?, baslangic=?, bitis=?, durum=?, notlar=? WHERE id=?", [...$d, $id]); }
    else { q("INSERT INTO santiyeler (ad, musteri_id, adres, anlasma_tutari, baslangic, bitis, durum, notlar) VALUES (?,?,?,?,?,?,?,?)", $d); $id = (int)db()->lastInsertId(); }
    q("DELETE FROM kullanici_santiye WHERE santiye_id=?", [$id]);
    foreach ((array)($_POST['yonetici'] ?? []) as $y) q("INSERT INTO kullanici_santiye VALUES (?,?)", [(int)$y, $id]);
    flash('Şantiye kaydedildi.'); redirect("santiye.php?id=$id");
}
$baslik = $id ? 'Şantiye Düzenle' : 'Yeni Şantiye'; $geri = $id ? "santiye.php?id=$id" : 'santiyeler.php';
include 'inc/layout_top.php'; ?>
<form method="post" class="kart" data-taslak="santiye<?= $id ?>"><?= csrf_field() ?>
  <label class="alan"><span>Şantiye adı *</span><input type="text" name="ad" required value="<?= e($s['ad'] ?? '') ?>"></label>
  <label class="alan"><span>Müşteri *</span>
    <select name="musteri_id" id="musteriSec" onchange="yeniMusteri(this)"><option value="">Seçin…</option>
      <?php $secM = (int)($s['musteri_id'] ?? get('musteri_id')); foreach ($musteriler as $mu): ?><option value="<?= $mu['id'] ?>" <?= $mu['id']==$secM?'selected':'' ?>><?= e($mu['ad']) ?></option><?php endforeach; ?>
      <option value="yeni"><svg class="ic" aria-hidden="true"><use href="#i-plus-circle"></use></svg> Yeni müşteri ekle…</option></select>
    <input type="text" name="yeni_musteri" id="yeniMusteri" placeholder="Yeni müşteri adı" style="display:none;margin-top:.5rem">
    <small>Bir müşterinin birden fazla şantiyesi olabilir. <a href="musteriler.php">Müşteri kartları</a></small></label>
  <label class="alan"><span>Adres / konum</span><textarea name="adres"><?= e($s['adres'] ?? '') ?></textarea></label>
  <label class="alan"><span>Anlaşma tutarı (₺)</span><input type="text" inputmode="decimal" class="tutar-input" name="anlasma_tutari" value="<?= $s ? number_format($s['anlasma_tutari'], 2, ',', '.') : '' ?>"></label>
  <div class="satir-form"><label class="alan"><span>Başlangıç</span><input type="date" name="baslangic" value="<?= e($s['baslangic'] ?? date('Y-m-d')) ?>"></label><label class="alan"><span>Bitiş (tahmini)</span><input type="date" name="bitis" value="<?= e($s['bitis'] ?? '') ?>"></label></div>
  <label class="alan"><span>Durum</span><select name="durum"><?php foreach (['aktif'=>'Aktif','beklemede'=>'Beklemede','tamamlandi'=>'Tamamlandı'] as $k=>$v): ?><option value="<?= $k ?>" <?= ($s['durum'] ?? 'aktif')===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></label>
  <label class="alan"><span>Atanan şantiye yöneticisi</span>
    <div class="secim"><?php foreach ($yoneticiler as $y): ?><input type="checkbox" name="yonetici[]" id="y<?= $y['id'] ?>" value="<?= $y['id'] ?>" <?= in_array($y['id'], $atanan)?'checked':'' ?>><label for="y<?= $y['id'] ?>"><svg class="ic" aria-hidden="true"><use href="#i-hard-hat"></use></svg> <?= e($y['ad_soyad']) ?></label><?php endforeach; if (!$yoneticiler): ?><small>Önce Ayarlar'dan şantiye yöneticisi kullanıcı ekleyin.</small><?php endif; ?></div></label>
  <label class="alan"><span>Notlar</span><textarea name="notlar"><?= e($s['notlar'] ?? '') ?></textarea></label>
  <button class="btn btn-turuncu btn-blok">Kaydet</button>
</form>
<script>function yeniMusteri(s){var i=document.getElementById('yeniMusteri');var y=s.value==='yeni';i.style.display=y?'':'none';i.required=y;if(y)i.focus();else i.value=''}</script>
<?php include 'inc/layout_bottom.php';
