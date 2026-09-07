function menuAc(){document.getElementById('menu').classList.add('acik')}
function menuKapat(){document.getElementById('menu').classList.remove('acik')}
// Çevrimdışı durum
function netGuncelle(){var el=document.getElementById('netDurum');if(!el)return;if(navigator.onLine){el.textContent='● Bağlı';el.classList.remove('offline')}else{el.textContent='● Çevrimdışı';el.classList.add('offline')}}
window.addEventListener('online',netGuncelle);window.addEventListener('offline',netGuncelle);netGuncelle();
// Form taslağı: kaybolmasın (localStorage)
document.querySelectorAll('form[data-taslak]').forEach(function(f){
  var key='taslak:'+f.dataset.taslak;
  try{var t=JSON.parse(localStorage.getItem(key)||'null');if(t){Object.keys(t).forEach(function(n){var el=f.elements[n];if(!el||el.type==='file'||n==='csrf')return;if(el.length&&el[0]&&el[0].type==='radio'){for(var i=0;i<el.length;i++)el[i].checked=(el[i].value===t[n])}else if(el.type==='checkbox')el.checked=!!t[n];else el.value=t[n]});}}catch(e){}
  f.addEventListener('input',function(){var o={};new FormData(f).forEach(function(v,k){if(typeof v==='string')o[k]=v});try{localStorage.setItem(key,JSON.stringify(o))}catch(e){}});
  f.addEventListener('submit',function(){try{localStorage.removeItem(key)}catch(e){}});
});
// Tutar formatı (binlik ayraç)
document.querySelectorAll('input.tutar-input').forEach(function(i){
  function fmt(){var v=i.value.replace(/[^\d,]/g,'');var p=v.split(',');p[0]=p[0].replace(/\B(?=(\d{3})+(?!\d))/g,'.');i.value=p.slice(0,2).join(',')}
  i.addEventListener('input',fmt);if(i.value)fmt();
});
// Tarih hızlı butonları
document.querySelectorAll('[data-tarih]').forEach(function(b){b.addEventListener('click',function(){var t=document.querySelector(b.dataset.hedef||'input[type=date]');var d=new Date();if(b.dataset.tarih==='dun')d.setDate(d.getDate()-1);t.value=d.toISOString().slice(0,10);t.dispatchEvent(new Event('change'))})});
// Fotoğraf önizleme
document.querySelectorAll('.foto-alan input[type=file]').forEach(function(inp){inp.addEventListener('change',function(){var on=inp.closest('.foto-alan').nextElementSibling;on.innerHTML='';Array.from(inp.files).forEach(function(f){var img=document.createElement('img');img.src=URL.createObjectURL(f);on.appendChild(img)})})});
// Silme onayı
document.querySelectorAll('[data-onay]').forEach(function(a){a.addEventListener('click',function(e){if(!confirm(a.dataset.onay||'Silinsin mi?'))e.preventDefault()})});
// Puantaj: herkes tam gün + günlük tutar
function herkesTam(){document.querySelectorAll('.puan-satir input[value=tam]').forEach(function(r){r.checked=true;r.dispatchEvent(new Event('change'))})}
document.querySelectorAll('.puan-satir').forEach(function(s){var y=parseFloat(s.dataset.yevmiye||0);var out=s.querySelector('.gun-tutar');if(!out)return;function hesap(){var d=(s.querySelector('input[type=radio]:checked')||{}).value;var m=parseFloat((s.querySelector('.mesai')||{}).value||0);var t=(d==='tam'?y:d==='yarim'?y/2:0)+m*y/9;out.textContent='₺'+t.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2})}s.addEventListener('change',hesap);s.addEventListener('input',hesap);hesap()});
// Çek alanları göster/gizle
document.querySelectorAll('[data-cek-toggle]').forEach(function(sel){function t(){var c=document.getElementById('cekAlanlari');if(c)c.style.display=(sel.value==='cek')?'':'none'}sel.addEventListener('change',t);t()});
// Şantiye seçici: son kullanılan
document.querySelectorAll('select[name=santiye_id]').forEach(function(s){try{var son=localStorage.getItem('son_santiye');if(son&&!s.dataset.sabit&&!s.value)s.value=son}catch(e){}s.addEventListener('change',function(){try{localStorage.setItem('son_santiye',s.value)}catch(e){}})});
// Sekme şeridi: aktif sekmeyi görünür alana kaydır
document.querySelectorAll('.sekmeler').forEach(function(s){var a=s.querySelector('a.aktif');if(a)s.scrollLeft=Math.max(0,a.offsetLeft-16)});
