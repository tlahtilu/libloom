<?php
// =====================================================================
// tentang.php - HALAMAN TENTANG PERPUSTAKAAN untuk Libloom
// Simpan di folder utama libloom/ (sejajar dengan index.php & hasil.php).
// File ini BERDIRI SENDIRI dan hanya memakai q(), h(), $BQ, $adm dari config.php.
// Semua isi yang perlu kamu sesuaikan ada di blok $T di bawah ini.
// =====================================================================
require_once __DIR__.'/config.php';
$adm = function_exists('isLoggedIn') ? isLoggedIn() : !empty($_SESSION['user_id']);
date_default_timezone_set('Asia/Jakarta');

// >>>>>>>>>>>>>>>>  UBAH ISI DI SINI SESUAI SEKOLAHMU  <<<<<<<<<<<<<<<<<<
$T=array(
 'sekolah'  =>'Libloom',
 'deskripsi'=>'Libloom adalah perpustakaan sekolah yang menyediakan buku bacaan, buku pelajaran, dan referensi untuk siswa dan guru. Katalognya bisa dicari dari mana saja, sedangkan peminjaman dan pengembalian dilayani langsung oleh pustakawan di meja sirkulasi.',
 'visi'     =>'Menjadi pusat sumber belajar yang nyaman dan menumbuhkan budaya membaca di lingkungan sekolah.',
 'misi'     =>array(
   'Menyediakan koleksi bacaan yang beragam dan mudah ditemukan.',
   'Memberikan layanan peminjaman yang cepat, tertib, dan ramah.',
   'Mengadakan kegiatan literasi untuk menumbuhkan minat baca.',
 ),
 // jam buka: 1=Senin ... 7=Minggu. Isi 'Buka'/'Tutup' dengan format JJ:MM, atau null kalau tutup.
 'jam'      =>array(
   1=>array('Senin','07:00','15:00'),
   2=>array('Selasa','07:00','15:00'),
   3=>array('Rabu','07:00','15:00'),
   4=>array('Kamis','07:00','15:00'),
   5=>array('Jumat','07:00','11:00'),
   6=>array('Sabtu',null,null),
   7=>array('Minggu',null,null),
 ),
 'istirahat'=>'Pelayanan sirkulasi dijeda saat jam istirahat sekolah.',
 'aturan'   =>array(
   array('Batas pinjam','Maksimal 3 buku untuk setiap anggota.'),
   array('Lama pinjam','7 hari. Buku referensi hanya boleh dibaca di tempat.'),
   array('Perpanjangan','Bisa 1 kali, selama bukunya tidak sedang dipesan orang lain.'),
   array('Denda','Rp500 per buku per hari keterlambatan. Buku hilang atau rusak diganti sesuai ketentuan sekolah.'),
 ),
 'tatatertib'=>array(
   'Jaga ketenangan di ruang baca.',
   'Simpan tas di tempat yang disediakan.',
   'Dilarang makan dan minum di area koleksi.',
   'Kembalikan buku yang sudah dibaca ke meja sirkulasi, bukan ke rak.',
 ),
 'fasilitas'=>array('Ruang baca ber-AC','Komputer katalog','Wi-Fi sekolah','Meja diskusi','Sudut baca santai'),
 'pustakawan'=>'Ardiyanti Rahma',
 'wa'       =>'0888-666-777',
 'email'    =>'libloom@gmail.com',
 'faq'      =>array(
   array('Bagaimana cara menjadi anggota?','Datanglah ke meja sirkulasi dengan membawa kartu pelajar. Pustakawan akan mendaftarkanmu ke sistem.'),
   array('Bagaimana tahu sebuah buku sedang ada di rak?','Cari bukunya di katalog. Setiap buku menampilkan jumlah eksemplar yang tersedia dan nomor panggilnya.'),
   array('Bisakah meminjam dari rumah?','Katalog bisa dilihat dari mana saja, tetapi peminjaman tetap dilakukan langsung di meja sirkulasi.'),
   array('Bagaimana kalau bukunya sedang dipinjam?','Tanyakan ke pustakawan. Kamu bisa menunggu sampai bukunya kembali atau mencari buku lain yang sejenis.'),
   array('Bagaimana kalau buku hilang atau rusak?','Segera lapor ke pustakawan. Buku akan diganti sesuai ketentuan yang berlaku di sekolah.'),
 ),
);
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

function tt_ic($n){
 $p=array(
  'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  'pin'=>'<path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
  'book'=>'<path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z"/><path d="M4 19V5"/>',
  'phone'=>'<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
  'mail'=>'<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
  'shield'=>'<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/>',
  'user'=>'<circle cx="12" cy="8" r="3.6"/><path d="M4.5 20c.7-3.8 3.7-5.6 7.5-5.6s6.8 1.8 7.5 5.6"/>',
  'spark'=>'<path d="M12 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2z"/>',
  'check'=>'<path d="M5 12.5l4.5 4.5L19 7.5"/>',
 );
 return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.(isset($p[$n])?$p[$n]:'').'</svg>';
}
function tt_jam($v){ return str_replace(':','.',$v); }

// status buka sekarang
$hari=(int)date('N'); $now=date('H:i');
$hr=$T['jam'][$hari];
$buka_skrg=($hr[1]!==null && $now>=$hr[1] && $now<$hr[2]);
if($buka_skrg){ $status_txt='Buka sekarang'; $status_sub='Tutup pukul '.tt_jam($hr[2]); }
elseif($hr[1]!==null && $now<$hr[1]){ $status_txt='Belum buka'; $status_sub='Buka hari ini pukul '.tt_jam($hr[1]); }
else{
 $status_txt='Sedang tutup'; $status_sub='';
 for($i=1;$i<=7;$i++){ $d=($hari-1+$i)%7+1; if($T['jam'][$d][1]!==null){ $status_sub='Buka lagi '.($i==1?'besok':$T['jam'][$d][0]).' pukul '.tt_jam($T['jam'][$d][1]); break; } }
}

// statistik koleksi asli
$st=q("SELECT COUNT(*) AS judul, COALESCE(SUM(tot),0) AS eks, COALESCE(SUM(av),0) AS ada FROM ($BQ) t")->fetch();
$n_judul=(int)$st['judul']; $n_eks=(int)$st['eks']; $n_ada=(int)$st['ada'];
$rk=q("SELECT COUNT(DISTINCT kategori) AS c FROM biblio WHERE kategori<>''")->fetch(); $n_kat=(int)$rk['c'];
?><!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Tentang perpustakaan Libloom: jam buka, cara meminjam, peraturan, dan kontak.">
<title>Tentang | Libloom</title>
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
h1,h2,h3{font-family:var(--head);font-weight:700;letter-spacing:-.03em;line-height:1.12;color:var(--ink)}
h2{font-family:var(--serif);font-weight:500;font-size:clamp(1.8rem,3.4vw,2.5rem);letter-spacing:-.02em}
section[id],.kf>div[id]{scroll-margin-top:96px}

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

.button{display:inline-flex;align-items:center;gap:10px;padding:13px 26px;border-radius:999px;font-weight:700;font-size:.85rem;border:0;cursor:pointer;font-family:inherit}
.button-primary{background:var(--accent);color:#fff;box-shadow:0 12px 24px rgba(217,116,31,.28)}
.button-primary:hover{background:#C4651A}
.button-line{border:1.5px solid var(--accent-d);color:var(--accent-d);background:transparent}
.button-line:hover{background:var(--tint)}

/* hero */
.page{padding-top:36px;padding-bottom:80px}
.crumb{font-size:.78rem;color:var(--muted);margin-bottom:16px}.crumb a{color:var(--accent);font-weight:600}
.hero{display:grid;grid-template-columns:1.15fr .85fr;gap:40px;align-items:center;padding:12px 0 44px}
.hero h1{font-family:var(--serif);font-weight:500;font-size:clamp(2.3rem,5vw,3.8rem);letter-spacing:-.02em;line-height:1.05}
.hero h1 span{color:var(--accent)}
.hero p{color:var(--muted);font-size:1rem;margin:18px 0 26px;max-width:520px}
.hero .acts{display:flex;gap:14px;flex-wrap:wrap}
.today{background:#fff;border:1px solid var(--line);border-radius:28px;padding:28px;box-shadow:0 30px 60px -28px rgba(63,107,44,.35);position:relative;overflow:hidden}
.today::before{content:"";position:absolute;right:-40px;top:-40px;width:140px;height:140px;border-radius:50%;background:var(--sun);opacity:.3}
.today>*{position:relative}
.today small{font-size:.72rem;font-weight:700;letter-spacing:.14em;color:var(--muted);text-transform:uppercase}
.state{display:flex;align-items:center;gap:12px;margin:12px 0 4px;font:700 1.7rem var(--head);letter-spacing:-.03em}
.dot{width:14px;height:14px;border-radius:50%;background:#C9674F;flex:none}
.dot.on{background:#5FA033;box-shadow:0 0 0 6px rgba(95,160,51,.2)}
.today p{margin:0;font-size:.9rem;color:var(--muted)}

/* statistik */
.stats{display:grid;grid-template-columns:repeat(4,1fr);background:var(--sand);border:1px solid var(--line);border-radius:26px;overflow:hidden}
.stat{padding:24px 26px;border-right:1px solid var(--line)}.stat:last-child{border-right:0}
.stat b{display:block;font:700 2.2rem var(--head);letter-spacing:-.04em;color:var(--accent-d);line-height:1.1}
.stat span{font-size:.84rem;color:var(--muted)}

/* blok umum */
.block{padding-top:72px}
.block>.head{margin-bottom:26px;max-width:640px}
.block>.head p{color:var(--muted);margin-top:10px;font-size:.95rem}
.two{display:grid;grid-template-columns:1fr 1fr;gap:22px}
.card{background:#fff;border:1px solid var(--line);border-radius:24px;padding:28px}
.card h3{font-size:1.1rem;display:flex;align-items:center;gap:12px;margin-bottom:14px}
.ico{width:44px;height:44px;border-radius:14px;display:grid;place-items:center;flex:none;color:var(--accent-d);background:var(--tint)}
.ico.s{background:#FBE7A6}.ico.p{background:#FADBE1}.ico.o{background:#FCDDBF}
.card p{color:#4f5848;font-size:.92rem}
.card ul li{position:relative;padding:7px 0 7px 22px;font-size:.92rem;color:#4f5848}
.card ul li::before{content:"";position:absolute;left:2px;top:17px;width:8px;height:8px;border-radius:50%;background:var(--orange)}
.vm{background:var(--accent-d);color:#fff;border-color:var(--accent-d)}
.vm h3{color:#fff}.vm p,.vm li{color:rgba(255,255,255,.88)}.vm li::before{background:var(--sun)}
.vm .ico{background:rgba(255,255,255,.14);color:#fff}

/* jam buka */
.card ul.jam li{display:flex;justify-content:space-between;gap:12px;padding:11px 14px;border-radius:12px;font-size:.92rem}
.card ul.jam li:nth-child(odd){background:#FFFAEE}
.card ul.jam li.now{background:var(--tint);font-weight:700;color:var(--accent-d)}
.card ul.jam li.now span:first-child::after{content:" · hari ini";font-weight:600;font-size:.78rem}
.card ul.jam li .tutup{color:#C9674F;font-weight:600}
.note{margin-top:14px;font-size:.84rem;color:var(--muted)}
.card ul.jam li::before{display:none}

/* langkah */
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;counter-reset:s}
.step{background:#fff;border:1px solid var(--line);border-radius:24px;padding:26px 22px;position:relative;counter-increment:s}
.step::before{content:counter(s);display:grid;place-items:center;width:40px;height:40px;border-radius:50%;background:var(--orange);color:#fff;font:700 1.1rem var(--head);margin-bottom:16px}
.step:nth-child(2)::before{background:var(--accent-d)}.step:nth-child(3)::before{background:var(--sun);color:var(--ink)}.step:nth-child(4)::before{background:var(--blush);color:var(--ink)}
.step h3{font-size:1.02rem;margin-bottom:6px}
.step p{font-size:.88rem;color:var(--muted)}

/* aturan */
.rule{display:grid;grid-template-columns:150px 1fr;gap:14px;padding:14px 0;border-bottom:1px dashed var(--line);font-size:.92rem}
.rule:last-child{border-bottom:0}
.rule b{color:var(--accent-d)}.rule span{color:#4f5848}
.tags{display:flex;flex-wrap:wrap;gap:10px}
.tag{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:999px;background:#fff;border:1px solid var(--line);font-size:.84rem;font-weight:600;color:var(--accent-d)}
.tag svg{width:16px;height:16px;color:var(--orange)}

/* kontak */
.kf{display:grid;grid-template-columns:1fr 1fr;gap:22px 40px;align-items:start}
.kf .head{margin-bottom:26px}
.kf .head p{color:var(--muted);margin-top:10px;font-size:.95rem}
.kontak-item{display:flex;gap:14px;align-items:flex-start;padding:14px 0;border-bottom:1px dashed var(--line)}
.kontak-item:last-child{border-bottom:0}
.kontak-item small{display:block;font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);font-weight:700}
.kontak-item strong{font-size:.98rem}
.kontak-item a:hover strong{color:var(--accent)}

/* faq */
.faq details{background:#fff;border:1px solid var(--line);border-radius:18px;margin-bottom:12px;padding:0 22px}
.faq summary{cursor:pointer;list-style:none;padding:18px 0;font-weight:600;display:flex;justify-content:space-between;gap:16px;align-items:center}
.faq summary::-webkit-details-marker{display:none}
.faq summary::after{content:"+";font:500 1.5rem var(--serif);color:var(--orange);flex:none;transition:transform .2s}
.faq details[open] summary::after{transform:rotate(45deg)}
.faq details p{padding:0 0 20px;color:#4f5848;font-size:.92rem}

/* footer */
.site-footer{padding:36px 0;background:var(--cream);border-top:1px solid var(--line)}
.footer-inner{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:20px}
.footer-tagline{font-family:var(--serif);font-style:italic;color:var(--muted);text-align:center}
.copyright{font-size:.75rem;color:var(--muted);text-align:right}

@media (max-width:900px){
  .menu-toggle{display:block}
  .main-nav{display:none;position:absolute;top:100%;left:0;right:0;flex-direction:column;align-items:flex-start;gap:6px;background:var(--cream);padding:16px 28px 22px;border-bottom:1px solid var(--line)}
  .main-nav.open{display:flex}
  .hero,.two,.kf{grid-template-columns:1fr}
  .stats{grid-template-columns:1fr 1fr}
  .stat:nth-child(2){border-right:0}.stat:nth-child(-n+2){border-bottom:1px solid var(--line)}
  .steps{grid-template-columns:1fr 1fr}
  .footer-inner{grid-template-columns:1fr;text-align:center;justify-items:center}.copyright{text-align:center}
}
@media (max-width:560px){
  .container{padding:0 18px}
  .steps{grid-template-columns:1fr}
  .rule{grid-template-columns:1fr;gap:2px}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}
</style></head><body>
<header class="site-header"><div class="container nav-wrap">
<a class="brand" href="index.php" aria-label="Libloom, beranda"><img class="brand-mark" src="assets/logolibloom.jpg" alt="Libloom" width="52" height="52" style="width:52px;height:52px;border-radius:12px;object-fit:cover"><span class="brand-text"><strong>Libloom</strong><small>WHERE DREAMS GROW BETWEEN PAGES</small></span></a>
<button class="menu-toggle" id="menuToggle" aria-label="Buka navigasi" aria-expanded="false">&#9776;</button>
<nav class="main-nav" id="mainNav"><a href="index.php">Beranda</a><a class="active" href="tentang.php">Tentang</a><a href="koleksiopac.php">Koleksi</a><a href="pages/layanan.php">Layanan</a><a href="pages/agenda.php">Agenda</a>
<?php if($adm){ ?><a class="nav-login" href="index.php?page=dashboard"><svg width="13" height="13" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="5" r="3.2" fill="currentColor"/><path d="M2 14.5c.6-3 3-4.6 6-4.6s5.4 1.6 6 4.6z" fill="currentColor"/></svg>Dashboard</a><a class="nav-logout" href="index.php?act=logout">Keluar</a>
<?php } else { ?><a class="nav-login" href="index.php?page=login"><svg width="13" height="13" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="5" r="3.2" fill="currentColor"/><path d="M2 14.5c.6-3 3-4.6 6-4.6s5.4 1.6 6 4.6z" fill="currentColor"/></svg>Login</a>
<?php } ?></nav></div></header>

<main class="container page">
<div class="crumb"><a href="index.php">Beranda</a> / Tentang</div>

<section class="hero">
 <div>
  <h1>Perpustakaan <span><?php echo h($T['sekolah']); ?></span></h1>
  <p><?php echo h($T['deskripsi']); ?></p>
  <div class="acts"><a class="button button-primary" href="koleksiopac.php">Lihat koleksi</a><a class="button button-line" href="#kontak">Hubungi pustakawan</a></div>
 </div>
 <aside class="today" aria-label="Status perpustakaan hari ini">
  <small>Hari ini, <?php echo h($hr[0]); ?></small>
  <div class="state"><span class="dot<?php echo $buka_skrg?' on':''; ?>"></span><?php echo h($status_txt); ?></div>
  <?php if($status_sub!==''){ ?><p><?php echo h($status_sub); ?></p><?php } ?>
 </aside>
</section>

<section class="stats" aria-label="Ringkasan koleksi">
 <div class="stat"><b><?php echo number_format($n_judul,0,',','.'); ?></b><span>judul buku</span></div>
 <div class="stat"><b><?php echo number_format($n_eks,0,',','.'); ?></b><span>total eksemplar</span></div>
 <div class="stat"><b><?php echo number_format($n_ada,0,',','.'); ?></b><span>ada di rak sekarang</span></div>
 <div class="stat"><b><?php echo $n_kat; ?></b><span>kategori koleksi</span></div>
</section>

<section class="block" id="visi">
 <div class="two">
  <div class="card vm"><h3><span class="ico"><?php echo tt_ic('spark'); ?></span>Visi</h3><p><?php echo h($T['visi']); ?></p></div>
  <div class="card"><h3><span class="ico s"><?php echo tt_ic('check'); ?></span>Misi</h3><ul><?php foreach($T['misi'] as $m){ echo '<li>'.h($m).'</li>'; } ?></ul></div>
 </div>
</section>

<section class="block" id="jam">
 <div class="head"><h2>Jam buka</h2><p>Perpustakaan melayani sirkulasi pada jam berikut.</p></div>
 <div class="two">
  <div class="card"><h3><span class="ico"><?php echo tt_ic('clock'); ?></span>Jadwal pelayanan</h3>
   <ul class="jam">
   <?php foreach($T['jam'] as $no=>$j){
     echo '<li'.($no===$hari?' class="now"':'').'><span>'.h($j[0]).'</span>'.($j[1]!==null?'<span>'.tt_jam($j[1]).' - '.tt_jam($j[2]).'</span>':'<span class="tutup">Tutup</span>').'</li>';
   } ?>
   </ul>
   <?php if($T['istirahat']!==''){ ?><p class="note"><?php echo h($T['istirahat']); ?></p><?php } ?>
  </div>
  <div class="card"><h3><span class="ico p"><?php echo tt_ic('user'); ?></span>Fasilitas</h3>
   <p style="margin-bottom:16px">Yang bisa kamu gunakan selama berada di perpustakaan:</p>
   <div class="tags"><?php foreach($T['fasilitas'] as $f){ echo '<span class="tag">'.tt_ic('check').h($f).'</span>'; } ?></div>
  </div>
 </div>
</section>

<section class="block" id="cara">
 <div class="head"><h2>Cara meminjam buku</h2><p>Empat langkah, tidak perlu antre lama.</p></div>
 <div class="steps">
  <div class="step"><h3>Cari di katalog</h3><p>Ketik judul atau pengarang di halaman Koleksi, lalu cek apakah bukunya tersedia.</p></div>
  <div class="step"><h3>Catat nomor panggil</h3><p>Nomor panggil menunjukkan letak buku di rak. Catat atau foto layarnya.</p></div>
  <div class="step"><h3>Ambil dari rak</h3><p>Cari bukunya di rak sesuai nomor panggil. Bingung? Tanya pustakawan.</p></div>
  <div class="step"><h3>Pinjam di sirkulasi</h3><p>Bawa buku dan kartu pelajar ke meja sirkulasi untuk dicatat.</p></div>
 </div>
</section>

<section class="block" id="aturan">
 <div class="head"><h2>Peraturan singkat</h2><p>Supaya semua nyaman dan bukunya awet dibaca banyak orang.</p></div>
 <div class="two">
  <div class="card"><h3><span class="ico o"><?php echo tt_ic('book'); ?></span>Peminjaman</h3>
   <?php foreach($T['aturan'] as $a){ echo '<div class="rule"><b>'.h($a[0]).'</b><span>'.h($a[1]).'</span></div>'; } ?>
  </div>
  <div class="card"><h3><span class="ico"><?php echo tt_ic('shield'); ?></span>Tata tertib ruangan</h3>
   <ul><?php foreach($T['tatatertib'] as $a){ echo '<li>'.h($a).'</li>'; } ?></ul>
  </div>
 </div>
</section>

<section class="block kf">
 <div id="kontak">
  <div class="head"><h2>Hubungi kami</h2><p>Punya pertanyaan atau mau mengusulkan buku? Langsung saja.</p></div>
  <div class="card">
   <div class="kontak-item"><span class="ico"><?php echo tt_ic('user'); ?></span><div><small>Pustakawan</small><strong><?php echo h($T['pustakawan']); ?></strong></div></div>
   <div class="kontak-item"><span class="ico s"><?php echo tt_ic('phone'); ?></span><div><small>WhatsApp</small><a href="https://wa.me/<?php echo h(preg_replace('/\D/','',preg_replace('/^0/','62',$T['wa']))); ?>" rel="noopener"><strong><?php echo h($T['wa']); ?></strong></a></div></div>
   <div class="kontak-item"><span class="ico p"><?php echo tt_ic('mail'); ?></span><div><small>Email</small><a href="mailto:<?php echo h($T['email']); ?>"><strong><?php echo h($T['email']); ?></strong></a></div></div>
  </div>
 </div>
 <div class="faq" id="faq">
  <div class="head"><h2>Pertanyaan yang sering muncul</h2><p>Jawaban singkat untuk hal yang paling sering ditanyakan.</p></div>
  <?php foreach($T['faq'] as $f){ echo '<details><summary>'.h($f[0]).'</summary><p>'.h($f[1]).'</p></details>'; } ?>
 </div>
</section>
</main>

<footer class="site-footer"><div class="container footer-inner">
<a class="brand" href="index.php" aria-label="Libloom"><img class="brand-mark" src="assets/logolibloom.jpg" alt="Libloom" width="52" height="52" style="width:52px;height:52px;border-radius:12px;object-fit:cover"><span class="brand-text"><strong>Libloom</strong><small>WHERE DREAMS GROW BETWEEN PAGES</small></span></a>
<p class="footer-tagline">Tempat cerita dan pengetahuan bertemu.</p>
<p class="copyright">&copy; <?php echo date('Y'); ?> Libloom. Dibuat untuk komunitas sekolah.</p>
</div></footer>
<script>
(function(){var b=document.getElementById('menuToggle'),n=document.getElementById('mainNav');if(!b||!n)return;
b.addEventListener('click',function(){var o=n.classList.toggle('open');b.setAttribute('aria-expanded',o?'true':'false');});})();
</script>
</body></html>
<?php exit;