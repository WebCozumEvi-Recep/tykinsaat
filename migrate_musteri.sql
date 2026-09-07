-- Müşteri modülü geçişi: santiyeler.musteri (metin) -> musteriler tablosu + musteri_id
USE santiye_takip;

CREATE TABLE IF NOT EXISTS musteriler (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ad VARCHAR(150) NOT NULL,
  yetkili VARCHAR(120), telefon VARCHAR(30), email VARCHAR(120),
  vergi_dairesi VARCHAR(80), vergi_no VARCHAR(30),
  adres TEXT, notlar TEXT,
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  olusturma DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ad (ad)
) ENGINE=InnoDB;

-- Mevcut şantiyelerdeki benzersiz müşteri adlarını müşteri kartına çevir
INSERT IGNORE INTO musteriler (ad) SELECT DISTINCT TRIM(musteri) FROM santiyeler WHERE TRIM(COALESCE(musteri,'')) <> '';

ALTER TABLE santiyeler ADD COLUMN musteri_id INT NULL AFTER ad;
UPDATE santiyeler s JOIN musteriler m ON m.ad = TRIM(s.musteri) SET s.musteri_id = m.id;
ALTER TABLE santiyeler ADD CONSTRAINT fk_santiye_musteri FOREIGN KEY (musteri_id) REFERENCES musteriler(id) ON DELETE RESTRICT;
ALTER TABLE santiyeler DROP COLUMN musteri;
