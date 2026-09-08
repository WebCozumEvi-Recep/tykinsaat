<?php
require __DIR__ . '/bootstrap.php';
$a   = site_ayarlar();
$hiz = site_hizmetler();
$ref = site_referanslar();
$sss = site_sss();
$gal = site_galeri();
$kok = site_adres();
$konum = trim($a['ilce'] . ', ' . $a['sehir'], ', ');
$tel_link = 'tel:' . $a['telefon_link'];
$wa_link  = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $a['whatsapp_link']);

// Yapılandırılmış veri: yerel işletme + hizmet listesi + sık sorulanlar
$ld = [
  '@context' => 'https://schema.org',
  '@graph' => [
    ['@type' => ['LocalBusiness', 'GeneralContractor'],
     '@id' => $kok . '/#firma',
     'name' => $a['firma_ad'],
     'description' => $a['seo_aciklama'],
     'url' => $kok . '/',
     'telephone' => $a['telefon_link'] ?: $a['telefon'],
     'email' => $a['eposta'],
     'address' => ['@type'=>'PostalAddress','streetAddress'=>$a['adres'],'addressLocality'=>$a['ilce'],
                   'addressRegion'=>$a['sehir'],'postalCode'=>$a['posta_kodu'],'addressCountry'=>'TR'],
     'geo' => ['@type'=>'GeoCoordinates','latitude'=>$a['harita_lat'],'longitude'=>$a['harita_lng']],
     'areaServed' => ['İstanbul', 'Üsküdar', 'Ümraniye', 'Kadıköy', 'Beykoz', 'Çekmeköy', 'Ataşehir', 'Maltepe'],
     'knowsAbout' => ['kuyu temel', 'kuyu temel kazısı', 'ahşap iksa', 'betonarme iksa perdesi'],
     'hasOfferCatalog' => ['@type'=>'OfferCatalog','name'=>'Kuyu temel hizmetleri',
        'itemListElement' => array_map(fn($h) => ['@type'=>'Offer','itemOffered'=>[
            '@type'=>'Service','name'=>$h['baslik'],'description'=>$h['ozet'],'serviceType'=>'Kuyu temel']], $hiz)],
    ],
    ['@type' => 'FAQPage',
     'mainEntity' => array_map(fn($s) => ['@type'=>'Question','name'=>$s['soru'],
        'acceptedAnswer'=>['@type'=>'Answer','text'=>$s['cevap']]], $sss)],
  ],
];
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($a['seo_baslik']) ?></title>
<meta name="description" content="<?= e($a['seo_aciklama']) ?>">
<?php if ($a['seo_anahtar']): ?><meta name="keywords" content="<?= e($a['seo_anahtar']) ?>"><?php endif; ?>
<link rel="canonical" href="<?= e($kok) ?>/">
<meta name="robots" content="index,follow,max-image-preview:large">
<meta name="theme-color" content="#EEF1F3">
<meta name="geo.region" content="TR-34"><meta name="geo.placename" content="<?= e($konum) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="tr_TR">
<meta property="og:site_name" content="<?= e($a['firma_ad']) ?>">
<meta property="og:title" content="<?= e($a['seo_baslik']) ?>">
<meta property="og:description" content="<?= e($a['seo_aciklama']) ?>">
<meta property="og:url" content="<?= e($kok) ?>/">
<?php if ($a['og_gorsel']): ?><meta property="og:image" content="<?= e($a['og_gorsel']) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<?php if ($a['dogrulama_google']): ?><meta name="google-site-verification" content="<?= e($a['dogrulama_google']) ?>"><?php endif; ?>
<?php if ($a['dogrulama_bing']): ?><meta name="msvalidate.01" content="<?= e($a['dogrulama_bing']) ?>"><?php endif; ?>
<?php if ($a['dogrulama_yandex']): ?><meta name="yandex-verification" content="<?= e($a['dogrulama_yandex']) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= VARLIK ?>site.css">
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php if ($a['analytics_id']): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($a['analytics_id']) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= e($a['analytics_id']) ?>');</script>
<?php endif; ?>
<?php if ($a['gtm_id']): ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= e($a['gtm_id']) ?>');</script>
<?php endif; ?>
<?= $a['ekstra_head'] ?>
</head>
<body>

<aside class="ray" aria-hidden="true">
  <div class="ray-cizgi"></div>
  <div class="ray-okuma"><span id="kot">±0.00</span><small>METRE</small></div>
</aside>

<header class="ust">
  <div class="ust-ic">
    <a class="marka" href="#tepe">
      <?php if (!empty($a['logo'])): ?>
        <img class="marka-im" src="<?= PANEL . UPLOAD_URL . e($a['logo']) ?>" alt="<?= e($a['firma_ad']) ?>">
      <?php else: ?>
        <span><b><?= e(mb_strtoupper($a['firma_ad'], 'UTF-8')) ?></b><span><?= e($konum) ?></span></span>
      <?php endif; ?>
    </a>
    <nav class="menu" id="menu" aria-label="Bölümler">
      <a href="#hizmetler">Hizmetler</a>
      <a href="#kuyu-temel">Kuyu temel nedir</a>
      <a href="#surec">Nasıl çalışıyoruz</a>
      <a href="#referanslar">Referanslar</a>
      <a href="#iletisim">İletişim</a>
    </nav>
    <a class="btn btn-panel" href="<?= PANEL ?>login.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
        <rect x="4" y="10.5" width="16" height="10" rx="1.5"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/>
      </svg>
      Saha<span class="uzun"> Takip</span> Girişi
    </a>
    <button class="hamb" id="hambDugme" type="button" aria-label="Menüyü aç" aria-expanded="false" aria-controls="menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
        <path d="M4 7h16M4 12h16M4 17h16"/>
      </svg>
    </button>
  </div>
</header>

<main id="tepe">

  <!-- HERO: metin + kuyu kesiti çizimi -->
  <section class="hero">
    <div class="kap hero-ic">
      <div>
        <h1 class="ac"><?= e($a['hero_baslik']) ?></h1>
        <p class="ac ac-2"><?= e($a['hero_metin']) ?></p>
        <div class="hero-eylem ac ac-3">
          <a class="btn btn-dolu" href="<?= e($tel_link) ?>"><?= e($a['telefon']) ?></a>
          <a class="btn" href="#iletisim">Keşif ve teklif isteyin</a>
        </div>
      </div>

      <!-- Kuyu temel kesiti: tabakalar, ahşap tahkimatlı kuyu, vinç ve pasa kovası -->
      <svg class="kesit" viewBox="0 0 420 360" role="img" aria-label="Kuyu temel kesiti: zemin tabakaları, ahşap tahkimatlı kuyu ve pasa kovası">
        <defs>
          <clipPath id="kesitAlan"><rect x="0" y="0" width="420" height="360"/></clipPath>
          <pattern id="tarama" width="10" height="10" patternTransform="rotate(35)" patternUnits="userSpaceOnUse">
            <line x1="0" y1="0" x2="0" y2="10" stroke="#ffffff" stroke-opacity=".22" stroke-width="1.6"/>
          </pattern>
        </defs>
        <g clip-path="url(#kesitAlan)">
          <rect x="0" y="0" width="420" height="120" fill="#E2E7EA"/>
          <rect x="0" y="120" width="420" height="52" fill="#C9A227"/>
          <rect x="0" y="172" width="420" height="58" fill="#B4603C"/>
          <rect x="0" y="230" width="420" height="60" fill="#8C8A6B"/>
          <rect x="0" y="290" width="420" height="70" fill="#5A6773"/>
          <rect x="0" y="120" width="420" height="240" fill="url(#tarama)"/>
          <line x1="0" y1="120" x2="420" y2="120" stroke="#16202A" stroke-width="4"/>

          <!-- vinç ayağı ve halat -->
          <path d="M150 120 L186 46 L222 120" fill="none" stroke="#16202A" stroke-width="4" stroke-linejoin="round"/>
          <line x1="186" y1="46" x2="186" y2="120" stroke="#16202A" stroke-width="2.5" stroke-dasharray="5 4"/>
          <rect x="172" y="96" width="28" height="20" rx="2" fill="#F2B705" stroke="#16202A" stroke-width="2.5"/>

          <!-- kuyu gövdesi -->
          <g class="kuyu-govde">
            <rect x="160" y="120" width="52" height="220" fill="#EEF1F3"/>
            <rect x="160" y="120" width="52" height="220" fill="none" stroke="#16202A" stroke-width="2.5"/>
            <!-- ahşap tahkimat elemanları -->
            <rect class="ahsap-el" x="150" y="146" width="72" height="9" fill="#A5713C" stroke="#16202A" stroke-width="2"/>
            <rect class="ahsap-el" x="150" y="184" width="72" height="9" fill="#A5713C" stroke="#16202A" stroke-width="2"/>
            <rect class="ahsap-el" x="150" y="222" width="72" height="9" fill="#A5713C" stroke="#16202A" stroke-width="2"/>
            <rect class="ahsap-el" x="150" y="260" width="72" height="9" fill="#A5713C" stroke="#16202A" stroke-width="2"/>
            <rect class="ahsap-el" x="150" y="298" width="72" height="9" fill="#A5713C" stroke="#16202A" stroke-width="2"/>
            <!-- kuyu tabanı -->
            <rect x="160" y="326" width="52" height="14" fill="#2E5A7A"/>
          </g>

          <!-- kenar açma: kuyular arası perde payı -->
          <rect x="236" y="120" width="16" height="200" fill="#2E5A7A" fill-opacity=".18" stroke="#2E5A7A" stroke-width="2" stroke-dasharray="6 5"/>
          <rect x="120" y="120" width="16" height="200" fill="#2E5A7A" fill-opacity=".18" stroke="#2E5A7A" stroke-width="2" stroke-dasharray="6 5"/>

          <!-- derinlik ölçeği -->
          <g stroke="#16202A" stroke-width="2" opacity=".55">
            <line x1="330" y1="120" x2="330" y2="340"/>
            <line x1="324" y1="120" x2="336" y2="120"/>
            <line x1="324" y1="230" x2="336" y2="230"/>
            <line x1="324" y1="340" x2="336" y2="340"/>
          </g>
          <g fill="#16202A" font-family="Barlow Condensed, sans-serif" font-size="15" font-weight="600">
            <text x="344" y="125">0.00</text>
            <text x="344" y="235">15.00</text>
            <text x="344" y="345">30.00</text>
          </g>
        </g>
      </svg>
    </div>

    <div class="zemin">
      <div class="kap">
        <div class="zemin-cizgi ac ac-4"><span>±0.00 ZEMİN KOTU</span><em>Aşağısı bizim işimiz</em></div>
      </div>
      <div class="strata" aria-hidden="true"></div>
    </div>

    <div class="kap">
      <div class="rakamlar">
        <div><span class="rakam"><?= e($a['yil_sayisi']) ?></span><p>yıllık saha tecrübesi</p></div>
        <div><span class="rakam"><?= e($a['derinlik']) ?></span><p>indiğimiz azami kuyu derinliği</p></div>
        <div><span class="rakam"><?= e($a['kuyu_sayisi']) ?></span><p>açılmış kuyu</p></div>
        <div><span class="rakam"><?= e($a['ekip_sayisi']) ?></span><p>kompresör ve vinçli kuyu ekibi</p></div>
      </div>
    </div>
  </section>

  <!-- HİZMETLER -->
  <section class="bolum" id="hizmetler">
    <div class="kap">
      <div class="bolum-basi"><span class="kot">-05.00<br>-30.00</span><h2>Kuyu temel hizmetleri</h2></div>
      <p class="giris">Kuyu temel; kazı, tahkimat ve kenar açma işinin birlikte yürütülmesidir. Makineli mi el ile mi ilerleyeceğimize parselin ulaşımı, komşu yapılar ve zemin karar verir. Çoğu sahada ikisi birlikte kullanılır.</p>
      <div class="tabakalar">
        <?php foreach ($hiz as $h): ?>
        <article class="tabaka">
          <div class="olcu"><?= e($h['olcu']) ?></div>
          <div><h3><?= e($h['baslik']) ?></h3><p><?= e($h['ozet']) ?></p></div>
          <ul><?php foreach (satirlar($h['maddeler']) as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- KUYU TEMEL NEDİR (arama motoru içeriği) -->
  <section class="bolum bilgi" id="kuyu-temel">
    <div class="kap">
      <div class="bolum-basi"><h2>Kuyu temel nedir, nasıl yapılır</h2></div>
      <div class="bilgi-ic">
        <div>
          <p>Kuyu temel, yapının yükünü üstteki zayıf tabakalardan geçirip alttaki sağlam zemine aktaran derin temel sistemidir. Projede belirlenen noktalarda kuyular açılır, kazı ilerledikçe kuyu ahşap elemanlarla tahkim edilir, kuyular kotuna indikten sonra aralarındaki kenarlar açılarak betonarme iksa perdesi teşkil edilir. Kentsel dönüşüm projelerinde derin kazının komşu parseli ve yolu tehdit etmemesi için en çok başvurulan yöntemlerden biridir.</p>

          <h3>Kuyu temel nerede tercih edilir</h3>
          <p>Makinenin manevra yapamadığı dar parseller, eğimli araziler, bitişik nizam yapılar ve derin bodrumlu projeler. Kuyu kazısı titreşim üretmediği için komşu binanın dibinde çalışmaya izin verir; toprak kaymasını daha kazı aşamasında durdurur ve çok katlı yapıda tabanı sağlam tabakaya oturtur.</p>

          <h3>Kuyu temel nasıl yapılır</h3>
          <p>Aplikasyonla kuyu yerleri sahaya işaretlenir. Kuyular el aletleri veya havalı kırıcıyla kazılır, çıkan hafriyat vinç ve pasa kovasıyla yukarı alınıp belirlenen döküm sahasına gönderilir. Kazı ilerledikçe ahşap destek elemanlarıyla iksa yapılır, su gelen kuyularda pompa devrede kalır. Kuyu kotuna indiğinde kenarlar açılır, perde ve temel kalıbı kurulup donatı hazırlanır, beton dökülür ve prizini alan kalıp sökülür. Aynı işlem bütün kuyularda tekrarlanınca toprak kayması riski ortadan kalkmış olur.</p>

          <h3>Kuyu temelde kullanılan ekipman</h3>
          <ul class="ekipman">
            <li>Ahşap destek elemanları</li><li>Kazma</li><li>Küskü</li><li>Havalı kırıcı</li>
            <li>Kompresör</li><li>Vinç</li><li>Pasa kovası</li><li>Kalıp</li><li>Su pompası</li>
          </ul>

          <h3>Kuyu temelde iş güvenliği</h3>
          <p>Kuyu temel, işçiliğin en riskli olduğu temel işidir. Dar ve derin bir hacimde çalışılır; göçme, düşme, su basması ve havasız kalma riskleri birlikte yönetilir. Bu yüzden kazı ile tahkimatı hiç ayırmıyor, kuyu ağzını sürekli emniyette tutuyor ve derin kuyularda havalandırmayı kesintisiz çalıştırıyoruz. İşi inşaat mühendisi ve iş güvenliği gözetiminde, bu işi yıllardır yapan ustalarla yürütüyoruz.</p>
        </div>

        <aside class="yan-kutu">
          <h3>Hizmet verdiğimiz bölgeler</h3>
          <p>Üsküdar, Ümraniye, Kadıköy, Ataşehir, Maltepe, Beykoz, Çekmeköy, Sancaktepe ve Anadolu Yakası genelinde çalışıyoruz. Avrupa Yakası için de proje bazında teklif veriyoruz.</p>
          <h3>Teklif için gereken</h3>
          <p>Parsel adresi, temel aplikasyon planı, varsa zemin etüdü raporu ve kuyu derinliği. Bunlar elinizde yoksa keşifte birlikte çıkarıyoruz.</p>
          <p><a class="btn btn-dolu" href="<?= e($wa_link) ?>">WhatsApp'tan gönderin</a></p>
        </aside>
      </div>
    </div>
  </section>

  <!-- SÜREÇ -->
  <section class="surec" id="surec">
    <div class="kap">
      <div class="bolum-basi"><span class="kot">01<br>06</span><h2>Bir kuyu temel işi bizde nasıl ilerler</h2></div>
      <p class="giris">Altı adım, sabit sıra. Hangi aşamada olduğunuzu her zaman bilirsiniz; saha ekibimiz açılan her kuyuyu aynı gün sisteme kaydeder.</p>
      <div class="adimlar">
        <article class="adim"><h3>Keşif</h3><p>Sahayı geziyor; ulaşımı, komşu yapıları, su durumunu, kuyu sayısını ve derinliğini yerinde değerlendiriyoruz.</p></article>
        <article class="adim"><h3>Teklif ve program</h3><p>Kuyu adedi ve derinliğe göre kalem kalem açık teklif. Süre taahhüdü ve ekip planı teklifin içindedir; sonradan kalem eklenmez.</p></article>
        <article class="adim"><h3>Aplikasyon</h3><p>Projedeki kuyu yerleri sahaya işaretlenir, kotlar alınır. Kazı bu işaretlemeye göre başlar.</p></article>
        <article class="adim"><h3>Kazı ve tahkimat</h3><p>Kuyu kotuna indirilirken ahşap destekler eş zamanlı yerleştirilir, hafriyat vinçle alınır, su varsa pompalanır.</p></article>
        <article class="adim"><h3>Kenar açma</h3><p>Kuyular arasındaki kenarlar perde ölçüsüne göre açılır, yüzeyler temizlenir ve ölçüsü kontrol edilir.</p></article>
        <article class="adim"><h3>Teslim ve rapor</h3><p>Kuyu tutanakları, ölçü listesi ve fotoğraflar tek dosyada teslim edilir. Kuyular kalıp, donatı ve betona hazırdır.</p></article>
      </div>
    </div>
  </section>

  <?php if ($gal): ?>
  <section class="bolum" id="galeri">
    <div class="kap">
      <div class="bolum-basi"><h2>Sahadan</h2></div>
      <ul class="galeri">
        <?php foreach ($gal as $g): ?>
        <li><figure style="margin:0">
          <img src="<?= PANEL . UPLOAD_URL . e($g['dosya']) ?>" alt="<?= e($g['aciklama'] ?: 'Kuyu temel uygulaması') ?>" loading="lazy">
          <?php if ($g['aciklama']): ?><figcaption><?= e($g['aciklama']) ?></figcaption><?php endif; ?>
        </figure></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
  <?php endif; ?>

  <!-- REFERANSLAR -->
  <section class="bolum" id="referanslar">
    <div class="kap">
      <div class="bolum-basi"><h2>Referanslar</h2></div>
      <p class="giris">Anadolu Yakası ağırlıklı olmak üzere kuyularını açtığımız işlerden bir bölümü.</p>
      <div class="isler-sar">
        <table class="isler">
          <caption>İşveren adları sözleşme gereği paylaşılmamaktadır.</caption>
          <thead><tr><th scope="col">Proje</th><th scope="col">Yöntem</th><th scope="col">Adet</th><th scope="col">Derinlik</th><th scope="col">Yıl</th></tr></thead>
          <tbody>
          <?php foreach ($ref as $r): ?>
            <tr>
              <td><?= e(trim($r['ilce'] ? $r['ilce'] . ', ' . $r['proje'] : $r['proje'])) ?></td>
              <td data-b="Yöntem:"><?= e($r['yontem']) ?></td>
              <td class="sayi" data-b="Adet:"><?= e($r['adet']) ?></td>
              <td class="sayi" data-b="Derinlik:"><?= e($r['derinlik']) ?></td>
              <td class="sayi" data-b="Yıl:"><?= e($r['yil']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- SIK SORULANLAR -->
  <section class="bolum" id="sorular">
    <div class="kap">
      <div class="bolum-basi"><h2>Sık sorulanlar</h2></div>
      <div class="sss">
        <?php foreach ($sss as $i => $s): ?>
        <details<?= $i === 0 ? ' open' : '' ?>>
          <summary><?= e($s['soru']) ?></summary>
          <p><?= e($s['cevap']) ?></p>
        </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- İLETİŞİM -->
  <section class="bolum iletisim" id="iletisim">
    <div class="kap">
      <div class="bolum-basi"><span class="kot">±0.00</span><h2>Keşif ücretsiz</h2></div>
      <div class="ile-grid">
        <div>
          <p class="giris" style="margin-top:0">Parselin adresini ve varsa temel aplikasyon planını gönderin; aynı gün içinde geri dönüp keşif için gün verelim. Kuyu adedi ve derinliği belliyse teklifi telefonda da verebiliriz.</p>
          <a class="kanal" href="<?= e($tel_link) ?>"><small>Telefon</small><strong><?= e($a['telefon']) ?></strong></a>
          <a class="kanal" href="<?= e($wa_link) ?>"><small>WhatsApp, proje dosyası gönderin</small><strong><?= e($a['whatsapp']) ?></strong></a>
          <a class="kanal" href="mailto:<?= e($a['eposta']) ?>"><small>E-posta</small><strong><?= e($a['eposta']) ?></strong></a>
        </div>
        <div>
          <h3 style="font-size:1.25rem;margin-bottom:.5rem">Ofis</h3>
          <address class="adres">
            <b><?= e($a['firma_ad']) ?></b><br>
            <?= e($a['adres']) ?><br>
            <?= e(trim($a['posta_kodu'] . ' ' . $konum)) ?>
          </address>
          <h3 style="font-size:1.25rem;margin:1.5rem 0 .5rem">Çalışma saatleri</h3>
          <p class="adres" style="margin:0"><?= implode('<br>', array_map('e', satirlar($a['saatler']))) ?></p>
          <div class="harita">
            <?php if (trim($a['harita_embed'])): ?>
              <?= $a['harita_embed'] ?>
            <?php else:
              $lat = (float)$a['harita_lat']; $lng = (float)$a['harita_lng']; $d = 0.006;
              $bbox = ($lng - $d) . ',' . ($lat - $d/2) . ',' . ($lng + $d) . ',' . ($lat + $d/2); ?>
              <iframe title="<?= e($a['firma_ad']) ?> konumu" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                src="https://www.openstreetmap.org/export/embed.html?bbox=<?= e($bbox) ?>&layer=mapnik&marker=<?= e($lat . ',' . $lng) ?>"></iframe>
            <?php endif; ?>
          </div>
          <?php if ($a['maps_link']): ?><p style="margin:.7rem 0 0"><a class="btn" href="<?= e($a['maps_link']) ?>" target="_blank" rel="noopener">Yol tarifi al</a></p><?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</main>

<footer class="alt">
  <div class="kap alt-ic">
    <span>&copy; <?= date('Y') ?> <?= e($a['firma_ad']) ?>, <?= e($konum) ?></span>
    <a href="<?= PANEL ?>login.php">Saha Takip Girişi</a>
    <span class="alt-son">Kuyu temel kazısı, ahşap iksa ve kenar açma işleri. İstanbul Anadolu Yakası genelinde hizmet.</span>
  </div>
</footer>

<script>
// Mobil menü
(function () {
  var dugme = document.getElementById('hambDugme'), menu = document.getElementById('menu');
  if (!dugme || !menu) return;
  function ayarla(acik) {
    menu.classList.toggle('acik', acik);
    dugme.setAttribute('aria-expanded', acik ? 'true' : 'false');
    dugme.setAttribute('aria-label', acik ? 'Menüyü kapat' : 'Menüyü aç');
  }
  dugme.addEventListener('click', function () { ayarla(!menu.classList.contains('acik')); });
  menu.addEventListener('click', function (o) { if (o.target.tagName === 'A') ayarla(false); });
  document.addEventListener('keydown', function (o) {
    if (o.key === 'Escape' && menu.classList.contains('acik')) { ayarla(false); dugme.focus(); }
  });
})();

(function () {
  var okuma = document.getElementById('kot');
  if (!okuma) return;
  var bekliyor = false;
  function guncelle() {
    var h = document.documentElement.scrollHeight - window.innerHeight;
    var o = h > 0 ? Math.min(1, Math.max(0, window.scrollY / h)) : 0;
    var d = o * 30;
    okuma.textContent = d < 0.05 ? '±0.00' : '-' + d.toFixed(2);
    bekliyor = false;
  }
  window.addEventListener('scroll', function () {
    if (!bekliyor) { bekliyor = true; requestAnimationFrame(guncelle); }
  }, { passive: true });
  guncelle();
})();
</script>
</body>
</html>
