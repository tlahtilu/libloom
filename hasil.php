<?php
// =====================================================================
// hasil.php - HASIL PENCARIAN + DETAIL KOLEKSI (ala SLiMS) untuk Libloom
// Simpan di folder utama libloom/ (sejajar dengan index.php).
// File ini BERDIRI SENDIRI (CSS, header, footer ada di sini) dan hanya
// memakai q(), h(), cover(), $BQ dari config.php -> tidak mengubah file lain.
// Dipanggil dari: hasil.php?q=...&kat=...  atau  hasil.php?id=...
// =====================================================================
require_once __DIR__.'/config.php';
require_once __DIR__.'/cover_asli.php';
$adm = function_exists('isLoggedIn') ? isLoggedIn() : !empty($_SESSION['user_id']);

// ---------- fungsi bantu ----------
// sorot kata kunci (per kata) pada teks yang sudah di-escape
function hs_hl($text,$words){
 $t=h($text);
 if(!$words) return $t;
 $pola=array();
 foreach($words as $w){ $pola[]=preg_quote(h($w),'/'); }
 $r=@preg_replace('/('.implode('|',$pola).')/iu','<span class="hl">$1</span>',$t);
 return ($r===null)?$t:$r;
}
function hs_chk($arr,$v){ return in_array($v,$arr,true)?'checked':''; }
// ambil parameter array dari URL, hanya string yang diterima
function hs_list($key){
 if(!isset($_GET[$key]) || !is_array($_GET[$key])) return array();
 $out=array();
 foreach($_GET[$key] as $v){ if(is_string($v) && $v!=='') $out[]=$v; }
 return array_values(array_unique($out));
}
function hs_dash($v){ $v=trim((string)$v); return ($v==='')?'-':h($v); }
// bangun URL hasil.php dengan parameter saat ini + perubahan ($over; null = hapus)
function hs_url($over=array()){
 $p=$_GET;
 foreach($over as $k=>$v){ if($v===null) unset($p[$k]); else $p[$k]=$v; }
 $s=http_build_query($p);
 return 'hasil.php'.($s!==''?'?'.$s:'');
}
// jumlah koleksi per nilai filter (untuk angka di samping checkbox)
function hs_counts($kol,$BQ){
 if(!in_array($kol,array('kategori','tipe','bahasa'),true)) return array();
 $out=array();
 foreach(q("SELECT $kol AS v, COUNT(*) AS c FROM ($BQ) t WHERE $kol<>'' GROUP BY $kol ORDER BY $kol")->fetchAll() as $r){ $out[$r['v']]=(int)$r['c']; }
 return $out;
}
// baris metadata; kolom opsional hanya tampil kalau ada isinya
function hs_meta($b,$words){
 $o='<div class="meta">';
 $o.='<b>Nomor panggil</b><span>'.hs_hl($b['callno'],$words).'</span>';
 $o.='<b>Kolasi</b><span>'.hs_dash($b['kolasi']).'</span>';
 $o.='<b>Judul series</b><span>'.hs_hl($b['series'],$words).'</span>';
 $o.='<b>Tipe koleksi</b><span>'.hs_dash($b['tipe']).'</span>';
 $o.='<b>Bahasa</b><span>'.hs_dash($b['bahasa']).'</span>';
 $opsi=array('penerbit'=>'Penerbit','tahun'=>'Tahun terbit','isbn'=>'ISBN','lokasi'=>'Lokasi rak');
 foreach($opsi as $k=>$lbl){
  if(isset($b[$k]) && trim((string)$b[$k])!=='') $o.='<b>'.$lbl.'</b><span>'.h($b[$k]).'</span>';
 }
 return $o.'</div>';
}
// kotak status peminjaman
function hs_avail($b){
 $av=(int)$b['av']; $tot=(int)$b['tot'];
 if($tot<=0) return '<div class="avail out"><small>KETERSEDIAAN</small><span class="none">Belum ada eksemplar</span></div>';
 if($av>0) return '<div class="avail"><small>KETERSEDIAAN</small><span>'.$av.'</span><em>dari '.$tot.' eksemplar</em><strong class="st ok">Tersedia di rak</strong></div>';
 return '<div class="avail out"><small>KETERSEDIAAN</small><span class="none">Sedang dipinjam</span><em>semua '.$tot.' eksemplar</em><strong class="st no">Belum tersedia</strong></div>';
}

// ---------- parameter ----------
$id=isset($_GET['id'])?(int)$_GET['id']:0;
$qs=trim(isset($_GET['q']) && is_string($_GET['q'])?$_GET['q']:'');
$tersedia=isset($_GET['tersedia']);
$kat_sel=hs_list('kategori'); $tipe_sel=hs_list('tipe'); $bahasa_sel=hs_list('bahasa');
// "kat" (satu kategori) datang dari kotak pencarian & tombol kategori di beranda
if(isset($_GET['kat']) && is_string($_GET['kat']) && $_GET['kat']!=='' && !in_array($_GET['kat'],$kat_sel,true)) $kat_sel[]=$_GET['kat'];
$sort=(isset($_GET['sort']) && is_string($_GET['sort']))?$_GET['sort']:'relevan';
$hal=isset($_GET['hal'])?max(1,(int)$_GET['hal']):1;
$perhal=10;
$words=($qs==='')?array():array_values(array_filter(preg_split('/\s+/u',$qs)));

// klik satu buku (tanpa filter) -> tampilkan detail buku itu + koleksi sekategori
$pilihan=null; $auto_kat=false;
if($id>0){
 $pilihan=q($BQ." WHERE b.id=?",array($id))->fetch();
 if($pilihan && !$kat_sel && !$tipe_sel && !$bahasa_sel && $qs===''){ $kat_sel=array($pilihan['kategori']); $auto_kat=true; }
}

// ---------- daftar filter (+ jumlah per pilihan) ----------
$cnt_kat=hs_counts('kategori',$BQ); $cnt_tipe=hs_counts('tipe',$BQ); $cnt_bahasa=hs_counts('bahasa',$BQ);

// ---------- query hasil ----------
$w=array(); $p=array();
// setiap kata harus cocok di salah satu kolom (AND antar kata)
foreach($words as $kw){
 $w[]="(judul LIKE ? ESCAPE '\\' OR pengarang LIKE ? ESCAPE '\\' OR callno LIKE ? ESCAPE '\\' OR series LIKE ? ESCAPE '\\' OR kategori LIKE ? ESCAPE '\\')";
 $like='%'.addcslashes($kw,'%_\\').'%';
 for($i=0;$i<5;$i++) $p[]=$like;
}
if($tersedia) $w[]="av>0";
foreach(array('kategori'=>$kat_sel,'tipe'=>$tipe_sel,'bahasa'=>$bahasa_sel) as $kol=>$arr){
 if($arr){ $w[]="$kol IN (".implode(',',array_fill(0,count($arr),'?')).")"; foreach($arr as $v) $p[]=(string)$v; }
}
if($sort==='judul') $order='judul COLLATE NOCASE ASC';
elseif($sort==='baru') $order='id DESC';
else $order='pop DESC, id DESC';
$semua=q("SELECT * FROM ($BQ) t".($w?' WHERE '.implode(' AND ',$w):'')." ORDER BY $order",$p)->fetchAll();
// buku yang dipilih sudah tampil di panel detail, jangan diulang di daftar
if($pilihan){
 $tmp=array();
 foreach($semua as $r){ if((int)$r['id']!==$id) $tmp[]=$r; }
 $semua=$tmp;
}
$total=count($semua);
$jml_hal=max(1,(int)ceil($total/$perhal));
if($hal>$jml_hal) $hal=$jml_hal;
$hasil=array_slice($semua,($hal-1)*$perhal,$perhal);

// ---------- chip filter aktif ----------
$aktif=array();
if($qs!=='') $aktif[]=array('Kata kunci: '.$qs,hs_url(array('q'=>null,'hal'=>null,'id'=>null)));
if($tersedia) $aktif[]=array('Hanya yang tersedia',hs_url(array('tersedia'=>null,'hal'=>null,'id'=>null)));
foreach(array('kategori'=>$kat_sel,'tipe'=>$tipe_sel,'bahasa'=>$bahasa_sel) as $kol=>$arr){
 foreach($arr as $v){
  $sisa=array_values(array_diff($arr,array($v)));
  $over=array($kol=>$sisa?$sisa:null,'hal'=>null,'id'=>null);
  if($kol==='kategori') $over['kat']=null;
  $aktif[]=array($v,hs_url($over));
 }
}
$ada_filter=(bool)$aktif;
$judul_tab=$pilihan?$pilihan['judul']:'Hasil Pencarian';
?><!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo h($judul_tab); ?> | Libloom</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;600;700;800&family=Figtree:wght@400;500;600;700&family=Newsreader:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
<style>
:root{--cream:#FFF7E6;--ink:#36432F;--muted:#6F7664;--green:#6CA24A;--green-d:#3F6B2C;--orange:#F28C38;--sage:#E3EED2;--sand:#FBF0D4;--line:#EFE2C4;--sun:#F4C542;--blush:#F7B7C4;--accent:#D9741F;--accent-d:#8F4A12;--tint:#FCEBBF;--ok:#4E8A2E;
--sans:'Figtree',system-ui,Arial,sans-serif;--head:'Bricolage Grotesque','Figtree',Arial,sans-serif;--serif:'Newsreader',Georgia,'Times New Roman',serif}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--sans);background:var(--cream);color:var(--ink);line-height:1.6;-webkit-font-smoothing:antialiased}
img,svg{max-width:100%;display:block}
a{color:inherit;text-decoration:none}
ul{list-style:none}
:focus-visible{outline:3px solid var(--orange);outline-offset:3px}
.container{width:100%;max-width:1160px;margin:0 auto;padding:0 28px}
h1,h2{font-family:var(--head);font-weight:700;letter-spacing:-.03em;line-height:1.12;color:var(--ink)}

/* header */
.site-header{background:var(--cream);border-bottom:1px solid var(--line);position:sticky;top:0;z-index:20}
.nav-wrap{position:relative;display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:80px}
.brand{display:flex;align-items:center;gap:12px}
.brand-mark{width:42px;height:42px;flex:none}
.brand-text{display:flex;flex-direction:column;line-height:1.15}
.brand-text strong{font-family:var(--head);font-weight:700;font-size:1.08rem}
.brand-text small{font-size:.65rem;letter-spacing:.2em;color:var(--muted)}
.main-nav{flex:1;justify-content:center;display:flex;align-items:center;gap:34px;font-size:.88rem;font-weight:600;color:var(--ink)}
.main-nav a{position:relative;padding:6px 0}
.main-nav a.active{color:var(--ink)}
.main-nav a.active::after{content:"";position:absolute;left:0;bottom:-2px;width:20px;height:2px;border-radius:2px;background:var(--orange)}
.main-nav .nav-login{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;border:1px solid var(--line);border-radius:999px;background:#FDEFD0;color:var(--accent-d);font-weight:700}
.main-nav .nav-login:hover{background:#FBE7A6}
.main-nav .nav-logout{color:var(--muted)}
.menu-toggle{display:none;background:none;border:1px solid var(--line);border-radius:10px;font-size:1.3rem;padding:6px 12px;color:var(--ink);cursor:pointer}

/* tombol */
.button{display:inline-flex;align-items:center;gap:14px;padding:13px 26px;border-radius:999px;font-weight:700;font-size:.85rem;border:0;cursor:pointer;font-family:inherit}
.button-primary{background:var(--accent);color:#fff;box-shadow:0 12px 24px rgba(217,116,31,.28)}
.button-primary:hover{background:#C4651A}

/* halaman */
.page{padding-top:32px;padding-bottom:80px}
.crumb{font-size:.78rem;color:var(--muted);margin-bottom:18px}.crumb a{color:var(--accent);font-weight:600}
.welcome{margin-bottom:22px}.welcome h1{font-size:clamp(1.8rem,3.6vw,2.4rem);margin-bottom:6px}
.welcome p{color:var(--muted);max-width:560px;font-size:.92rem}
.sbar{display:flex;gap:10px;align-items:center;background:#fff;border:1px solid var(--line);border-radius:999px;padding:8px 8px 8px 24px;box-shadow:0 14px 34px rgba(54,67,47,.07);margin-bottom:22px}
.sbar input{flex:1;min-width:0;font:500 .9rem var(--sans);color:var(--ink);border:0;background:transparent;padding:12px 4px}
.sbar input:focus{outline:none}
.sbar:focus-within{border-color:var(--accent);box-shadow:0 0 0 3px rgba(217,116,31,.18)}

/* detail buku terpilih */
.detail{background:#fff;border:1px solid var(--accent);box-shadow:0 0 0 3px rgba(217,116,31,.14);border-radius:24px;padding:26px;margin-bottom:34px;display:grid;grid-template-columns:150px minmax(0,1fr) 210px;gap:28px}
.detail .cv{width:150px;height:216px}
.detail .cv b{width:84px;height:84px;font-size:2.4rem}
.detail h1{font-size:clamp(1.5rem,2.8vw,2.1rem);margin:6px 0 6px}
.detail .oleh{color:var(--muted);font-size:.95rem;margin-bottom:16px}
.detail .meta{grid-template-columns:130px 1fr;font-size:.86rem;row-gap:7px}
.cara{margin-top:16px;background:var(--sand);border-radius:14px;padding:12px 14px;font-size:.8rem;color:#5d6657}
.cara b{color:var(--accent-d)}
.sec-judul{font-family:var(--head);font-weight:700;font-size:1.2rem;letter-spacing:-.02em;margin:0 0 14px}

/* filter + daftar */
.layout{display:grid;grid-template-columns:270px minmax(0,1fr);gap:26px;align-items:start}
.filter{background:#fff;border:1px solid var(--line);border-radius:22px;padding:22px;position:sticky;top:100px}
.filter summary{display:none;cursor:pointer;font:700 .95rem var(--head);list-style:none}
.filter summary::-webkit-details-marker{display:none}
.filter h2{font-size:1.2rem;letter-spacing:-.02em;margin-bottom:4px}
.fgroup{border-bottom:1px solid var(--line);padding:14px 0}.fgroup:last-of-type{border-bottom:0}
.fgroup h4{font-size:.7rem;letter-spacing:.14em;text-transform:uppercase;color:var(--accent-d);margin-bottom:8px}
.fgroup label{display:flex;align-items:center;gap:9px;margin:7px 0;color:#5d6657;font-size:.86rem;cursor:pointer}
.fgroup label em{margin-left:auto;font-style:normal;font-size:.75rem;color:var(--muted)}
.fgroup input[type=checkbox]{accent-color:var(--accent);width:15px;height:15px}
.apply{width:100%;justify-content:center;margin-top:8px}
.reset{display:block;text-align:center;margin-top:14px;color:var(--accent);font-size:.8rem;font-weight:700}
.aktif{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
.aktif a{display:inline-flex;gap:8px;align-items:center;background:#FBE7A6;border:1px solid #F0D58A;color:var(--accent-d);border-radius:999px;padding:6px 14px;font-size:.78rem;font-weight:600}
.aktif a:hover{background:#cfe0ba}
.bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:12px;flex-wrap:wrap}
.found{font-size:.85rem;color:var(--muted)}.found b{color:var(--ink)}
.bar label{font-weight:700;font-size:.78rem;margin-right:8px;color:var(--accent-d)}
.bar select{border:1px solid var(--line);background:#fff;border-radius:999px;padding:9px 16px;font:500 .85rem var(--sans);color:var(--ink)}
.rcard{background:#fff;border:1px solid var(--line);border-radius:22px;padding:18px;margin-bottom:14px;display:grid;grid-template-columns:104px minmax(0,1fr) 160px;gap:20px;transition:box-shadow .2s}
.rcard:hover{box-shadow:0 14px 30px rgba(54,67,47,.09)}
.cv{width:104px;height:150px;border-radius:6px 12px 12px 6px;flex:none;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;box-shadow:0 10px 20px -8px rgba(0,0,0,.35);border-left:6px solid rgba(255,255,255,.35)}
.cv b{font:500 1.9rem var(--serif);border:2px solid rgba(255,255,255,.55);border-radius:50%;width:66px;height:66px;display:grid;place-items:center}
.cv small{font-size:.55rem;letter-spacing:.2em;margin-top:14px}
.book-tag{font-size:.7rem;color:var(--accent);font-weight:700;text-transform:uppercase;letter-spacing:.12em}
.title{font:600 1.15rem/1.35 var(--sans);margin:6px 0 10px}
.title a:hover{color:var(--accent)}
.pill{display:inline-block;background:#FADBE1;color:var(--accent-d);border-radius:999px;padding:5px 14px;font-size:.78rem;font-weight:700;margin-bottom:12px}
.meta{display:grid;grid-template-columns:110px 1fr;row-gap:5px;font-size:.82rem}.meta b{color:var(--accent-d)}.meta span{color:#5d6657}
.hl{color:var(--orange);font-weight:700}
.avail{background:#FFF3D6;border-radius:16px;text-align:center;padding:16px 10px;min-height:110px}
.avail small{display:block;color:var(--accent-d);font-size:.65rem;letter-spacing:.14em;font-weight:800}
.avail span{font:500 2.3rem var(--head);display:block;margin-top:8px;color:var(--ink);line-height:1}
.avail em{display:block;font-style:normal;font-size:.76rem;color:var(--muted);margin-top:8px}
.avail .none{font-size:1rem;font-weight:700;color:#CF7940;margin-top:14px;line-height:1.3}
.avail.out{background:#FBEFE3}
.avail .st{display:inline-block;margin-top:12px;font-size:.72rem;font-weight:700;padding:4px 12px;border-radius:999px}
.avail .st.ok{background:#DDEFD0;color:var(--ok)}
.avail .st.no{background:#F6DCC5;color:#A9531F}
.empty{background:#fff;border:1px dashed #D9DDCF;border-radius:18px;padding:30px;text-align:center;color:var(--muted)}
.empty a{color:var(--accent);font-weight:700}
.paging{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-top:22px}
.paging a,.paging span{min-width:40px;height:40px;padding:0 14px;display:grid;place-items:center;border-radius:999px;border:1px solid var(--line);background:#fff;font-size:.84rem;font-weight:600;color:var(--accent-d)}
.paging a:hover{background:#FBE7A6}
.paging .now{background:var(--accent);border-color:var(--accent);color:#fff}
.paging .gap{border:0;background:none}

@media (max-width:900px){
  .layout{grid-template-columns:1fr}.filter{position:static}
  .filter summary{display:block;margin-bottom:6px}
  .detail{grid-template-columns:104px minmax(0,1fr);gap:18px;padding:18px}
  .detail .cv{width:104px;height:150px}.detail .cv b{width:66px;height:66px;font-size:1.9rem}
  .detail .side{grid-column:1/-1}
  .detail .meta{grid-template-columns:110px 1fr}
  .rcard{grid-template-columns:88px minmax(0,1fr)}.rcard .cv{width:88px;height:128px}.rcard .cv b{width:54px;height:54px;font-size:1.5rem}
  .rcard .side{grid-column:1/-1}.avail{min-height:0}
  .menu-toggle{display:block}
  .main-nav{display:none;position:absolute;top:100%;left:0;right:0;flex-direction:column;align-items:flex-start;gap:6px;background:var(--cream);padding:16px 28px 22px;border-bottom:1px solid var(--line)}
  .main-nav.open{display:flex}
  .footer-inner{grid-template-columns:1fr;text-align:center;justify-items:center}.copyright{text-align:center}
  .sbar{flex-wrap:wrap;border-radius:26px;padding:14px}.sbar input{flex:1 1 100%}.sbar .button{width:100%;justify-content:center}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}

/* footer */
.site-footer{padding:36px 0;background:var(--cream);border-top:1px solid var(--line)}
.footer-inner{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:20px}
.footer-tagline{font-family:var(--serif);font-style:italic;color:var(--muted);text-align:center}
.copyright{font-size:.75rem;color:var(--muted);text-align:right}
</style></head><body>
<header class="site-header"><div class="container nav-wrap">
<a class="brand" href="index.php" aria-label="Libloom, beranda"><img class="brand-mark" src="assets/logolibloom.jpg" alt="Libloom" width="52" height="52" style="width:52px;height:52px;border-radius:12px;object-fit:cover"><span class="brand-text"><strong>Libloom</strong><small>WHERE DREAMS GROW BETWEEN PAGES</small></span></a>
<button class="menu-toggle" id="menuToggle" aria-label="Buka navigasi" aria-expanded="false">&#9776;</button>
<nav class="main-nav" id="mainNav"><a href="index.php">Beranda</a><a href="tentang.php">Tentang</a><a class="active" href="koleksiopac.php">Koleksi</a><a href="pages/layanan.php">Layanan</a><a href="pages/agenda.php">Agenda</a>
<?php if($adm){ ?><a class="nav-login" href="index.php?page=dashboard"><svg width="13" height="13" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="5" r="3.2" fill="currentColor"/><path d="M2 14.5c.6-3 3-4.6 6-4.6s5.4 1.6 6 4.6z" fill="currentColor"/></svg>Dashboard</a><a class="nav-logout" href="index.php?act=logout">Keluar</a>
<?php } else { ?><a class="nav-login" href="index.php?page=login"><svg width="13" height="13" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="5" r="3.2" fill="currentColor"/><path d="M2 14.5c.6-3 3-4.6 6-4.6s5.4 1.6 6 4.6z" fill="currentColor"/></svg>Login</a>
<?php } ?></nav></div></header>
<main class="container page">
<div class="crumb"><a href="index.php">Beranda</a> / <a href="hasil.php">Koleksi</a> / <?php echo $pilihan ? h($pilihan['judul']) : 'Hasil pencarian'; ?></div>

<?php if($pilihan){ $b=$pilihan; ?>
<!-- DETAIL BUKU YANG DIKLIK -->
<article class="detail">
 <?php echo cv_wrap($b); ?>
 <div>
  <span class="book-tag"><?php echo h($b['kategori']); ?></span>
  <h1><?php echo h($b['judul']); ?></h1>
  <p class="oleh">oleh <?php echo hs_dash($b['pengarang']); ?></p>
  <?php echo hs_meta($b,array()); ?>
  <p class="cara"><b>Cara meminjam:</b> catat nomor panggil <b><?php echo hs_dash($b['callno']); ?></b>, lalu bawa bukunya ke meja sirkulasi perpustakaan.</p>
 </div>
 <div class="side"><?php echo hs_avail($b); ?></div>
</article>
<?php } elseif($id>0){ ?>
<div class="empty" style="margin-bottom:24px">Koleksi dengan ID tersebut tidak ditemukan. Silakan cari lewat kotak di bawah.</div>
<?php } ?>

<form method="get" action="hasil.php">
<?php if(!$pilihan){ ?>
<section class="welcome">
<h1>Cari koleksi perpustakaan</h1>
<p>Ketik judul, pengarang, atau nomor panggil, lalu saring berdasarkan kategori, tipe, dan bahasa.</p></section>
<?php } else { ?>
<h2 class="sec-judul"><?php echo $auto_kat ? 'Koleksi sejenis' : 'Hasil pencarian'; ?></h2>
<?php } ?>
<div class="sbar"><input type="text" name="q" value="<?php echo h($qs); ?>" placeholder="Cari judul buku, pengarang, atau topik..." aria-label="Cari koleksi"><button class="button button-primary" type="submit">Cari koleksi</button></div>
<div class="layout">
<aside>
<details class="filter" id="filterBox" open>
<summary>Filter &amp; urutkan</summary>
<h2>Filter</h2>
<div class="fgroup"><h4>Ketersediaan</h4><label><input type="checkbox" name="tersedia" <?php echo $tersedia?'checked':''; ?>> Hanya yang ada di rak</label></div>
<div class="fgroup"><h4>Kategori</h4><?php foreach($cnt_kat as $j=>$n){ ?><label><input type="checkbox" name="kategori[]" value="<?php echo h($j); ?>" <?php echo hs_chk($kat_sel,$j); ?>> <?php echo h($j); ?><em><?php echo $n; ?></em></label><?php } ?></div>
<div class="fgroup"><h4>Tipe koleksi</h4><?php foreach($cnt_tipe as $j=>$n){ ?><label><input type="checkbox" name="tipe[]" value="<?php echo h($j); ?>" <?php echo hs_chk($tipe_sel,$j); ?>> <?php echo h($j); ?><em><?php echo $n; ?></em></label><?php } ?></div>
<div class="fgroup"><h4>Bahasa</h4><?php foreach($cnt_bahasa as $j=>$n){ ?><label><input type="checkbox" name="bahasa[]" value="<?php echo h($j); ?>" <?php echo hs_chk($bahasa_sel,$j); ?>> <?php echo h($j); ?><em><?php echo $n; ?></em></label><?php } ?></div>
<?php if($pilihan){ ?><input type="hidden" name="id" value="<?php echo $id; ?>"><?php } ?>
<button class="button button-primary apply" type="submit">Terapkan filter</button>
<a class="reset" href="hasil.php<?php echo $qs!==''?'?q='.urlencode($qs):''; ?>">Hapus filter &times;</a>
</details>
</aside>
<section>
<?php if($ada_filter && !$auto_kat){ ?>
<div class="aktif" aria-label="Filter aktif"><?php foreach($aktif as $a){ echo '<a href="'.h($a[1]).'" title="Hapus filter ini">'.h($a[0]).' <span aria-hidden="true">&times;</span></a>'; } ?></div>
<?php } ?>
<div class="bar">
<div class="found">Ditemukan <b><?php echo $total; ?></b> koleksi<?php echo $qs!=='' ? ' untuk kata kunci <b>'.h($qs).'</b>' : ''; ?><?php if($total>$perhal){ echo ' &middot; halaman '.$hal.' dari '.$jml_hal; } ?></div>
<div><label for="sort">Urutkan</label><select id="sort" name="sort" onchange="this.form.submit()"><option value="relevan" <?php echo $sort==='relevan'?'selected':''; ?>>Paling populer</option><option value="judul" <?php echo $sort==='judul'?'selected':''; ?>>Judul A-Z</option><option value="baru" <?php echo $sort==='baru'?'selected':''; ?>>Terbaru ditambahkan</option></select></div>
</div>
<?php if(!$hasil){ ?><div class="empty">Belum ada koleksi yang cocok.<?php if($ada_filter || $qs!==''){ ?> Coba kurangi filter atau pakai kata kunci yang lebih singkat, atau <a href="hasil.php">lihat semua koleksi</a>.<?php } ?></div><?php } ?>
<?php foreach($hasil as $b){ ?>
<article class="rcard">
<?php echo cv_wrap($b); ?>
<div>
<span class="book-tag"><?php echo h($b['kategori']); ?></span>
<h3 class="title"><a href="hasil.php?id=<?php echo (int)$b['id']; ?>"><?php echo hs_hl($b['judul'],$words); ?></a></h3>
<span class="pill"><?php echo hs_hl($b['pengarang'],$words); ?></span>
<?php echo hs_meta($b,$words); ?>
</div>
<div class="side"><?php echo hs_avail($b); ?></div>
</article>
<?php } ?>
<?php if($jml_hal>1){ ?>
<nav class="paging" aria-label="Halaman hasil">
<?php
 if($hal>1) echo '<a href="'.h(hs_url(array('hal'=>$hal-1))).'">&larr; Sebelumnya</a>';
 $tampil=array();
 for($i=1;$i<=$jml_hal;$i++){ if($i==1||$i==$jml_hal||abs($i-$hal)<=1) $tampil[]=$i; }
 $prev=0;
 foreach($tampil as $i){
  if($prev && $i-$prev>1) echo '<span class="gap">&hellip;</span>';
  echo ($i==$hal) ? '<span class="now" aria-current="page">'.$i.'</span>' : '<a href="'.h(hs_url(array('hal'=>$i))).'">'.$i.'</a>';
  $prev=$i;
 }
 if($hal<$jml_hal) echo '<a href="'.h(hs_url(array('hal'=>$hal+1))).'">Berikutnya &rarr;</a>';
?>
</nav>
<?php } ?>
</section></div></form>
</main>
<footer class="site-footer"><div class="container footer-inner">
<a class="brand" href="index.php" aria-label="Libloom"><img class="brand-mark" src="assets/logolibloom.jpg" alt="Libloom" width="52" height="52" style="width:52px;height:52px;border-radius:12px;object-fit:cover"><span class="brand-text"><strong>Libloom</strong><small>WHERE DREAMS GROW BETWEEN PAGES</small></span></a>
<p class="footer-tagline">Tempat cerita dan pengetahuan bertemu.</p>
<p class="copyright">&copy; <?php echo date('Y'); ?> Libloom. Dibuat untuk komunitas sekolah.</p>
</div></footer>
<script>
(function(){
 var b=document.getElementById('menuToggle'),n=document.getElementById('mainNav');
 if(b&&n){b.addEventListener('click',function(){var o=n.classList.toggle('open');b.setAttribute('aria-expanded',o?'true':'false');});}
 // di layar kecil, kotak filter dilipat dulu supaya hasil langsung terlihat
 var f=document.getElementById('filterBox');
 if(f&&window.matchMedia('(max-width:900px)').matches){f.removeAttribute('open');}
})();
</script>
<?php echo cv_script(); ?>
</body></html>
<?php exit;