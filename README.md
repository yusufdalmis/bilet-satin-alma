
# Bilet Satın Alma Platformu

Bu proje, modern web teknolojileri kullanılarak geliştirilmiş, çok kullanıcılı (Admin, Firma Yetkilisi, Yolcu) bir otobüs bileti satış platformudur. Proje, PHP dilinde yetkinlik kazanma, SQLite veritabanı yönetimi, rol tabanlı yetkilendirme sistemleri ve temel web güvenlik prensiplerini uygulama amacıyla geliştirilmiştir.

Proje, Docker ile paketlenmiş olup, herhangi bir ortamda tek komutla çalıştırılmaya hazırdır.

## Özellikler

### 👤 Yolcu (User) Özellikleri

  - Sisteme kayıt olma ve giriş yapma.
  - Kalkış ve varış noktasına göre sefer arama ve listeleme.
  - Sefer detaylarını ve koltuk planını görüntüleme.
  - Sanal bakiye kullanarak bilet satın alma.
  - İndirim kuponu kullanabilme.
  - Satın alınmış biletleri listeleme.
  - Kalkış saatine 1 saatten fazla süre varsa bileti iptal etme ve ücret iadesi alma.
  - Satın alınmış biletleri PDF formatında indirme.

### 🏢 Firma Yetkilisi (Company Admin) Özellikleri

  - Sadece kendi firmasına ait seferleri yönetme (CRUD - Ekleme, Görüntüleme, Güncelleme, Silme).
  - Yeni seferler (güzergah, tarih, saat, fiyat, kapasite vb.) oluşturma.
  - Mevcut seferlerin bilgilerini düzenleme.

### ⚙️ Sistem Yöneticisi (Admin) Özellikleri

  - Sistemdeki tüm otobüs firmalarını yönetme (CRUD).
  - Yeni "Firma Yetkilisi" kullanıcıları oluşturma ve bu kullanıcıları belirli bir firmaya atama.
  - İndirim kuponları (kod, oran, limit, son kullanma tarihi) oluşturma ve yönetme.

## 🛠️ Kullanılan Teknolojiler

  - **Backend:** PHP 8.2
  - **Veritabanı:** SQLite
  - **Web Sunucusu:** Apache
  - **Paketleme ve Dağıtım:** Docker

## 🚀 Kurulum ve Çalıştırma (Docker ile)

Projeyi yerel makinenizde çalıştırmak için tek ihtiyacınız olan **Docker Desktop**'ın kurulu olmasıdır.

1.  **Projeyi Klonlayın:**

    ```bash
    git clone https://github.com/yusufdalmis/bilet-satin-alma.git
    ```

2.  **Proje Dizinine Gidin:**

    ```bash
    cd bilet-satin-alma
    ```

3.  **Docker İmajını Build Edin (İnşa Edin):**
    Bu komut, `Dockerfile` dosyasını okuyarak projenin çalışması için gereken tüm ortamı (PHP, Apache, eklentiler) içeren bir imaj oluşturur.

    ```bash
    docker build -t bilet-satin-alma .
    ```

4.  **Docker Konteynerini Çalıştırın:**
    Bu komut, oluşturulan imajdan bir konteyner başlatır ve uygulamanızı çalışır hale getirir.

    ```bash
    docker run -d -p 8080:80 --name bilet-uygulamasi bilet-satin-alma
    ```

      - `-p 8080:80` komutu, bilgisayarınızın `8080` portunu konteynerin `80` portuna bağlar.

5.  **Uygulamaya Erişin:**
    Web tarayıcınızı açın ve `http://localhost:8080` adresine gidin.

## 🔑 Varsayılan Giriş Bilgileri

Sistemi test etmek için aşağıdaki varsayılan kullanıcıları kullanabilirsiniz.

| Rol                          | E-posta          | Şifre |
| ---------------------------- | ---------------- | ----- |
| Sistem Yöneticisi (Admin)    | `admin@test.com` | `123`   |
| Firma Yetkilisi (Company Admin) | `firma@test.com` | `123`   |
| Yolcu (User)                 | Kayıt ol sayfasından yeni bir hesap oluşturulabilir. | -     |

-----
