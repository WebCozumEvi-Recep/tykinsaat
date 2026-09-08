-- NOT: Yorum satırlarını ayıklamadan çalıştıran araçlar ilk tabloyu atlayabilir.
-- TYK Saha Takip · Web sitesi yönetimi tabloları
-- Kurulum: mysql -u KULLANICI -p VERITABANI < site_schema.sql

CREATE TABLE IF NOT EXISTS site_ayar (
  anahtar VARCHAR(64) NOT NULL PRIMARY KEY,
  deger   TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_hizmet (
  id INT AUTO_INCREMENT PRIMARY KEY,
  baslik      VARCHAR(120) NOT NULL,
  olcu        VARCHAR(60)  NULL,        -- derinlik/kapsam etiketi, ör. "-5 / -30 m"
  ozet        TEXT         NULL,
  maddeler    TEXT         NULL,        -- her satır bir madde
  slug        VARCHAR(120) NULL,
  sira        INT NOT NULL DEFAULT 0,
  aktif       TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_referans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proje    VARCHAR(160) NOT NULL,
  ilce     VARCHAR(80)  NULL,
  yontem   VARCHAR(120) NULL,
  adet     VARCHAR(40)  NULL,
  derinlik VARCHAR(40)  NULL,
  yil      VARCHAR(9)   NULL,
  sira     INT NOT NULL DEFAULT 0,
  aktif    TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_galeri (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dosya   VARCHAR(160) NOT NULL,
  aciklama VARCHAR(200) NULL,
  sira    INT NOT NULL DEFAULT 0,
  aktif   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_sss (
  id INT AUTO_INCREMENT PRIMARY KEY,
  soru  VARCHAR(255) NOT NULL,
  cevap TEXT NOT NULL,
  sira  INT NOT NULL DEFAULT 0,
  aktif TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
