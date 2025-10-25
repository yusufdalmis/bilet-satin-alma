<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../giris_yap.php");
    exit;
}
require_once '../db.php';
?>