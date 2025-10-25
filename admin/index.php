<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
</head>
<body>
    <h1>Admin Paneli</h1>
    <p>Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>
    <nav>
        <ul>
            <li><a href="firmalar.php">Firmaları Yönet</a></li>
            <li><a href="firma_adminleri.php">Firma Adminlerini Yönet</a></li>
            <li><a href="kuponlar.php">Kuponları Yönet</a></li>
        </ul>
    </nav>
    <a href="../auth.php?action=logout">Çıkış Yap</a>
</body>
</html>