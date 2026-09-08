<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli(); patron_gerekli();
require_once __DIR__ . '/inc/site.php';

$sekme = get('s', 'genel');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $i = post('islem');
    if ($i === 'genel') {
        // Ad, adres, telefon ve logo firma kaydında tutulur; panelin Ayarlar
        // sayfasıyla ortaktır, iki yerde ayrı ayrı girilmez.
        $f = firma_bilgi();
        $logo = $f['logo'] ?? null;
        if ($l = foto_yukle('logo')) $logo = $l[0];
        q("UPDATE firma SET ad=?, adres=?, telefon=?, logo=? WHERE id=1",
          [post('firma_ad'), post('adres'), post('telefon'), $logo]);
        site_kaydet([
            'slogan'=>post('slogan'),
            'hero_baslik'=>post('hero_baslik'), 'hero_metin'=>post('hero_metin'),
            'whatsapp'=>post('whatsapp'), 'whatsapp_link'=>preg_replace('/[^0-9]/','',post('whatsapp_link')),
            'eposta'=>post('eposta'), 'ilce'=>post('ilce'), 'sehir'=>post('sehir'),
            'posta_kodu'=>post('posta_kodu'), 'saatler'=>post('saatler'),
            'yil_sayisi'=>post('yil_sayisi'), 'derinlik'=>post('derinlik'),
            'kuyu_sayisi'=>post('kuyu_sayisi'), 'ekip_sayisi'=>post('ekip_sayisi'),
        ]);
        flash('Site bilgileri güncellendi.');
    }
    if ($i === 'seo') {
        site_kaydet([
            'seo_baslik'=>post('seo_baslik'), 'seo_aciklama'=>post('seo_aciklama'), 'seo_anahtar'=>post('seo_anahtar'),
            'site_url'=>rtrim(post('site_url'), '/'), 'og_gorsel'=>post('og_gorsel'),
            'dogrulama_google'=>post('dogrulama_google'), 'dogrulama_bing'=>post('dogrulama_bing'),
            'dogrulama_yandex'=>post('dogrulama_yandex'), 'analytics_id'=>post('analytics_id'),
            'gtm_id'=>post('gtm_id'), 'ekstra_head'=>post('ekstra_head'),
        ]);
        flash('SEO ayarları güncellendi.');
    }
    if ($i === 'harita') {
        site_kaydet(['harita_lat'=>post('harita_lat'), 'harita_lng'=>post('harita_lng'),
                     'harita_zoom'=>post('harita_zoom'), 'harita_embed'=>post('harita_embed'), 'maps_link'=>post('maps_link')]);
        flash('Harita ayarları güncellendi.');
    }
    if ($i === 'hizmet_kaydet') {
        $p = [post('baslik'), post('olcu'), post('ozet'), post('maddeler'), post('slug'), (int)post('sira'), (int)!!post('aktif')];
        if ($id = (int)post('id')) { q("UPDATE site_hizmet SET baslik=?,olcu=?,ozet=?,maddeler=?,slug=?,sira=?,aktif=? WHERE id=?", array_merge($p,[$id])); flash('Hizmet güncellendi.'); }
        else { q("INSERT INTO site_hizmet (baslik,olcu,ozet,maddeler,slug,sira,aktif) VALUES (?,?,?,?,?,?,?)", $p); flash('Hizmet eklendi.'); }
    }
    if ($i === 'hizmet_sil') { q("DELETE FROM site_hizmet WHERE id=?", [(int)post('id')]); flash('Hizmet silindi.'); }
    if ($i === 'ref_kaydet') {
        $p = [post('proje'), post('ilce'), post('yontem'), post('adet'), post('derinlik'), post('yil'), (int)post('sira'), (int)!!post('aktif')];
        if ($id = (int)post('id')) { q("UPDATE site_referans SET proje=?,ilce=?,yontem=?,adet=?,derinlik=?,yil=?,sira=?,aktif=? WHERE id=?", array_merge($p,[$id])); flash('Referans güncellendi.'); }
        else { q("INSERT INTO site_referans (proje,ilce,yontem,adet,derinlik,yil,sira,aktif) VALUES (?,?,?,?,?,?,?,?)", $p); flash('Referans eklendi.'); }
    }
    if ($i === 'ref_sil') { q("DELETE FROM site_referans WHERE id=?", [(int)post('id')]); flash('Referans silindi.'); }
    if ($i === 'sss_kaydet') {
        $p = [post('soru'), post('cevap'), (int)post('sira'), (int)!!post('aktif')];
        if ($id = (int)post('id')) { q("UPDATE site_sss SET soru=?,cevap=?,sira=?,aktif=? WHERE id=?", array_merge($p,[$id])); flash('Soru güncellendi.'); }
        else { q("INSERT INTO site_sss (soru,cevap,sira,aktif) VALUES (?,?,?,?)", $p); flash('Soru eklendi.'); }
    }
    if ($i === 'sss_sil') { q("DELETE FROM site_sss WHERE id=?", [(int)post('id')]); flash('Soru silindi.'); }
    if ($i === 'galeri_ekle') {
        foreach (foto_yukle('foto') as $f) q("INSERT INTO site_galeri (dosya, aciklama, sira) VALUES (?,?,0)", [$f, post('aciklama')]);
        flash('Fotoğraflar yüklendi.');
    }
    if ($i === 'galeri_sil') {
        $g = row("SELECT * FROM site_galeri WHERE id=?", [(int)post('id')]);
        if ($g && is_file(UPLOAD_DIR . $g['dosya'])) @unlink(UPLOAD_DIR . $g['dosya']);
        q("DELETE FROM site_galeri WHERE id=?", [(int)post('id')]);
        flash('Fotoğraf silindi.');
    }
  } catch (PDOException $ex) {
    // Tablolar henüz kurulmamışsa kullanıcıya hata sayfası yerine açıklama göster
    flash('Kayıt yapılamadı. Site tabloları kurulu değil; sunucuda site_schema.sql dosyasını veritabanına aktarın.', 'hata');
  }
  redirect('site_yonetimi.php?s=' . urlencode(post('s', 'genel')));
}

$a = site_ayarlar();
$sekmeler = ['genel'=>['storefront','Genel ve iletişim'], 'seo'=>['magnifying-glass','SEO ve doğrulama'],
             'harita'=>['map-pin','Harita'], 'hizmetler'=>['toolbox','Hizmetler'],
             'referanslar'=>['crane-tower','Referanslar'], 'sss'=>['notebook','Sık sorulanlar'], 'galeri'=>['image-square','Galeri']];
$baslik = 'Site Yönetimi'; include 'inc/layout_top.php';

/** Tabloların kurulup kurulmadığını kontrol et */
$kurulu = true;
try { val("SELECT 1 FROM site_ayar LIMIT 1"); } catch (Throwable $ex) { $kurulu = false; }
?>
<?php if (!$kurulu): ?>
<div class="flash flash-hata" style="margin-bottom:1rem"><?= ikon('warning-circle') ?>
  Site tabloları henüz kurulmamış. Sunucuda <code>site_schema.sql</code> dosyasını veritabanına aktarın; o zamana kadar site varsayılan içeriklerle yayında olur.
</div>
<?php endif; ?>

<nav class="sekmeler" style="display:flex;gap:.4rem;overflow-x:auto;padding-bottom:.5rem;margin-bottom:1rem">
<?php foreach ($sekmeler as $k => [$ik, $ad]): ?>
  <a class="btn btn-kucuk <?= $sekme===$k ? 'btn-turuncu' : 'btn-gri' ?>" href="?s=<?= $k ?>" style="white-space:nowrap"><?= ikon($ik) ?> <?= $ad ?></a>
<?php endforeach; ?>
</nav>

<?php if ($sekme === 'genel'): ?>
<form method="post" enctype="multipart/form-data" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="genel"><input type="hidden" name="s" value="genel">
  <h2 style="margin-top:0">Firma kimliği</h2>
  <p><small>Bu dört alan panelin Ayarlar sayfasıyla ortaktır. Buradan değiştirdiğinizde panelde ve sitede birlikte güncellenir; iki yere ayrı ayrı girmenize gerek yok.</small></p>
  <div class="satir-form"><label class="alan"><span>Firma adı</span><input type="text" name="firma_ad" value="<?= e($a['firma_ad']) ?>"></label>
  <label class="alan"><span>Telefon</span><input type="tel" name="telefon" value="<?= e($a['telefon']) ?>" placeholder="0216 000 00 00">
    <small>Arama bağlantısı buradan otomatik üretilir: <?= e(tel_link($a['telefon'])) ?></small></label></div>
  <label class="alan"><span>Adres</span><textarea name="adres" rows="2"><?= e($a['adres']) ?></textarea></label>
  <label class="foto-alan"><?= ikon('image-square') ?> Logo yükle (PNG/JPG)<input type="file" name="logo[]" accept="image/*"></label>
  <div class="onizleme"><?php if (!empty($a['logo'])): ?><img src="<?= UPLOAD_URL . e($a['logo']) ?>" alt="Firma logosu"><?php endif; ?></div>

  <h2>Ana sayfa</h2>
  <label class="alan"><span>Slogan</span><input type="text" name="slogan" value="<?= e($a['slogan']) ?>"></label>
  <label class="alan"><span>Ana başlık</span><input type="text" name="hero_baslik" value="<?= e($a['hero_baslik']) ?>"></label>
  <label class="alan"><span>Ana başlık altındaki metin</span><textarea name="hero_metin" rows="3"><?= e($a['hero_metin']) ?></textarea></label>

  <h2>Rakamlar</h2>
  <div class="satir-form"><label class="alan"><span>Tecrübe (yıl)</span><input type="text" name="yil_sayisi" value="<?= e($a['yil_sayisi']) ?>"></label>
  <label class="alan"><span>Azami derinlik</span><input type="text" name="derinlik" value="<?= e($a['derinlik']) ?>"></label></div>
  <div class="satir-form"><label class="alan"><span>Açılan kuyu</span><input type="text" name="kuyu_sayisi" value="<?= e($a['kuyu_sayisi']) ?>"></label>
  <label class="alan"><span>Ekip sayısı</span><input type="text" name="ekip_sayisi" value="<?= e($a['ekip_sayisi']) ?>"></label></div>

  <h2>Diğer iletişim kanalları</h2>
  <div class="satir-form"><label class="alan"><span>WhatsApp (görünen)</span><input type="text" name="whatsapp" value="<?= e($a['whatsapp']) ?>"></label>
  <label class="alan"><span>WhatsApp (wa.me numarası)</span><input type="text" name="whatsapp_link" value="<?= e($a['whatsapp_link']) ?>" placeholder="905000000000"></label></div>
  <label class="alan"><span>E-posta</span><input type="email" name="eposta" value="<?= e($a['eposta']) ?>"></label>
  <div class="satir-form"><label class="alan"><span>İlçe</span><input type="text" name="ilce" value="<?= e($a['ilce']) ?>"></label>
  <label class="alan"><span>Şehir</span><input type="text" name="sehir" value="<?= e($a['sehir']) ?>"></label>
  <label class="alan"><span>Posta kodu</span><input type="text" name="posta_kodu" value="<?= e($a['posta_kodu']) ?>"></label></div>
  <label class="alan"><span>Çalışma saatleri (her satır ayrı gösterilir)</span><textarea name="saatler" rows="3"><?= e($a['saatler']) ?></textarea></label>
  <button class="btn btn-turuncu btn-blok">Kaydet</button>
</form>

<?php elseif ($sekme === 'seo'): ?>
<form method="post" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="seo"><input type="hidden" name="s" value="seo">
  <h2 style="margin-top:0">Arama motoru bilgileri</h2>
  <label class="alan"><span>Sayfa başlığı (title)</span><input type="text" name="seo_baslik" value="<?= e($a['seo_baslik']) ?>" maxlength="70">
    <small>Google'da mavi link olarak görünür. 55-60 karakter ideal.</small></label>
  <label class="alan"><span>Açıklama (description)</span><textarea name="seo_aciklama" rows="3" maxlength="170"><?= e($a['seo_aciklama']) ?></textarea>
    <small>Başlığın altındaki gri metin. 150-160 karakter ideal.</small></label>
  <label class="alan"><span>Anahtar kelimeler</span><input type="text" name="seo_anahtar" value="<?= e($a['seo_anahtar']) ?>"></label>
  <label class="alan"><span>Site adresi</span><input type="url" name="site_url" value="<?= e($a['site_url']) ?>" placeholder="https://www.tykinsaat.com">
    <small>Canonical adres, site haritası ve paylaşım kartları için gerekli.</small></label>
  <label class="alan"><span>Paylaşım görseli (tam adres)</span><input type="url" name="og_gorsel" value="<?= e($a['og_gorsel']) ?>" placeholder="https://.../uploads/kapak.jpg"></label>

  <h2>Site doğrulama</h2>
  <label class="alan"><span>Google Search Console doğrulama kodu</span><input type="text" name="dogrulama_google" value="<?= e($a['dogrulama_google']) ?>" placeholder="google-site-verification içeriği">
    <small>Search Console &gt; Mülk ekle &gt; HTML etiketi adımındaki <code>content="..."</code> değerini yapıştırın.</small></label>
  <label class="alan"><span>Bing Webmaster doğrulama kodu</span><input type="text" name="dogrulama_bing" value="<?= e($a['dogrulama_bing']) ?>"></label>
  <label class="alan"><span>Yandex doğrulama kodu</span><input type="text" name="dogrulama_yandex" value="<?= e($a['dogrulama_yandex']) ?>"></label>

  <h2>Ölçümleme</h2>
  <div class="satir-form"><label class="alan"><span>Google Analytics ID</span><input type="text" name="analytics_id" value="<?= e($a['analytics_id']) ?>" placeholder="G-XXXXXXX"></label>
  <label class="alan"><span>Google Tag Manager ID</span><input type="text" name="gtm_id" value="<?= e($a['gtm_id']) ?>" placeholder="GTM-XXXXXX"></label></div>
  <label class="alan"><span>Ek head kodu</span><textarea name="ekstra_head" rows="3"><?= e($a['ekstra_head']) ?></textarea>
    <small>Doğrulama etiketi, pixel gibi kodlar. Yalnızca güvendiğiniz kodu yapıştırın.</small></label>
  <button class="btn btn-turuncu btn-blok">Kaydet</button>
</form>
<div class="kart"><h2 style="margin-top:0">Site haritası</h2>
  <p>Search Console'a şu adresi gönderin:</p>
  <p><code><?= e($a['site_url'] ?: 'https://siteniz.com') ?>/site/sitemap.php</code></p>
  <p><small>robots.txt bu adresi otomatik bildirir. İçerik her değiştiğinde harita kendini günceller.</small></p>
</div>

<?php elseif ($sekme === 'harita'): ?>
<form method="post" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="harita"><input type="hidden" name="s" value="harita">
  <h2 style="margin-top:0">Konum</h2>
  <div class="satir-form"><label class="alan"><span>Enlem (lat)</span><input type="text" name="harita_lat" value="<?= e($a['harita_lat']) ?>"></label>
  <label class="alan"><span>Boylam (lng)</span><input type="text" name="harita_lng" value="<?= e($a['harita_lng']) ?>"></label>
  <label class="alan"><span>Yakınlaştırma</span><input type="number" name="harita_zoom" value="<?= e($a['harita_zoom']) ?>" min="10" max="19"></label></div>
  <p><small>Koordinatı Google Haritalar'da ofise sağ tıklayıp ilk satırdaki sayıları kopyalayarak alabilirsiniz.</small></p>
  <label class="alan"><span>Google Haritalar yol tarifi bağlantısı</span><input type="url" name="maps_link" value="<?= e($a['maps_link']) ?>" placeholder="https://maps.app.goo.gl/..."></label>
  <label class="alan"><span>Gömülü harita kodu (iframe)</span><textarea name="harita_embed" rows="4"><?= e($a['harita_embed']) ?></textarea>
    <small>Boş bırakırsanız site, koordinatlardan OpenStreetMap haritası gösterir. Google Haritalar'da "Paylaş &gt; Harita yerleştir" kodunu yapıştırırsanız o kullanılır.</small></label>
  <button class="btn btn-turuncu btn-blok">Kaydet</button>
</form>

<?php elseif ($sekme === 'hizmetler'): ?>
<?php $duzen = (int)get('id') ? row("SELECT * FROM site_hizmet WHERE id=?", [(int)get('id')]) : null; ?>
<form method="post" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="hizmet_kaydet"><input type="hidden" name="s" value="hizmetler">
  <input type="hidden" name="id" value="<?= (int)($duzen['id'] ?? 0) ?>">
  <h2 style="margin-top:0"><?= $duzen ? 'Hizmeti düzenle' : 'Yeni hizmet' ?></h2>
  <div class="satir-form"><label class="alan"><span>Başlık</span><input type="text" name="baslik" required value="<?= e($duzen['baslik'] ?? '') ?>"></label>
  <label class="alan"><span>Ölçü etiketi</span><input type="text" name="olcu" value="<?= e($duzen['olcu'] ?? '') ?>" placeholder="-5 / -30 m"></label></div>
  <label class="alan"><span>Açıklama</span><textarea name="ozet" rows="3"><?= e($duzen['ozet'] ?? '') ?></textarea></label>
  <label class="alan"><span>Maddeler (her satır bir madde)</span><textarea name="maddeler" rows="3"><?= e($duzen['maddeler'] ?? '') ?></textarea></label>
  <div class="satir-form"><label class="alan"><span>Bağlantı adı (slug)</span><input type="text" name="slug" value="<?= e($duzen['slug'] ?? '') ?>" placeholder="el-ile-kuyu-kazisi"></label>
  <label class="alan"><span>Sıra</span><input type="number" name="sira" value="<?= (int)($duzen['sira'] ?? 0) ?>"></label></div>
  <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem"><input type="checkbox" name="aktif" value="1" <?= ($duzen['aktif'] ?? 1) ? 'checked' : '' ?> style="width:22px;height:22px"> Sitede yayında</label>
  <button class="btn btn-turuncu btn-blok"><?= $duzen ? 'Güncelle' : 'Ekle' ?></button>
  <?php if ($duzen): ?><a class="btn btn-gri btn-blok" href="?s=hizmetler" style="margin-top:.5rem">Vazgeç</a><?php endif; ?>
</form>
<div class="kart liste">
<?php $hz = []; try { $hz = rows("SELECT * FROM site_hizmet ORDER BY sira, id"); } catch (Throwable $ex) {} ?>
<?php if (!$hz): ?><p style="padding:1rem 0"><small>Henüz hizmet eklenmemiş. Site şimdilik varsayılan dört hizmeti gösteriyor.</small></p><?php endif; ?>
<?php foreach ($hz as $h): ?>
  <div class="satir"><a class="govde" href="?s=hizmetler&id=<?= $h['id'] ?>" style="text-decoration:none;color:inherit">
    <div class="ad"><?= e($h['baslik']) ?> <?= $h['aktif'] ? '' : etiket('Gizli','gri') ?></div><small><?= e($h['olcu']) ?></small></a>
    <form method="post" style="margin:0"><?= csrf_field() ?><input type="hidden" name="islem" value="hizmet_sil"><input type="hidden" name="s" value="hizmetler"><input type="hidden" name="id" value="<?= $h['id'] ?>">
      <button class="btn btn-gri btn-kucuk" data-onay="Hizmet silinsin mi?"><?= ikon('trash') ?></button></form>
  </div>
<?php endforeach; ?>
</div>

<?php elseif ($sekme === 'referanslar'): ?>
<?php $duzen = (int)get('id') ? row("SELECT * FROM site_referans WHERE id=?", [(int)get('id')]) : null; ?>
<form method="post" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="ref_kaydet"><input type="hidden" name="s" value="referanslar">
  <input type="hidden" name="id" value="<?= (int)($duzen['id'] ?? 0) ?>">
  <h2 style="margin-top:0"><?= $duzen ? 'Referansı düzenle' : 'Yeni referans' ?></h2>
  <div class="satir-form"><label class="alan"><span>Proje</span><input type="text" name="proje" required value="<?= e($duzen['proje'] ?? '') ?>" placeholder="9 katlı konut"></label>
  <label class="alan"><span>İlçe / semt</span><input type="text" name="ilce" value="<?= e($duzen['ilce'] ?? '') ?>" placeholder="Üsküdar, Bulgurlu"></label></div>
  <label class="alan"><span>Yöntem</span><input type="text" name="yontem" value="<?= e($duzen['yontem'] ?? '') ?>" placeholder="Makineli kuyu, kenar açma"></label>
  <div class="satir-form"><label class="alan"><span>Adet</span><input type="text" name="adet" value="<?= e($duzen['adet'] ?? '') ?>" placeholder="148 kuyu"></label>
  <label class="alan"><span>Derinlik</span><input type="text" name="derinlik" value="<?= e($duzen['derinlik'] ?? '') ?>" placeholder="22 m"></label>
  <label class="alan"><span>Yıl</span><input type="text" name="yil" value="<?= e($duzen['yil'] ?? '') ?>"></label>
  <label class="alan"><span>Sıra</span><input type="number" name="sira" value="<?= (int)($duzen['sira'] ?? 0) ?>"></label></div>
  <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem"><input type="checkbox" name="aktif" value="1" <?= ($duzen['aktif'] ?? 1) ? 'checked' : '' ?> style="width:22px;height:22px"> Sitede yayında</label>
  <button class="btn btn-turuncu btn-blok"><?= $duzen ? 'Güncelle' : 'Ekle' ?></button>
  <?php if ($duzen): ?><a class="btn btn-gri btn-blok" href="?s=referanslar" style="margin-top:.5rem">Vazgeç</a><?php endif; ?>
</form>
<div class="kart liste">
<?php $rf = []; try { $rf = rows("SELECT * FROM site_referans ORDER BY sira, id"); } catch (Throwable $ex) {} ?>
<?php if (!$rf): ?><p style="padding:1rem 0"><small>Henüz referans eklenmemiş. Site şimdilik örnek listeyi gösteriyor; kendi işlerinizi ekleyin.</small></p><?php endif; ?>
<?php foreach ($rf as $r): ?>
  <div class="satir"><a class="govde" href="?s=referanslar&id=<?= $r['id'] ?>" style="text-decoration:none;color:inherit">
    <div class="ad"><?= e($r['proje']) ?> <?= $r['aktif'] ? '' : etiket('Gizli','gri') ?></div>
    <small><?= e(trim($r['ilce'] . ' · ' . $r['adet'] . ' · ' . $r['derinlik'] . ' · ' . $r['yil'], ' ·')) ?></small></a>
    <form method="post" style="margin:0"><?= csrf_field() ?><input type="hidden" name="islem" value="ref_sil"><input type="hidden" name="s" value="referanslar"><input type="hidden" name="id" value="<?= $r['id'] ?>">
      <button class="btn btn-gri btn-kucuk" data-onay="Referans silinsin mi?"><?= ikon('trash') ?></button></form>
  </div>
<?php endforeach; ?>
</div>

<?php elseif ($sekme === 'sss'): ?>
<?php $duzen = (int)get('id') ? row("SELECT * FROM site_sss WHERE id=?", [(int)get('id')]) : null; ?>
<form method="post" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="sss_kaydet"><input type="hidden" name="s" value="sss">
  <input type="hidden" name="id" value="<?= (int)($duzen['id'] ?? 0) ?>">
  <h2 style="margin-top:0"><?= $duzen ? 'Soruyu düzenle' : 'Yeni soru' ?></h2>
  <p><small>Bu bölüm Google'a yapılandırılmış veri (FAQ) olarak gönderilir; arama sonucunda soru-cevap olarak görünebilir.</small></p>
  <label class="alan"><span>Soru</span><input type="text" name="soru" required value="<?= e($duzen['soru'] ?? '') ?>"></label>
  <label class="alan"><span>Cevap</span><textarea name="cevap" rows="4" required><?= e($duzen['cevap'] ?? '') ?></textarea></label>
  <div class="satir-form"><label class="alan"><span>Sıra</span><input type="number" name="sira" value="<?= (int)($duzen['sira'] ?? 0) ?>"></label></div>
  <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem"><input type="checkbox" name="aktif" value="1" <?= ($duzen['aktif'] ?? 1) ? 'checked' : '' ?> style="width:22px;height:22px"> Sitede yayında</label>
  <button class="btn btn-turuncu btn-blok"><?= $duzen ? 'Güncelle' : 'Ekle' ?></button>
  <?php if ($duzen): ?><a class="btn btn-gri btn-blok" href="?s=sss" style="margin-top:.5rem">Vazgeç</a><?php endif; ?>
</form>
<div class="kart liste">
<?php $sr = []; try { $sr = rows("SELECT * FROM site_sss ORDER BY sira, id"); } catch (Throwable $ex) {} ?>
<?php if (!$sr): ?><p style="padding:1rem 0"><small>Henüz soru eklenmemiş. Site şimdilik varsayılan altı soruyu gösteriyor.</small></p><?php endif; ?>
<?php foreach ($sr as $s): ?>
  <div class="satir"><a class="govde" href="?s=sss&id=<?= $s['id'] ?>" style="text-decoration:none;color:inherit">
    <div class="ad"><?= e($s['soru']) ?> <?= $s['aktif'] ? '' : etiket('Gizli','gri') ?></div></a>
    <form method="post" style="margin:0"><?= csrf_field() ?><input type="hidden" name="islem" value="sss_sil"><input type="hidden" name="s" value="sss"><input type="hidden" name="id" value="<?= $s['id'] ?>">
      <button class="btn btn-gri btn-kucuk" data-onay="Soru silinsin mi?"><?= ikon('trash') ?></button></form>
  </div>
<?php endforeach; ?>
</div>

<?php else: ?>
<form method="post" enctype="multipart/form-data" class="kart"><?= csrf_field() ?><input type="hidden" name="islem" value="galeri_ekle"><input type="hidden" name="s" value="galeri">
  <h2 style="margin-top:0">Saha fotoğrafı yükle</h2>
  <p><small>Kuyu içi, tahkimat ve teslim fotoğrafları sitede en ikna edici bölümdür. Yatay çekilmiş fotoğraflar daha iyi durur.</small></p>
  <label class="alan"><span>Açıklama (isteğe bağlı)</span><input type="text" name="aciklama" placeholder="Üsküdar, 18 m kuyu"></label>
  <label class="foto-alan"><?= ikon('image-square') ?> Fotoğraf seç<input type="file" name="foto[]" accept="image/*" multiple></label>
  <button class="btn btn-turuncu btn-blok" style="margin-top:1rem">Yükle</button>
</form>
<?php $gl = site_galeri(); ?>
<?php if (!$gl): ?><div class="kart"><p style="margin:0"><small>Galeri boş. Fotoğraf yüklenene kadar sitede galeri bölümü gösterilmez.</small></p></div><?php endif; ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.6rem">
<?php foreach ($gl as $g): ?>
  <div class="kart" style="padding:.5rem">
    <img src="<?= UPLOAD_URL . e($g['dosya']) ?>" alt="" style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:8px">
    <small style="display:block;margin:.35rem 0"><?= e($g['aciklama']) ?></small>
    <form method="post" style="margin:0"><?= csrf_field() ?><input type="hidden" name="islem" value="galeri_sil"><input type="hidden" name="s" value="galeri"><input type="hidden" name="id" value="<?= $g['id'] ?>">
      <button class="btn btn-gri btn-kucuk btn-blok" data-onay="Fotoğraf silinsin mi?"><?= ikon('trash') ?> Sil</button></form>
  </div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include 'inc/layout_bottom.php';
