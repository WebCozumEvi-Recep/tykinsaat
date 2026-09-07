<?php
// Bu dosyayı sunucuda /home/<kullanici>/web/<domain>/tyk-config.php olarak
// (yani public_html'in BİR ÜST dizinine) kopyalayıp doldurun.
define('DB_HOST', 'localhost');
define('DB_NAME', 'kullanici_tyk');
define('DB_USER', 'kullanici_tyk');
define('DB_PASS', 'BURAYA_GUCLU_SIFRE');
define('APP_NAME', 'TYK İnşaat');
define('UPLOAD_DIR', __DIR__ . '/public_html/uploads/');
define('UPLOAD_URL', 'uploads/');
date_default_timezone_set('Europe/Istanbul');
