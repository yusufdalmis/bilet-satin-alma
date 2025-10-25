<?php
// Sayfanın en başına session'ı başlatıyoruz.
session_start();

// Veritabanı bağlantımızı ve diğer ayarları dahil ediyoruz.
require_once 'db.php';

// Arama sonuçlarını tutmak için boş bir dizi oluşturuyoruz.
$trips = [];
$search_performed = false;

// Eğer form GET metodu ile gönderildiyse ve arama parametreleri varsa
if ($_SERVER['REQUEST_METHOD'] == 'GET' && (isset($_GET['departure_city']) || isset($_GET['arrival_city']))) {
    $search_performed = true;
    $departure_city = trim($_GET['departure_city']);
    $arrival_city = trim($_GET['arrival_city']);

    // Temel SQL sorgusu
    // Join ile Bus_Company tablosundan firma adını da alıyoruz.
    $sql = "SELECT Trips.*, Bus_Company.name as company_name 
            FROM Trips 
            JOIN Bus_Company ON Trips.company_id = Bus_Company.id 
            WHERE 1=1";
    
    $params = [];

    if (!empty($departure_city)) {
        $sql .= " AND departure_city LIKE ?";
        $params[] = '%' . $departure_city . '%';
    }

    if (!empty($arrival_city)) {
        $sql .= " AND destination_city LIKE ?";
        $params[] = '%' . $arrival_city . '%';
    }

    $sql .= " ORDER BY departure_time ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $trips = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet Satın Alma Platformu</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .container { max-width: 960px; margin: 20px auto; padding: 0 20px; }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        nav a { margin-left: 15px; text-decoration: none; color: #007bff; }
        .search-form { background-color: #f4f4f4; padding: 20px; border-radius: 5px; margin-top: 20px; }
        .search-form input { padding: 10px; margin-right: 10px; }
        .search-form button { padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer; }
        .trip { border: 1px solid #ddd; padding: 15px; margin-top: 15px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .trip-info { flex-grow: 1; }
        .trip-action a { background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1><a href="index.php" style="text-decoration: none; color: inherit;">Bilet Platformu</a></h1>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Hoş geldiniz, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                <a href="biletlerim.php">Biletlerim</a>
                <a href="auth.php?action=logout">Çıkış Yap</a>
            <?php else: ?>
                <a href="giris_yap.php">Giriş Yap</a>
                <a href="kayit_ol.php">Kayıt Ol</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <div class="search-form">
            <h2>Nereye Gitmek İstersiniz?</h2>
            <form action="index.php" method="GET">
                <input type="text" name="departure_city" placeholder="Kalkış Şehri" value="<?php echo isset($_GET['departure_city']) ? htmlspecialchars($_GET['departure_city']) : ''; ?>">
                <input type="text" name="arrival_city" placeholder="Varış Şehri" value="<?php echo isset($_GET['arrival_city']) ? htmlspecialchars($_GET['arrival_city']) : ''; ?>">
                <button type="submit">Sefer Bul</button>
            </form>
        </div>

        <div class="trips-list">
            <h3>Seferler</h3>
            <?php if ($search_performed): ?>
                <?php if (count($trips) > 0): ?>
                    <?php foreach ($trips as $trip): ?>
                        <div class="trip">
                            <div class="trip-info">
                                <strong><?php echo htmlspecialchars($trip['company_name']); ?></strong><br>
                                Kalkış: <strong><?php echo htmlspecialchars($trip['departure_city']); ?></strong> - Varış: <strong><?php echo htmlspecialchars($trip['destination_city']); ?></strong><br>
                                Tarih: <?php echo htmlspecialchars(date('d M Y, H:i', strtotime($trip['departure_time']))); ?>
                            </div>
                            <div class="trip-price">
                                Fiyat: <strong><?php echo htmlspecialchars($trip['price']); ?> TL</strong>
                            </div>
                            <div class="trip-action">
                                <a href="bilet_al.php?trip_id=<?php echo $trip['id']; ?>">Bilet Al</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Bu kriterlere uygun sefer bulunamadı.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Lütfen kalkış ve varış şehirlerini seçerek arama yapın.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>