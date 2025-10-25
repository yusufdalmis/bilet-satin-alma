<?php
// Session'ı başlat. Bu, her zaman sayfanın en başında olmalıdır!
session_start();

// Eğer kullanıcı zaten giriş yapmışsa, onu ana sayfaya yönlendir.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // E-postaya göre kullanıcıyı veritabanından bul.
    $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Kullanıcı bulunduysa VE girilen şifre veritabanındaki hash ile eşleşiyorsa...
    if ($user && password_verify($password, $user['password'])) {
        // Giriş başarılı! Kullanıcı bilgilerini session'a kaydet.
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role']; // Yetkilendirme için rolü sakla.

        // Kullanıcıyı ana sayfaya yönlendir.
        header("Location: index.php");
        exit;
    } else {
        // Giriş başarısız.
        $error_message = "E-posta veya şifre hatalı!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        form { border: 1px solid #ccc; padding: 20px; border-radius: 5px; }
        input { display: block; margin-bottom: 10px; padding: 8px; width: 250px; }
        button { padding: 10px; width: 100%; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <form action="giris_yap.php" method="POST">
        <h2>Giriş Yap</h2>
        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <p class="success">Kayıt işlemi başarılı! Lütfen giriş yapın.</p>
        <?php endif; ?>
        <input type="email" name="email" placeholder="E-posta Adresiniz" required>
        <input type="password" name="password" placeholder="Şifreniz" required>
        <button type="submit">Giriş Yap</button>
        <p>Hesabınız yok mu? <a href="kayit_ol.php">Kayıt Olun</a></p>
    </form>
</body>
</html>