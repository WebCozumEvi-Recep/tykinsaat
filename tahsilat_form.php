<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = (int)post('santiye_id'); $tutar = tutar_parse(post('tutar')); $tip = post('tip', 'nakit'); $cid = null;
    if ($tip === 'cek') { q("INSERT INTO cekler (yon, cek_no, banka, tutar, vade, durum, santiye_id, aciklama) VALUES ('alinan',?,?,?,?,?,?,?)", [post('cek_no'), post('banka'), $tutar, post('vade') ?: post('tarih'), post('cek_durum', 'portfoy'), $sid, post('aciklama')]); $cid = (int)db()->lastInsertId(); }
    q("INSERT INTO tahsilatlar (santiye_id, tarih, tutar, tip, cek_id, aciklama) VALUES (?,?,?,?,?,?)", [$sid, post('tarih') ?: date('Y-m-d'), $tutar, $tip, $cid, post('aciklama')]);
    flash('Tahsilat kaydedildi <svg class="ic" aria-hidden="true"><use href="#i-check-circle"></use></svg>'); redirect("santiye.php?id=$sid&sek=tahsilat");
}
$baslik = 'Tahsilat Ekle'; $secSid = (int)get('santiye_id'); $secMid = (int)get('musteri_id');
$geri = $secMid ? "musteri.php?id=$secMid" : 'tahsilat.php';
// şantiyeleri müşteriye göre grupla
$grup = []; foreach (santiyelerim() as $s) { if ($secMid && (int)$s['musteri_id'] !== $secMid) continue; $grup[$s['musteri'] ?: 'Müşterisiz'][] = $s; }
include 'inc/layout_top.php'; ?>
<form method="post" class="kart" data-taslak="tahsilat"><?= csrf_field() ?>
  <label class="alan"><span>Şantiye *</span><select name="santiye_id" required <?= $secSid?'data-sabit="1"':'' ?>><option value="">Seçin…</option>
    <?php foreach ($grup as $mad => $liste): ?><optgroup label="<?= e($mad) ?>"><?php foreach ($liste as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$secSid?'selected':'' ?>><?= e($s['ad']) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select>
    <small>Tahsilat bir şantiyeye işlenir; müşterinin toplam alacağına otomatik yansır.</small></label>
  <div class="satir-form"><label class="alan"><span>Tarih</span><input type="date" name="tarih" value="<?= date('Y-m-d') ?>"></label><label class="alan"><span>Tutar (₺) *</span><input type="text" inputmode="decimal" class="tutar-input" name="tutar" required></label></div>
  <label class="alan"><span>Tip</span><div class="secim"><?php foreach (['nakit'=>'<svg class="ic" aria-hidden="true"><use href="#i-money-wavy"></use></svg> Nakit','havale'=>'<svg class="ic" aria-hidden="true"><use href="#i-bank"></use></svg> Havale','cek'=>'<svg class="ic" aria-hidden="true"><use href="#i-files"></use></svg> Çek'] as $k=>$v): ?><input type="radio" name="tip" id="tip<?= $k ?>" value="<?= $k ?>" data-cek-toggle <?= $k==='nakit'?'checked':'' ?> onchange="document.getElementById('cekAlanlari').style.display=this.value==='cek'?'':'none'"><label for="tip<?= $k ?>"><?= $v ?></label><?php endforeach; ?></div></label>
  <div id="cekAlanlari" style="display:none"><div class="satir-form"><label class="alan"><span>Çek no</span><input type="text" name="cek_no"></label><label class="alan"><span>Banka</span><input type="text" name="banka"></label></div>
  <div class="satir-form"><label class="alan"><span>Vade tarihi</span><input type="date" name="vade"></label><label class="alan"><span>Durum</span><select name="cek_durum"><option value="portfoy">Portföyde</option><option value="tahsil">Tahsil edildi</option><option value="ciro">Ciro edildi</option><option value="karsiliksiz">Karşılıksız</option></select></label></div></div>
  <label class="alan"><span>Açıklama</span><input type="text" name="aciklama"></label>
  <button class="btn btn-yesil btn-blok"><svg class="ic" aria-hidden="true"><use href="#i-coins"></use></svg> Tahsilatı Kaydet</button>
</form>
<?php include 'inc/layout_bottom.php';
