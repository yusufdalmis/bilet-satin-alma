<?php
// Session'ı başlat
session_start();

// Tüm session değişkenlerini sil
$_SESSION = array();

// Session'ı yok et
session_destroy();

// Kullanıcıyı giriş sayfasına yönlendir
header("Location: giris_yap.php");
exit;
?>