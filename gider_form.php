<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli();
$id = (int)get('id'); $g = $id ? row("SELECT * FROM giderler WHERE id=?", [$id]) : null;
if ($g) santiye_erisim((int)$g['santiye_id']);
$sant = santiyelerim(true); $kat = rows("SELECT * FROM gider_kategorileri ORDER BY sira, ad"); $ted = rows("SELECT id, firma FROM tedarikciler ORDER BY firma");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = (int)post('santiye_id'); santiye_erisim($sid);
    $tid = (int)post('tedarikci_id') ?: null;
    if (post('yeni_tedarikci')) { q("INSERT INTO tedarikciler (firma, kategori) VALUES (?,?)", [post('yeni_tedarikci'), val("SELECT ad FROM gider_kategorileri WHERE id=?", [(int)post('kategori_id')])]); $tid = (int)db()->lastInsertId(); }
    $foto = array_merge($g ? fotolar($g['fotolar']) : [], foto_yukle('foto'));
    $d = [$sid, post('tarih') ?: date('Y-m-d'), (int)post('kategori_id') ?: null, $tid, post('aciklama'), tutar_parse(post('tutar')), post('fis_no'), post('odeme_durumu') === 'odendi' ? 'odendi' : 'borc', json_encode($foto)];
    if ($id) q("UPDATE giderler SET santiye_id=?, tarih=?, kategori_id=?, tedarikci_id=?, aciklama=?, tutar=?, fis_no=?, odeme_durumu=?, fotolar=? WHERE id=?", [...$d, $id]);
    else q("INSERT INTO giderler (santiye_id, tarih, kategori_id, tedarikci_id, aciklama, tutar, fis_no, odeme_durumu, fotolar, kaydeden) VALUES (?,?,?,?,?,?,?,?,?,?)", [...$d, user()['id']]);
    flash('Gider kaydedildi <svg class="ic" aria-hidden="true"><use href="#i-check-circle"></use></svg>'); redirect(finans_gorur() ? "santiye.php?id=$sid&sek=gider" : "index.php");
}
$baslik = $id ? 'Gider Düzenle' : 'Gider / Malzeme Ekle'; $geri = $g ? "santiye.php?id={$g['santiye_id']}&sek=gider" : 'index.php';
$secSid = (int)($g['santiye_id'] ?? get('santiye_id')); $fin = finans_gorur();
include 'inc/layout_top.php'; ?>
<form method="post" enctype="multipart/form-data" class="kart" data-taslak="gider<?= $id ?>"><?= csrf_field() ?>
  <label class="alan"><span>Şantiye *</span><select name="santiye_id" required <?= $secSid ? 'data-sabit="1"' : '' ?>><option value="">Seçin…</option><?php foreach ($sant as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$secSid?'selected':'' ?>><?= e($s['ad']) ?></option><?php endforeach; ?></select></label>
  <label class="alan"><span>Tarih</span><input type="date" name="tarih" value="<?= e($g['tarih'] ?? date('Y-m-d')) ?>"><div class="hizli"><button type="button" class="btn btn-gri btn-kucuk" data-tarih="bugun" data-hedef="input[name=tarih]">Bugün</button><button type="button" class="btn btn-gri btn-kucuk" data-tarih="dun" data-hedef="input[name=tarih]">Dün</button></div></label>
  <label class="alan"><span>Kategori</span><div class="secim ikonlu"><?php foreach ($kat as $k): ?><input type="radio" name="kategori_id" id="k<?= $k['id'] ?>" value="<?= $k['id'] ?>" <?= ($g['kategori_id'] ?? $kat[count($kat)-1]['id'] ?? 0)==$k['id']?'checked':'' ?>><label for="k<?= $k['id'] ?>"><b><?= kat_ikon($k['ikon']) ?></b><?= e($k['ad']) ?></label><?php endforeach; ?></div></label>
  <label class="alan"><span>Tedarikçi</span><input type="text" list="tedList" name="tedarikci_ad" placeholder="Firma ara…" value="<?= e($g && $g['tedarikci_id'] ? val("SELECT firma FROM tedarikciler WHERE id=?", [$g['tedarikci_id']]) : '') ?>" oninput="tedSec(this)"><datalist id="tedList"><?php foreach ($ted as $t): ?><option value="<?= e($t['firma']) ?>" data-id="<?= $t['id'] ?>"><?php endforeach; ?></datalist>
    <input type="hidden" name="tedarikci_id" id="tedId" value="<?= (int)($g['tedarikci_id'] ?? 0) ?>"><input type="hidden" name="yeni_tedarikci" id="tedYeni"><small id="tedNot"></small></label>
  <label class="alan"><span>Açıklama</span><input type="text" name="aciklama" placeholder="C30 beton 12 m³…" value="<?= e($g['aciklama'] ?? '') ?>"></label>
  <div class="satir-form"><label class="alan"><span>Tutar (₺) *</span><input type="text" inputmode="decimal" class="tutar-input" name="tutar" required value="<?= $g ? number_format($g['tutar'], 2, ',', '.') : '' ?>"></label><label class="alan"><span>Fatura / fiş no</span><input type="text" name="fis_no" value="<?= e($g['fis_no'] ?? '') ?>"></label></div>
  <label class="alan"><span>Ödeme durumu</span><div class="secim"><input type="radio" name="odeme_durumu" id="od1" value="odendi" <?= ($g['odeme_durumu'] ?? '')==='odendi'?'checked':'' ?>><label for="od1"><svg class="ic" aria-hidden="true"><use href="#i-check-circle"></use></svg> Ödendi</label><input type="radio" name="odeme_durumu" id="od2" value="borc" <?= ($g['odeme_durumu'] ?? 'borc')==='borc'?'checked':'' ?>><label for="od2"><svg class="ic" aria-hidden="true"><use href="#i-clipboard-text"></use></svg> Borç olarak kaydet</label></div></label>
  <div class="bilgi">ℹ Bu kayıt hem şantiye giderine hem de seçilen tedarikçinin borç hesabına işlenir.</div>
  <label class="foto-alan"><svg class="ic" aria-hidden="true"><use href="#i-camera"></use></svg> Fiş / irsaliye fotoğrafı çek veya yükle<input type="file" name="foto[]" accept="image/*" capture="environment" multiple></label><div class="onizleme"></div>
  <?php if ($g && ($f = fotolar($g['fotolar']))): ?><div class="foto-galeri"><?php foreach ($f as $ff): ?><a href="<?= UPLOAD_URL . e($ff) ?>" target="_blank"><img src="<?= UPLOAD_URL . e($ff) ?>" alt=""></a><?php endforeach; ?></div><?php endif; ?>
  <button class="btn btn-turuncu btn-blok" style="margin-top:1rem"><svg class="ic" aria-hidden="true"><use href="#i-floppy-disk-back"></use></svg> Kaydet</button>
  <?php if ($id && $fin): ?><a class="btn btn-gri btn-blok" style="margin-top:.5rem" href="sil.php?t=giderler&id=<?= $id ?>&geri=<?= urlencode($geri) ?>" data-onay="Gider silinsin mi?"><svg class="ic" aria-hidden="true"><use href="#i-trash"></use></svg> Sil</a><?php endif; ?>
</form>
<script>
function tedSec(inp){var v=inp.value.trim(),id=0;document.querySelectorAll('#tedList option').forEach(function(o){if(o.value.toLowerCase()===v.toLowerCase())id=o.dataset.id});
document.getElementById('tedId').value=id;document.getElementById('tedYeni').value=(!id&&v)?v:'';document.getElementById('tedNot').textContent=(!id&&v)?'<svg class="ic" aria-hidden="true"><use href="#i-plus-circle"></use></svg> "'+v+'" yeni tedarikçi olarak eklenecek':''}
</script>
<?php include 'inc/layout_bottom.php';
