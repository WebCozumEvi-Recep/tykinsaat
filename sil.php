<?php
require_once __DIR__ . '/inc/auth.php'; login_gerekli();
$izin = ['giderler'=>'finans','tahsilatlar'=>'finans','tedarikci_odemeleri'=>'finans','personel_odeme'=>'finans','personel'=>'patron','tedarikciler'=>'patron','makineler'=>'finans','makine_kiralama'=>'finans','is_gunlugu'=>'saha','santiyeler'=>'patron','musteriler'=>'patron'];
$t = get('t'); $id = (int)get('id'); $geri = get('geri', 'index.php');
if (!isset($izin[$t])) exit('Geçersiz.');
if ($izin[$t] === 'patron') patron_gerekli(); elseif ($izin[$t] === 'finans') finans_gerekli();
if ($t === 'is_gunlugu' && !finans_gorur()) { $sid = (int)val("SELECT santiye_id FROM is_gunlugu WHERE id=?", [$id]); santiye_erisim($sid); }
if ($t === 'musteriler' && val("SELECT COUNT(*) FROM santiyeler WHERE musteri_id=?", [$id])) { flash('Bu müşteriye bağlı şantiye var, silinemez.', 'hata'); redirect($geri); }
if (in_array($t, ['tahsilatlar','tedarikci_odemeleri'])) { $cid = val("SELECT cek_id FROM $t WHERE id=?", [$id]); if ($cid) q("DELETE FROM cekler WHERE id=?", [$cid]); }
q("DELETE FROM $t WHERE id=?", [$id]);
flash('Kayıt silindi.'); redirect($geri);
