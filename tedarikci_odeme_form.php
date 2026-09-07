<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$tid = (int)get('tedarikci_id', post('tedarikci_id'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = (int)post('tedarikci_id'); $tutar = tutar_parse(post('tutar')); $tip = post('tip', 'nakit'); $sid = (int)post('santiye_id') ?: null; $cid = null;
    if ($tip === 'cek') { q("INSERT INTO cekler (yon, cek_no, banka, tutar, vade, durum, santiye_id, tedarikci_id, aciklama) VALUES ('verilen',?,?,?,?,'portfoy',?,?,?)", [post('cek_no'), post('banka'), $tutar, post('vade') ?: post('tarih'), $sid, $tid, post('aciklama')]); $cid = (int)db()->lastInsertId(); }
    q("INSERT INTO tedarikci_odemeleri (tedarikci_id, santiye_id, tarih, tutar, tip, cek_id, aciklama) VALUES (?,?,?,?,?,?,?)", [$tid, $sid, post('tarih') ?: date('Y-m-d'), $tutar, $tip, $cid, post('aciklama')]);
    flash('Ödeme kaydedildi <svg class="ic" aria-hidden="true"><use href="#i-check-circle"></use></svg>'); redirect("tedarikci.php?id=$tid");
}
$baslik = 'Tedarikçi Ödemesi'; $geri = $tid ? "tedarikci.php?id=$tid" : 'tedarikciler.php'; include 'inc/layout_top.php'; ?>
<form method="post" class="kart" data-taslak="todeme"><?= csrf_field() ?>
  <label class="alan"><span>Tedarikçi *</span><select name="tedarikci_id" required><option value="">Seçin…</option><?php foreach (rows("SELECT id, firma FROM tedarikciler ORDER BY firma") as $t): $b = tedarikci_bakiye($t['id']); ?><option value="<?= $t['id'] ?>" <?= $t['id']==$tid?'selected':'' ?>><?= e($t['firma']) ?> (borç <?= para($b['bakiye']) ?>)</option><?php endforeach; ?></select></label>
  <label class="alan"><span>Şantiye (opsiyonel)</span><select name="santiye_id"><option value="">Genel</option><?php foreach (santiyelerim() as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['ad']) ?></option><?php endforeach; ?></select></label>
  <div class="satir-form"><label class="alan"><span>Tarih</span><input type="date" name="tarih" value="<?= date('Y-m-d') ?>"></label><label class="alan"><span>Tutar (₺) *</span><input type="text" inputmode="decimal" class="tutar-input" name="tutar" required></label></div>
  <label class="alan"><span>Ödeme şekli</span><div class="secim"><?php foreach (['nakit'=>'<svg class="ic" aria-hidden="true"><use href="#i-money-wavy"></use></svg> Nakit','havale'=>'<svg class="ic" aria-hidden="true"><use href="#i-bank"></use></svg> Havale','cek'=>'<svg class="ic" aria-hidden="true"><use href="#i-files"></use></svg> Çek'] as $k=>$v): ?><input type="radio" name="tip" id="tip<?= $k ?>" value="<?= $k ?>" <?= $k==='nakit'?'checked':'' ?> onchange="document.getElementById('cekAlanlari').style.display=this.value==='cek'?'':'none'"><label for="tip<?= $k ?>"><?= $v ?></label><?php endforeach; ?></div></label>
  <div id="cekAlanlari" style="display:none"><div class="satir-form"><label class="alan"><span>Çek no</span><input type="text" name="cek_no"></label><label class="alan"><span>Banka</span><input type="text" name="banka"></label></div><label class="alan"><span>Vade tarihi</span><input type="date" name="vade"></label></div>
  <label class="alan"><span>Açıklama</span><input type="text" name="aciklama"></label>
  <button class="btn btn-yesil btn-blok">Ödemeyi Kaydet</button>
</form>
<?php include 'inc/layout_bottom.php';
