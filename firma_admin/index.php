<?php
require_once 'auth_check.php';

$company_id = $_SESSION['user_company_id'];
$message = '';

// Silme işlemi (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_trip_id'])) {
    $trip_id_to_delete = $_POST['delete_trip_id'];
    
    // GÜVENLİK: Silinecek seferin bu firmaya ait olduğunu kontrol et!
    $stmt = $pdo->prepare("DELETE FROM Trips WHERE id = ? AND company_id = ?");
    $stmt->execute([$trip_id_to_delete, $company_id]);
    
    if ($stmt->rowCount() > 0) {
        $message = "Sefer başarıyla silindi.";
    } else {
        $message = "Hata: Sefer silinemedi veya yetkiniz yok.";
    }
}

// Sadece bu firmaya ait seferleri listele
$stmt = $pdo->prepare("SELECT * FROM Trips WHERE company_id = ? ORDER BY departure_time DESC");
$stmt->execute([$company_id]);
$trips = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Admin Paneli</title>
    </head>
<body>
    <div class="container">
        <header>
            <h1>Firma Paneli - Sefer Yönetimi</h1>
            <nav>
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="../auth.php?action=logout">Çıkış Yap</a>
            </nav>
        </header>
        <main>
            <a href="sefer_duzenle.php">Yeni Sefer Ekle</a>
            <hr>
            <?php if ($message): ?><p><?php echo $message; ?></p><?php endif; ?>
            
            <h3>Mevcut Seferler</h3>
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Güzergah</th>
                        <th>Kalkış Zamanı</th>
                        <th>Fiyat</th>
                        <th>Kapasite</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $trip): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($trip['departure_city']); ?> - <?php echo htmlspecialchars($trip['destination_city']); ?></td>
                        <td><?php echo htmlspecialchars($trip['departure_time']); ?></td>
                        <td><?php echo htmlspecialchars($trip['price']); ?> TL</td>
                        <td><?php echo htmlspecialchars($trip['capacity']); ?></td>
                        <td>
                            <a href="sefer_duzenle.php?id=<?php echo $trip['id']; ?>">Düzenle</a>
                            <form action="index.php" method="POST" style="display:inline;" onsubmit="return confirm('Bu seferi silmek istediğinizden emin misiniz?');">
                                <input type="hidden" name="delete_trip_id" value="<?php echo $trip['id']; ?>">
                                <button type="submit">Sil</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>