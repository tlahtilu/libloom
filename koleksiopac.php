<?php
// =====================================================================
// koleksiopac.php - SEMUA KOLEKSI (tampilan rak buku) untuk Libloom
// Simpan di folder utama libloom/ (sejajar dengan index.php & hasil.php).
// File ini BERDIRI SENDIRI (CSS, header, footer, JS ada di sini) dan hanya
// memakai q(), h(), cover(), $BQ dari config.php -> tidak mengubah file lain.
// Pencarian/filter/urutan berjalan langsung di halaman (tanpa reload).
// Klik buku -> hasil.php?id=...  |  Filter lanjutan -> hasil.php
//
// COVER ASLI: cover() dari config.php tetap jadi cadangan. Di browser, cover asli
// dicari otomatis dari Open Library (lewat ISBN) lalu Google Books (ISBN/judul),
// disimpan di localStorage supaya tidak dicari ulang. Kalau tidak ketemu, cover
// bawaan tetap tampil.
// =====================================================================
require_once __DIR__.'/config.php';
require_once __DIR__.'/cover_asli.php';
$adm = function_exists('isLoggedIn') ? isLoggedIn() : !empty($_SESSION['user_id']);

function ko_av($b){
 $av=(int)$b['av']; $tot=(int)$b['tot'];
 if($tot<=0) return '<span class="stt off">Belum ada eksemplar</span>';
 if($av>0)   return '<span class="stt ok">'.$av.' dari '.$tot.' tersedia</span>';
 return '<span class="stt no">Dipinjam semua</span>';
}

$semua=q("$BQ ORDER BY pop DESC, id DESC")->fetchAll();
$total=count($semua);

// DAFTAR NAMA FILE COVER: buka koleksiopac.php?cvnama
if(isset($_GET['cvnama'])){
 echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nama file cover</title><style>body{font:15px/1.5 system-ui,Arial,sans-serif;margin:28px;color:#36432F;background:#FFF7E6}h1{font-size:1.3rem}table{border-collapse:collapse;width:100%;background:#fff}th,td{border:1px solid #EFE2C4;padding:8px 12px;text-align:left;vertical-align:top}th{background:#FCEBBF}code{background:#FBF0D4;padding:2px 6px;border-radius:5px}.ok{color:#4E8A2E;font-weight:700}.no{color:#C9674F}</style></head><body>';
 echo '<h1>Nama file cover per buku</h1><p>Simpan gambar di folder <code>assets/covers/</code> dengan salah satu nama di kolom "Nama file". Ekstensi boleh jpg, jpeg, png, atau webp.</p><table><tr><th>ID</th><th>Judul</th><th>Nama file (pilih salah satu)</th><th>Status</th></tr>';
 foreach($semua as $b){
  $nm=array(); foreach(cv_nama($b) as $x){ $nm[]='<code>'.h($x).'.jpg</code>'; }
  $f=cv_file($b);
  echo '<tr><td>'.(int)$b['id'].'</td><td>'.h($b['judul']).'</td><td>'.implode(' &nbsp;atau&nbsp; ',$nm).'</td><td>'.($f!==''?'<span class="ok">sudah ada</span>':'<span class="no">belum ada</span>').'</td></tr>';
 }
 echo '</table></body></html>';
 exit;
}

// statistik & kategori
$sum_av=0; $sum_tot=0; $kats=array();
foreach($semua as $b){
 $sum_av+=(int)$b['av']; $sum_tot+=(int)$b['tot'];
 $k=trim((string)$b['kategori']); if($k==='') $k='Lainnya';
 $kats[$k]=isset($kats[$k])?$kats[$k]+1:1;
}
arsort($kats);
$kat_top=$kats?key($kats):''; $kat_top_n=$kats?current($kats):0;
$persen=($sum_tot>0)?round($sum_av/$sum_tot*100):0;

$unggul=$total?$semua[0]:null;   // paling populer
$baru=$semua;                    // empat yang terakhir ditambahkan
usort($baru,function($a,$b){ return (int)$b['id']-(int)$a['id']; });
$baru=array_slice($baru,0,4);

// nilai awal dari URL (mis. dari beranda)
$qs=trim(isset($_GET['q']) && is_string($_GET['q'])?$_GET['q']:'');
$kat_awal=(isset($_GET['kat']) && is_string($_GET['kat']) && isset($kats[$_GET['kat']]))?$_GET['kat']:'';
?><!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Seluruh koleksi buku perpustakaan Libloom.">
<title>Koleksi | Libloom</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;600;700;800&family=Figtree:wght@400;500;600;700&family=Newsreader:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
<style>
:root{--cream:#FFF7E6;--ink:#36432F;--muted:#6F7664;--green:#6CA24A;--green-d:#3F6B2C;--orange:#F28C38;--sage:#E3EED2;--sage-m:#F0D58A;--sand:#FBF0D4;--sun:#F4C542;--blush:#F7B7C4;--line:#EFE2C4;--accent:#D9741F;--accent-d:#8F4A12;--tint:#FCEBBF;--ok:#4E8A2E;
--sans:'Figtree',system-ui,Arial,sans-serif;--head:'Bricolage Grotesque','Figtree',Arial,sans-serif;--serif:'Newsreader',Georgia,'Times New Roman',serif}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--sans);background:var(--cream);color:var(--ink);line-height:1.6;-webkit-font-smoothing:antialiased}
img,svg{max-width:100%;display:block}
a{color:inherit;text-decoration:none}
ul{list-style:none}
:focus-visible{outline:3px solid var(--orange);outline-offset:3px}
.container{width:100%;max-width:1160px;margin:0 auto;padding:0 28px}
h1,h2{font-family:var(--head);font-weight:700;letter-spacing:-.03em;line-height:1.1;color:var(--ink)}
[hidden]{display:none!important}

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
.button{display:inline-flex;align-items:center;gap:10px;padding:13px 26px;border-radius:999px;font-weight:700;font-size:.85rem;border:0;cursor:pointer;font-family:inherit}
.button-primary{background:var(--accent);color:#fff;box-shadow:0 12px 24px rgba(217,116,31,.28)}
.button-primary:hover{background:#C4651A}
.ghost{display:inline-flex;align-items:center;justify-content:center;padding:7px 18px;border:1.5px solid var(--orange);border-radius:999px;color:#B45F1C;font-size:.78rem;font-weight:700;background:transparent;transition:background .2s,color .2s}
.ghost:hover{background:var(--orange);color:#fff}

/* ===== PANGGUNG (hero ala rak buku) ===== */
.page{padding-top:36px;padding-bottom:80px}
.crumb{font-size:.78rem;color:var(--muted);margin-bottom:16px}.crumb a{color:var(--accent);font-weight:600}
.stage{border-radius:36px;background:#fff;box-shadow:0 40px 80px -30px rgba(63,107,44,.28),0 0 0 1px var(--line);overflow:hidden}
.stage-top{background:linear-gradient(180deg,var(--sand) 0%,var(--cream) 100%);padding:48px 48px 0;display:grid;grid-template-columns:minmax(0,1.05fr) auto minmax(0,.95fr);gap:44px;align-items:end;position:relative}
.stage-top::before{content:"";position:absolute;right:-60px;top:-70px;width:260px;height:260px;border-radius:50%;background:var(--sun);opacity:.22}
.stage-top::after{content:"";position:absolute;left:-50px;top:120px;width:150px;height:150px;border-radius:50%;background:var(--blush);opacity:.28}
.stage-top>*{position:relative;z-index:1}
.intro{align-self:center;padding-bottom:40px}
.intro h1{font-family:var(--serif);font-weight:500;font-size:clamp(2.2rem,4.4vw,3.4rem);letter-spacing:-.02em;line-height:1.05}
.intro h1 span{color:var(--accent)}
.intro p{color:var(--muted);font-size:.92rem;margin:14px 0 20px;max-width:340px}
.pill-search{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid var(--line);border-radius:999px;padding:6px 8px 6px 18px;box-shadow:0 12px 28px rgba(63,107,44,.1);max-width:380px}
.pill-search svg{flex:none;color:var(--accent)}
.pill-search input{flex:1;min-width:0;border:0;background:transparent;font:500 .9rem var(--sans);color:var(--ink);padding:10px 0}
.pill-search input:focus{outline:none}
.pill-search:focus-within{border-color:var(--accent);box-shadow:0 0 0 3px rgba(217,116,31,.2)}
.pill-search button{padding:10px 20px}
.spot{display:inline-flex;align-items:center;gap:10px;margin-top:22px;font-size:.82rem;color:var(--muted);max-width:380px}
.spot i{flex:none;width:8px;height:8px;border-radius:50%;background:var(--orange)}
.spot b{color:var(--ink)}
.spot:hover b{color:var(--accent)}

/* buku unggulan di atas rak */
.feat{display:block;position:relative;margin-bottom:-2px}
.feat .cv{width:230px;height:332px;border-radius:8px 16px 16px 8px;box-shadow:-6px 0 0 rgba(0,0,0,.12),0 30px 44px -14px rgba(40,50,30,.55);transform:rotate(-2deg);transform-origin:bottom center;transition:transform .35s}
.feat:hover .cv{transform:rotate(0) translateY(-6px)}
.feat .cv b{width:120px;height:120px;font-size:3.6rem}
.feat .cv small{font-size:.7rem;margin-top:20px}
.badge{position:absolute;top:-14px;right:-18px;z-index:2;background:var(--orange);color:#fff;font-size:.66rem;font-weight:800;letter-spacing:.1em;padding:7px 12px;border-radius:999px;box-shadow:0 8px 16px rgba(242,140,56,.4);transform:rotate(6deg)}

/* dua kartu kecil di kanan */
.side-cards{display:flex;gap:18px;align-items:flex-end;justify-content:flex-end;padding-bottom:0}
.vl{writing-mode:vertical-rl;transform:rotate(180deg);font-size:.72rem;font-weight:700;letter-spacing:.14em;color:var(--accent-d);text-transform:uppercase;align-self:flex-start;margin-top:10px}
.mini{width:158px;border-radius:26px 26px 0 0;padding:22px 16px 24px;text-align:center;box-shadow:0 22px 34px -16px rgba(63,107,44,.35)}
.mini.a{background:var(--sun);height:300px;display:flex;flex-direction:column;align-items:center;justify-content:center}
.mini.b{background:#fff;height:262px;display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px solid var(--line);border-bottom:0}
.mini small{font-size:.66rem;letter-spacing:.12em;font-weight:700;text-transform:uppercase;color:rgba(54,67,47,.7)}
.mini strong{display:block;font:500 1.35rem/1.15 var(--serif);margin:10px 0 6px;color:var(--ink)}
.mini p{font-size:.76rem;color:rgba(54,67,47,.75);line-height:1.4}
.mini .num{font:700 2.6rem var(--head);letter-spacing:-.04em;line-height:1;color:var(--accent-d);margin:12px 0 2px}
.bar{height:8px;width:100%;border-radius:99px;background:var(--tint);margin:12px 0 8px;overflow:hidden}
.bar i{display:block;height:100%;border-radius:99px;background:var(--accent)}

/* rak */
.shelf{height:18px;background:linear-gradient(180deg,#fff 0%,#F3E6C6 100%);box-shadow:0 20px 30px -8px rgba(63,107,44,.22);position:relative;z-index:2}

/* baris "baru ditambahkan" di bawah rak */
.recent{padding:36px 40px 40px;display:grid;grid-template-columns:auto repeat(4,minmax(0,1fr));gap:26px;align-items:center;background:#fff}
.ri{display:flex;gap:14px;align-items:center;min-width:0}
.ri .cv{width:76px;height:110px;border-radius:5px 9px 9px 5px;border-left-width:4px;flex:none}
.ri .cv b{width:44px;height:44px;font-size:1.2rem;border-width:1.5px}
.ri .cv small{display:none}
.ri .in{display:flex;flex-direction:column;gap:2px;min-width:0}
.ri h3{font:600 .9rem/1.3 var(--sans);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.ri h3 a:hover{color:var(--accent)}
.ri p{font-size:.76rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ri .ghost{align-self:flex-start;margin-top:8px}

/* ===== DAFTAR SEMUA KOLEKSI ===== */
.all{padding-top:64px}
.all-head{display:flex;justify-content:space-between;align-items:end;gap:20px;flex-wrap:wrap;margin-bottom:22px}
.all-head h2{font-family:var(--serif);font-weight:500;font-size:clamp(1.8rem,3.4vw,2.5rem);letter-spacing:-.02em}
.all-head p{color:var(--muted);font-size:.9rem}.all-head p b{color:var(--ink)}
.all-head a{color:var(--accent);font-weight:700;font-size:.85rem}.all-head a:hover{color:var(--accent-d)}
.tools{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:24px}
.tabs{display:flex;flex-wrap:wrap;gap:8px}
.tab{display:inline-flex;gap:8px;align-items:center;padding:9px 18px;border-radius:999px;background:#fff;border:1px solid var(--line);font:600 .82rem var(--sans);color:var(--accent-d);cursor:pointer;transition:background .2s,border-color .2s}
.tab em{font-style:normal;font-size:.74rem;color:var(--muted)}
.tab:hover{background:var(--tint)}
.tab.on{background:var(--accent);border-color:var(--accent);color:#fff}
.tab.on em{color:rgba(255,255,255,.85)}
.opts{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
.opts label{display:inline-flex;align-items:center;gap:8px;font-size:.84rem;color:#5d6657;cursor:pointer}
.opts input[type=checkbox]{accent-color:var(--accent);width:16px;height:16px}
.opts select{border:1px solid var(--line);background:#fff;border-radius:999px;padding:9px 16px;font:500 .84rem var(--sans);color:var(--ink)}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:18px}
.bcard{background:#fff;border:1px solid var(--line);border-radius:22px;padding:16px;display:flex;gap:16px;transition:transform .25s,box-shadow .25s;animation:naik .5s both}
.bcard:hover{transform:translateY(-4px);box-shadow:0 18px 32px rgba(63,107,44,.14)}
.bcard .cvlink{flex:none;display:block;border-radius:10px}
.bcard .cv{width:96px;height:138px}
.bcard .cv b{width:58px;height:58px;font-size:1.7rem}
.bcard .cv small{font-size:.5rem}
.bcard .bi{display:flex;flex-direction:column;flex:1;min-width:0}
.kt{color:var(--accent);font-weight:700;letter-spacing:.12em;font-size:.68rem;text-transform:uppercase}
.bcard h3{font:600 1rem/1.3 var(--sans);margin:5px 0 3px}
.bcard h3 a:hover{color:var(--accent)}
.bcard p{color:var(--muted);font-size:.82rem}
.bcard .no{font-size:.74rem;color:#9A7A45;margin-top:2px}
.ft{margin-top:auto;padding-top:10px;display:flex;justify-content:space-between;align-items:center;gap:8px}
.stt{font-size:.76rem;font-weight:700}
.stt.ok{color:var(--ok)}.stt.no{color:#C9674F}.stt.off{color:var(--muted)}
.cv{border-radius:6px 12px 12px 6px;flex:none;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;box-shadow:0 10px 20px -8px rgba(0,0,0,.35);border-left:6px solid rgba(255,255,255,.35)}
.cv b{font:500 1.9rem var(--serif);border:2px solid rgba(255,255,255,.55);border-radius:50%;width:66px;height:66px;display:grid;place-items:center}
.cv small{font-size:.5rem;letter-spacing:.2em;margin-top:14px}
.empty{background:#fff;border:1px dashed #D9DDCF;border-radius:18px;padding:34px;text-align:center;color:var(--muted)}
.empty button{margin-top:10px;background:none;border:0;color:var(--accent);font:700 .85rem var(--sans);cursor:pointer}
@keyframes naik{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}

/* footer */
.site-footer{padding:36px 0;background:var(--cream);border-top:1px solid var(--line)}
.footer-inner{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:20px}
.footer-tagline{font-family:var(--serif);font-style:italic;color:var(--muted);text-align:center}
.copyright{font-size:.75rem;color:var(--muted);text-align:right}

@media (max-width:1020px){
  .stage-top{grid-template-columns:1fr;justify-items:center;text-align:center;padding:36px 24px 0;gap:30px}
  .intro{padding-bottom:0}.intro p,.pill-search,.spot{margin-left:auto;margin-right:auto}
  .side-cards{justify-content:center;width:100%}
  .recent{grid-template-columns:repeat(2,minmax(0,1fr));padding:28px 24px}
  .recent .vl{grid-column:1/-1;writing-mode:horizontal-tb;transform:none;margin:0}
}
@media (max-width:900px){
  .menu-toggle{display:block}
  .main-nav{display:none;position:absolute;top:100%;left:0;right:0;flex-direction:column;align-items:flex-start;gap:6px;background:var(--cream);padding:16px 28px 22px;border-bottom:1px solid var(--line)}
  .main-nav.open{display:flex}
  .footer-inner{grid-template-columns:1fr;text-align:center;justify-items:center}.copyright{text-align:center}
}
@media (max-width:620px){
  .container{padding:0 18px}
  .stage{border-radius:26px}
  .feat .cv{width:190px;height:274px}.feat .cv b{width:96px;height:96px;font-size:2.8rem}
  .mini{width:140px}.mini.a{height:260px}.mini.b{height:230px}
  .recent{grid-template-columns:1fr}
  .grid{grid-template-columns:1fr}
  .tabs{flex-wrap:nowrap;overflow-x:auto;padding-bottom:6px;width:100%}.tab{flex:none}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important;scroll-behavior:auto!important}}
</style></head><body>
<header class="site-header"><div class="container nav-wrap">
<a class="brand" href="index.php" aria-label="Libloom, beranda"><img class="brand-mark" src="assets/logolibloom.jpg" alt="Libloom" width="52" height="52" style="width:52px;height:52px;border-radius:12px;object-fit:cover"><span class="brand-text"><strong>Libloom</strong><small>WHERE DREAMS GROW BETWEEN PAGES</small></span></a>
<button class="menu-toggle" id="menuToggle" aria-label="Buka navigasi" aria-expanded="false">&#9776;</button>
<nav class="main-nav" id="mainNav"><a href="index.php">Beranda</a><a href="tentang.php">Tentang</a><a class="active" href="koleksiopac.php">Koleksi</a><a href="pages/layanan.php">Layanan</a><a href="pages/agenda.php">Agenda</a>
<?php if($adm){ ?><a class="nav-login" href="index.php?page=dashboard"><svg width="13" height="13" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="5" r="3.2" fill="currentColor"/><path d="M2 14.5c.6-3 3-4.6 6-4.6s5.4 1.6 6 4.6z" fill="currentColor"/></svg>Dashboard</a><a class="nav-logout" href="index.php?act=logout">Keluar</a>
<?php } else { ?><a class="nav-login" href="index.php?page=login"><svg width="13" height="13" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="5" r="3.2" fill="currentColor"/><path d="M2 14.5c.6-3 3-4.6 6-4.6s5.4 1.6 6 4.6z" fill="currentColor"/></svg>Login</a>
<?php } ?></nav></div></header>

<main class="container page">
<div class="crumb"><a href="index.php">Beranda</a> / Koleksi</div>

<section class="stage" aria-label="Sorotan koleksi">
 <div class="stage-top">
  <div class="intro">
   <h1>Semua <span>koleksi</span> kami</h1>
   <p>Jelajahi seluruh buku yang sudah terdata di perpustakaan, dari yang paling sering dipinjam sampai yang baru tiba.</p>
   <form class="pill-search" id="formCari" role="search">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
    <input type="search" id="cari" value="<?php echo h($qs); ?>" placeholder="Judul, pengarang, atau topik" aria-label="Cari di seluruh koleksi" autocomplete="off">
    <button class="button button-primary" type="submit">Cari</button>
   </form>
   <?php if($unggul){ ?><a class="spot" href="hasil.php?id=<?php echo (int)$unggul['id']; ?>"><i></i><span>Paling sering dipinjam: <b><?php echo h($unggul['judul']); ?></b> &rarr;</span></a><?php } ?>
  </div>

  <?php if($unggul){ ?>
  <a class="feat" href="hasil.php?id=<?php echo (int)$unggul['id']; ?>" aria-label="Lihat detail <?php echo h($unggul['judul']); ?>">
   <span class="badge">TERPOPULER</span>
   <?php echo cv_wrap($unggul); ?>
  </a>
  <?php } else { ?><div></div><?php } ?>

  <div class="side-cards">
   <span class="vl">Sorotan rak</span>
   <div class="mini a">
    <small>Kategori terbanyak</small>
    <strong><?php echo h($kat_top!==''?$kat_top:'-'); ?></strong>
    <p><?php echo (int)$kat_top_n; ?> judul<br>di rak ini</p>
   </div>
   <div class="mini b">
    <small>Ada di rak</small>
    <div class="num"><?php echo (int)$sum_av; ?></div>
    <p>dari <?php echo (int)$sum_tot; ?> eksemplar</p>
    <div class="bar" role="img" aria-label="<?php echo $persen; ?> persen eksemplar tersedia"><i style="width:<?php echo $persen; ?>%"></i></div>
    <p><?php echo $persen; ?>% tersedia</p>
   </div>
  </div>
 </div>

 <div class="shelf" aria-hidden="true"></div>

 <?php if($baru){ ?>
 <div class="recent">
  <span class="vl">Baru ditambahkan</span>
  <?php foreach($baru as $b){ ?>
  <div class="ri">
   <a href="hasil.php?id=<?php echo (int)$b['id']; ?>" tabindex="-1" aria-hidden="true"><?php echo cv_wrap($b); ?></a>
   <div class="in">
    <h3><a href="hasil.php?id=<?php echo (int)$b['id']; ?>"><?php echo h($b['judul']); ?></a></h3>
    <p><?php echo h($b['pengarang']); ?></p>
    <?php echo ko_av($b); ?>
    <a class="ghost" href="hasil.php?id=<?php echo (int)$b['id']; ?>">Lihat detail</a>
   </div>
  </div>
  <?php } ?>
 </div>
 <?php } ?>
</section>

<section class="all" id="semua">
 <div class="all-head">
  <div><h2>Rak lengkap</h2><p>Menampilkan <b id="hitung"><?php echo $total; ?></b> dari <?php echo $total; ?> judul</p></div>
  <a href="hasil.php">Butuh filter lanjutan? Buka pencarian katalog &rarr;</a>
 </div>

 <div class="tools">
  <div class="tabs" role="group" aria-label="Filter kategori">
   <button type="button" class="tab<?php echo $kat_awal===''?' on':''; ?>" data-kat=""><span>Semua</span><em><?php echo $total; ?></em></button>
   <?php foreach($kats as $k=>$n){ ?><button type="button" class="tab<?php echo $kat_awal===$k?' on':''; ?>" data-kat="<?php echo h($k); ?>"><span><?php echo h($k); ?></span><em><?php echo $n; ?></em></button><?php } ?>
  </div>
  <div class="opts">
   <label><input type="checkbox" id="hanyaAda"> Hanya yang ada di rak</label>
   <select id="urut" aria-label="Urutkan"><option value="pop">Paling populer</option><option value="judul">Judul A-Z</option><option value="baru">Terbaru ditambahkan</option></select>
  </div>
 </div>

 <div class="grid" id="list">
 <?php foreach($semua as $i=>$b){
  $kk=trim((string)$b['kategori']); if($kk==='') $kk='Lainnya';
  $cari=$b['judul'].' '.$b['pengarang'].' '.$b['callno'].' '.$b['series'].' '.$kk;
 ?>
  <article class="bcard" style="animation-delay:<?php echo min($i,12)*40; ?>ms" data-id="<?php echo (int)$b['id']; ?>" data-pop="<?php echo (int)$b['pop']; ?>" data-av="<?php echo ((int)$b['av']>0)?'1':'0'; ?>" data-kat="<?php echo h($kk); ?>" data-judul="<?php echo h(strtolower($b['judul'])); ?>" data-s="<?php echo h($cari); ?>">
   <a class="cvlink" href="hasil.php?id=<?php echo (int)$b['id']; ?>" tabindex="-1" aria-hidden="true"><?php echo cv_wrap($b); ?></a>
   <div class="bi">
    <span class="kt"><?php echo h($kk); ?></span>
    <h3><a href="hasil.php?id=<?php echo (int)$b['id']; ?>"><?php echo h($b['judul']); ?></a></h3>
    <p><?php echo h($b['pengarang']); ?></p>
    <?php if(trim((string)$b['callno'])!==''){ ?><span class="no">No. panggil: <?php echo h($b['callno']); ?></span><?php } ?>
    <div class="ft"><?php echo ko_av($b); ?><a class="ghost" href="hasil.php?id=<?php echo (int)$b['id']; ?>">Detail</a></div>
   </div>
  </article>
 <?php } ?>
 </div>
 <div class="empty" id="kosong" <?php echo $total?'hidden':''; ?>>
  <?php echo $total ? 'Tidak ada buku yang cocok dengan pencarianmu.' : 'Belum ada buku yang diinput.'; ?>
  <?php if($total){ ?><br><button type="button" id="reset">Hapus semua filter</button><?php } ?>
 </div>
</section>
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

 var list=document.getElementById('list');
 if(!list)return;
 var cards=[].slice.call(list.querySelectorAll('.bcard')),
     inp=document.getElementById('cari'),sel=document.getElementById('urut'),chk=document.getElementById('hanyaAda'),
     tabs=[].slice.call(document.querySelectorAll('.tab')),cnt=document.getElementById('hitung'),
     emp=document.getElementById('kosong'),rst=document.getElementById('reset'),
     kat=<?php echo json_encode($kat_awal); ?>;

 function terapkan(){
  var kw=(inp.value||'').toLowerCase().split(/\s+/).filter(Boolean),n=0;
  cards.forEach(function(c){
   var s=(c.getAttribute('data-s')||'').toLowerCase(),ok=true;
   kw.forEach(function(w){if(s.indexOf(w)<0)ok=false;});
   if(kat&&c.getAttribute('data-kat')!==kat)ok=false;
   if(chk.checked&&c.getAttribute('data-av')!=='1')ok=false;
   c.hidden=!ok; if(ok)n++;
  });
  cnt.textContent=n; emp.hidden=(n!==0);
 }
 function urutkan(){
  var m=sel.value;
  cards.sort(function(a,b){
   if(m==='judul')return a.getAttribute('data-judul').localeCompare(b.getAttribute('data-judul'),'id');
   var ia=+a.getAttribute('data-id'),ib=+b.getAttribute('data-id');
   if(m==='baru')return ib-ia;
   return (+b.getAttribute('data-pop'))-(+a.getAttribute('data-pop'))||ib-ia;
  });
  cards.forEach(function(c){list.appendChild(c);});
 }
 inp.addEventListener('input',terapkan);
 chk.addEventListener('change',terapkan);
 sel.addEventListener('change',urutkan);
 tabs.forEach(function(t){t.addEventListener('click',function(){
  kat=t.getAttribute('data-kat')||'';
  tabs.forEach(function(x){x.classList.toggle('on',x===t);});
  terapkan();
 });});
 document.getElementById('formCari').addEventListener('submit',function(e){
  e.preventDefault(); terapkan();
  document.getElementById('semua').scrollIntoView({behavior:'smooth'});
 });
 if(rst){rst.addEventListener('click',function(){
  inp.value='';chk.checked=false;kat='';
  tabs.forEach(function(x,i){x.classList.toggle('on',i===0);});
  terapkan();
 });}
 terapkan();
})();
</script>
<?php echo cv_script(); ?>
</body></html>
<?php exit;