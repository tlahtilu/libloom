<?php
// =====================================================================
// cover_asli.php - COVER BUKU ASLI untuk Libloom (dipakai bersama)
// Simpan di folder utama libloom/ (sejajar dengan index.php & hasil.php).
// Dipakai oleh: koleksiopac.php, hasil.php, opac.php
//
// 1) COVER MANUAL: taruh gambar di folder assets/covers/ (atau covers/).
//    Nama file boleh salah satu dari ini (ekstensi jpg, jpeg, png, webp):
//      <id>.jpg           contoh: 12.jpg
//      <judul-buku>.jpg   contoh: bumi-manusia.jpg  (huruf kecil, spasi jadi tanda -)
//      <isbn>.jpg         contoh: 9786021234567.jpg
//    Daftar nama file tiap buku: buka koleksiopac.php?cvnama
// 2) COVER OTOMATIS: kalau tidak ada file manual, browser mencari di
//    Open Library (ISBN) lalu Google Books (ISBN, lalu judul+pengarang).
//    Hasilnya disimpan di localStorage supaya tidak dicari ulang.
// 3) Kalau tidak ketemu, cover bawaan dari config.php tetap tampil.
// =====================================================================

// folder tempat cover manual (urutan dicari)
function cv_folders(){ return array('assets/covers','covers'); }

// "Bumi Manusia" -> "bumi-manusia"
function cv_slug($t){
 $t=strtolower(trim((string)$t));
 $t=preg_replace('/[^a-z0-9]+/','-',$t);
 return trim($t,'-');
}
function cv_isbn($b){
 $isbn=isset($b['isbn'])?preg_replace('/[^0-9Xx]/','',(string)$b['isbn']):'';
 return (strlen($isbn)===10 || strlen($isbn)===13)?$isbn:'';
}
// daftar nama file (tanpa ekstensi) yang dikenali untuk satu buku
function cv_nama($b){
 $n=array((string)(int)$b['id']);
 $sl=cv_slug(isset($b['judul'])?$b['judul']:''); if($sl!=='') $n[]=$sl;
 $is=cv_isbn($b); if($is!=='') $n[]=$is;
 return $n;
}
// alamat file cover manual kalau ada, kalau tidak '' (relatif terhadap folder utama)
function cv_file($b){
 foreach(cv_nama($b) as $nm){ foreach(cv_folders() as $fd){ foreach(array('jpg','jpeg','png','webp') as $x){
  $f=__DIR__.'/'.$fd.'/'.$nm.'.'.$x;
  if(is_file($f)) return $fd.'/'.$nm.'.'.$x.'?v='.filemtime($f);
 } } }
 return '';
}

// atribut data untuk elemen pembungkus (dipakai oleh cv_script)
function cv_attr($b){
 return ' data-cv-id="'.(int)$b['id'].'" data-cv-src="'.h(cv_file($b)).'" data-cv-isbn="'.h(cv_isbn($b)).'" data-cv-t="'.h(isset($b['judul'])?$b['judul']:'').'" data-cv-a="'.h(isset($b['pengarang'])?$b['pengarang']:'').'"';
}

// cover bawaan + pembungkus (ganti cover($judul,$id) dengan cv_wrap($b))
function cv_wrap($b){
 return '<div class="cvw"'.cv_attr($b).'>'.cover($b['judul'],$b['id']).'</div>';
}

// panggil echo cv_script() sekali saja, tepat sebelum penutup body
function cv_script(){
 static $sudah=false; if($sudah) return ''; $sudah=true;
 return <<<'CVJS'
<style>
.cvw{display:contents}
.cv.has-img{background-color:#EFE2C4;background-repeat:no-repeat}
.cv.has-img>*{visibility:hidden}
</style>
<script>
(function(){
 var KEY='libloom_cov_v2',HARI=864e5,cache={},
     DBG=/[?&]cvdebug/.test(location.search);
 function log(){ if(DBG&&window.console)console.log.apply(console,['[cover]'].concat([].slice.call(arguments))); }
 if(/[?&]cvreset/.test(location.search)){ try{localStorage.removeItem(KEY);localStorage.removeItem('libloom_cov_v1');}catch(e){} }
 try{cache=JSON.parse(localStorage.getItem(KEY)||'{}')||{};}catch(e){cache={};}
 function simpan(){try{localStorage.setItem(KEY,JSON.stringify(cache));}catch(e){}}

 var grup={};
 [].forEach.call(document.querySelectorAll('[data-cv-id]'),function(w){
  var id=w.getAttribute('data-cv-id'),cv=w.querySelector('.cv'); if(!cv)return;
  if(!grup[id])grup[id]={id:id,src:w.getAttribute('data-cv-src')||'',isbn:w.getAttribute('data-cv-isbn')||'',t:w.getAttribute('data-cv-t')||'',a:w.getAttribute('data-cv-a')||'',els:[]};
  grup[id].els.push(cv);
 });

 function pasang(g,url){
  g.els.forEach(function(cv){
   cv.style.backgroundImage='url("'+url+'")';
   cv.style.backgroundSize='cover';
   cv.style.backgroundPosition='center';
   cv.classList.add('has-img');
  });
 }
 function muat(url){
  return new Promise(function(ok,no){
   var im=new Image();
   im.onload=function(){(im.naturalWidth>50&&im.naturalHeight>50)?ok(url):no();};
   im.onerror=no; im.src=url;
  });
 }
 function mirip(q,t){
  var qw=q.toLowerCase().split(/[^a-z0-9]+/).filter(function(x){return x.length>2;});
  if(!qw.length)return true;
  var tl=(t||'').toLowerCase(),c=0;
  qw.forEach(function(w){if(tl.indexOf(w)>-1)c++;});
  return c/qw.length>=.6;
 }
 function google(g,pakaiIsbn){
  var q=pakaiIsbn?'isbn:'+g.isbn:'intitle:'+encodeURIComponent(g.t)+(g.a?'+inauthor:'+encodeURIComponent(g.a):'');
  return fetch('https://www.googleapis.com/books/v1/volumes?maxResults=5&fields=items(volumeInfo(title,imageLinks))&q='+q)
   .then(function(r){ if(r.status===429)throw 'rate'; if(!r.ok)throw 0; return r.json(); })
   .then(function(d){
    var it=d.items||[];
    for(var i=0;i<it.length;i++){
     var v=it[i].volumeInfo||{},l=v.imageLinks||{},u=l.thumbnail||l.smallThumbnail;
     if(u&&(pakaiIsbn||mirip(g.t,v.title))){
      return muat(u.replace(/^http:/,'https:').replace('&edge=curl',''));
     }
    }
    throw 0;
   });
 }
 function olJudul(g){
  return fetch('https://openlibrary.org/search.json?limit=5&fields=title,cover_i&title='+encodeURIComponent(g.t)+(g.a?'&author='+encodeURIComponent(g.a):''))
   .then(function(r){ if(r.status===429)throw 'rate'; if(!r.ok)throw 0; return r.json(); })
   .then(function(d){
    var it=d.docs||[];
    for(var i=0;i<it.length;i++){
     if(it[i].cover_i&&mirip(g.t,it[i].title)){
      return muat('https://covers.openlibrary.org/b/id/'+it[i].cover_i+'-M.jpg?default=false');
     }
    }
    throw 0;
   });
 }
 function googleJudul(g){
  return fetch('https://www.googleapis.com/books/v1/volumes?maxResults=5&fields=items(volumeInfo(title,imageLinks))&q=intitle:'+encodeURIComponent(g.t))
   .then(function(r){ if(r.status===429)throw 'rate'; if(!r.ok)throw 0; return r.json(); })
   .then(function(d){
    var it=d.items||[];
    for(var i=0;i<it.length;i++){
     var v=it[i].volumeInfo||{},l=v.imageLinks||{},u=l.thumbnail||l.smallThumbnail;
     if(u&&mirip(g.t,v.title)){ return muat(u.replace(/^http:/,'https:').replace('&edge=curl','')); }
    }
    throw 0;
   });
 }
 // urutan: ISBN (Open Library, Google) -> judul+pengarang (Google, Open Library) -> judul saja (Google)
 function cari(g){
  var langkah=[];
  if(g.isbn){
   langkah.push(['OL isbn',function(){return muat('https://covers.openlibrary.org/b/isbn/'+g.isbn+'-M.jpg?default=false');}]);
   langkah.push(['Google isbn',function(){return google(g,true);}]);
  }
  langkah.push(['Google judul+pengarang',function(){return google(g,false);}]);
  langkah.push(['OL judul',function(){return olJudul(g);}]);
  langkah.push(['Google judul',function(){return googleJudul(g);}]);
  var p=Promise.reject(0);
  langkah.forEach(function(l){
   p=p.catch(function(e){
    if(e==='rate')throw e;
    return l[1]().then(function(u){log(g.t,'ketemu via',l[0]);return u;},function(er){log(g.t,'gagal:',l[0],er);throw er;});
   });
  });
  return p;
 }

 var antre=[],jalan=0;
 function pompa(){
  while(jalan<3&&antre.length){
   (function(g){
    jalan++;
    cari(g).then(function(u){ cache[g.id]={u:u,t:Date.now()}; pasang(g,u); })
     .catch(function(e){ if(e!=='rate')cache[g.id]={u:'',t:Date.now()}; })
     .then(function(){ jalan--; simpan(); pompa(); });
   })(antre.shift());
  }
 }
 function minta(g){ if(g.sudah)return; g.sudah=true; antre.push(g); pompa(); }

 var io=('IntersectionObserver' in window)?new IntersectionObserver(function(es){
  es.forEach(function(e){ if(e.isIntersecting){ io.unobserve(e.target); minta(e.target.__g); } });
 },{rootMargin:'200px'}):null;

 Object.keys(grup).forEach(function(id){
  var g=grup[id],c=cache[id];
  if(g.src){ pasang(g,g.src); return; }
  if(c&&c.u){ pasang(g,c.u); return; }
  if(c&&!c.u&&Date.now()-c.t<3*HARI)return;
  if(io){ g.els.forEach(function(el){ el.__g=g; io.observe(el); }); }
  else minta(g);
 });
})();
</script>
CVJS;
}