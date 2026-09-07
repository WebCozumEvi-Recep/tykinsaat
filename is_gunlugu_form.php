<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli();
$id = (int)get('id'); $g = $id ? row("SELECT * FROM is_gunlugu WHERE id=?", [$id]) : null; if ($g) santiye_erisim((int)$g['santiye_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = (int)post('santiye_id'); santiye_erisim($sid);
    $foto = array_merge($g ? fotolar($g['fotolar']) : [], foto_yukle('foto'));
    $d = [$sid, post('tarih') ?: date('Y-m-d'), post('aciklama'), post('metraj') !== '' ? tutar_parse(post('metraj')) : null, post('birim') ?: null, post('hava', 'gunesli'), json_encode($foto)];
    if ($id) q("UPDATE is_gunlugu SET santiye_id=?, tarih=?, aciklama=?, metraj=?, birim=?, hava=?, fotolar=? WHERE id=?", [...$d, $id]); else q("INSERT INTO is_gunlugu (santiye_id, tarih, aciklama, metraj, birim, hava, fotolar, kaydeden) VALUES (?,?,?,?,?,?,?,?)", [...$d, user()['id']]);
    flash('İş kaydı eklendi <svg class="ic" aria-hidden="true"><use href="#i-check-circle"></use></svg>'); redirect("is_gunlugu.php?santiye_id=$sid");
}
$baslik = $id ? 'İş Kaydı Düzenle' : 'İş Kaydı'; $geri = 'is_gunlugu.php'; $secSid = (int)($g['santiye_id'] ?? get('santiye_id')); include 'inc/layout_top.php'; ?>
<form method="post" enctype="multipart/form-data" class="kart" data-taslak="isg<?= $id ?>"><?= csrf_field() ?>
  <label class="alan"><span>Şantiye *</span><select name="santiye_id" required <?= $secSid?'data-sabit="1"':'' ?>><option value="">Seçin…</option><?php foreach (santiyelerim(true) as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$secSid?'selected':'' ?>><?= e($s['ad']) ?></option><?php endforeach; ?></select></label>
  <label class="alan"><span>Tarih</span><input type="date" name="tarih" value="<?= e($g['tarih'] ?? date('Y-m-d')) ?>"><div class="hizli"><button type="button" class="btn btn-gri btn-kucuk" data-tarih="bugun" data-hedef="input[name=tarih]">Bugün</button><button type="button" class="btn btn-gri btn-kucuk" data-tarih="dun" data-hedef="input[name=tarih]">Dün</button></div></label>
  <label class="alan"><span>Hava</span><div class="secim ikonlu"><?php foreach (['gunesli'=>'Güneşli','bulutlu'=>'Bulutlu','yagmurlu'=>'Yağmurlu','karli'=>'Karlı'] as $k=>$v): ?><input type="radio" name="hava" id="h<?= $k ?>" value="<?= $k ?>" <?= ($g['hava'] ?? 'gunesli')===$k?'checked':'' ?>><label for="h<?= $k ?>"><b><?= hava_ikon($k) ?></b><?= $v ?></label><?php endforeach; ?></div></label>
  <label class="alan"><span>Yapılan iş *</span><textarea name="aciklama" required placeholder="Temel kazısı devam etti, 3. aks demir bağlandı…"><?= e($g['aciklama'] ?? '') ?></textarea></label>
  <div class="satir-form"><label class="alan"><span>Metraj</span><input type="text" inputmode="decimal" name="metraj" value="<?= $g && $g['metraj'] !== null ? number_format($g['metraj'], 2, ',', '.') : '' ?>"></label><label class="alan"><span>Birim</span><select name="birim"><option value="">-</option><?php foreach (['m3'=>'m³','m2'=>'m²','mt'=>'mt','adet'=>'adet'] as $k=>$v): ?><option value="<?= $k ?>" <?= ($g['birim'] ?? '')===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></label></div>
  <label class="foto-alan"><svg class="ic" aria-hidden="true"><use href="#i-camera"></use></svg> Fotoğraf çek / yükle<input type="file" name="foto[]" accept="image/*" capture="environment" multiple></label><div class="onizleme"></div>
  <?php if ($g && ($f = fotolar($g['fotolar']))): ?><div class="foto-galeri"><?php foreach ($f as $ff): ?><img src="<?= UPLOAD_URL . e($ff) ?>" alt=""><?php endforeach; ?></div><?php endif; ?>
  <button class="btn btn-turuncu btn-blok" style="margin-top:1rem"><svg class="ic" aria-hidden="true"><use href="#i-floppy-disk-back"></use></svg> Kaydet</button>
  <?php if ($id): ?><a class="btn btn-gri btn-blok" style="margin-top:.5rem" href="sil.php?t=is_gunlugu&id=<?= $id ?>&geri=is_gunlugu.php" data-onay="Kayıt silinsin mi?"><svg class="ic" aria-hidden="true"><use href="#i-trash"></use></svg> Sil</a><?php endif; ?>
</form>
<?php include 'inc/layout_bottom.php';
