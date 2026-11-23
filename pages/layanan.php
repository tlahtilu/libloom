<?php
require dirname(__DIR__).'/config.php';
$adm = function_exists('isLoggedIn') ? isLoggedIn() : !empty($_SESSION['user_id']);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Layanan Perpustakaan · Libloom</title>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700;800&family=Figtree:wght@400;500;600;700&family=Lora:ital,wght@1,500&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
--cream:#fff7e6;
--paper:#fffdf7;
--sage:#e8edd9;
--green:#3a4630;
--muted:#777b6c;
--orange:#e49a4e;
--line:#e8e0ce;
}
*{box-sizing:border-box}
body{
margin:0;
background:var(--cream);
color:var(--green);
font-family:Outfit,sans-serif;
}
.wrap{width:min(1080px,88%);margin:auto}
.intro{padding:65px 0 30px}
.eyebrow{
font-size:11px;
letter-spacing:.16em;
text-transform:uppercase;
font-weight:800;
color:#78815f;
}
.intro h1{
font:700 clamp(38px,5vw,58px)/1.1 'Bricolage Grotesque';
margin:12px 0;
}
.intro p{
max-width:680px;
color:var(--muted);
line-height:1.8;
}
.service-list{
display:grid;
gap:18px;
padding:15px 0 75px;
}
.service{
display:grid;
grid-template-columns:260px 1fr;
min-height:190px;
background:var(--paper);
border:1px solid var(--line);
border-radius:22px;
overflow:hidden;
}
.service img{
width:100%;
height:100%;
min-height:190px;
object-fit:cover;
}
.copy{
padding:25px 30px;
align-self:center;
}
.copy small{
font-size:10px;
letter-spacing:.14em;
font-weight:800;
color:#a17a43;
text-transform:uppercase;
}
.copy h2{
font:700 23px 'Bricolage Grotesque';
margin:8px 0;
}
.copy p{
font-size:14px;
line-height:1.8;
color:var(--muted);
margin:0;
}
.back{
display:inline-block;
margin:0 0 65px;
color:#66734f;
text-decoration:none;
font-weight:700;
font-size:13px;
}

/* HEADER */
.site-header{
--cream:#FFF7E6;
--ink:#36432F;
--muted:#6F7664;
--green:#6CA24A;
--green-d:#3F6B2C;
--orange:#F28C38;
--line:#EFE2C4;
--accent-d:#8F4A12;
--sans:'Figtree',system-ui,Arial,sans-serif;
--head:'Bricolage Grotesque','Figtree',Arial,sans-serif;
font-family:var(--sans);
line-height:1.6;
margin:0;
}
.site-header *{margin:0;box-sizing:border-box}
.site-header a{color:inherit;text-decoration:none}
.site-header svg{display:block;max-width:100%}
.site-header .container{
width:100%;
max-width:1160px;
margin:0 auto;
padding:0 28px;
}
.site-header{
background:var(--cream);
border-bottom:1px solid var(--line);
position:sticky;
top:0;
z-index:20;
}
.nav-wrap{
display:flex;
align-items:center;
justify-content:space-between;
gap:24px;
min-height:80px;
position:relative;
}
.brand{display:flex;align-items:center;gap:12px}
.brand-mark{width:42px;height:42px;flex:none}
.brand-text{display:flex;flex-direction:column;line-height:1.15}
.brand-text strong{
font-family:var(--head);
font-weight:700;
font-size:1.08rem;
}
.brand-text small{
font-size:.6rem;
letter-spacing:.2em;
color:var(--muted);
}
.main-nav{
display:flex;
align-items:center;
gap:34px;
font-size:.88rem;
font-weight:600;
color:var(--green-d);
flex:1;
justify-content:center;
}
.main-nav a{position:relative;padding:6px 0}
.main-nav a.active{color:var(--ink)}
.main-nav a.active::after{
content:"";
position:absolute;
left:0;
bottom:-2px;
width:20px;
height:2px;
border-radius:2px;
background:var(--orange);
}
.main-nav .nav-login{
display:inline-flex;
align-items:center;
gap:8px;
padding:10px 22px;
border:1px solid var(--line);
border-radius:999px;
background:#FDEFD0;
color:var(--accent-d);
font-weight:700;
}
.main-nav .nav-login:hover{background:#FBE7A6}
.main-nav .nav-logout{color:var(--muted)}
.menu-toggle{
display:none;
background:none;
border:1px solid var(--line);
border-radius:10px;
font-size:1.3rem;
padding:6px 12px;
color:var(--ink);
cursor:pointer;
}

/* FOOTER */
.lb-footer *{margin:0;box-sizing:border-box}
.lb-footer a{text-decoration:none}
.lb-footer{
background:#fff7e6;
border-top:1px solid #e8e0ce;
padding:48px 6% 20px;
margin-top:0;
}
.lb-footer-main{
max-width:1160px;
margin:auto;
display:grid;
grid-template-columns:1.3fr .7fr 1fr;
gap:48px;
padding-bottom:32px;
}
.lb-footer h2{
font:700 30px 'Bricolage Grotesque',sans-serif;
color:#65744f;
}
.lb-footer h2 span{color:#e49a4e}
.lb-footer-tag{
font:italic 14px Lora,'Newsreader',Georgia,serif;
color:#687451;
margin:5px 0 12px;
}
.lb-footer p,.lb-footer a{
font-size:13px;
color:#777b6c;
line-height:1.8;
}
.lb-footer h3{
font:700 16px 'Bricolage Grotesque',sans-serif;
margin:5px 0 14px;
}
.lb-footer-nav{
display:flex;
flex-direction:column;
align-items:flex-start;
gap:8px;
}
.lb-footer-map{max-width:310px;margin-top:16px}
.lb-footer-map iframe{
width:100%;
height:145px;
border:1px solid #dce4ce;
border-radius:14px;
}
.lb-footer-bottom{
max-width:1160px;
margin:auto;
border-top:1px solid #e8e0ce;
padding-top:16px;
display:flex;
justify-content:space-between;
gap:14px;
font-size:11px;
color:#777b6c;
}

@media(max-width:900px){
.menu-toggle{display:block}
.main-nav{
display:none;
position:absolute;
top:100%;
left:0;
right:0;
flex-direction:column;
align-items:flex-start;
gap:6px;
background:var(--cream);
padding:16px 28px 22px;
border-bottom:1px solid var(--line);
}
.main-nav.open{display:flex}
}
@media(max-width:850px){
.lb-footer-main{grid-template-columns:1fr 1fr}
.lb-footer-brand{grid-column:1/-1}
}
@media(max-width:650px){
.wrap{width:90%}
.intro{padding:42px 0 22px}
.service{grid-template-columns:1fr}
.service img{height:190px;min-height:190px}
.copy{padding:22px}
.lb-footer-main{grid-template-columns:1fr;gap:27px}
.lb-footer-brand{grid-column:auto}
.lb-footer-bottom{flex-direction:column}
}
</style>
</head>

<body>

<header class="site-header">
<div class="container nav-wrap">

<a class="brand" href="../" aria-label="Libloom, beranda">
<img class="brand-mark" src="../assets/logolibloom.jpg" alt="Libloom" width="52" height="52" style="width:52px;height:52px;border-radius:12px;object-fit:cover">
<span class="brand-text">
<strong>Libloom</strong>
<small>WHERE DREAMS GROW BETWEEN PAGES</small>
</span>
</a>

<button class="menu-toggle" id="menuToggle" aria-label="Buka navigasi" aria-expanded="false">&#9776;</button>

<nav class="main-nav" id="mainNav">
<a href="../">Beranda</a>
<a href="../tentang.php">Tentang</a>
<a href="../koleksiopac.php">Koleksi</a>
<a class="active" href="layanan.php">Layanan</a>
<a href="agenda.php">Agenda</a>

<?php if($adm){ ?>
<a class="nav-login" href="../?page=dashboard">Dashboard</a>
<a class="nav-logout" href="../?act=logout">Keluar</a>
<?php } else { ?>
<a class="nav-login" href="../?page=login">Login</a>
<?php } ?>
</nav>

</div>
</header>

<main class="wrap">

<header class="intro">
<div class="eyebrow">Jelajahi fasilitas kami</div>
<h1>Layanan Perpustakaan</h1>
<p>
Berbagai layanan disediakan untuk membantu kegiatan membaca, belajar,
dan penelusuran informasi. Temukan fasilitas yang dapat kamu manfaatkan
selama berada di perpustakaan.
</p>
</header>

<section class="service-list">

<!-- 01 SIRKULASI -->
<article class="service">
<img src="../assets/layanansirkulasi.jpeg" alt="Layanan sirkulasi perpustakaan">
<div class="copy">
<small>01 · Layanan koleksi</small>
<h2>Layanan Sirkulasi</h2>
<p>
Layanan peminjaman, pengembalian, dan perpanjangan masa pinjam koleksi
perpustakaan. Pengguna dapat memanfaatkan koleksi sesuai ketentuan yang berlaku.
</p>
</div>
</article>

<!-- 02 KOMPUTER -->
<article class="service">
<img src="../assets/layanankomputer.jpeg" alt="Layanan komputer perpustakaan">
<div class="copy">
<small>02 · Fasilitas digital</small>
<h2>Layanan Komputer</h2>
<p>
Penyediaan fasilitas komputer untuk mendukung kegiatan belajar,
mengerjakan tugas, mencari referensi, dan mengakses informasi digital.
</p>
</div>
</article>

<!-- 03 RUANG BACA -->
<article class="service">
<img src="../assets/ruangbaca.jpeg" alt="Ruang baca dan belajar perpustakaan">
<div class="copy">
<small>03 · Ruang belajar</small>
<h2>Ruang Baca dan Belajar</h2>
<p>
Ruang yang nyaman untuk membaca, belajar mandiri, mengerjakan tugas,
maupun melakukan kegiatan pembelajaran dengan suasana yang tenang.
</p>
</div>
</article>

<!-- 04 OPAC -->
<article class="service">
<img src="../assets/layananOPAC.jpeg" alt="Layanan OPAC perpustakaan">
<div class="copy">
<small>04 · Akses koleksi</small>
<h2>Layanan OPAC</h2>
<p>
Fasilitas penelusuran katalog secara online untuk membantu pengguna
menemukan koleksi berdasarkan judul, pengarang, kategori, maupun
informasi bibliografis lainnya.
</p>
</div>
</article>

<!-- 05 PENELUSURAN -->
<article class="service">
<img src="../assets/layananpenelusuran.jpeg" alt="Layanan penelusuran informasi">
<div class="copy">
<small>05 · Dukungan informasi</small>
<h2>Layanan Penelusuran Informasi</h2>
<p>
Bantuan bagi pengguna dalam menemukan sumber informasi yang relevan
untuk memenuhi kebutuhan tugas, pembelajaran, dan pencarian informasi lainnya.
</p>
</div>
</article>

<!-- 06 REFERENSI -->
<article class="service">
<img src="../assets/fotolayananreferensi.jpeg" alt="Layanan referensi perpustakaan">
<div class="copy">
<small>06 · Sumber rujukan</small>
<h2>Layanan Referensi</h2>
<p>
Layanan pemanfaatan sumber-sumber referensi untuk membantu pengguna
memperoleh jawaban atas pertanyaan atau kebutuhan informasi yang lebih spesifik.
</p>
</div>
</article>

</section>

<a class="back" href="../">← Kembali ke Beranda</a>

</main>

<footer class="lb-footer"><div class="lb-footer-main">
  <div class="lb-footer-brand"><h2>Libloom<span>.</span></h2><div class="lb-footer-tag">Where Dreams Grow Between Pages</div><p>Ruang untuk menemukan pengetahuan, menjelajahi imajinasi, dan menumbuhkan kecintaan terhadap membaca.</p><div class="lb-footer-map"><strong style="display:block;font-size:10px;letter-spacing:.13em;margin:12px 0 7px;color:#687451">TEMUKAN LOKASI KAMI</strong><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d9766.192141529253!2d4.538814411728285!3d52.26975188292057!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47c5c20434806f1d%3A0x29417663c1c3b37d!2sStationsweg%20166A%2C%202161%20AM%20Lisse%2C%20Belanda!5e0!3m2!1sid!2sid!4v1794799412265!5m2!1sid!2sid" title="Peta lokasi Libloom" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div></div>
  <div><h3>Jelajahi</h3><div class="lb-footer-nav"><a href="../">Beranda</a><a href="../tentang.php">Tentang Kami</a><a href="../koleksiopac.php">Koleksi</a><a href="layanan.php">Layanan</a><a href="agenda.php">Agenda</a></div></div>
  <div><h3>Hubungi Kami</h3><p>Email<br><a href="mailto:libloom@gmail.com">libloom@gmail.com</a></p><p style="margin-top:10px">Telepon<br><a href="tel:+62888666777">+62 888-666-777</a></p><p style="margin-top:10px">Instagram<br><a href="https://instagram.com/libloom" target="_blank">@libloom</a></p><p style="margin-top:10px">Alamat<br>Stationsweg 166A, 2161 AM Lisse, Belanda</p></div>
</div><div class="lb-footer-bottom"><span>© <?= date('Y') ?> Perpustakaan Libloom. Where Dreams Grow Between Pages.</span><span>Read · Discover · Grow</span></div></footer>

<script>
(function(){
    var b = document.getElementById('menuToggle');
    var n = document.getElementById('mainNav');

    if(b && n){
        b.addEventListener('click',function(){
            var o = n.classList.toggle('open');
            b.setAttribute('aria-expanded',o?'true':'false');
        });
    }
})();
</script>

</body>
</html>