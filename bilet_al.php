<?php
session_start();
require_once 'db.php';

// 1. ADIM: YETKİ KONTROLÜ
// Kullanıcı giriş yapmamışsa, onu giriş sayfasına yönlendir.
if (!isset($_SESSION['user_id'])) {
    // 
    header("Location: giris_yap.php?error=login_required");
    exit;
}

// URL'den sefer ID'sini al. Eğer yoksa veya geçersizse ana sayfaya yönlendir.
if (!isset($_GET['trip_id']) || !is_numeric($_GET['trip_id'])) {
    header("Location: index.php");
    exit;
}

$trip_id = $_GET['trip_id'];
$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// 2. ADIM: SEFER BİLGİLERİNİ VE DOLU KOLTUKLARI ÇEKME
try {
    // Sefer bilgilerini çek
    $stmt = $pdo->prepare("SELECT T.*, B.name as company_name FROM Trips T JOIN Bus_Company B ON T.company_id = B.id WHERE T.id = ?");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch();

    if (!$trip) {
        // Sefer bulunamazsa ana sayfaya yönlendir.
        header("Location: index.php");
        exit;
    }

    // Bu sefere ait dolu koltuk numaralarını çek
    $stmt = $pdo->prepare("SELECT BS.seat_number FROM Booked_Seats BS JOIN Tickets T ON BS.ticket_id = T.id WHERE T.trip_id = ?");
    $stmt->execute([$trip_id]);
    $booked_seats_raw = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $booked_seats = array_map('intval', $booked_seats_raw); // Gelen değerleri integer yap.

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// 3. ADIM: SATIN ALMA FORMUNU İŞLEME (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['seats'])) {
        $error_message = "Lütfen en az bir koltuk seçin.";
    } else {
        $selected_seats = $_POST['seats'];
        $coupon_code = trim($_POST['coupon_code']);
        $total_price = count($selected_seats) * $trip['price'];
        $final_price = $total_price;
        
        // Kupon kontrolü
        if (!empty($coupon_code)) {
            $stmt = $pdo->prepare("SELECT * FROM Coupons WHERE code = ? AND expire_date > date('now') AND usage_limit > 0");
            $stmt->execute([$coupon_code]);
            $coupon = $stmt->fetch();
            if ($coupon) {
                // İndirimi uygula (örneğin %10 indirim)
                $final_price = $total_price - ($total_price * ($coupon['discount'] / 100.0));
            } else {
                $error_message = "Geçersiz veya süresi dolmuş kupon kodu.";
            }
        }

        // Bakiye ve diğer kontroller başarılıysa işleme devam et
        if (empty($error_message)) {
            if ($_SESSION['user_balance'] < $final_price) {
                $error_message = "Yetersiz bakiye! Mevcut Bakiyeniz: " . $_SESSION['user_balance'] . " TL";
            } else {
                // VERİTABANI TRANSACTION: Tüm işlemlerin başarılı olmasını garanti eder.
                try {
                    $pdo->beginTransaction();

                    // Adım 1: Yeni bir bilet oluştur
                    $stmt = $pdo->prepare("INSERT INTO Tickets (trip_id, user_id, total_price, status) VALUES (?, ?, ?, 'active')");
                    $stmt->execute([$trip_id, $user_id, $final_price]);
                    $ticket_id = $pdo->lastInsertId();

                    // Adım 2: Seçilen koltukları kaydet
                    $stmt_seat = $pdo->prepare("INSERT INTO Booked_Seats (ticket_id, seat_number) VALUES (?, ?)");
                    foreach ($selected_seats as $seat) {
                        $stmt_seat->execute([$ticket_id, $seat]);
                    }

                    // Adım 3: Kullanıcının bakiyesini güncelle
                    $new_balance = $_SESSION['user_balance'] - $final_price;
                    $stmt = $pdo->prepare("UPDATE User SET balance = ? WHERE id = ?");
                    $stmt->execute([$new_balance, $user_id]);

                    // Eğer kupon kullanıldıysa, limitini düşür (veya User_Coupons'a ekle)
                    if (isset($coupon) && $coupon) {
                        $stmt = $pdo->prepare("UPDATE Coupons SET usage_limit = usage_limit - 1 WHERE id = ?");
                        $stmt->execute([$coupon['id']]);
                    }
                    
                    $pdo->commit(); // Tüm işlemler başarılı, değişiklikleri onayla.

                    $_SESSION['user_balance'] = $new_balance; // Session'daki bakiyeyi de güncelle.
                    $success_message = "Biletiniz başarıyla satın alındı! Biletlerim sayfasına yönlendiriliyorsunuz...";
                    header("Refresh:3; url=biletlerim.php");

                } catch (Exception $e) {
                    $pdo->rollBack(); // Hata oluştu, tüm değişiklikleri geri al.
                    $error_message = "Satın alma sırasında bir hata oluştu: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet Satın Al</title>
     <style>
        body { font-family: sans-serif; color: #333; }
        .container { max-width: 960px; margin: 20px auto; padding: 0 20px; }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        nav a { margin-left: 15px; text-decoration: none; color: #007bff; }
        .seat-map { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; max-width: 250px; margin: 20px 0; }
        .seat { border: 1px solid #ccc; padding: 10px; text-align: center; cursor: pointer; border-radius: 5px; }
        .seat.booked { background-color: #dc3545; color: white; cursor: not-allowed; }
        .seat.selected { background-color: #28a745; color: white; }
        .seat input { display: none; }
        .info { background-color: #f4f4f4; padding: 15px; border-radius: 5px; }
        .error { color: #dc3545; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;}
        .success { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;}
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1><a href="index.php" style="text-decoration: none; color: inherit;">Bilet Platformu</a></h1>
        <nav>
            <span>Bakiye: <strong><?php echo htmlspecialchars($_SESSION['user_balance']); ?> TL</strong></span>
            <a href="biletlerim.php">Biletlerim</a>
            <a href="cikis_yap.php">Çıkış Yap</a>
        </nav>
    </header>

    <main>
        <h2>Bilet Satın Alma</h2>
        <div class="info">
            <h3>Sefer Bilgileri</h3>
            <p><strong>Firma:</strong> <?php echo htmlspecialchars($trip['company_name']); ?></p>
            <p><strong>Güzergah:</strong> <?php echo htmlspecialchars($trip['departure_city']); ?> -> <?php echo htmlspecialchars($trip['destination_city']); ?></p>
            <p><strong>Kalkış Zamanı:</strong> <?php echo htmlspecialchars(date('d M Y, H:i', strtotime($trip['departure_time']))); ?></p>
            <p><strong>Tek Koltuk Fiyatı:</strong> <?php echo htmlspecialchars($trip['price']); ?> TL</p>
        </div>

        <?php if ($success_message): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php else: ?>
            <?php if ($error_message): ?>
                <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <form action="bilet_al.php?trip_id=<?php echo $trip_id; ?>" method="POST">
                <h3>Koltuk Seçimi</h3>
                <div class="seat-map">
                    <?php for ($i = 1; $i <= $trip['capacity']; $i++): ?>
                        <?php $is_booked = in_array($i, $booked_seats); ?>
                        <label class="seat <?php if ($is_booked) echo 'booked'; ?>" id="seat-label-<?php echo $i; ?>">
                            <?php echo $i; ?>
                            <input type="checkbox" name="seats[]" value="<?php echo $i; ?>" <?php if ($is_booked) echo 'disabled'; ?> onchange="toggleSeatColor(this)">
                        </label>
                    <?php endfor; ?>
                </div>

                <h3>Ödeme</h3>
                <input type="text" name="coupon_code" placeholder="İndirim Kodu (varsa)">
                <button type="submit">Satın Al</button>
            </form>
        <?php endif; ?>
    </main>
</div>

<script>
    // Koltuk seçildiğinde rengini değiştiren basit bir javascript kodu
    function toggleSeatColor(checkbox) {
        const label = document.getElementById('seat-label-' + checkbox.value);
        if (checkbox.checked) {
            label.classList.add('selected');
        } else {
            label.classList.remove('selected');
        }
    }
</script>

</body>
</html>