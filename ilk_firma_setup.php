<?php
require_once 'db.php';

try {
    echo "Kurulum başlıyor...<br>";

    // 1. Yeni bir otobüs firması oluştur
    $company_name = "Kayseri Seyahat";
    $stmt = $pdo->prepare("INSERT INTO Bus_Company (name) VALUES (?)");
    $stmt->execute([$company_name]);
    $company_id = $pdo->lastInsertId();
    echo "Firma oluşturuldu: $company_name (ID: $company_id)<br>";

    // 2. Bu firmayı yönetecek bir 'company_admin' kullanıcısı oluştur
    $admin_fullname = "Ali Veli";
    $admin_email = "firma@test.com";
    $admin_password = "123"; // Test için basit bir şifre
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare(
        "INSERT INTO User (full_name, email, password, role, company_id) 
         VALUES (?, ?, ?, 'company_admin', ?)"
    );
    $stmt->execute([$admin_fullname, $admin_email, $hashed_password, $company_id]);
    echo "Firma Admin kullanıcısı oluşturuldu: $admin_email<br>";
    echo "<hr>Kurulum tamamlandı! Şimdi <strong>firma@test.com</strong> ve şifre: <strong>123</strong> ile giriş yapabilirsiniz.";

} catch (PDOException $e) {
    // Kod 23000 (UNIQUE constraint) ise, kayıtlar zaten var demektir.
    if ($e->getCode() == 23000) {
        echo "<hr>Hata: Girdiğiniz firma veya e-posta zaten mevcut. Bu betik sadece bir kez çalıştırılmalıdır.";
    } else {
        die("Bir hata oluştu: " . $e->getMessage());
    }
}
?>