<?php
// Veritabanı bağlantı bilgilerini ve ayarlarını içerir.

$db_dir = __DIR__ . '/database';
$db_file = $db_dir . '/bilet_platformu.sqlite';

try {
    // PDO (PHP Data Objects) ile veritabanı bağlantısı oluşturulur.
    $pdo = new PDO('sqlite:' . $db_file);

    // Hata modunu istisna (exception) olarak ayarla. Bu, hataları yakalamayı kolaylaştırır.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // fetch() fonksiyonunun varsayılan olarak associatve array (sütun adlarıyla) getirmesini sağlar.
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Bağlantı başarısız olursa, programı durdur ve hata mesajı göster.
    die("Veritabanı bağlantısı kurulamadı: " . $e->getMessage());
}
?>