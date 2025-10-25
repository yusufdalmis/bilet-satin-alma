<?php
session_start();
require_once '../logger.php'; // Loglama fonksiyonunu dahil et

log_message("firma_admin/auth_check.php çalıştı. Session ID: " . session_id());
log_message("Session içindeki rol: " . ($_SESSION['user_role'] ?? 'TANIMSIZ'));
log_message("Session içindeki company_id: " . ($_SESSION['user_company_id'] ?? 'TANIMSIZ'));

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company_admin') {
    log_message("YETKİSİZ ERİŞİM! Giriş sayfasına yönlendiriliyor.");
    header("Location: ../auth.php");
    exit;
}

log_message("Yetki kontrolü başarılı.");
require_once '../db.php';
?>