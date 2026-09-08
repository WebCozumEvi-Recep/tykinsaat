-- ŞantiyeTakip veritabanı şeması (MySQL 5.7+/8, utf8mb4)
-- Not: veritabanı önceden oluşturulmuş olmalı; bu dosya SEÇİLİ veritabanına yüklenir.
--   Yerel:   mysql -u kullanici -p veritabani < schema.sql
--   Hestia:  Hestia'da DB'yi oluştur, sonra bu dosyayı ona yükle.

CREATE TABLE firma (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ad VARCHAR(150) NOT NULL DEFAULT 'Firma Adı',
  vergi_no VARCHAR(30), adres TEXT, telefon VARCHAR(30), logo VARCHAR(255)
) ENGINE=InnoDB;
INSERT INTO firma (id, ad) VALUES (1, 'İnşaat Firması');

CREATE TABLE kullanicilar (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kullanici_adi VARCHAR(60) NOT NULL UNIQUE,
  sifre_hash VARCHAR(255) NOT NULL,
  ad_soyad VARCHAR(120) NOT NULL,
  rol ENUM('patron','santiye','muhasebe') NOT NULL DEFAULT 'santiye',
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  remember_token VARCHAR(64) NULL,
  olusturma DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- varsayılan giriş: admin / admin123
INSERT INTO kullanicilar (kullanici_adi, sifre_hash, ad_soyad, rol) VALUES
('admin', '$2y$12$DJCmPPixAx/2ZCoKV4TfDe1N6wYNP/UomGjKRDH39ulngLksT8B1i', 'Yönetici', 'patron');

CREATE TABLE musteriler (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ad VARCHAR(150) NOT NULL,
  yetkili VARCHAR(120), telefon VARCHAR(30), email VARCHAR(120),
  vergi_dairesi VARCHAR(80), vergi_no VARCHAR(30),
  adres TEXT, notlar TEXT,
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  olusturma DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ad (ad)
) ENGINE=InnoDB;

CREATE TABLE santiyeler (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ad VARCHAR(150) NOT NULL,
  musteri_id INT NULL,
  adres TEXT,
  anlasma_tutari DECIMAL(14,2) NOT NULL DEFAULT 0,
  baslangic DATE, bitis DATE,
  durum ENUM('aktif','tamamlandi','beklemede') NOT NULL DEFAULT 'aktif',
  notlar TEXT,
  olusturma DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (musteri_id) REFERENCES musteriler(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE kullanici_santiye (
  kullanici_id INT NOT NULL, santiye_id INT NOT NULL,
  PRIMARY KEY (kullanici_id, santiye_id),
  FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
  FOREIGN KEY (santiye_id) REFERENCES santiyeler(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE personel (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ad_soyad VARCHAR(120) NOT NULL,
  gorev ENUM('usta','isci','operator','sofor','diger') NOT NULL DEFAULT 'isci',
  yevmiye DECIMAL(10,2) NOT NULL DEFAULT 0,
  telefon VARCHAR(30),
  aktif TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE puantaj (
  id INT PRIMARY KEY AUTO_INCREMENT,
  santiye_id INT NOT NULL, personel_id INT NOT NULL, tarih DATE NOT NULL,
  durum ENUM('tam','yarim','yok') NOT NULL DEFAULT 'yok',
  mesai_saat DECIMAL(4,1) NOT NULL DEFAULT 0,
  yevmiye DECIMAL(10,2) NOT NULL DEFAULT 0, -- o günkü yevmiye (sabitlenir)
  kaydeden INT,
  UNIQUE KEY uq (personel_id, tarih),
  FOREIGN KEY (santiye_id) REFERENCES santiyeler(id) ON DELETE CASCADE,
  FOREIGN KEY (personel_id) REFERENCES personel(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE personel_odeme (
  id INT PRIMARY KEY AUTO_INCREMENT,
  personel_id INT NOT NULL, tarih DATE NOT NULL, tutar DECIMAL(12,2) NOT NULL, aciklama VARCHAR(255),
  FOREIGN KEY (personel_id) REFERENCES personel(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tedarikciler (
  id INT PRIMARY KEY AUTO_INCREMENT,
  firma VARCHAR(150) NOT NULL,
  kategori VARCHAR(60),
  vergi_no VARCHAR(30), yetkili VARCHAR(120), telefon VARCHAR(30), notlar TEXT
) ENGINE=InnoDB;

CREATE TABLE gider_kategorileri (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ad VARCHAR(60) NOT NULL, ikon VARCHAR(10) NOT NULL DEFAULT '📦', sira INT DEFAULT 0
) ENGINE=InnoDB;
INSERT INTO gider_kategorileri (ad, ikon, sira) VALUES
('Beton','🧱',1),('Demir','🔩',2),('Akaryakıt','⛽',3),('Kiralama','🚜',4),('Nakliye','🚚',5),('Yemek','🍲',6),('Diğer','📦',9);

CREATE TABLE giderler (
  id INT PRIMARY KEY AUTO_INCREMENT,
  santiye_id INT NOT NULL, tarih DATE NOT NULL,
  kategori_id INT, tedarikci_id INT NULL,
  aciklama VARCHAR(255), tutar DECIMAL(14,2) NOT NULL,
  fis_no VARCHAR(60),
  odeme_durumu ENUM('odendi','borc') NOT NULL DEFAULT 'borc',
  fotolar TEXT, -- JSON dizi
  kaydeden INT, olusturma DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (santiye_id) REFERENCES santiyeler(id) ON DELETE CASCADE,
  FOREIGN KEY (kategori_id) REFERENCES gider_kategorileri(id) ON DELETE SET NULL,
  FOREIGN KEY (tedarikci_id) REFERENCES tedarikciler(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tedarikci_odemeleri (
  id INT PRIMARY KEY AUTO_INCREMENT,
  tedarikci_id INT NOT NULL, santiye_id INT NULL, tarih DATE NOT NULL,
  tutar DECIMAL(14,2) NOT NULL, tip ENUM('nakit','havale','cek') NOT NULL DEFAULT 'nakit',
  cek_id INT NULL, aciklama VARCHAR(255),
  FOREIGN KEY (tedarikci_id) REFERENCES tedarikciler(id) ON DELETE CASCADE,
  FOREIGN KEY (santiye_id) REFERENCES santiyeler(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tahsilatlar (
  id INT PRIMARY KEY AUTO_INCREMENT,
  santiye_id INT NOT NULL, tarih DATE NOT NULL, tutar DECIMAL(14,2) NOT NULL,
  tip ENUM('nakit','havale','cek') NOT NULL DEFAULT 'nakit',
  cek_id INT NULL, aciklama VARCHAR(255),
  FOREIGN KEY (santiye_id) REFERENCES santiyeler(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cekler (
  id INT PRIMARY KEY AUTO_INCREMENT,
  yon ENUM('alinan','verilen') NOT NULL,
  cek_no VARCHAR(60), banka VARCHAR(100), tutar DECIMAL(14,2) NOT NULL, vade DATE NOT NULL,
  durum ENUM('portfoy','tahsil','ciro','karsiliksiz','odendi') NOT NULL DEFAULT 'portfoy',
  santiye_id INT NULL, tedarikci_id INT NULL, aciklama VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE makineler (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ad VARCHAR(120) NOT NULL, plaka VARCHAR(40),
  tip ENUM('ekskavator','kepce','kamyon','silindir','dozer','diger') NOT NULL DEFAULT 'diger',
  santiye_id INT NULL, durum ENUM('calisiyor','bosta','bakimda') NOT NULL DEFAULT 'bosta',
  FOREIGN KEY (santiye_id) REFERENCES santiyeler(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE makine_kiralama (
  id INT PRIMARY KEY AUTO_INCREMENT,
  tedarikci_id INT NOT NULL, makine_adi VARCHAR(120) NOT NULL, santiye_id INT NOT NULL,
  tip ENUM('gunluk','aylik') NOT NULL DEFAULT 'gunluk',
  birim_fiyat DECIMAL(12,2) NOT NULL, baslangic DATE NOT NULL, bitis DATE NULL,
  toplam DECIMAL(14,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (tedarikci_id) REFERENCES tedarikciler(id) ON DELETE CASCADE,
  FOREIGN KEY (santiye_id) REFERENCES santiyeler(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE makine_gunluk (
  id INT PRIMARY KEY AUTO_INCREMENT,
  makine_id INT NOT NULL, santiye_id INT NOT NULL, tarih DATE NOT NULL, saat DECIMAL(4,1) DEFAULT 0, notlar VARCHAR(255),
  FOREIGN KEY (makine_id) REFERENCES makineler(id) ON DELETE CASCADE,
  FOREIGN KEY (santiye_id) REFERENCES santiyeler(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE is_gunlugu (
  id INT PRIMARY KEY AUTO_INCREMENT,
  santiye_id INT NOT NULL, tarih DATE NOT NULL, aciklama TEXT NOT NULL,
  metraj DECIMAL(12,2) NULL, birim ENUM('m3','m2','adet','mt') NULL,
  hava ENUM('gunesli','bulutlu','yagmurlu','karli') DEFAULT 'gunesli',
  fotolar TEXT, kaydeden INT, olusturma DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (santiye_id) REFERENCES santiyeler(id) ON DELETE CASCADE
) ENGINE=InnoDB;
