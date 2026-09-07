<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli();
$fin = finans_gorur(); [$fw, $fp] = santiye_filtre('m');
$mak = $fin ? rows("SELECT m.*, s.ad santiye FROM makineler m LEFT JOIN santiyeler s ON s.id=m.santiye_id ORDER BY m.ad") : rows("SELECT m.*, s.ad santiye FROM makineler m LEFT JOIN santiyeler s ON s.id=m.santiye_id WHERE 1=1 $fw ORDER BY m.ad", $fp);
[$kw, $kp] = santiye_filtre('mk');
$kira = rows("SELECT mk.*, t.firma, s.ad santiye FROM makine_kiralama mk JOIN tedarikciler t ON t.id=mk.tedarikci_id JOIN santiyeler s ON s.id=mk.santiye_id WHERE 1=1 $kw ORDER BY mk.bitis IS NULL DESC, mk.baslangic DESC", $kp);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('islem') === 'gunluk') {
    $sid = (int)post('santiye_id'); santiye_erisim($sid);
    q("INSERT INTO makine_gunluk (makine_id, santiye_id, tarih, saat, notlar) VALUES (?,?,?,?,?)", [(int)post('makine_id'), $sid, post('tarih'), (float)post('saat'), post('notlar')]);
    q("UPDATE makineler SET santiye_id=?, durum='calisiyor' WHERE id=?", [$sid, (int)post('makine_id')]);
    flash('Makine günlüğü kaydedildi.'); redirect('makineler.php');
}
$gunluk = rows("SELECT g.*, m.ad makine, s.ad santiye FROM makine_gunluk g JOIN makineler m ON m.id=g.makine_id JOIN santiyeler s ON s.id=g.santiye_id WHERE 1=1 " . str_replace('m.', 'g.', $fw) . " ORDER BY g.tarih DESC, g.id DESC LIMIT 30", $fp);
$baslik = 'Makineler'; $fab = $fin ? 'makine_form.php' : null; include 'inc/layout_top.php'; ?>
<h2 style="margin-top:0">Kendi makinelerimiz</h2>
<div class="kart liste"><?php if (!$mak): ?><div class="bos"><div class="b-ico"><svg class="ic" aria-hidden="true"><use href="#i-tractor"></use></svg></div>Makine yok.</div><?php endif; foreach ($mak as $m): ?>
  <a class="satir" href="<?= $fin ? "makine_form.php?id={$m['id']}" : '#' ?>"><div class="ico-b"><?= ['kamyon'=>'<svg class="ic" aria-hidden="true"><use href="#i-truck"></use></svg>','ekskavator'=>'<svg class="ic" aria-hidden="true"><use href="#i-tractor"></use></svg>','kepce'=>'<svg class="ic" aria-hidden="true"><use href="#i-crane-tower"></use></svg>','silindir'=>'<svg class="ic" aria-hidden="true"><use href="#i-steering-wheel"></use></svg>','dozer'=>'<svg class="ic" aria-hidden="true"><use href="#i-tractor"></use></svg>','diger'=>'<svg class="ic" aria-hidden="true"><use href="#i-gear"></use></svg>'][$m['tip']] ?></div><div class="govde"><div class="ad"><?= e($m['ad']) ?></div><small><?= tip_adi($m['tip']) ?> · <?= e($m['plaka'] ?: '-') ?> · <svg class="ic" aria-hidden="true"><use href="#i-map-pin"></use></svg> <?= e($m['santiye'] ?: 'Atanmamış') ?></small></div><?= durum_etiketi($m['durum']) ?></a><?php endforeach; ?></div>

<h2>Günlük çalışma kaydı</h2>
<form method="post" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="gunluk">
<div class="satir-form satir-form-3"><label class="alan"><span>Makine</span><select name="makine_id" required><?php foreach ($mak as $m): ?><option value="<?= $m['id'] ?>"><?= e($m['ad']) ?></option><?php endforeach; ?></select></label>
<label class="alan"><span>Şantiye</span><select name="santiye_id" required><?php foreach (santiyelerim(true) as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['ad']) ?></option><?php endforeach; ?></select></label>
<label class="alan"><span>Tarih</span><input type="date" name="tarih" value="<?= date('Y-m-d') ?>"></label></div>
<div class="satir-form"><label class="alan"><span>Çalışma saati</span><input type="number" step="0.5" name="saat" placeholder="8"></label><label class="alan"><span>Not</span><input type="text" name="notlar"></label></div>
<button class="btn btn-turuncu btn-blok" <?= $mak ? '' : 'disabled' ?>>Kaydet</button></form>
<div class="kart liste"><?php foreach ($gunluk as $g): ?><div class="satir"><div class="ico-b"><svg class="ic" aria-hidden="true"><use href="#i-calendar-blank"></use></svg></div><div class="govde"><div class="ad"><?= e($g['makine']) ?> → <?= e($g['santiye']) ?></div><small><?= tarih_tr($g['tarih']) ?> · <?= $g['saat'] ?> saat <?= e($g['notlar']) ?></small></div></div><?php endforeach; if (!$gunluk): ?><div class="bos">Kayıt yok.</div><?php endif; ?></div>

<h2>Dış kiralama <?php if ($fin): ?><a class="btn btn-cizgi btn-kucuk" href="makine_kiralama_form.php">+ Ekle</a><?php endif; ?></h2>
<div class="kart liste"><?php if (!$kira): ?><div class="bos">Kiralama kaydı yok.</div><?php endif; foreach ($kira as $k): ?>
  <a class="satir" href="<?= $fin ? "makine_kiralama_form.php?id={$k['id']}" : '#' ?>"><div class="ico-b"><svg class="ic" aria-hidden="true"><use href="#i-factory"></use></svg></div><div class="govde"><div class="ad"><?= e($k['makine_adi']) ?> · <?= e($k['firma']) ?></div><small><?= e($k['santiye']) ?> · <?= tarih_tr($k['baslangic']) ?> → <?= $k['bitis'] ? tarih_tr($k['bitis']) : 'devam' ?> · <?= tip_adi($k['tip']) ?><?= $fin ? ' ' . para($k['birim_fiyat']) : '' ?></small></div><?php if ($fin): ?><div class="tutar"><?= para($k['toplam']) ?></div><?php endif; ?></a><?php endforeach; ?></div>
<?php include 'inc/layout_bottom.php';
