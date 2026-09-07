<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$id = (int)get('id'); $m = $id ? row("SELECT * FROM makineler WHERE id=?", [$id]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [post('ad'), post('plaka'), post('tip', 'diger'), (int)post('santiye_id') ?: null, post('durum', 'bosta')];
    if ($id) q("UPDATE makineler SET ad=?, plaka=?, tip=?, santiye_id=?, durum=? WHERE id=?", [...$d, $id]); else q("INSERT INTO makineler (ad, plaka, tip, santiye_id, durum) VALUES (?,?,?,?,?)", $d);
    flash('Makine kaydedildi.'); redirect('makineler.php');
}
$baslik = $id ? 'Makine Düzenle' : 'Yeni Makine'; $geri = 'makineler.php'; include 'inc/layout_top.php'; ?>
<form method="post" class="kart"><?= csrf_field() ?>
  <label class="alan"><span>Makine adı *</span><input type="text" name="ad" required value="<?= e($m['ad'] ?? '') ?>" placeholder="CAT 320 Ekskavatör"></label>
  <label class="alan"><span>Plaka / seri no</span><input type="text" name="plaka" value="<?= e($m['plaka'] ?? '') ?>"></label>
  <label class="alan"><span>Tip</span><div class="secim"><?php foreach (['ekskavator'=>'Ekskavatör','kepce'=>'Kepçe','kamyon'=>'Kamyon','silindir'=>'Silindir','dozer'=>'Dozer','diger'=>'Diğer'] as $k=>$v): ?><input type="radio" name="tip" id="t<?= $k ?>" value="<?= $k ?>" <?= ($m['tip'] ?? 'diger')===$k?'checked':'' ?>><label for="t<?= $k ?>"><?= $v ?></label><?php endforeach; ?></div></label>
  <label class="alan"><span>Şu an hangi şantiyede</span><select name="santiye_id"><option value="">Atanmamış</option><?php foreach (santiyelerim() as $s): ?><option value="<?= $s['id'] ?>" <?= ($m['santiye_id'] ?? 0)==$s['id']?'selected':'' ?>><?= e($s['ad']) ?></option><?php endforeach; ?></select></label>
  <label class="alan"><span>Durum</span><div class="secim"><?php foreach (['calisiyor'=>'Çalışıyor','bosta'=>'Boşta','bakimda'=>'Bakımda'] as $k=>$v): ?><input type="radio" name="durum" id="d<?= $k ?>" value="<?= $k ?>" <?= ($m['durum'] ?? 'bosta')===$k?'checked':'' ?>><label for="d<?= $k ?>"><?= $v ?></label><?php endforeach; ?></div></label>
  <button class="btn btn-turuncu btn-blok">Kaydet</button>
  <?php if ($id): ?><a class="btn btn-gri btn-blok" style="margin-top:.5rem" href="sil.php?t=makineler&id=<?= $id ?>&geri=makineler.php" data-onay="Makine silinsin mi?"><svg class="ic" aria-hidden="true"><use href="#i-trash"></use></svg> Sil</a><?php endif; ?>
</form>
<?php include 'inc/layout_bottom.php';
