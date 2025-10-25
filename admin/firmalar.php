<?php
require_once 'auth_check.php';

// Silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_company_id'])) {
    // İlgili firmaya bağlı kullanıcıların company_id'sini null yap
    $stmt = $pdo->prepare("UPDATE User SET company_id = NULL WHERE company_id = ?");
    $stmt->execute([$_POST['delete_company_id']]);
    
    // Firmayı sil
    $stmt = $pdo->prepare("DELETE FROM Bus_Company WHERE id = ?");
    $stmt->execute([$_POST['delete_company_id']]);
    header("Location: firmalar.php");
    exit;
}

$companies = $pdo->query("SELECT * FROM Bus_Company")->fetchAll();
?>
<!DOCTYPE html>
<html>
<body>
    <h1>Firmaları Yönet</h1>
    <a href="index.php">Geri</a> | <a href="firma_duzenle.php">Yeni Firma Ekle</a>
    <table border="1">
        <tr><th>ID</th><th>Firma Adı</th><th>İşlemler</th></tr>
        <?php foreach ($companies as $company): ?>
        <tr>
            <td><?php echo $company['id']; ?></td>
            <td><?php echo htmlspecialchars($company['name']); ?></td>
            <td>
                <a href="firma_duzenle.php?id=<?php echo $company['id']; ?>">Düzenle</a>
                <form method="POST" onsubmit="return confirm('Bu firmayı silmek istediğinizden emin misiniz?');" style="display:inline;">
                    <input type="hidden" name="delete_company_id" value="<?php echo $company['id']; ?>">
                    <button type="submit">Sil</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>