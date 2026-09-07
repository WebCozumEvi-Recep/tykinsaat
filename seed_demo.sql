USE santiye_takip;
INSERT INTO kullanicilar (kullanici_adi, sifre_hash, ad_soyad, rol) VALUES
('saha', '$2y$12$DJCmPPixAx/2ZCoKV4TfDe1N6wYNP/UomGjKRDH39ulngLksT8B1i', 'Mehmet Kaya', 'santiye'),
('muhasebe', '$2y$12$DJCmPPixAx/2ZCoKV4TfDe1N6wYNP/UomGjKRDH39ulngLksT8B1i', 'Ayşe Demir', 'muhasebe');
INSERT INTO santiyeler (ad, musteri, adres, anlasma_tutari, baslangic, bitis, durum) VALUES
('Yıldız Konutları Temel', 'Yıldız İnşaat A.Ş.', 'Başakşehir, İstanbul', 2850000, CURDATE()-INTERVAL 45 DAY, CURDATE()+INTERVAL 60 DAY, 'aktif'),
('Lojistik Depo Hafriyat', 'Kargo Plus Ltd.', 'Hadımköy, İstanbul', 1450000, CURDATE()-INTERVAL 20 DAY, CURDATE()+INTERVAL 30 DAY, 'aktif'),
('Villa Projesi Kaba', 'Ahmet Yılmaz', 'Çekmeköy, İstanbul', 980000, CURDATE()-INTERVAL 90 DAY, CURDATE()-INTERVAL 5 DAY, 'tamamlandi'),
('AVM Otopark Kazısı', 'Mega Yapı', 'Beylikdüzü, İstanbul', 3200000, CURDATE()+INTERVAL 15 DAY, NULL, 'beklemede');
INSERT INTO kullanici_santiye VALUES (2,1),(2,2);
INSERT INTO personel (ad_soyad, gorev, yevmiye, telefon) VALUES
('Ali Çelik','usta',1800,'0532 111 11 11'),('Hasan Yurt','isci',1200,'0533 222 22 22'),('Murat Aksoy','operator',2200,'0534 333 33 33'),
('Kemal Öz','sofor',1500,''),('Serkan Ak','isci',1200,''),('Osman Taş','isci',1250,''),('Veli Kurt','usta',1900,'');
INSERT INTO tedarikciler (firma, kategori, yetkili, telefon, vergi_no) VALUES
('Akçansa Beton','Beton','Fatih Bey','0212 555 10 10','1234567890'),('Demirsan Metal','Demir','Ercan Bey','0212 555 20 20','2345678901'),
('Petrol Ofisi Bayii','Akaryakıt','','0212 555 30 30',''),('Hızlı Nakliyat','Nakliye','Cem Bey','0535 444 44 44',''),('Mega İş Makinaları','Kiralama','Selim Bey','0216 555 50 50','3456789012');
INSERT INTO makineler (ad, plaka, tip, santiye_id, durum) VALUES
('CAT 320 Ekskavatör','34 ABC 123','ekskavator',1,'calisiyor'),('JCB 3CX Kepçe','34 DEF 456','kepce',2,'calisiyor'),('Mercedes Damperli','34 GHI 789','kamyon',1,'calisiyor'),('Silindir Bomag','','silindir',NULL,'bakimda');
INSERT INTO makine_kiralama (tedarikci_id, makine_adi, santiye_id, tip, birim_fiyat, baslangic, bitis, toplam) VALUES
(5,'Hitachi 30 ton ekskavatör',1,'gunluk',9000,CURDATE()-INTERVAL 20 DAY,CURDATE()-INTERVAL 6 DAY,135000),(5,'Paletli dozer',2,'aylik',180000,CURDATE()-INTERVAL 15 DAY,NULL,180000);
INSERT INTO giderler (santiye_id, tarih, kategori_id, tedarikci_id, aciklama, tutar, fis_no, odeme_durumu, kaydeden) VALUES
(1, CURDATE()-INTERVAL 25 DAY, 1, 1, 'C30 beton 120 m³', 336000, 'A-10021', 'borc', 2),
(1, CURDATE()-INTERVAL 18 DAY, 2, 2, 'Ø12 nervürlü demir 18 ton', 414000, 'B-5511', 'borc', 2),
(1, CURDATE()-INTERVAL 10 DAY, 3, 3, 'Mazot 1.200 lt', 52800, 'P-778', 'odendi', 2),
(1, CURDATE()-INTERVAL 3 DAY, 6, NULL, 'Ekip yemeği (haftalık)', 8400, '', 'odendi', 2),
(2, CURDATE()-INTERVAL 12 DAY, 5, 4, 'Hafriyat nakliye 40 sefer', 96000, 'N-311', 'borc', 2),
(2, CURDATE()-INTERVAL 6 DAY, 3, 3, 'Mazot 800 lt', 35200, 'P-802', 'borc', 2),
(2, CURDATE()-INTERVAL 1 DAY, 1, 1, 'C25 beton 40 m³', 104000, 'A-10098', 'borc', 2),
(3, CURDATE()-INTERVAL 60 DAY, 1, 1, 'C30 beton 60 m³', 168000, 'A-9901', 'odendi', 1),
(3, CURDATE()-INTERVAL 50 DAY, 2, 2, 'Demir 8 ton', 184000, 'B-5300', 'odendi', 1);
INSERT INTO cekler (yon, cek_no, banka, tutar, vade, durum, santiye_id, tedarikci_id) VALUES
('alinan','0012345','Ziraat Bankası',500000,CURDATE()+INTERVAL 5 DAY,'portfoy',1,NULL),
('alinan','0098761','İş Bankası',300000,CURDATE()+INTERVAL 22 DAY,'portfoy',2,NULL),
('verilen','4455667','Garanti BBVA',200000,CURDATE()+INTERVAL 3 DAY,'portfoy',1,1),
('verilen','4455668','Garanti BBVA',150000,CURDATE()+INTERVAL 40 DAY,'portfoy',1,2),
('alinan','0055555','Akbank',250000,CURDATE()-INTERVAL 10 DAY,'tahsil',3,NULL);
INSERT INTO tahsilatlar (santiye_id, tarih, tutar, tip, cek_id, aciklama) VALUES
(1, CURDATE()-INTERVAL 40 DAY, 700000, 'havale', NULL, 'Avans'),
(1, CURDATE()-INTERVAL 8 DAY, 500000, 'cek', 1, '1. hakediş'),
(2, CURDATE()-INTERVAL 15 DAY, 400000, 'havale', NULL, 'Avans'),
(2, CURDATE()-INTERVAL 2 DAY, 300000, 'cek', 2, ''),
(3, CURDATE()-INTERVAL 80 DAY, 500000, 'nakit', NULL, ''),(3, CURDATE()-INTERVAL 10 DAY, 250000, 'cek', 5, 'Son ödeme'),(3, CURDATE()-INTERVAL 6 DAY, 230000, 'havale', NULL, '');
INSERT INTO tedarikci_odemeleri (tedarikci_id, santiye_id, tarih, tutar, tip, cek_id) VALUES
(1, 1, CURDATE()-INTERVAL 15 DAY, 200000, 'cek', 3),(2, 1, CURDATE()-INTERVAL 9 DAY, 150000, 'cek', 4),(1, 1, CURDATE()-INTERVAL 4 DAY, 100000, 'havale', NULL),(4, 2, CURDATE()-INTERVAL 2 DAY, 50000, 'nakit', NULL);
INSERT INTO personel_odeme (personel_id, tarih, tutar, aciklama) VALUES (1, CURDATE()-INTERVAL 7 DAY, 15000, 'Haftalık'),(2, CURDATE()-INTERVAL 7 DAY, 10000, 'Haftalık'),(3, CURDATE()-INTERVAL 7 DAY, 20000, 'Haftalık');
INSERT INTO is_gunlugu (santiye_id, tarih, aciklama, metraj, birim, hava, kaydeden) VALUES
(1, CURDATE()-INTERVAL 2 DAY, 'B blok temel kazısı tamamlandı, grobeton döküldü.', 320, 'm3', 'gunesli', 2),
(1, CURDATE()-INTERVAL 1 DAY, 'Radye temel demir bağlama %60 seviyesinde.', 18, 'adet', 'bulutlu', 2),
(2, CURDATE(), 'Hafriyat devam ediyor, 40 sefer döküm yapıldı.', 600, 'm3', 'yagmurlu', 2);
INSERT INTO makine_gunluk (makine_id, santiye_id, tarih, saat, notlar) VALUES (1,1,CURDATE()-INTERVAL 1 DAY,9,''),(2,2,CURDATE(),8,'Yağmur nedeniyle erken bitti');
