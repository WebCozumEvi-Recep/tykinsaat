# ŞantiyeTakip (PHP + MySQL)

İnşaat firmaları için şantiye, puantaj, gider, tahsilat, tedarikçi ve çek takibi. Mobil öncelikli, saf PHP (framework yok), PDO/MySQL.

## Kurulum
1. `schema.sql` dosyasını MySQL'e aktar: `mysql -u root -p < schema.sql`
2. `config.php` içinde DB bilgilerini düzenle.
3. Klasörü web köküne kopyala; `uploads/` yazılabilir olsun (`chmod 775 uploads`).
4. Giriş: **admin / admin123** (ilk girişte Ayarlar > Şifremi değiştir).

Gereksinim: PHP 8.0+, MySQL 5.7+/MariaDB 10.3+, `fileinfo` eklentisi.

## Roller
- **patron**: her şey. **muhasebe**: tüm finans, kullanıcı/şantiye yönetimi hariç.
- **santiye**: sadece atandığı şantiyeler; fiyat/kâr/borç görmez. Puantaj, gider, iş kaydı, makine günlüğü girer.

## Sayfalar
index (panel / saha ana sayfa) · musteriler, musteri (detay), musteri_form · santiyeler, santiye (sekmeli detay), santiye_form · puantaj, puantaj_aylik · personel, personel_form · makineler, makine_form, makine_kiralama_form · gider, gider_form · tahsilat, tahsilat_form · tedarikciler, tedarikci, tedarikci_form, tedarikci_odeme_form · cek_durum · is_gunlugu, is_gunlugu_form · raporlar (kâr/zarar, müşteri alacak, hakediş, borç, çek takvimi, nakit akışı) · export (CSV/Excel) · ayarlar, kullanici_form

## Müşteri ve alacak yapısı
- Her şantiye bir müşteriye bağlıdır (`santiyeler.musteri_id`); bir müşterinin birden fazla şantiyesi olabilir.
- Alacak iki seviyede izlenir: **şantiye bazında** (sözleşme − tahsilat) ve **müşteri bazında** (tüm şantiyelerinin toplamı).
- Yaşlandırma (0-30 / 30-60 / 60+) şantiyenin son tahsilat tarihinden, hiç tahsilat yoksa şantiye başlangıcından sayılır; müşteri satırı kendi şantiyelerinin kovalarını toplar.
- Bağlı şantiyesi olan müşteri silinemez.
- Mevcut kurulumu güncelliyorsanız `migrate_musteri.sql` dosyasını çalıştırın; şantiyelerdeki müşteri adları otomatik olarak müşteri kartına dönüşür.

## Hesap kuralları
- Yevmiye: tam gün = yevmiye, yarım = yevmiye/2, mesai saati = yevmiye/9 × saat. Puantaj günkü yevmiyeyi sabitler.
- Gider "borç" ise tedarikçi bakiyesine eklenir; "ödendi" ise alım + ödeme olarak işlenir. Kiralama toplamı da tedarikçi borcudur.
- Çekli tahsilat/ödeme `cekler` tablosuna da düşer; durumu çek takviminden değiştirilir.
- Formlar tarayıcıda taslak olarak saklanır (localStorage), bağlantı kesilirse veri kaybolmaz; üst barda çevrimdışı uyarısı görünür.
