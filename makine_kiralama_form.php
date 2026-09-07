<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$id = (int)get('id'); $k = $id ? row("SELECT * FROM makine_kiralama WHERE id=?", [$id]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bas = post('baslangic'); $bit = post('bitis') ?: null; $bf = tutar_parse(post('birim_fiyat')); $tip = post('tip', 'gunluk');
    $toplam = tutar_parse(post('toplam'));
    if ($toplam <= 0 && $bit) { $gun = max(1, (strtotime($bit) - strtotime($bas)) / 86400 + 1); $toplam = $tip === 'gunluk' ? $gun * $bf : ceil($gun / 30) * $bf; }
    $d = [(int)post('tedarikci_id'), post('makine_adi'), (int)post('santiye_id'), $tip, $bf, $bas, $bit, $toplam];
    if ($id) q("UPDATE makine_kiralama SET tedarikci_id=?, makine_adi=?, santiye_id=?, tip=?, birim_fiyat=?, baslangic=?, bitis=?, toplam=? WHERE id=?", [...$d, $id]); else q("INSERT INTO makine_kiralama (tedarikci_id, makine_adi, santiye_id, tip, birim_fiyat, baslangic, bitis, toplam) VALUES (?,?,?,?,?,?,?,?)", $d);
    flash('Kiralama kaydedildi. Tutar tedarikçi borcuna işlendi.'); redirect('makineler.php');
}
$baslik = $id ? 'Kiralama Düzenle' : 'Dış Kiralama Kaydı'; $geri = 'makineler.php'; include 'inc/layout_top.php'; ?>
<form method="post" class="kart"><?= csrf_field() ?>
  <label class="alan"><span>Tedarikçi firma *</span><select name="tedarikci_id" required><option value="">Seçin…</option><?php foreach (rows("SELECT id, firma FROM tedarikciler ORDER BY firma") as $t): ?><option value="<?= $t['id'] ?>" <?= ($k['tedarikci_id'] ?? 0)==$t['id']?'selected':'' ?>><?= e($t['firma']) ?></option><?php endforeach; ?></select><small>Yoksa önce <a href="tedarikci_form.php">tedarikçi ekleyin</a>.</small></label>
  <label class="alan"><span>Makine *</span><input type="text" name="makine_adi" required value="<?= e($k['makine_adi'] ?? '') ?>" placeholder="Kiralık ekskavatör 30 ton"></label>
  <label class="alan"><span>Şantiye *</span><select name="santiye_id" required><?php foreach (santiyelerim() as $s): ?><option value="<?= $s['id'] ?>" <?= ($k['santiye_id'] ?? 0)==$s['id']?'selected':'' ?>><?= e($s['ad']) ?></option><?php endforeach; ?></select></label>
  <label class="alan"><span>Kiralama tipi</span><div class="secim"><input type="radio" name="tip" id="tg" value="gunluk" <?= ($k['tip'] ?? 'gunluk')==='gunluk'?'checked':'' ?>><label for="tg">Günlük</label><input type="radio" name="tip" id="ta" value="aylik" <?= ($k['tip'] ?? '')==='aylik'?'checked':'' ?>><label for="ta">Aylık</label></div></label>
  <label class="alan"><span>Birim fiyat (₺)</span><input type="text" inputmode="decimal" class="tutar-input" name="birim_fiyat" value="<?= $k ? number_format($k['birim_fiyat'], 2, ',', '.') : '' ?>"></label>
  <div class="satir-form"><label class="alan"><span>Başlangıç</span><input type="date" name="baslangic" required value="<?= e($k['baslangic'] ?? date('Y-m-d')) ?>"></label><label class="alan"><span>Bitiş</span><input type="date" name="bitis" value="<?= e($k['bitis'] ?? '') ?>"></label></div>
  <label class="alan"><span>Toplam (₺) <small>boş bırakılırsa tarih ve birim fiyattan hesaplanır</small></span><input type="text" inputmode="decimal" class="tutar-input" name="toplam" value="<?= $k ? number_format($k['toplam'], 2, ',', '.') : '' ?>"></label>
  <button class="btn btn-turuncu btn-blok">Kaydet</button>
  <?php if ($id): ?><a class="btn btn-gri btn-blok" style="margin-top:.5rem" href="sil.php?t=makine_kiralama&id=<?= $id ?>&geri=makineler.php" data-onay="Kiralama kaydı silinsin mi?"><svg class="ic" aria-hidden="true"><use href="#i-trash"></use></svg> Sil</a><?php endif; ?>
</form>
<?php include 'inc/layout_bottom.php';
