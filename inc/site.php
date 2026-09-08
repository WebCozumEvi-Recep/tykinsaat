<?php
// Web sitesi içerik katmanı: site_ayar tablosu + tablo boşken kullanılan varsayılanlar.
// Panelden (site_yonetimi.php) doldurulan her alan burada okunur.

function site_varsayilan(): array {
    return [
        'firma_ad'        => 'TYK İnşaat',
        'slogan'          => 'Üsküdar merkezli kuyu temel ekibi',
        'hero_baslik'     => 'Toprak kaymasın diye, kuyu kuyu ilerliyoruz.',
        'hero_metin'      => 'Kuyu temel işi yapıyoruz. Kuyuyu makineyle veya el ile kazıyor, ilerledikçe ahşap iksa ile tahkim ediyor, kenarları açıp betonarme perdeye hazır teslim ediyoruz.',
        'telefon'         => '0216 000 00 00',
        'logo'            => '',
        'whatsapp'        => '0500 000 00 00',
        'whatsapp_link'   => '905000000000',
        'eposta'          => 'info@tykinsaat.com',
        'adres'           => 'Mimar Sinan Mah. Örnek Cad. No: 00',
        'ilce'            => 'Üsküdar',
        'sehir'           => 'İstanbul',
        'posta_kodu'      => '34664',
        'saatler'         => "Pazartesi-Cuma 08.00-18.00\nCumartesi 08.00-14.00\nSaha ekipleri hafta sonu da çalışır.",
        'yil_sayisi'      => '18',
        'derinlik'        => '30 m',
        'kuyu_sayisi'     => '2 400+',
        'ekip_sayisi'     => '5',
        // SEO
        'seo_baslik'      => 'Kuyu Temel | İstanbul Kuyu Temel Kazısı ve İksa - TYK İnşaat',
        'seo_aciklama'    => 'İstanbul Üsküdar merkezli kuyu temel firması. Makineli ve el ile kuyu temel kazısı, ahşap iksa, betonarme perdeye hazır teslim. Ücretsiz keşif için arayın.',
        'seo_anahtar'     => 'kuyu temel, kuyu temel kazısı, istanbul kuyu temel, üsküdar kuyu temel, el ile kuyu kazısı, kuyu temel iksa, kuyu temel firması',
        'site_url'        => '',
        'og_gorsel'       => '',
        // Doğrulama ve ölçüm
        'dogrulama_google'=> '',
        'dogrulama_bing'  => '',
        'dogrulama_yandex'=> '',
        'analytics_id'    => '',
        'gtm_id'          => '',
        'ekstra_head'     => '',
        // Harita
        'harita_lat'      => '41.0225',
        'harita_lng'      => '29.0330',
        'harita_zoom'     => '15',
        'harita_embed'    => '',
        'maps_link'       => '',
    ];
}

/** Ayarlar > Firma bilgileri kaydı. Ad, adres, telefon ve logo tek yerden yönetilir. */
function firma_bilgi(): array {
    static $f = null;
    if ($f !== null) return $f;
    try { $f = row("SELECT * FROM firma WHERE id=1") ?: []; }
    catch (Throwable $ex) { $f = []; }
    return $f;
}

function site_ayarlar(): array {
    static $c = null;
    if ($c !== null) return $c;
    $c = site_varsayilan();
    try {
        foreach (rows("SELECT anahtar, deger FROM site_ayar") as $r) {
            if ($r['deger'] !== null && $r['deger'] !== '') $c[$r['anahtar']] = $r['deger'];
        }
    } catch (Throwable $ex) { /* tablo henüz kurulmadıysa varsayılanlarla devam */ }

    // Firma kaydı site ayarlarını ezer: bu alanlar iki yerde ayrı ayrı girilmez.
    $f = firma_bilgi();
    if (!empty($f['ad']))      $c['firma_ad'] = $f['ad'];
    if (!empty($f['adres']))   $c['adres']    = $f['adres'];
    if (!empty($f['telefon'])) $c['telefon']  = $f['telefon'];
    $c['logo'] = $f['logo'] ?? '';
    $c['telefon_link'] = tel_link($c['telefon']);
    return $c;
}

/** Görünen telefonu tıklanabilir hale getirir: "0216 000 00 00" -> "+902160000000" */
function tel_link(string $t): string {
    $r = preg_replace('/[^0-9+]/', '', $t);
    if (str_starts_with($r, '+')) return $r;
    if (str_starts_with($r, '00')) return '+' . substr($r, 2);
    if (str_starts_with($r, '0'))  return '+90' . substr($r, 1);
    if (strlen($r) === 10)         return '+90' . $r;
    return $r;
}
function sa(string $k, string $d = ''): string { $a = site_ayarlar(); return (string)($a[$k] ?? $d); }
function site_kaydet(array $veri): void {
    foreach ($veri as $k => $v) {
        q("INSERT INTO site_ayar (anahtar, deger) VALUES (?,?) ON DUPLICATE KEY UPDATE deger=VALUES(deger)", [$k, $v]);
    }
}

/** Tablo boşsa gösterilecek başlangıç içerikleri. */
function site_hizmetler(): array {
    try { $r = rows("SELECT * FROM site_hizmet WHERE aktif=1 ORDER BY sira, id"); if ($r) return $r; } catch (Throwable $ex) {}
    return [
        ['baslik'=>'Makineli kuyu kazısı','olcu'=>'-5 / -30 m','slug'=>'makineli-kuyu-kazisi',
         'ozet'=>'Havalı kırıcı ve kompresörle yürütülen kazı. Sert ve kayalık zeminlerde el aletinin sökemediği tabakayı hızla geçer; pasa vinç ve kova ile yukarı alınır.',
         'maddeler'=>"Havalı kırıcı, kompresör, vinç\nSert ve kayalık zemin\nHafriyatın sahadan tahliyesi"],
        ['baslik'=>'El ile kuyu temel kazısı','olcu'=>'-5 / -25 m','slug'=>'el-ile-kuyu-kazisi',
         'ozet'=>'Kuyu temelin aslı olan iş. Makinenin giremediği dar parseller, eğimli araziler ve bitişik nizam yapılar için; gürültü ve titreşim üretmediğinden komşu binanın dibinde güvenle çalışılır.',
         'maddeler'=>"Kazma, küskü, havalı kırıcı\nDar parsel, bitişik nizam\nTitreşimsiz, düşük gürültü"],
        ['baslik'=>'Ahşap iksa ve tahkimat','olcu'=>'Kazı boyunca','slug'=>'ahsap-iksa-tahkimat',
         'ozet'=>'Kuyu indikçe ahşap destek elemanları yerleştirilir, kuyu ağzı emniyete alınır. Su gelen kuyularda pompa, derin kuyularda havalandırma sürekli çalışır.',
         'maddeler'=>"Ahşap destek ve tahkimat\nSu pompası, havalandırma\nİş güvenliği gözetiminde çalışma"],
        ['baslik'=>'Kenar açma ve perdeye hazırlık','olcu'=>'Kuyular arası','slug'=>'kenar-acma',
         'ozet'=>'Kuyular kotuna indikten sonra aralarındaki kenarların açılması. Betonarme iksa perdesi bu kenarlarda teşkil edilir; kuyular kalıp ve donatıya hazır teslim edilir.',
         'maddeler'=>"Projedeki perde ölçüsüne göre\nKalıp ve donatıya hazır teslim\nÖlçü kontrolü ve fotoğraf kaydı"],
    ];
}
function site_referanslar(): array {
    try { $r = rows("SELECT * FROM site_referans WHERE aktif=1 ORDER BY sira, id"); if ($r) return $r; } catch (Throwable $ex) {}
    return [
        ['proje'=>'9 katlı konut','ilce'=>'Üsküdar, Bulgurlu','yontem'=>'Makineli kuyu','adet'=>'148 kuyu','derinlik'=>'22 m','yil'=>'2025'],
        ['proje'=>'Ofis binası','ilce'=>'Ümraniye, Çakmak','yontem'=>'Makineli kuyu, kenar açma','adet'=>'96 kuyu','derinlik'=>'26 m','yil'=>'2025'],
        ['proje'=>'Bitişik nizam konut','ilce'=>'Beykoz, Kavacık','yontem'=>'El ile kuyu','adet'=>'54 kuyu','derinlik'=>'17 m','yil'=>'2024'],
        ['proje'=>'Villa grubu','ilce'=>'Üsküdar, Kısıklı','yontem'=>'El ile kuyu, kenar açma','adet'=>'64 kuyu','derinlik'=>'15 m','yil'=>'2024'],
        ['proje'=>'Kentsel dönüşüm bloğu','ilce'=>'Kadıköy, Fikirtepe','yontem'=>'Makineli kuyu','adet'=>'305 kuyu','derinlik'=>'28 m','yil'=>'2023'],
        ['proje'=>'Depo yapısı','ilce'=>'Çekmeköy, Taşdelen','yontem'=>'Makineli kuyu, ahşap iksa','adet'=>'120 kuyu','derinlik'=>'19 m','yil'=>'2023'],
    ];
}
function site_sss(): array {
    try { $r = rows("SELECT * FROM site_sss WHERE aktif=1 ORDER BY sira, id"); if ($r) return $r; } catch (Throwable $ex) {}
    return [
        ['soru'=>'Kuyu temel nedir?','cevap'=>'Kuyu temel, yapının yükünü üstteki zayıf tabakalardan geçirip alttaki sağlam zemine aktarmak için açılan derin kuyulara oturan bir temel sistemidir. Kuyular el ile veya makineyle kazılır, kazı ilerledikçe ahşap elemanlarla tahkim edilir, ardından kuyular arasındaki kenarlar açılarak betonarme iksa perdesi teşkil edilir.'],
        ['soru'=>'Kuyu temel hangi durumlarda tercih edilir?','cevap'=>'Derin kazı yapılan, komşu parsele veya yola bitişik, makinenin manevra yapamadığı dar ve eğimli arazilerde tercih edilir. Kentsel dönüşüm projelerinde toprak kaymasını önlediği ve çok katlı yapılarda tabanı sağlam zemine oturttuğu için yaygınlaşmıştır.'],
        ['soru'=>'Kuyu temel kaç metre derinliğe kadar iner?','cevap'=>'Zemine ve projeye göre değişir. Uygulamada 5 metreden başlayıp 25-30 metreye kadar inen kuyular açılır. Derinliğe zemin etüdü raporu ve statik proje karar verir.'],
        ['soru'=>'Kuyu temelde hangi ekipmanlar kullanılır?','cevap'=>'El ile kazıda kazma, küskü, havalı kırıcı ve kompresör; pasanın yukarı alınmasında vinç ve pasa kovası; kuyu güvenliğinde ahşap destek elemanları, su pompası ve havalandırma; perde imalatında kalıp kullanılır.'],
        ['soru'=>'Kuyu temel ne kadar sürer?','cevap'=>'Kuyu adedi, derinlik ve zemin sertliği belirler. Keşiften sonra kuyu adedine göre gün bazında program veriyor, bu süreyi teklifin içinde taahhüt ediyoruz.'],
        ['soru'=>'Keşif ücretli mi?','cevap'=>'Hayır. İstanbul içinde keşif ücretsizdir. Parselin adresini ve varsa temel aplikasyon planını gönderdiğinizde aynı gün dönüş yapıp keşif için gün veriyoruz.'],
    ];
}
function site_galeri(): array {
    try { return rows("SELECT * FROM site_galeri WHERE aktif=1 ORDER BY sira, id"); } catch (Throwable $ex) { return []; }
}
/** Metni satırlara böler (maddeler alanı için). */
function satirlar(?string $s): array {
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$s))));
}
