<?php
// $baslik ve isteğe bağlı $geri (url) beklenir
require_once __DIR__ . '/site.php';
$__u = user(); $__flash = flash(); $__firma = firma_bilgi();
// [url, ikon, etiket] üçlüleri; ikonlar assets/icons.svg sprite'ından
$__gruplar = patron() || finans_gorur()
  ? ['Saha' => [['index.php','squares-four','Panel'],['santiyeler.php','crane-tower','Şantiyeler'],['personel.php','users-three','Personel'],['makineler.php','tractor','Makineler'],['is_gunlugu.php','notebook','İş Günlüğü']],
     'Finans' => [['musteriler.php','handshake','Müşteriler'],['tahsilat.php','coins','Tahsilat'],['gider.php','receipt','Giderler'],['tedarikciler.php','factory','Tedarikçiler'],['raporlar.php','chart-line-up','Raporlar']],
     'Web sitesi' => [['site_yonetimi.php','storefront','Site Yönetimi']],
     '' => [['ayarlar.php','gear','Ayarlar']]]
  : ['Bugün' => [['index.php','house','Ana Sayfa'],['puantaj.php','check-circle','Puantaj'],['gider_form.php','receipt','Gider Ekle'],['is_gunlugu.php','notebook','İş Kaydı'],['makineler.php','tractor','Makineler']]];
$__menu = array_merge(...array_values($__gruplar));
$__alt = patron() || finans_gorur()
  ? [['index.php','squares-four','Panel'],['santiyeler.php','crane-tower','Şantiye'],['musteriler.php','handshake','Müşteri'],['tahsilat.php','coins','Tahsilat'],['#menu','list','Menü']]
  : [['index.php','house','Ana Sayfa'],['puantaj.php','check-circle','Puantaj'],['gider_form.php','receipt','Gider'],['is_gunlugu.php','notebook','İş Kaydı'],['#menu','list','Menü']];
$__cur = basename($_SERVER['SCRIPT_NAME']);
$__rol = ['patron'=>'Patron','santiye'=>'Şantiye Yön.','muhasebe'=>'Muhasebe'][$__u['rol']] ?? $__u['rol'];
$__bas = mb_substr($__u['ad_soyad'], 0, 1, 'UTF-8');
if (preg_match('/\s(\p{L})/u', $__u['ad_soyad'], $__m)) $__bas .= $__m[1];
$__gunler = ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];
$__aylar = [1=>'Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
$__tarih = date('j') . ' ' . $__aylar[(int)date('n')] . ' ' . date('Y') . ' ' . $__gunler[(int)date('w')];
?><!DOCTYPE html>
<html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($baslik ?? APP_NAME) ?> · <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/app.css"><meta name="theme-color" content="#f4f5f7"></head>
<body class="<?= patron()||finans_gorur() ? 'mod-patron' : 'mod-saha' ?>">
<?= file_get_contents(__DIR__ . '/../assets/icons.svg') ?>
<a class="atla" href="#icerik">İçeriğe atla</a>
<aside class="sidebar" id="menu">
  <a class="brand" href="index.php">
    <?php if (!empty($__firma['logo'])): ?>
      <img class="brand-logo" src="<?= UPLOAD_URL . e($__firma['logo']) ?>" alt="<?= e($__firma['ad'] ?? APP_NAME) ?>">
    <?php else: ?>
      <span class="brand-mark"><?= ikon('crane-tower') ?></span><span class="brand-ad"><?= APP_NAME ?><small>Saha yönetimi</small></span>
    <?php endif; ?>
  </a>
  <nav aria-label="Ana menü"><?php foreach ($__gruplar as $__g => $__ler): ?>
    <?php if ($__g): ?><p class="nav-grup"><?= $__g ?></p><?php endif; ?>
    <?php foreach ($__ler as [$__mh,$__mi,$__mt]): ?><a href="<?= $__mh ?>" class="<?= $__cur===$__mh?'aktif':'' ?>" <?= $__cur===$__mh?'aria-current="page"':'' ?>><?= ikon($__mi) ?><?= $__mt ?></a><?php endforeach; ?>
  <?php endforeach; ?></nav>
  <div class="sb-user"><span class="avatar" aria-hidden="true"><?= e($__bas) ?></span><span class="sb-ad"><?= e($__u['ad_soyad']) ?><small><?= $__rol ?></small></span><a href="logout.php" title="Çıkış" aria-label="Çıkış"><?= ikon('sign-out') ?></a></div>
</aside>
<div class="sb-overlay" onclick="menuKapat()"></div>
<main class="main">
  <header class="topbar">
    <?php if (!empty($geri)): ?><a class="geri" href="<?= e($geri) ?>" aria-label="Geri"><?= ikon('caret-left') ?></a>
    <?php else: ?><button class="hamb" onclick="menuAc()" aria-label="Menüyü aç"><?= ikon('list') ?></button><?php endif; ?>
    <div class="tb-metin"><p class="tb-tarih"><?= $__tarih ?></p><h1><?= e($baslik ?? '') ?></h1></div>
    <span class="net-durum" id="netDurum"></span>
    <span class="avatar tb-avatar" title="<?= e($__u['ad_soyad']) ?>"><?= e($__bas) ?></span>
  </header>
  <?php if ($__flash): ?><div class="flash flash-<?= $__flash[1] ?>"><?= ikon($__flash[1]==='ok'?'check-circle':'warning-circle') ?><?= e($__flash[0]) ?></div><?php endif; ?>
  <div class="icerik" id="icerik">
