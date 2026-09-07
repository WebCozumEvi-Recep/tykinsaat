<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli();
$sant = santiyelerim(true); $sid = (int)get('santiye_id', $sant[0]['id'] ?? 0); $tarih = get('tarih', date('Y-m-d'));
if ($sid) santiye_erisim($sid);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = (int)post('santiye_id'); $tarih = post('tarih'); santiye_erisim($sid);
    $pdo = db(); $pdo->beginTransaction();
    foreach ((array)($_POST['durum'] ?? []) as $pid => $durum) {
        $pid = (int)$pid; if (!in_array($durum, ['tam','yarim','yok'])) continue;
        $mesai = (float)str_replace(',', '.', $_POST['mesai'][$pid] ?? 0);
        $yev = (float)val("SELECT yevmiye FROM personel WHERE id=?", [$pid]);
        q("INSERT INTO puantaj (santiye_id, personel_id, tarih, durum, mesai_saat, yevmiye, kaydeden) VALUES (?,?,?,?,?,?,?)
           ON DUPLICATE KEY UPDATE santiye_id=VALUES(santiye_id), durum=VALUES(durum), mesai_saat=VALUES(mesai_saat), kaydeden=VALUES(kaydeden)", [$sid, $pid, $tarih, $durum, $mesai, $yev, user()['id']]);
    }
    $pdo->commit(); flash('Puantaj kaydedildi <svg class="ic" aria-hidden="true"><use href="#i-check-circle"></use></svg>'); redirect("puantaj.php?santiye_id=$sid&tarih=$tarih");
}
$personel = rows("SELECT * FROM personel WHERE aktif=1 ORDER BY ad_soyad");
$mevcut = $sid ? rows("SELECT * FROM puantaj WHERE tarih=?", [$tarih]) : []; $mv = []; foreach ($mevcut as $m) $mv[$m['personel_id']] = $m;
$baslik = 'Puantaj'; $fin = finans_gorur();
include 'inc/layout_top.php'; ?>
<?php if (!$sant): ?><div class="kart bos"><div class="b-ico"><svg class="ic" aria-hidden="true"><use href="#i-crane-tower"></use></svg></div>Aktif şantiye yok.</div><?php else: ?>
<form method="get" class="kart"><div class="satir-form">
  <label class="alan" style="margin:0"><span>Şantiye</span><select name="santiye_id" data-sabit="1" onchange="this.form.submit()"><?php foreach ($sant as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$sid?'selected':'' ?>><?= e($s['ad']) ?></option><?php endforeach; ?></select></label>
  <label class="alan" style="margin:0"><span>Tarih</span><input type="date" name="tarih" value="<?= e($tarih) ?>" onchange="this.form.submit()"></label></div>
  <div class="hizli"><button type="button" class="btn btn-gri btn-kucuk" data-tarih="bugun" data-hedef="input[name=tarih]">Bugün</button><button type="button" class="btn btn-gri btn-kucuk" data-tarih="dun" data-hedef="input[name=tarih]">Dün</button><?php if ($fin): ?><a class="btn btn-cizgi btn-kucuk" href="puantaj_aylik.php?santiye_id=<?= $sid ?>"><svg class="ic" aria-hidden="true"><use href="#i-calendar-blank"></use></svg> Aylık</a><?php endif; ?></div>
</form>
<form method="post" data-taslak="puantaj-<?= $sid ?>-<?= $tarih ?>"><?= csrf_field() ?><input type="hidden" name="santiye_id" value="<?= $sid ?>"><input type="hidden" name="tarih" value="<?= e($tarih) ?>">
<div class="kart">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem"><b><?= count($personel) ?> personel</b><button type="button" class="btn btn-yesil btn-kucuk" onclick="herkesTam()"><svg class="ic" aria-hidden="true"><use href="#i-check-circle"></use></svg> Herkes tam gün</button></div>
  <?php if (!$personel): ?><div class="bos">Aktif personel yok.</div><?php endif; ?>
  <?php foreach ($personel as $p): $m = $mv[$p['id']] ?? null; $d = $m['durum'] ?? ''; $baska = $m && $m['santiye_id'] != $sid; ?>
  <div class="puan-satir" data-yevmiye="<?= $fin ? $p['yevmiye'] : 0 ?>">
    <div class="ad"><?= e($p['ad_soyad']) ?><br><small><?= gorev_adi($p['gorev']) ?><?= $baska ? ' · <span class="sari">başka şantiyede kayıtlı</span>' : '' ?></small></div>
    <div class="secim">
      <input type="radio" name="durum[<?= $p['id'] ?>]" id="t<?= $p['id'] ?>" value="tam" <?= $d==='tam'?'checked':'' ?>><label for="t<?= $p['id'] ?>">Tam</label>
      <input type="radio" name="durum[<?= $p['id'] ?>]" id="y<?= $p['id'] ?>" value="yarim" <?= $d==='yarim'?'checked':'' ?>><label for="y<?= $p['id'] ?>">Yarım</label>
      <input type="radio" name="durum[<?= $p['id'] ?>]" id="x<?= $p['id'] ?>" value="yok" <?= $d==='yok'?'checked':'' ?>><label for="x<?= $p['id'] ?>">Gelmedi</label>
    </div>
    <input type="number" class="mesai" name="mesai[<?= $p['id'] ?>]" step="0.5" min="0" max="12" placeholder="Mesai" value="<?= $m && $m['mesai_saat'] > 0 ? $m['mesai_saat'] : '' ?>" title="Mesai saati">
    <?php if ($fin): ?><div class="gun-tutar"></div><?php endif; ?>
  </div><?php endforeach; ?>
</div>
<div class="sabit-alt"><button class="btn btn-turuncu btn-blok"><svg class="ic" aria-hidden="true"><use href="#i-floppy-disk-back"></use></svg> Kaydet</button></div>
</form>
<?php endif; include 'inc/layout_bottom.php';
