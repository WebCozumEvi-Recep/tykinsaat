<?php
session_start();
require_once __DIR__ . '/inc/db.php'; require_once __DIR__ . '/inc/helpers.php';
if (!empty($_SESSION['uid'])) redirect('index.php');
$hata = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = row("SELECT * FROM kullanicilar WHERE kullanici_adi=? AND aktif=1", [post('kullanici_adi')]);
    if ($u && password_verify(post('sifre'), $u['sifre_hash'])) {
        session_regenerate_id(true); $_SESSION['uid'] = $u['id'];
        if (!empty($_POST['hatirla'])) {
            $t = bin2hex(random_bytes(32)); q("UPDATE kullanicilar SET remember_token=? WHERE id=?", [$t, $u['id']]);
            setcookie('remember', $t, ['expires' => time() + 86400 * 30, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        }
        redirect('index.php');
    }
    $hata = 'Kullanıcı adı veya şifre hatalı.';
}
$firma = row("SELECT * FROM firma WHERE id=1");
?><!DOCTYPE html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Giriş · <?= APP_NAME ?></title><link rel="stylesheet" href="assets/app.css"></head>
<body><?= file_get_contents(__DIR__ . '/assets/icons.svg') ?><div class="login"><div class="kart">
  <div class="logo"><?php if (!empty($firma['logo'])): ?><img src="<?= UPLOAD_URL . e($firma['logo']) ?>" alt=""><?php else: ?><svg class="ic" aria-hidden="true"><use href="#i-crane-tower"></use></svg><?php endif; ?></div>
  <h2 style="text-align:center;margin:0 0 1.2rem"><?= e($firma['ad'] ?? APP_NAME) ?></h2>
  <?php if ($hata): ?><div class="flash flash-hata" style="margin:0 0 1rem"><?= e($hata) ?></div><?php endif; ?>
  <form method="post">
    <label class="alan"><span>Kullanıcı adı</span><input type="text" name="kullanici_adi" required autofocus autocomplete="username"></label>
    <label class="alan"><span>Şifre</span><input type="password" name="sifre" required autocomplete="current-password"></label>
    <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem"><input type="checkbox" name="hatirla" value="1" style="width:22px;height:22px"> Beni hatırla</label>
    <button class="btn btn-turuncu btn-blok">Giriş Yap</button>
  </form>
</div></div></body></html>
