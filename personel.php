<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); finans_gerekli();
$id = (int)get('id'); $fab = 'personel_form.php';
if ($id) {
  $kisi = row("SELECT * FROM personel WHERE id=?", [$id]);
  if (!$kisi) { flash('Personel bulunamadı.', 'hata'); redirect('personel.php'); }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('islem') === 'odeme') {
    $tutar = tutar_parse(post('tutar'));
    if ($tutar <= 0) flash('Tutar sıfırdan büyük olmalı.', 'hata');
    else { q("INSERT INTO personel_odeme (personel_id, tarih, tutar, aciklama) VALUES (?,?,?,?)", [$id, post('tarih') ?: date('Y-m-d'), $tutar, post('aciklama')]); flash('Ödeme kaydedildi.'); }
    redirect("personel.php?id=$id");
  }

  $ay = get('ay', date('Y-m'));
  $hak = personel_hakedis($id);                    // tüm zamanlar
  $gun = row("SELECT SUM(durum='tam') tam, SUM(durum='yarim') yarim, SUM(durum='yok') yok, COALESCE(SUM(mesai_saat),0) mesai FROM puantaj WHERE personel_id=?", [$id]);
  // Seçili ayın kırılımı
  $ayOzet = row("SELECT SUM(durum='tam') tam, SUM(durum='yarim') yarim, COALESCE(SUM(mesai_saat),0) mesai,
      COALESCE(SUM(CASE durum WHEN 'tam' THEN yevmiye WHEN 'yarim' THEN yevmiye/2 ELSE 0 END + mesai_saat*yevmiye/9),0) hakedis
      FROM puantaj WHERE personel_id=? AND DATE_FORMAT(tarih,'%Y-%m')=?", [$id, $ay]);
  $ayOdenen = (float)val("SELECT COALESCE(SUM(tutar),0) FROM personel_odeme WHERE personel_id=? AND DATE_FORMAT(tarih,'%Y-%m')=?", [$id, $ay]);
  $santiyeler = rows("SELECT s.id, s.ad, m.ad musteri, SUM(p.durum='tam') tam, SUM(p.durum='yarim') yarim, COALESCE(SUM(p.mesai_saat),0) mesai,
      COALESCE(SUM(CASE p.durum WHEN 'tam' THEN p.yevmiye WHEN 'yarim' THEN p.yevmiye/2 ELSE 0 END + p.mesai_saat*p.yevmiye/9),0) tutar
      FROM puantaj p JOIN santiyeler s ON s.id=p.santiye_id LEFT JOIN musteriler m ON m.id=s.musteri_id
      WHERE p.personel_id=? GROUP BY s.id ORDER BY tutar DESC", [$id]);
  $sonPuantaj = rows("SELECT p.*, s.ad santiye FROM puantaj p JOIN santiyeler s ON s.id=p.santiye_id WHERE p.personel_id=? AND DATE_FORMAT(p.tarih,'%Y-%m')=? ORDER BY p.tarih DESC", [$id, $ay]);
  $odemeler = rows("SELECT * FROM personel_odeme WHERE personel_id=? ORDER BY tarih DESC, id DESC LIMIT 50", [$id]);

  $baslik = $kisi['ad_soyad']; $geri = 'personel.php'; $fab = null;
  include 'inc/layout_top.php'; ?>

  <div class="kart" style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:.75rem">
      <div class="ico-b" style="width:52px;height:52px;font-size:1.6rem"><svg class="ic" aria-hidden="true"><use href="#i-hard-hat"></use></svg></div>
      <div><b style="font-size:1.05rem"><?= gorev_adi($kisi['gorev']) ?></b><br>
      <small>Günlük yevmiye <b><?= para($kisi['yevmiye']) ?></b><?= $kisi['telefon'] ? ' · <svg class="ic" aria-hidden="true"><use href="#i-phone"></use></svg> <a href="tel:' . e($kisi['telefon']) . '">' . e($kisi['telefon']) . '</a>' : '' ?></small></div>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center"><?= $kisi['aktif'] ? etiket('Aktif','yesil') : etiket('Pasif','gri') ?>
      <?php if (patron()): ?><a class="btn btn-cizgi btn-kucuk" href="personel_form.php?id=<?= $id ?>"><svg class="ic" aria-hidden="true"><use href="#i-pencil-simple"></use></svg> Düzenle</a><?php endif; ?></div>
  </div>

  <h2 style="margin-top:0">Genel durum</h2>
  <div class="grid">
    <div class="ozet ozet-mavi"><div class="baslik">Toplam hakediş</div><div class="rakam"><?= para($hak['hakedis']) ?></div><div class="alt"><?= (int)$gun['tam'] ?> tam · <?= (int)$gun['yarim'] ?> yarım gün</div></div>
    <div class="ozet ozet-yesil"><div class="baslik">Ödenen</div><div class="rakam"><?= para($hak['odenen']) ?></div><div class="alt"><?= $hak['hakedis'] > 0 ? round($hak['odenen'] / $hak['hakedis'] * 100) : 0 ?>% ödendi</div></div>
    <div class="ozet <?= $hak['kalan'] > 0 ? 'ozet-kirmizi' : 'ozet-gri' ?>"><div class="baslik">Kalan borç</div><div class="rakam"><?= para($hak['kalan']) ?></div><div class="alt"><?= $hak['kalan'] > 0 ? 'ödenmemiş hakediş' : 'borç yok' ?></div></div>
    <div class="ozet ozet-turuncu"><div class="baslik">Toplam mesai</div><div class="rakam"><?= rtrim(rtrim(number_format((float)$gun['mesai'], 1, ',', '.'), '0'), ',') ?> sa</div><div class="alt"><?= (int)$gun['yok'] ?> gün gelmedi</div></div>
  </div>

  <h2>Aylık kırılım</h2>
  <div class="araclar"><input type="month" value="<?= e($ay) ?>" onchange="location='?id=<?= $id ?>&ay='+this.value">
    <a class="btn btn-cizgi btn-kucuk" href="puantaj_aylik.php?ay=<?= $ay ?>"><svg class="ic" aria-hidden="true"><use href="#i-calendar-blank"></use></svg> Aylık puantaj tablosu</a></div>
  <div class="grid">
    <div class="ozet ozet-mavi"><div class="baslik">Çalışılan gün</div><div class="rakam"><?= (int)$ayOzet['tam'] ?> + <?= (int)$ayOzet['yarim'] ?>½</div><div class="alt">tam + yarım</div></div>
    <div class="ozet ozet-turuncu"><div class="baslik">Mesai</div><div class="rakam"><?= rtrim(rtrim(number_format((float)$ayOzet['mesai'], 1, ',', '.'), '0'), ',') ?> sa</div><div class="alt">bu ay</div></div>
    <div class="ozet ozet-mavi"><div class="baslik">Ay hakedişi</div><div class="rakam"><?= para($ayOzet['hakedis']) ?></div></div>
    <div class="ozet ozet-yesil"><div class="baslik">Ay içi ödeme</div><div class="rakam"><?= para($ayOdenen) ?></div></div>
  </div>

  <h2>Şantiye bazında çalışma</h2>
  <div class="tablo-kutu"><table>
    <tr><th>Şantiye</th><th class="num">Tam</th><th class="num">Yarım</th><th class="num">Mesai</th><th class="num">Hakediş</th></tr>
    <?php foreach ($santiyeler as $sr): ?>
    <tr onclick="location='santiye.php?id=<?= $sr['id'] ?>'" style="cursor:pointer"><td><b><?= e($sr['ad']) ?></b><?= $sr['musteri'] ? '<br><small>' . e($sr['musteri']) . '</small>' : '' ?></td>
      <td class="num"><?= (int)$sr['tam'] ?></td><td class="num"><?= (int)$sr['yarim'] ?></td><td class="num"><?= $sr['mesai'] > 0 ? rtrim(rtrim(number_format((float)$sr['mesai'], 1, ',', '.'), '0'), ',') : '-' ?></td>
      <td class="num b"><?= para($sr['tutar']) ?></td></tr>
    <?php endforeach; if (!$santiyeler): ?><tr><td colspan="5" class="bos">Henüz puantaj kaydı yok.</td></tr><?php endif; ?>
    <?php if ($santiyeler): ?><tfoot><tr><td>Toplam</td><td class="num"><?= (int)$gun['tam'] ?></td><td class="num"><?= (int)$gun['yarim'] ?></td><td class="num"><?= rtrim(rtrim(number_format((float)$gun['mesai'], 1, ',', '.'), '0'), ',') ?></td><td class="num"><?= para($hak['hakedis']) ?></td></tr></tfoot><?php endif; ?>
  </table></div>

  <h2><?= date('m.Y', strtotime($ay . '-01')) ?> puantaj kayıtları</h2>
  <div class="kart liste">
    <?php foreach ($sonPuantaj as $pk): $gunluk = ($pk['durum'] === 'tam' ? $pk['yevmiye'] : ($pk['durum'] === 'yarim' ? $pk['yevmiye'] / 2 : 0)) + $pk['mesai_saat'] * $pk['yevmiye'] / 9; ?>
      <a class="satir" href="puantaj.php?santiye_id=<?= $pk['santiye_id'] ?>&tarih=<?= $pk['tarih'] ?>">
        <div class="ico-b"><?= ['tam' => '<svg class="ic" aria-hidden="true"><use href="#i-check-circle"></use></svg>', 'yarim' => '<svg class="ic" aria-hidden="true"><use href="#i-circle-half"></use></svg>', 'yok' => '<svg class="ic" aria-hidden="true"><use href="#i-x-circle"></use></svg>'][$pk['durum']] ?></div>
        <div class="govde"><div class="ad"><?= tarih_tr($pk['tarih']) ?> · <?= ['tam' => 'Tam gün', 'yarim' => 'Yarım gün', 'yok' => 'Gelmedi'][$pk['durum']] ?></div>
          <small><?= e($pk['santiye']) ?><?= $pk['mesai_saat'] > 0 ? ' · ' . rtrim(rtrim(number_format((float)$pk['mesai_saat'], 1, ',', '.'), '0'), ',') . ' sa mesai' : '' ?></small></div>
        <div class="tutar <?= $gunluk > 0 ? '' : 'gri' ?>"><?= para($gunluk) ?></div></a>
    <?php endforeach; if (!$sonPuantaj): ?><div class="bos">Bu ay puantaj kaydı yok.</div><?php endif; ?>
  </div>

  <h2>Ödeme ekle</h2>
  <form method="post" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="odeme">
    <div class="satir-form">
      <label class="alan"><span>Tarih</span><input type="date" name="tarih" value="<?= date('Y-m-d') ?>"></label>
      <label class="alan"><span>Tutar (₺) *</span><input type="text" inputmode="decimal" class="tutar-input" name="tutar" required></label>
    </div>
    <label class="alan"><span>Açıklama</span><input type="text" name="aciklama" placeholder="Haftalık avans…"></label>
    <?php if ($hak['kalan'] > 0): ?><div class="bilgi">Bu personelin ödenmemiş hakedişi: <b><?= para($hak['kalan']) ?></b></div><?php endif; ?>
    <button class="btn btn-yesil btn-blok"><svg class="ic" aria-hidden="true"><use href="#i-coins"></use></svg> Ödemeyi Kaydet</button>
  </form>

  <h2>Ödeme geçmişi</h2>
  <div class="kart liste">
    <?php foreach ($odemeler as $od): ?>
      <div class="satir"><div class="ico-b"><svg class="ic" aria-hidden="true"><use href="#i-money-wavy"></use></svg></div>
        <div class="govde"><div class="ad"><?= tarih_tr($od['tarih']) ?></div><small><?= e($od['aciklama'] ?: 'Açıklama yok') ?></small></div>
        <div class="tutar yesil"><?= para($od['tutar']) ?></div>
        <a class="btn btn-gri btn-kucuk" href="sil.php?t=personel_odeme&id=<?= $od['id'] ?>&geri=<?= urlencode("personel.php?id=$id") ?>" data-onay="Bu ödeme silinsin mi?"><svg class="ic" aria-hidden="true"><use href="#i-trash"></use></svg></a></div>
    <?php endforeach; if (!$odemeler): ?><div class="bos">Henüz ödeme yapılmamış.</div><?php endif; ?>
  </div>

  <?php include 'inc/layout_bottom.php'; exit;
}
$ara = get('q'); $liste = rows("SELECT * FROM personel WHERE ad_soyad LIKE ? ORDER BY aktif DESC, ad_soyad", ["%$ara%"]);
$baslik = 'Personel'; include 'inc/layout_top.php'; ?>
<form class="araclar"><input type="text" name="q" placeholder="Ara…" value="<?= e($ara) ?>"><button class="btn btn-kucuk">Ara</button></form>
<div class="kart liste"><?php if (!$liste): ?><div class="bos"><div class="b-ico"><svg class="ic" aria-hidden="true"><use href="#i-hard-hat"></use></svg></div>Henüz personel yok.</div><?php endif; foreach ($liste as $p): $h = personel_hakedis($p['id']); ?>
  <a class="satir" href="personel.php?id=<?= $p['id'] ?>"><div class="ico-b"><svg class="ic" aria-hidden="true"><use href="#i-hard-hat"></use></svg></div><div class="govde"><div class="ad"><?= e($p['ad_soyad']) ?> <?= $p['aktif'] ? '' : etiket('Pasif','gri') ?></div><small><?= gorev_adi($p['gorev']) ?> · Yevmiye <?= para($p['yevmiye']) ?></small></div><div class="tutar <?= $h['kalan']>0?'kirmizi':'gri' ?>"><?= para($h['kalan']) ?><br><small>kalan</small></div></a>
<?php endforeach; ?></div>
<?php include 'inc/layout_bottom.php';
