<?php
require_once 'db.php';

try {
    echo "Ana Admin kurulumu başlıyor...<br>";

    $admin_fullname = "Sistem Yöneticisi";
    $admin_email = "admin@test.com";
    $admin_password = "123";
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // 'admin' rolündeki kullanıcının company_id'si olmaz (NULL kalır).
    $stmt = $pdo->prepare(
        "INSERT INTO User (full_name, email, password, role) 
         VALUES (?, ?, ?, 'admin')"
    );
    $stmt->execute([$admin_fullname, $admin_email, $hashed_password]);

    echo "Ana Admin kullanıcısı oluşturuldu: $admin_email / Şifre: 123<br>";
    echo "<hr>Kurulum tamamlandı!";

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "<hr>Hata: 'admin@test.com' e-postası zaten mevcut. Bu betik sadece bir kez çalıştırılmalıdır.";
    } else {
        die("Bir hata oluştu: " . $e->getMessage());
    }
}
?>