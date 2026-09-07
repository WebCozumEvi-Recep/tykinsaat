<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$id = (int)get('id'); $c = row("SELECT * FROM cekler WHERE id=?", [$id]); if (!$c) redirect('raporlar.php');
$geri = get('geri', 'raporlar.php?r=cek');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $d = post('durum'); if (in_array($d, ['portfoy','tahsil','ciro','karsiliksiz','odendi'])) q("UPDATE cekler SET durum=? WHERE id=?", [$d, $id]); flash('Çek durumu güncellendi.'); redirect($geri); }
$baslik = 'Çek Durumu'; include 'inc/layout_top.php'; ?>
<form method="post" class="kart"><?= csrf_field() ?><p><b><?= $c['yon']==='alinan'?'Alınan':'Verilen' ?> çek</b> · <?= e($c['cek_no']) ?> · <?= e($c['banka']) ?> · vade <?= tarih_tr($c['vade']) ?> · <b><?= para($c['tutar']) ?></b></p>
<div class="secim"><?php $ops = $c['yon']==='alinan' ? ['portfoy'=>'Portföyde','tahsil'=>'Tahsil edildi','ciro'=>'Ciro edildi','karsiliksiz'=>'Karşılıksız'] : ['portfoy'=>'Portföyde','odendi'=>'Ödendi','karsiliksiz'=>'Karşılıksız']; foreach ($ops as $k=>$v): ?><input type="radio" name="durum" id="d<?= $k ?>" value="<?= $k ?>" <?= $c['durum']===$k?'checked':'' ?>><label for="d<?= $k ?>"><?= $v ?></label><?php endforeach; ?></div>
<button class="btn btn-turuncu btn-blok" style="margin-top:1rem">Güncelle</button></form>
<?php include 'inc/layout_bottom.php';
