# Resmi PHP 8.2 ve Apache sunucusu imajını temel alarak başlıyoruz.
FROM php:8.2-apache

# --- YENİ EKLENEN ADIM ---
# Önce sistemin paket listesini güncelliyor, ardından pdo_sqlite eklentisinin
# ihtiyaç duyduğu temel 'libsqlite3-dev' kütüphanesini kuruyoruz.
RUN apt-get update && apt-get install -y libsqlite3-dev

# Projenin çalışması için gereken SQLite PHP eklentilerini kuruyoruz.
# Bu komut artık başarılı olacaktır çünkü bağımlılığı bir üst adımda kurduk.
RUN docker-php-ext-install pdo pdo_sqlite

# Proje dosyalarımızı (mevcut dizindeki her şeyi)
# konteynerin içindeki Apache sunucusunun web kök dizinine kopyalıyoruz.
COPY . /var/www/html/

# (İsteğe bağlı) Veritabanı dosyasına yazma izni verelim.
RUN chown www-data:www-data /var/www/html/database/bilet_platformu.sqlite && \
    chmod 664 /var/www/html/database/bilet_platformu.sqlite