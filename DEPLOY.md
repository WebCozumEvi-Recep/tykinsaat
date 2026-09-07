# tykinsaat.com.tr — HestiaCP kurulum ve güncelleme

Sunucu nginx + php-fpm (Hestia varsayılanı). Uygulama düz PHP 8 + MySQL, build adımı yok.

## 0. Ön koşullar
- DNS: `tykinsaat.com.tr` ve `www` A kaydı sunucunun IP'sine bakmalı (SSL için şart).
- PHP 8.1+ (`pdo_mysql`, `mbstring`, `fileinfo` eklentileri), MySQL/MariaDB.

## 1. Web domain (Hestia paneli)
`WEB > Add Web Domain`
- Domain: `tykinsaat.com.tr`, "Alias: www" işaretli
- Backend Template: **PHP-8.2** (veya 8.3)
- Kaydettikten sonra domaini düzenle:
  - `SSL Support` ✔ + `Lets Encrypt Support` ✔ (DNS yayılmadan sertifika alınmaz)
  - `Force SSL` ✔

## 2. Veritabanı
`DB > Add Database`
- Database: `tyk`, User: `tyk`, güçlü şifre (Hestia `kullanici_tyk` ön ekini ekler)
- Charset: **utf8mb4**

Şemayı yükle (phpMyAdmin > Import ya da SSH):
```
mysql -u KULLANICI_tyk -p KULLANICI_tyk < schema.sql
```
Demo veri istemiyorsan `seed_demo.sql` ve `seed_puantaj.sql` dosyalarını YÜKLEME.

## 3. Dosyalar — GitHub üzerinden (önerilen)

Depo: https://github.com/WebCozumEvi-Recep/tykinsaat (herkese açık, sır içermez)

Sunucuda **bir kez** kurulum (SSH ile, domain kullanıcısı olarak):
```
cd /home/<kullanici>/web/tykinsaat.com.tr
git clone https://github.com/WebCozumEvi-Recep/tykinsaat.git app
cd app && ./deploy.sh
```
Dizin düzeni şöyle olur:
```
web/tykinsaat.com.tr/
├── app/              → git deposu (web'den erişilemez)
├── tyk-config.php    → sırlar (web'den erişilemez)
└── public_html/      → yayınlanan dosyalar (deploy.sh buraya kopyalar)
```

Sonraki her güncelleme tek komut:
```
cd /home/<kullanici>/web/tykinsaat.com.tr/app && ./deploy.sh
```
`deploy.sh` depoyu çeker ve `public_html`'e rsync'ler; `*.sql`, `config*.php`, `.git` ve
`uploads/` hariç tutulur — yani veritabanı dosyaları ve sırlar web'e asla çıkmaz,
yüklenen fotoğraflar güncellemede silinmez.

### 3b. Dosyalar — elle (SSH yoksa)
`/home/<kullanici>/web/tykinsaat.com.tr/public_html/` içine yükle.

**Yüklenmeyecekler** (nginx'te .htaccess çalışmaz, bunlar herkese açık olur):
`schema.sql`, `seed_*.sql`, `migrate_*.sql`, `config.php`, `config.ornek.php`, `*.bak`, `DEPLOY.md`, `.claude/`

rsync ile:
```
rsync -av --delete \
  --exclude='*.sql' --exclude='config*.php' --exclude='*.bak' \
  --exclude='.claude' --exclude='DEPLOY.md' --exclude='uploads' \
  ./ kullanici@sunucu:/home/kullanici/web/tykinsaat.com.tr/public_html/
```

## 4. Yapılandırma (webroot DIŞINDA)
`config.ornek.php` dosyasını sunucuda şuraya kopyala:
```
/home/<kullanici>/web/tykinsaat.com.tr/tyk-config.php
```
`public_html`'in bir üstü — tarayıcıdan erişilemez. `inc/db.php` önce bu dosyayı arar,
bulamazsa proje içindeki `config.php`'ye düşer (yerel geliştirme için).

İçini doldur: DB adı/kullanıcı/şifre, `APP_NAME`, ve
`UPLOAD_DIR` = `__DIR__ . '/public_html/uploads/'`.

## 5. uploads izni
```
mkdir -p /home/<kullanici>/web/tykinsaat.com.tr/public_html/uploads
chown -R <kullanici>:<kullanici> /home/<kullanici>/web/tykinsaat.com.tr/public_html/uploads
chmod 775 /home/<kullanici>/web/tykinsaat.com.tr/public_html/uploads
```
Saha fotoğrafları için PHP limitleri (Hestia > Server > Configure > PHP, ya da domain PHP ayarları):
`upload_max_filesize = 16M`, `post_max_size = 32M`, `max_file_uploads = 20`.

## 6. İlk giriş ve güvenlik
- `https://tykinsaat.com.tr` > `admin` / `admin123`
- **Hemen** Ayarlar > Kullanıcılar'dan admin şifresini değiştir; gerekiyorsa kullanıcı adını da.
- Demo kullanıcılar (`saha`, `muhasebe`) canlıda gereksizse pasife al.
- Hestia'da `Server > Firewall` ve `fail2ban` açık kalsın.

## 7. Güncelleme akışı
Geliştiricide: `git push` → sunucuda: `cd .../app && ./deploy.sh`
SSH yoksa: yeni zip'i `public_html` içinde açmak.
`tyk-config.php` ve `uploads/` webroot dışında / hariç tutulduğu için güncellemede ezilmez.
Şema değişikliği varsa önce yedek:
```
mysqldump -u KULLANICI_tyk -p KULLANICI_tyk > yedek_$(date +%F).sql
```

## 8. Yedekleme
Hestia > `USER > Backup` günlük otomatik yedek alır (dosya + veritabanı).
Kritik veri olduğu için ayrıca haftalık `mysqldump` çıktısını sunucu dışına kopyalamanı öneririm.
