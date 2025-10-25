<?php
// Geliştirme sırasında hataları görmek için
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Veritabanı klasörünün var olduğundan emin ol
    $db_dir = __DIR__ . '/database';
    if (!is_dir($db_dir)) {
        mkdir($db_dir, 0775, true);
    }

    // Mutlak dosya yolu ile veritabanına bağlan
    $pdo = new PDO('sqlite:' . $db_dir . '/bilet_platformu.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Veritabanı bağlantısı başarılı.<br><hr>";

    // --- YENİ VE NET ŞEMAYA GÖRE TABLO OLUŞTURMA ---

    // Tablo: Bus_Company (Otobüs Firmaları)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Bus_Company (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        logo_path TEXT,
        created_at DATETIME DEFAULT (datetime('now','localtime'))
    )");
    echo "'Bus_Company' tablosu başarıyla oluşturuldu/kontrol edildi.<br>";

    // Tablo: User (Kullanıcılar)
    $pdo->exec("CREATE TABLE IF NOT EXISTS User (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        role TEXT NOT NULL CHECK(role IN ('user', 'company_admin', 'admin')) DEFAULT 'user',
        password TEXT NOT NULL,
        company_id INTEGER, -- 'company_admin' rolü için.
        balance REAL NOT NULL DEFAULT 800.0,
        created_at DATETIME DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (company_id) REFERENCES Bus_Company (id) ON DELETE SET NULL
    )");
    echo "'User' tablosu başarıyla oluşturuldu/kontrol edildi.<br>";

    // Tablo: Trips (Seferler)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Trips (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_id INTEGER NOT NULL,
        destination_city TEXT NOT NULL,
        arrival_time DATETIME NOT NULL,
        departure_time DATETIME NOT NULL,
        departure_city TEXT NOT NULL,
        price REAL NOT NULL,
        capacity INTEGER NOT NULL,
        created_at DATETIME DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (company_id) REFERENCES Bus_Company (id) ON DELETE CASCADE
    )");
    echo "'Trips' tablosu başarıyla oluşturuldu/kontrol edildi.<br>";

    // Tablo: Tickets (Biletler)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Tickets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        trip_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        status TEXT NOT NULL CHECK(status IN ('active', 'canceled', 'expired')) DEFAULT 'active',
        total_price REAL NOT NULL,
        created_at DATETIME DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (trip_id) REFERENCES Trips (id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE
    )");
    echo "'Tickets' tablosu başarıyla oluşturuldu/kontrol edildi.<br>";

    // Tablo: Booked_Seats (Satın Alınan Koltuklar)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Booked_Seats (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ticket_id INTEGER NOT NULL,
        seat_number INTEGER NOT NULL,
        created_at DATETIME DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (ticket_id) REFERENCES Tickets (id) ON DELETE CASCADE,
        UNIQUE(ticket_id, seat_number) -- Bir bilette aynı koltuk no olamaz.
    )");
    // Not: Bir seferdeki koltuğun tekrar satılmasını engellemek için kod tarafında ayrıca kontrol gerekir.
    echo "'Booked_Seats' tablosu başarıyla oluşturuldu/kontrol edildi.<br>";

    // Tablo: Coupons (Kuponlar)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Coupons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE NOT NULL,
        discount REAL NOT NULL,
        usage_limit INTEGER NOT NULL,
        expire_date DATETIME NOT NULL,
        created_at DATETIME DEFAULT (datetime('now','localtime'))
    )");
    echo "'Coupons' tablosu başarıyla oluşturuldu/kontrol edildi.<br>";

    // Tablo: User_Coupons (Kullanıcıların kullandığı kuponları takip etmek için)
    $pdo->exec("CREATE TABLE IF NOT EXISTS User_Coupons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        coupon_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (coupon_id) REFERENCES Coupons (id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE
    )");
    echo "'User_Coupons' tablosu başarıyla oluşturuldu/kontrol edildi.<br>";

    echo "<hr>Tüm işlemler tamamlandı. Veritabanı şeması, sağladığınız net görsele göre oluşturuldu.";

} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
}
?>