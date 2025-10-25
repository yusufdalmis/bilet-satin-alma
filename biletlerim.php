<?php
session_start();
require_once 'db.php';

// Kullanıcı giriş yapmamışsa, onu giriş sayfasına yönlendir.
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Bilet İptal Etme Mantığı (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_ticket_id'])) {
    $ticket_id_to_cancel = $_POST['cancel_ticket_id'];

    try {
        // İptal edilecek biletin ve seferin bilgilerini çek.
        // GÜVENLİK: Biletin mevcut kullanıcıya ait olup olmadığını sorguda kontrol et!
        $stmt = $pdo->prepare("
            SELECT T.*, TR.departure_time 
            FROM Tickets T 
            JOIN Trips TR ON T.trip_id = TR.id 
            WHERE T.id = ? AND T.user_id = ? AND T.status = 'active'
        ");
        $stmt->execute([$ticket_id_to_cancel, $user_id]);
        $ticket = $stmt->fetch();

        if ($ticket) {
            $departure_time = new DateTime($ticket['departure_time']);
            $current_time = new DateTime();
            $interval = $current_time->diff($departure_time);
            $hours_until_departure = ($interval->days * 24) + $interval->h;

            // Kalkışa 1 saatten fazla varsa iptal et.
            if ($interval->invert == 0 && $hours_until_departure >= 1) {
                $pdo->beginTransaction();

                // 1. Biletin durumunu 'canceled' olarak güncelle.
                $stmt_cancel = $pdo->prepare("UPDATE Tickets SET status = 'canceled' WHERE id = ?");
                $stmt_cancel->execute([$ticket_id_to_cancel]);

                // 2. Bilet ücretini kullanıcının bakiyesine iade et.
                $refund_amount = $ticket['total_price'];
                $stmt_refund = $pdo->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
                $stmt_refund->execute([$refund_amount, $user_id]);

                $pdo->commit();

                // Session'daki bakiyeyi de güncelle.
                $_SESSION['user_balance'] += $refund_amount;
                
                $message = "Biletiniz başarıyla iptal edildi ve ücret iadesi yapıldı.";
                $message_type = 'success';
            } else {
                $message = "Kalkış saatine 1 saatten az kaldığı için bilet iptal edilemez.";
                $message_type = 'error';
            }
        } else {
            $message = "İptal edilecek geçerli bir bilet bulunamadı.";
            $message_type = 'error';
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = "İptal işlemi sırasında bir hata oluştu: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Kullanıcının tüm biletlerini çekme
// GROUP_CONCAT ile her bilete ait koltuk numaralarını tek bir satırda birleştiriyoruz.
$stmt = $pdo->prepare("
    SELECT 
        T.*, 
        TR.departure_city, 
        TR.destination_city, 
        TR.departure_time, 
        BC.name as company_name,
        GROUP_CONCAT(BS.seat_number) as seat_numbers
    FROM Tickets T
    JOIN Trips TR ON T.trip_id = TR.id
    JOIN Bus_Company BC ON TR.company_id = BC.id
    LEFT JOIN Booked_Seats BS ON BS.ticket_id = T.id
    WHERE T.user_id = ?
    GROUP BY T.id
    ORDER BY TR.departure_time DESC
");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Biletlerim</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .container { max-width: 960px; margin: 20px auto; padding: 0 20px; }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        nav a { margin-left: 15px; text-decoration: none; color: #007bff; }
        .ticket { border: 1px solid #ddd; padding: 15px; margin-top: 15px; border-radius: 5px; }
        .ticket-header { display: flex; justify-content: space-between; align-items: center; }
        .ticket-details { margin-top: 10px; }
        .ticket-actions { margin-top: 10px; }
        .ticket-actions button, .ticket-actions a { padding: 8px 12px; border: none; border-radius: 5px; text-decoration: none; cursor: pointer; }
        .btn-cancel { background-color: #dc3545; color: white; }
        .btn-pdf { background-color: #17a2b8; color: white; }
        .status-active { color: #28a745; }
        .status-canceled { color: #6c757d; }
        .message { padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        .message.success { color: #155724; background-color: #d4edda; }
        .message.error { color: #721c24; background-color: #f8d7da; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1><a href="index.php" style="text-decoration: none; color: inherit;">Bilet Platformu</a></h1>
        <nav>
            <span>Bakiye: <strong><?php echo htmlspecialchars($_SESSION['user_balance'] ?? '0'); ?> TL</strong></span>
            <a href="index.php">Ana Sayfa</a>
            <a href="auth.php?action=logout">Çıkış Yap</a>
        </nav>
    </header>

    <main>
        <h2>Biletlerim</h2>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (count($tickets) > 0): ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket">
                    <div class="ticket-header">
                        <h3><?php echo htmlspecialchars($ticket['departure_city']); ?> &rarr; <?php echo htmlspecialchars($ticket['destination_city']); ?></h3>
                        <span class="status-<?php echo htmlspecialchars($ticket['status']); ?>">
                            <strong><?php echo ucfirst($ticket['status']); ?></strong>
                        </span>
                    </div>
                    <div class="ticket-details">
                        <p><strong>Firma:</strong> <?php echo htmlspecialchars($ticket['company_name']); ?></p>
                        <p><strong>Kalkış Zamanı:</strong> <?php echo htmlspecialchars(date('d M Y, H:i', strtotime($ticket['departure_time']))); ?></p>
                        <p><strong>Koltuk No:</strong> <?php echo htmlspecialchars($ticket['seat_numbers']); ?></p>
                        <p><strong>Ödenen Tutar:</strong> <?php echo htmlspecialchars($ticket['total_price']); ?> TL</p>
                    </div>
                    <div class="ticket-actions">
                        <?php
                        $is_cancellable = false;
                        if ($ticket['status'] == 'active') {
                            $departure_time = new DateTime($ticket['departure_time']);
                            $current_time = new DateTime();
                            $interval = $current_time->diff($departure_time);
                            $hours_until_departure = ($interval->days * 24) + $interval->h;
                            if ($interval->invert == 0 && $hours_until_departure >= 1) {
                                $is_cancellable = true;
                            }
                        }
                        ?>
                        <?php if ($is_cancellable): ?>
                            <form action="biletlerim.php" method="POST" style="display: inline;">
                                <input type="hidden" name="cancel_ticket_id" value="<?php echo $ticket['id']; ?>">
                                <button type="submit" class="btn-cancel" onclick="return confirm('Bu bileti iptal etmek istediğinizden emin misiniz?');">İptal Et</button>
                            </form>
                        <?php endif; ?>
                        
                        <a href="bilet_pdf.php?ticket_id=<?php echo $ticket['id']; ?>" target="_blank" class="btn-pdf">PDF İndir</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Henüz satın alınmış biletiniz bulunmamaktadır.</p>
        <?php endif; ?>
    </main>
</div>
</body>
</html>