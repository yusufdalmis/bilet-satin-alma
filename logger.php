<?php
function log_message($message) {
    $timestamp = date("Y-m-d H:i:s");
    // __DIR__ dosyanın bulunduğu dizini verir, bu sayede yol sorunu yaşamayız.
    $log_file = __DIR__ . '/app.log';
    $log_entry = "[$timestamp] " . $message . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}
?>