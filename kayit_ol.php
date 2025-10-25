<?php
// Veritabanı bağlantımızı dahil ediyoruz.
require_once 'db.php';

$error_message = ''; // Hata mesajları için boş bir değişken.

// Sayfaya POST isteği geldiyse (yani form gönderildiyse)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // GÜVENLİK: Şifreyi asla düz metin olarak saklama! password_hash() ile şifrele.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // GÜVENLİK: SQL Injection'ı önlemek için Prepared Statements kullan.
        $stmt = $pdo->prepare("INSERT INTO User (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$full_name, $email, $hashed_password]);

        // Kayıt başarılıysa, kullanıcıyı giriş sayfasına yönlendir.
        header("Location: giris_yap.php?status=success");
        exit;

    } catch (PDOException $e) {
        // Eğer e-posta zaten kayıtlıysa (UNIQUE kısıtlaması nedeniyle) hata verecektir.
        if ($e->getCode() == 23000) { // SQLSTATE[23000]: Integrity constraint violation
            $error_message = "Bu e-posta adresi zaten kayıtlı!";
        } else {
            $error_message = "Bir hata oluştu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        form { border: 1px solid #ccc; padding: 20px; border-radius: 5px; }
        input { display: block; margin-bottom: 10px; padding: 8px; width: 250px; }
        button { padding: 10px; width: 100%; }
        .error { color: red; }
    </style>
</head>
<body>
    <form action="kayit_ol.php" method="POST">
        <h2>Yeni Kullanıcı Kaydı</h2>
        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <input type="text" name="full_name" placeholder="Adınız Soyadınız" required>
        <input type="email" name="email" placeholder="E-posta Adresiniz" required>
        <input type="password" name="password" placeholder="Şifreniz" required>
        <button type="submit">Kayıt Ol</button>
        <p>Zaten bir hesabınız var mı? <a href="giris_yap.php">Giriş Yapın</a></p>
    </form>
</body>
</html>