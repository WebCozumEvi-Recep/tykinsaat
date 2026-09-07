<?php
session_start(); require_once __DIR__ . '/inc/db.php';
if (!empty($_SESSION['uid'])) q("UPDATE kullanicilar SET remember_token=NULL WHERE id=?", [$_SESSION['uid']]);
setcookie('remember', '', time() - 3600, '/'); session_destroy(); header('Location: login.php'); exit;
