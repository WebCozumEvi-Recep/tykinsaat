<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli();
$sid = (int)get('santiye_id'); [$w, $p] = santiye_filtre('g'); if ($sid) { santiye_erisim($sid); $w .= " AND g.santiye_id=?"; $p[] = $sid; }
$liste = rows("SELECT g.*, s.ad santiye FROM is_gunlugu g JOIN santiyeler s ON s.id=g.santiye_id WHERE 1=1 $w ORDER BY g.tarih DESC, g.id DESC LIMIT 100", $p);
$baslik = 'İş Günlüğü'; $fab = 'is_gunlugu_form.php' . ($sid ? "?santiye_id=$sid" : ''); include 'inc/layout_top.php'; ?>
<div class="araclar"><select onchange="location='?santiye_id='+this.value"><option value="0">Tüm şantiyeler</option><?php foreach (santiyelerim() as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$sid?'selected':'' ?>><?= e($s['ad']) ?></option><?php endforeach; ?></select><a class="btn btn-turuncu btn-kucuk" href="<?= $fab ?>">+ İş kaydı</a></div>
<?php if (!$liste): ?><div class="kart bos"><div class="b-ico"><svg class="ic" aria-hidden="true"><use href="#i-notebook"></use></svg></div>Henüz iş kaydı yok.</div><?php endif; $son = ''; foreach ($liste as $r): if ($son !== $r['tarih']): $son = $r['tarih']; ?><h3 style="margin:1rem 0 .5rem;color:var(--gri)"><?= tarih_tr($r['tarih']) ?></h3><?php endif; ?>
  <div class="kart"><div style="display:flex;justify-content:space-between;gap:.5rem"><b><?= hava_ikon($r['hava']) ?> <?= e($r['santiye']) ?></b><div><?php if ($r['metraj']): ?><span class="tag tag-mavi"><?= number_format($r['metraj'], 2, ',', '.') ?> <?= $r['birim'] ?></span><?php endif; ?> <a class="btn btn-gri btn-kucuk" href="is_gunlugu_form.php?id=<?= $r['id'] ?>"><svg class="ic" aria-hidden="true"><use href="#i-pencil-simple"></use></svg></a></div></div>
  <p style="margin:.5rem 0"><?= nl2br(e($r['aciklama'])) ?></p>
  <?php if ($f = fotolar($r['fotolar'])): ?><div class="foto-galeri"><?php foreach ($f as $ff): ?><a href="<?= UPLOAD_URL . e($ff) ?>" target="_blank"><img src="<?= UPLOAD_URL . e($ff) ?>" alt=""></a><?php endforeach; ?></div><?php endif; ?></div>
<?php endforeach; include 'inc/layout_bottom.php';
