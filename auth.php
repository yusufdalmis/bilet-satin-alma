<?php
// Bu dosya login, logout ve login formunu tek başına yönetecek.
session_start();
require_once 'db.php';

// Hangi işlemin yapılacağını URL'den alıyoruz (?action=... şeklinde)
$action = $_GET['action'] ?? 'show_login_form';

switch ($action) {
    case 'logout':
        // GARANTİLİ ÇIKIŞ İŞLEMİ
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        // Çıkış yaptıktan sonra temiz bir giriş formuna yönlendir.
        header('Location: auth.php?action=show_login_form');
        exit;
        

    case 'login':
        // GİRİŞ FORMUNU İŞLEME MANTIĞI
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // POST değilse, forma geri yolla.
            header('Location: auth.php?action=show_login_form');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $error_message = '';

        if (empty($email) || empty($password)) {
            // Hata durumunda, hata mesajıyla birlikte forma geri yönlendir.
            header('Location: auth.php?action=show_login_form&error=empty');
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_balance'] = $user['balance'];

            if ($user['role'] === 'company_admin') {
                $_SESSION['user_company_id'] = $user['company_id'];
                $redirect_url = 'firma_admin/index.php';
            } elseif ($user['role'] === 'admin') {
                $redirect_url = 'admin/index.php';
            } else {
                $redirect_url = 'index.php';
            }
            header("Location: " . $redirect_url);
            exit;
        } else {
            // Hata durumunda, hata mesajıyla birlikte forma geri yönlendir.
            header('Location: auth.php?action=show_login_form&error=invalid');
            exit;
        }
        

    case 'show_login_form':
    default:
        // GİRİŞ FORMUNU GÖSTERME
        // Eğer kullanıcı zaten giriş yapmışsa, onu ilgili sayfaya yönlendir.
        if (isset($_SESSION['user_id'])) {
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'company_admin') {
                header('Location: firma_admin/index.php'); exit;
            } elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                header('Location: admin/index.php'); exit;
            } else {
                header('Location: index.php'); exit;
            }
        }
        
        // Hata mesajlarını URL'den alıp kullanıcıya göstermek için değişkeni burada tanımla.
        $error_message_for_html = '';
        if (isset($_GET['error'])) {
            if ($_GET['error'] === 'invalid') {
                $error_message_for_html = "E-posta veya şifre hatalı!";
            } elseif ($_GET['error'] === 'empty') {
                $error_message_for_html = "E-posta ve şifre alanları boş bırakılamaz.";
            }
        }
        
        // PHP'den çıkıp normal HTML yazmaya başla.
        ?> 
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        form { border: 1px solid #ccc; padding: 20px; border-radius: 5px; min-width: 300px; }
        input { display: block; margin-bottom: 10px; padding: 8px; width: 95%; }
        button { padding: 10px; width: 100%; }
        .error { color: red; }
    </style>
</head>
<body>
    <form action="auth.php?action=login" method="POST">
        <h2>Sisteme Giriş</h2>
        <?php if (!empty($error_message_for_html)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message_for_html); ?></p>
        <?php endif; ?>
        <input type="email" name="email" placeholder="E-posta Adresiniz" required>
        <input type="password" name="password" placeholder="Şifreniz" required>
        <button type="submit">Giriş Yap</button>
        <p>Hesabınız yok mu? <a href="kayit_ol.php">Kayıt Olun</a></p>
    </form>
</body>
</html>
        <?php // HTML bitti, PHP'ye geri dön (switch'i bitirmek için).
        break;
}
?>