<?php
session_start();
require_once 'db.php';
require_once 'fpdf.php'; // FPDF kütüphanesini dahil et

// 1. ADIM: YETKİ VE GÜVENLİK KONTROLÜ
if (!isset($_SESSION['user_id'])) {
    die("Bu sayfaya erişim yetkiniz yok.");
}
if (!isset($_GET['ticket_id']) || !is_numeric($_GET['ticket_id'])) {
    die("Geçersiz bilet ID'si.");
}

$ticket_id = $_GET['ticket_id'];
$user_id = $_SESSION['user_id'];

// GÜVENLİK: İndirilmek istenen biletin, giriş yapmış olan kullanıcıya ait olup olmadığını kontrol et.
$stmt = $pdo->prepare("
    SELECT 
        T.*, 
        U.full_name,
        TR.departure_city, 
        TR.destination_city, 
        TR.departure_time, 
        BC.name as company_name,
        GROUP_CONCAT(BS.seat_number) as seat_numbers
    FROM Tickets T
    JOIN User U ON T.user_id = U.id
    JOIN Trips TR ON T.trip_id = TR.id
    JOIN Bus_Company BC ON TR.company_id = BC.id
    LEFT JOIN Booked_Seats BS ON BS.ticket_id = T.id
    WHERE T.id = ? AND T.user_id = ?
    GROUP BY T.id
");
$stmt->execute([$ticket_id, $user_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Bilet bulunamadı veya bu bileti görüntüleme yetkiniz yok.");
}


// 2. ADIM: PDF OLUŞTURMA

class PDF extends FPDF
{
    // Sayfa başlığı
    function Header()
    {
        // Logo veya Başlık
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-9', 'Elektronik Otobüs Bileti'), 0, 1, 'C');
        $this->Ln(10); // Boşluk
    }

    // Sayfa alt bilgisi
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-9', 'İyi yolculuklar dileriz!'), 0, 0, 'C');
    }

    // Bilet bilgi tablosu
    function TicketInfo($label, $value)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(50, 10, iconv('UTF-8', 'ISO-8859-9', $label), 0, 0);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-9', $value), 0, 1);
    }
}

// PDF objesini oluştur
$pdf = new PDF();
$pdf->AddPage();

// Bilet bilgilerini PDF'e yazdır
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-9', $ticket['company_name']), 0, 1, 'C');
$pdf->Ln(5);

$pdf->TicketInfo('Yolcu Adı Soyadı:', $ticket['full_name']);
$pdf->TicketInfo('Güzergah:', $ticket['departure_city'] . ' -> ' . $ticket['destination_city']);
$pdf->TicketInfo('Kalkış Zamanı:', date('d.m.Y H:i', strtotime($ticket['departure_time'])));
$pdf->TicketInfo('Koltuk Numarası:', $ticket['seat_numbers']);
$pdf->TicketInfo('Ödenen Tutar:', $ticket['total_price'] . ' TL');
$pdf->TicketInfo('Bilet Numarası:', 'TICKET-00' . $ticket['id']);
$pdf->TicketInfo('Durum:', ucfirst($ticket['status']));

// Çıktıyı oluştur
// 'D' parametresi, PDF'in doğrudan indirilmesini sağlar.
$pdf->Output('D', 'Bilet-'.$ticket['id'].'.pdf');
?>