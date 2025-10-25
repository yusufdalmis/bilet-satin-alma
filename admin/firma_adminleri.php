<?php
require_once 'auth_check.php';

// JOIN ile kullanıcıları ve atandıkları firmaları birlikte listele
$stmt = $pdo->query("
    SELECT User.*, Bus_Company.name as company_name 
    FROM User 
    LEFT JOIN Bus_Company ON User.company_id = Bus_Company.id 
    WHERE User.role = 'company_admin'
");
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<body>
    <h1>Firma Adminlerini Yönet</h1>
    <a href="index.php">Geri</a> | <a href="firma_admin_duzenle.php">Yeni Firma Admini Ekle</a>
    <table border="1">
        <tr><th>Adı Soyadı</th><th>Email</th><th>Atandığı Firma</th><th>İşlemler</th></tr>
        <?php foreach ($admins as $admin): ?>
        <tr>
            <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
            <td><?php echo htmlspecialchars($admin['email']); ?></td>
            <td><?php echo htmlspecialchars($admin['company_name'] ?? 'Atanmamış'); ?></td>
            <td><a href="firma_admin_duzenle.php?id=<?php echo $admin['id']; ?>">Düzenle</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>