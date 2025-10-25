<?php
require_once 'auth_check.php';

$company_id = $_SESSION['user_company_id'];
$trip = null;
$page_title = "Yeni Sefer Ekle";

// Düzenleme modu: URL'de id varsa
if (isset($_GET['id'])) {
    $trip_id = $_GET['id'];
    // GÜVENLİK: Düzenlenecek seferin bu firmaya ait olduğunu kontrol et!
    $stmt = $pdo->prepare("SELECT * FROM Trips WHERE id = ? AND company_id = ?");
    $stmt->execute([$trip_id, $company_id]);
    $trip = $stmt->fetch();
    if ($trip) {
        $page_title = "Seferi Düzenle";
    }
}

// Form gönderildiğinde (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $departure_city = $_POST['departure_city'];
    $destination_city = $_POST['destination_city'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    $trip_id_hidden = $_POST['trip_id'] ?? null;

    if ($trip_id_hidden) { // Güncelleme
        // GÜVENLİK: Güncellenecek seferin bu firmaya ait olduğunu kontrol et!
        $stmt = $pdo->prepare("UPDATE Trips SET departure_city=?, destination_city=?, departure_time=?, arrival_time=?, price=?, capacity=? WHERE id=? AND company_id=?");
        $stmt->execute([$departure_city, $destination_city, $departure_time, $arrival_time, $price, $capacity, $trip_id_hidden, $company_id]);
    } else { // Ekleme
        $stmt = $pdo->prepare("INSERT INTO Trips (company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$company_id, $departure_city, $destination_city, $departure_time, $arrival_time, $price, $capacity]);
    }
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
</head>
<body>
    <h1><?php echo $page_title; ?></h1>
    <form action="sefer_duzenle.php" method="POST">
        <?php if ($trip): ?>
            <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
        <?php endif; ?>
        
        <p>Kalkış Şehri: <input type="text" name="departure_city" value="<?php echo htmlspecialchars($trip['departure_city'] ?? ''); ?>" required></p>
        <p>Varış Şehri: <input type="text" name="destination_city" value="<?php echo htmlspecialchars($trip['destination_city'] ?? ''); ?>" required></p>
        <p>Kalkış Zamanı: <input type="datetime-local" name="departure_time" value="<?php echo htmlspecialchars($trip['departure_time'] ?? ''); ?>" min="<?php echo date('Y-m-d\TH:i'); ?>" max="2099-12-31T23:59" required></p>
        <p>Varış Zamanı: <input type="datetime-local" name="arrival_time" value="<?php echo htmlspecialchars($trip['arrival_time'] ?? ''); ?>" min="<?php echo date('Y-m-d\TH:i'); ?>" max="2099-12-31T23:59" required></p>
        <p>Fiyat: <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($trip['price'] ?? ''); ?>" required></p>
        <p>Kapasite: <input type="number" name="capacity" value="<?php echo htmlspecialchars($trip['capacity'] ?? ''); ?>" required></p>
        <button type="submit"><?php echo $trip ? 'Güncelle' : 'Ekle'; ?></button>
        <a href="index.php">İptal</a>
    </form>
</body>
</html>