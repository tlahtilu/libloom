<?php
// =====================================================================
// HALAMAN DEPAN + OPAC  (pages/opac.php)
// File ini BERDIRI SENDIRI: CSS, HTML, header, footer, dan JS ada di sini.
// Tidak mengubah/bergantung pada layout.php maupun assets/style.css,
// jadi aman digabung dengan perubahan teman satu tim.
// Hanya memakai fungsi/variabel dari config.php: q(), h(), card(), $CATS, $BQ, $adm
// Pencarian & klik buku diteruskan ke hasil.php (file terpisah, satu folder dengan index.php).
// =====================================================================
$qs=trim((isset($_GET['q'])?$_GET['q']:''));
$kat=(isset($_GET['kat'])?$_GET['kat']:'');
$glyph=array('Buku Teks'=>'▤','Fiksi'=>'❧','Nonfiksi'=>'✳','Referensi'=>'✿');

// ---------------------------------------------------------------------
// DATA BAGIAN BAWAH BERANDA
// >>> Samakan jam buka & lokasi dengan yang ada di tentang.php <<<
$INFO=array(
 'lokasi'=>'Gedung ..., lantai ... (isi lokasi ruang perpustakaan)',
 'jam'=>array(
  1=>array('Senin','07:00','15:00'),
  2=>array('Selasa','07:00','15:00'),
  3=>array('Rabu','07:00','15:00'),
  4=>array('Kamis','07:00','15:00'),
  5=>array('Jumat','07:00','11:00'),
  6=>array('Sabtu',null,null),
  7=>array('Minggu',null,null),
 ),
);
// FORM USULAN BUKU: tempel alamat Google Form di sini (contoh: https://forms.gle/xxxxx).
// Kalau diisi, bagian usulan menampilkan tombol ke Google Form. Kalau kosong, memakai form bawaan.
$FORM_USULAN='';

// AGENDA di beranda: isinya sama dengan pages/agenda.php. Format: array(label, judul, keterangan, jadwal)
$AGENDA=array(
 array('Agenda rutin · Setiap Jumat','Silent Reading','Sesi membaca bersama dalam suasana tenang. Peserta dapat membawa bacaan masing-masing dan menikmati waktu membaca tanpa gangguan.','Jadwal: Setiap hari Jumat'),
 array('Agenda spesial · Jadwal menyusul','Book Talk: Janji','Diskusi bersama mengenai novel Janji karya Tere Liye. Bagikan kesan, pemahaman, dan sudut pandangmu terhadap cerita.','Jadwal: Menyusul'),
);
date_default_timezone_set('Asia/Jakarta');
function ob_jam($v){ return str_replace(':','.',$v); }
function ob_tgl($t){
 $ts=strtotime($t); if(!$ts) return h($t);
 $bln=array('','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
 return date('j',$ts).' '.$bln[(int)date('n',$ts)].' '.date('Y',$ts);
}
function ob_row($judul,$rid,$data){
 echo '<div class="row-head"><h3>'.$judul.'</h3><span class="row-tools"><button type="button" class="arr" data-row="'.$rid.'" data-dir="-1" aria-label="Geser ke kiri">&larr;</button><button type="button" class="arr" data-row="'.$rid.'" data-dir="1" aria-label="Geser ke kanan">&rarr;</button></span></div>';
 if(!$data){ echo '<p class="empty-note">Belum ada koleksi yang ditambahkan.</p>'; return; }
 echo '<div class="row" id="'.$rid.'">';
 foreach($data as $b){ echo '<a class="bk-link" href="hasil.php?id='.(int)$b['id'].'">'.card($b).'</a>'; }
 echo '</div>';
}

// status buka sekarang
$hari=(int)date('N'); $now=date('H:i'); $hr=$INFO['jam'][$hari];
$buka_skrg=($hr[1]!==null && $now>=$hr[1] && $now<$hr[2]);
if($buka_skrg){ $status_txt='Buka sekarang'; $status_sub='Tutup pukul '.ob_jam($hr[2]); }
elseif($hr[1]!==null && $now<$hr[1]){ $status_txt='Belum buka'; $status_sub='Buka hari ini pukul '.ob_jam($hr[1]); }
else{
 $status_txt='Sedang tutup'; $status_sub='';
 for($i=1;$i<=7;$i++){ $d=($hari-1+$i)%7+1; if($INFO['jam'][$d][1]!==null){ $status_sub='Buka lagi '.($i==1?'besok':$INFO['jam'][$d][0]).' pukul '.ob_jam($INFO['jam'][$d][1]); break; } }
}

// statistik asli
$st=q("SELECT COUNT(*) AS judul, COALESCE(SUM(tot),0) AS eks, COALESCE(SUM(av),0) AS ada FROM ($BQ) t")->fetch();
$n_judul=(int)$st['judul']; $n_eks=(int)$st['eks']; $n_ada=(int)$st['ada']; $n_pinjam=max(0,$n_eks-$n_ada);

// buku & rak kategori
$populer=q("$BQ ORDER BY pop DESC,id LIMIT 8")->fetchAll();
$baru=q("$BQ ORDER BY id DESC LIMIT 8")->fetchAll();

// pengumuman (tabel `pengumuman`, opsional). Kalau tabel belum ada, bagiannya otomatis disembunyikan.
$pengumuman=array();
try{
 $ada=q("SELECT name FROM sqlite_master WHERE type='table' AND name='pengumuman'")->fetch();
 if($ada){ $pengumuman=q("SELECT id,judul,isi,tanggal FROM pengumuman ORDER BY id DESC LIMIT 3")->fetchAll(); }
}catch(Exception $e){ $pengumuman=array(); }
catch(Throwable $e){ $pengumuman=array(); }

// ---------------------------------------------------------------------
// FORM USULAN BUKU: dikirim ke usulan.php; hasilnya (tanpa JavaScript) dibaca lewat sesi
if(function_exists('session_status') && session_status()===PHP_SESSION_NONE && !headers_sent()){ @session_start(); }
if(empty($_SESSION['csrf_usulan'])){ $_SESSION['csrf_usulan']=md5(uniqid(mt_rand(),true)); }
$us_old=array('judul'=>'','pengarang'=>'','nama'=>'','kelas'=>'','alasan'=>'');
$us_err=array(); $us_ok=false;
if(!empty($_SESSION['usulan_flash'])){ $us_ok=true; unset($_SESSION['usulan_flash']); }
if(!empty($_SESSION['usulan_err']) && is_array($_SESSION['usulan_err'])){ $us_err=$_SESSION['usulan_err']; unset($_SESSION['usulan_err']); }
if(!empty($_SESSION['usulan_old']) && is_array($_SESSION['usulan_old'])){
 foreach($us_old as $k=>$v){ if(isset($_SESSION['usulan_old'][$k])) $us_old[$k]=(string)$_SESSION['usulan_old'][$k]; }
 unset($_SESSION['usulan_old']);
}
?><!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Libloom - ruang nyaman untuk membaca, belajar, dan menjelajahi pengetahuan.">
<title>Beranda | Libloom</title>
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
h1,h2{font-family:var(--head);font-weight:700;letter-spacing:-.03em;line-height:1.08;color:var(--ink)}
h1 em,h2 em{font-family:var(--serif);font-style:italic;font-weight:400;color:var(--green);letter-spacing:-.01em}
.eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:.68rem;font-weight:700;letter-spacing:.16em;color:var(--green-d);margin-bottom:18px}
.eyebrow i{width:8px;height:8px;border-radius:50%;background:var(--orange)}

/* header */
.site-header{background:var(--cream);border-bottom:1px solid var(--line);position:sticky;top:0;z-index:20}
.nav-wrap{display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:80px}
.brand{display:flex;align-items:center;gap:12px}
.brand-mark{width:42px;height:42px;flex:none}
.brand-text{display:flex;flex-direction:column;line-height:1.15}
.brand-text strong{font-family:var(--head);font-weight:700;font-size:1.08rem}
.brand-text small{font-size:.6rem;letter-spacing:.2em;color:var(--muted)}
.main-nav{display:flex;align-items:center;gap:34px;font-size:.88rem;font-weight:600;color:var(--green-d)}
.main-nav a{position:relative;padding:6px 0}
.main-nav a.active{color:var(--ink)}
.main-nav a.active::after{content:"";position:absolute;left:0;bottom:-2px;width:20px;height:2px;border-radius:2px;background:var(--orange)}
.main-nav .nav-login{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;border:1px solid var(--line);border-radius:999px;background:#FDEFD0;color:var(--accent-d);font-weight:700}
.main-nav .nav-login:hover{background:#FBE7A6}
.main-nav .nav-logout{color:var(--muted)}
.main-nav>a:first-child{margin-left:auto}
.nav-wrap{position:relative}
.main-nav{flex:1;justify-content:center;margin-left:0}
.main-nav>a:first-child{margin-left:0}
.menu-toggle{display:none;background:none;border:1px solid var(--line);border-radius:10px;font-size:1.3rem;padding:6px 12px;color:var(--ink);cursor:pointer}

/* hero: muat satu layar penuh di desktop */
.hero{padding:0;min-height:calc(100vh - 81px);display:flex;align-items:center}
.hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:40px;align-items:center;padding-top:clamp(12px,3vh,40px);padding-bottom:clamp(12px,3vh,40px)}
.hero .eyebrow{margin-bottom:clamp(8px,2vh,18px)}
.hero h1{font-size:clamp(2.3rem,min(5.4vw,9.5vh),4.5rem)}
.hero h1 em{display:block}
.hero-description{max-width:460px;margin:clamp(10px,2.4vh,26px) 0 clamp(14px,3.4vh,34px);color:var(--muted);font-size:clamp(.88rem,1.9vh,.98rem)}
.hero-actions{display:flex;align-items:center;gap:30px;flex-wrap:wrap}
.button{display:inline-flex;align-items:center;gap:14px;padding:16px 26px;border-radius:999px;font-weight:700;font-size:.85rem;border:0;cursor:pointer;font-family:inherit}
.hero .button{padding:clamp(11px,2vh,16px) 26px}
.button-primary{background:var(--green);color:#fff;box-shadow:0 12px 24px rgba(108,162,74,.28)}
.button-primary:hover{background:#5C9040}
.button-dark{background:var(--green-d);color:#fff}
.button-dark:hover{background:#2E4E20}
.button-secondary{border:1.5px solid var(--green-d);color:var(--green-d);background:transparent}
.arrow-chip{margin-left:8px;font-size:1rem}
.text-link{font-weight:700;font-size:.85rem;color:var(--green-d);display:inline-flex;gap:8px;align-items:center}
.text-link:hover{color:var(--green)}
.hero-note{display:flex;align-items:center;gap:12px;margin-top:clamp(16px,5vh,56px)}
.dots{display:flex}
.dots i{width:28px;height:28px;border-radius:50%;display:grid;place-items:center;font-style:normal;font-size:.8rem;margin-right:-6px;border:2px solid var(--cream)}
.dots i:nth-child(1){background:#C7DDAE;color:var(--green-d)}
.dots i:nth-child(2){background:#F4C542;color:var(--green-d)}
.dots i:nth-child(3){background:#F7B7C4;color:var(--green-d)}
.hero-note span:last-child{margin-left:10px;display:flex;flex-direction:column;line-height:1.35}
.hero-note strong{font-size:.76rem}
.hero-note small{font-size:.68rem;color:var(--muted)}
.hero-art{display:flex;justify-content:flex-end}
.hero-art svg{width:100%;height:auto;max-height:calc(100vh - 81px - 48px);border-radius:0 0 24px 24px}

/* hero: search bar + jelajahi (tambahan) */
.hero .sbar{margin-top:0}
.hero .sbar .button{padding:clamp(10px,1.6vh,13px) 26px}
.hero .chips{margin-top:clamp(10px,2vh,18px)}
.hero .hero-note{margin-top:clamp(14px,3.5vh,40px)}

/* ===== bagian bawah beranda ===== */
h2{font-size:clamp(1.8rem,3.4vw,2.5rem)}
.sec-sub{color:var(--muted);font-size:.95rem;margin-top:8px;max-width:520px}
.sec-top{display:flex;justify-content:space-between;align-items:end;gap:20px;flex-wrap:wrap}
.more{font-weight:700;font-size:.85rem;color:#D9741F}.more:hover{color:var(--ink)}

/* statistik + jam buka */
.band{background:var(--sand);border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:30px 0}
.band-grid{display:grid;grid-template-columns:1.5fr 1fr;gap:22px;align-items:stretch}
.stats{display:grid;grid-template-columns:repeat(4,1fr);background:#fff;border:1px solid var(--line);border-radius:24px;overflow:hidden}
.stat{padding:22px 20px;border-right:1px solid var(--line);display:flex;flex-direction:column;justify-content:center}
.stat:last-child{border-right:0}
.stat b{font:700 2rem var(--head);letter-spacing:-.04em;color:var(--ink);line-height:1.1}
.stat span{font-size:.82rem;color:var(--muted)}
.open-card{background:#fff;color:var(--ink);border:1px solid var(--line);border-radius:24px;padding:22px 26px;display:flex;flex-direction:column;justify-content:center;gap:2px;position:relative;overflow:hidden;box-shadow:0 18px 40px -24px rgba(54,67,47,.3)}
.open-card::after{content:"";position:absolute;right:-34px;top:-34px;width:120px;height:120px;border-radius:50%;background:var(--blush);opacity:.35}
.open-card>*{position:relative;z-index:1}
.open-card small{font-size:.7rem;letter-spacing:.14em;font-weight:700;text-transform:uppercase;color:var(--muted)}
.open-state{display:flex;align-items:center;gap:10px;font:700 1.5rem var(--head);letter-spacing:-.02em;margin-top:4px}
.dot{width:12px;height:12px;border-radius:50%;background:var(--orange);flex:none}
.dot.on{background:#5FA033;box-shadow:0 0 0 5px rgba(95,160,51,.22)}
.open-card p{font-size:.85rem;color:var(--muted)}
.open-card .more{color:#D9741F;margin-top:8px;font-size:.82rem}
.open-card .more:hover{color:var(--ink)}

/* buku */
.books{padding:52px 0 56px}
.row-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin:30px 0 12px}
.row-head h3{font-family:var(--head);font-weight:700;font-size:1.3rem;letter-spacing:-.02em}
.row-tools{display:flex;gap:8px}
.arr{width:34px;height:34px;border-radius:50%;border:1px solid var(--line);background:#fff;color:var(--green-d);cursor:pointer;font-size:.95rem}
.arr:hover{background:#FBE7A6}
.empty-note{color:var(--muted);background:#fff;border:1px dashed #D9DDCF;border-radius:16px;padding:22px}

/* layanan */
.access{padding:52px 0 60px}
.access-grid{display:grid;grid-template-columns:1fr 1fr;gap:50px;align-items:start}
.access h2,.agenda h2{font-size:clamp(2.1rem,4.2vw,3rem)}
.access h2 em,.agenda h2 em{display:inline;font-family:var(--serif);font-style:italic;font-weight:400;color:var(--orange);letter-spacing:-.01em}
.access .eyebrow,.agenda .eyebrow{color:var(--ink)}
.access p{color:var(--muted);font-size:.92rem;margin:16px 0 22px;max-width:400px}
.access .button-dark{background:var(--orange);color:#fff;box-shadow:0 12px 24px rgba(242,140,56,.28)}
.access .button-dark:hover{background:#E07A26}
.access-list{border-top:1px solid var(--line)}
.access-list li{border-bottom:1px solid var(--line)}
.access-list .al{display:flex;align-items:center;gap:22px;padding:22px 4px}
.access-list .no{font-family:var(--serif);color:var(--orange);font-size:1.05rem;width:28px}
.access-list span:nth-child(2){flex:1;display:flex;flex-direction:column;line-height:1.4}
.access-list strong{font-size:.92rem}
.access-list small{font-size:.78rem;color:var(--muted)}

/* agenda */
.agenda{background:#FCEBBF;padding:52px 0}
.agenda-grid{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
.agenda-list{display:flex;flex-direction:column;gap:18px}
.agenda-card{background:var(--cream);border-radius:26px;padding:36px 30px;box-shadow:0 22px 50px rgba(54,67,47,.1)}
.agenda-tag{font-size:.66rem;font-weight:700;letter-spacing:.14em;color:var(--orange)}
.agenda-card h3{font-family:var(--serif);font-weight:400;font-size:1.65rem;margin:14px 0 8px;line-height:1.25}
.agenda-card p{font-size:.84rem;color:var(--muted);margin-bottom:22px}
.agenda-card p:last-child{margin-bottom:0}
.pill{display:inline-block;background:#FADBE1;color:var(--ink);font-size:.72rem;font-weight:700;padding:9px 16px;border-radius:999px}

/* kotak bantuan */
.helpsec{padding:44px 0 56px}
.help{display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;background:#fff;color:var(--ink);border:1px solid var(--line);border-radius:26px;padding:26px 32px;position:relative;overflow:hidden;box-shadow:0 18px 40px -24px rgba(54,67,47,.3)}
.help::after{content:"";position:absolute;right:150px;top:-60px;width:150px;height:150px;border-radius:50%;background:var(--blush);opacity:.3}
.help>*{position:relative;z-index:1}
.help strong{font:500 1.5rem var(--serif);display:block}
.help span{font-size:.9rem;color:var(--muted)}
.help .acts{display:flex;gap:12px;flex-wrap:wrap}
.help .button-light{background:var(--orange);color:#fff;box-shadow:0 12px 24px rgba(242,140,56,.28)}.help .button-light:hover{background:#E07A26}
.button-ghost{border:1.5px solid var(--ink);color:var(--ink)}.button-ghost:hover{background:rgba(54,67,47,.08)}

/* usulan buku */
.usulan{background:var(--sand);padding:56px 0 64px}
.usulan-grid{display:grid;grid-template-columns:.9fr 1.1fr;gap:48px;align-items:start}
.usulan h2{font-size:clamp(2.1rem,4.2vw,3rem)}
.usulan h2 em{display:inline;font-family:var(--serif);font-style:italic;font-weight:400;color:var(--orange);letter-spacing:-.01em}
.usulan .eyebrow{color:var(--ink)}
.usulan .u-intro p{color:var(--muted);font-size:.92rem;margin:16px 0 20px;max-width:400px}
.u-points li{position:relative;padding:6px 0 6px 22px;font-size:.88rem;color:#4f5848}
.u-points li::before{content:"";position:absolute;left:2px;top:16px;width:8px;height:8px;border-radius:50%;background:var(--orange)}
.u-card{background:#fff;border:1px solid var(--line);border-radius:26px;padding:30px;box-shadow:0 22px 50px -28px rgba(54,67,47,.35)}
.u-cta h3{font:500 1.55rem/1.25 var(--serif);margin-bottom:8px}
.u-cta p{color:var(--muted);font-size:.9rem;margin-bottom:22px}
.u-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.u-f{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.u-f label{font-size:.78rem;font-weight:700;color:var(--ink)}
.u-f label i{font-style:normal;color:#C9674F}
.u-f input,.u-f textarea{font:500 .92rem var(--sans);color:var(--ink);background:#FFFBF2;border:1px solid var(--line);border-radius:14px;padding:12px 14px;width:100%;transition:border-color .2s,box-shadow .2s}
.u-f textarea{resize:vertical;min-height:92px}
.u-f input:focus,.u-f textarea:focus{outline:none;border-color:var(--orange);box-shadow:0 0 0 3px rgba(242,140,56,.2);background:#fff}
.u-f small{font-size:.72rem;color:var(--muted)}
.hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
.btn-send{background:var(--orange);color:#fff;box-shadow:0 12px 24px rgba(242,140,56,.28);width:100%;justify-content:center}
.btn-send:hover{background:#E07A26}
.btn-send[disabled]{opacity:.7;cursor:wait}
.u-ok,.u-err{border-radius:16px;padding:14px 18px;margin-bottom:18px;font-size:.88rem}
.u-ok{background:#E9F5DD;border:1px solid #BFDDA6;color:#2F5A1B}
.u-ok strong{display:block;font-size:.98rem}
.u-err{background:#FDECE7;border:1px solid #F3C2B4;color:#8F3A22}
.u-err ul{margin:0;padding-left:18px;list-style:disc}
.u-err[hidden]{display:none}
.modal{position:fixed;inset:0;z-index:60;display:grid;place-items:center;padding:20px;background:rgba(54,67,47,.5)}
.modal[hidden]{display:none}
.m-box{background:#fff;border-radius:28px;padding:32px 30px 28px;max-width:400px;width:100%;text-align:center;box-shadow:0 30px 80px rgba(0,0,0,.3);animation:pop .25s ease both}
.m-ico{width:62px;height:62px;border-radius:50%;display:grid;place-items:center;margin:0 auto 16px;font:700 1.9rem var(--head);color:#fff}
.m-ico.q{background:var(--orange)}.m-ico.ok{background:#5FA033}
.m-box h3{font:500 1.55rem/1.25 var(--serif);margin-bottom:8px}
.m-box p{font-size:.9rem;color:var(--muted)}
.m-acts{display:flex;gap:10px;justify-content:center;margin-top:22px;flex-wrap:wrap}
.m-no{border:1.5px solid var(--ink);color:var(--ink);background:transparent}.m-no:hover{background:rgba(54,67,47,.08)}
.m-yes{background:var(--orange);color:#fff;box-shadow:0 12px 24px rgba(242,140,56,.28)}.m-yes:hover{background:#E07A26}
@keyframes pop{from{opacity:0;transform:scale(.92) translateY(10px)}to{opacity:1;transform:none}}

/* footer (dari opac teman) */
.lb-footer *{margin:0;box-sizing:border-box}
.lb-footer a{text-decoration:none}
.lb-footer{background:#fff7e6;border-top:1px solid #e8e0ce;padding:48px 6% 20px;margin-top:0}.lb-footer-main{max-width:1160px;margin:auto;display:grid;grid-template-columns:1.3fr .7fr 1fr;gap:48px;padding-bottom:32px}.lb-footer h2{font:700 30px 'Bricolage Grotesque',sans-serif;color:#65744f}.lb-footer h2 span{color:#e49a4e}.lb-footer-tag{font:italic 14px Lora,'Newsreader',Georgia,serif;color:#687451;margin:5px 0 12px}.lb-footer p,.lb-footer a{font-size:13px;color:#777b6c;line-height:1.8}.lb-footer h3{font:700 16px 'Bricolage Grotesque',sans-serif;margin:5px 0 14px}.lb-footer-nav{display:flex;flex-direction:column;align-items:flex-start;gap:8px}.lb-footer-map{max-width:310px;margin-top:16px}.lb-footer-map iframe{width:100%;height:145px;border:1px solid #dce4ce;border-radius:14px}.lb-footer-bottom{max-width:1160px;margin:auto;border-top:1px solid #e8e0ce;padding-top:16px;display:flex;justify-content:space-between;gap:14px;font-size:11px;color:#777b6c}
@media(max-width:850px){.lb-footer-main{grid-template-columns:1fr 1fr}.lb-footer-brand{grid-column:1/-1}}
@media(max-width:620px){.lb-footer-main{grid-template-columns:1fr;gap:27px}.lb-footer-brand{grid-column:auto}.lb-footer-bottom{flex-direction:column}}

/* responsive */
@media (max-width:900px){
  .hero-grid,.band-grid,.access-grid,.agenda-grid,.usulan-grid{grid-template-columns:1fr;gap:36px}
  .band-grid{gap:16px}
  .stats{grid-template-columns:1fr 1fr}
  .stat:nth-child(2){border-right:0}.stat:nth-child(-n+2){border-bottom:1px solid var(--line)}
  .menu-toggle{display:block}
  .main-nav{display:none;position:absolute;top:100%;left:0;right:0;flex-direction:column;align-items:flex-start;gap:6px;background:var(--cream);padding:16px 28px 22px;border-bottom:1px solid var(--line)}
  .main-nav.open{display:flex}
  .hero{min-height:0;display:block;padding:40px 0 20px}
  .hero-art{justify-content:center}
  .hero-art svg{max-height:none}
  .row-tools{display:none}
}
@media (max-width:560px){
  .u-row{grid-template-columns:1fr}
  .u-card{padding:22px}
  .help{padding:22px}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}

/* anchor tidak tertutup header sticky */
section[id]{scroll-margin-top:81px}

/* OPAC: pencarian + buku (menggantikan "Jelajahi koleksi kami") */
.opac-sec h2 em{display:inline}
.sbar{display:flex;gap:10px;align-items:center;background:#fff;border:1px solid var(--line);border-radius:999px;padding:8px 8px 8px 24px;margin-top:30px;box-shadow:0 14px 34px rgba(54,67,47,.07)}
.sbar input,.sbar select{font:500 .9rem var(--sans);color:var(--ink);border:0;background:transparent}
.sbar input{flex:1;min-width:0;padding:12px 4px}
.sbar input:focus,.sbar select:focus{outline:none}
.sbar select{width:200px;padding:12px 14px;border-left:1px solid var(--line);cursor:pointer}
.sbar .button{padding:13px 26px;white-space:nowrap}
.sbar:focus-within{border-color:var(--green);box-shadow:0 0 0 3px rgba(108,162,74,.18)}
.chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
.chip{display:inline-flex;gap:8px;align-items:center;padding:9px 18px;border-radius:999px;background:#fff;border:1px solid var(--line);font-size:.8rem;font-weight:600;color:var(--green-d);transition:background .2s,border-color .2s}
.chip span{color:var(--orange)}
.chip:hover,.chip.on{background:#DCEBC8;border-color:#C7DDAE}
.sec-title{font-family:var(--head);font-weight:700;font-size:1.35rem;letter-spacing:-.02em;margin:28px 0 14px;display:flex;justify-content:space-between;align-items:baseline;gap:16px}
.sec-title a{font:600 .8rem var(--sans);letter-spacing:0;color:var(--green)}
.sec-title a:hover{color:var(--green-d)}
.opac-sec .row{display:flex;gap:16px;overflow-x:auto;padding:4px 2px 14px;scroll-snap-type:x mandatory}
.opac-sec .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px}
.opac-sec .empty{color:var(--muted)}
.opac-sec .bk-link{display:flex;flex:none;border-radius:22px}
.opac-sec .bk{min-width:330px;background:#fff;border:1px solid var(--line);border-radius:22px;padding:16px;display:flex;gap:16px;scroll-snap-align:start;transition:transform .2s,box-shadow .2s}
.opac-sec .bk:hover{transform:translateY(-3px);box-shadow:0 16px 30px rgba(54,67,47,.1)}
.opac-sec .grid .bk{min-width:0}
.opac-sec .cv{width:104px;height:150px;border-radius:6px 12px 12px 6px;flex:none;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;box-shadow:0 10px 20px -8px rgba(0,0,0,.35);border-left:6px solid rgba(255,255,255,.35)}
.opac-sec .cv b{font:500 1.9rem var(--serif);border:2px solid rgba(255,255,255,.55);border-radius:50%;width:66px;height:66px;display:grid;place-items:center}
.opac-sec .cv small{font-size:.5rem;letter-spacing:.2em;margin-top:14px}
.opac-sec .bi{display:flex;flex-direction:column;flex:1;min-width:0}
.opac-sec .kt{color:#D9741F;font-weight:700;letter-spacing:.12em;font-size:.65rem}
.opac-sec .bi h4{font:600 1rem/1.3 var(--sans);margin:6px 0 4px}
.opac-sec .bi p{color:var(--muted);font-size:.82rem}
.opac-sec .ft{margin-top:auto;display:flex;justify-content:space-between;gap:8px;font-size:.76rem;color:#A67A3A}
.opac-sec .ft .ok{color:var(--green-d);font-weight:600}
.opac-sec .ft .no{color:#C9674F;font-weight:600}
@media (max-width:900px){
  .sbar{flex-wrap:wrap;border-radius:26px;padding:14px}
  .sbar input{flex:1 1 100%;padding:10px 6px}
  .sbar select{flex:1;width:auto;border-left:0;border:1px solid var(--line);border-radius:999px}
  .opac-sec .bk{min-width:290px}
}

</style></head><body>
<header class="site-header"><div class="container nav-wrap">
<a class="brand" href="?" aria-label="Libloom, beranda"><img class="brand-mark" src="assets/logolibloom.jpg" alt="Libloom" width="52" height="52" style="width:52px;height:52px;border-radius:12px;object-fit:cover"><span class="brand-text"><strong>Libloom</strong><small>WHERE DREAMS GROW BETWEEN PAGES</small></span></a>
<button class="menu-toggle" id="menuToggle" aria-label="Buka navigasi" aria-expanded="false">&#9776;</button>
<nav class="main-nav" id="mainNav"><a class="active" href="?">Beranda</a><a href="tentang.php">Tentang</a><a href="koleksiopac.php">Koleksi</a><a href="pages/layanan.php">Layanan</a><a href="pages/agenda.php">Agenda</a>
<?php if($adm){ ?><a class="nav-login" href="?page=dashboard"><svg width="13" height="13" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="5" r="3.2" fill="currentColor"/><path d="M2 14.5c.6-3 3-4.6 6-4.6s5.4 1.6 6 4.6z" fill="currentColor"/></svg>Dashboard</a><a class="nav-logout" href="?act=logout">Keluar</a>
<?php } else { ?><a class="nav-login" href="?page=login"><svg width="13" height="13" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="5" r="3.2" fill="currentColor"/><path d="M2 14.5c.6-3 3-4.6 6-4.6s5.4 1.6 6 4.6z" fill="currentColor"/></svg>Login</a>
<?php } ?></nav></div></header>
<main>
<section class="hero"><div class="container hero-grid">
<div class="hero-copy">
<span class="eyebrow"><i></i>SELAMAT DATANG DI LIBLOOM</span>
<h1>Temukan cerita,<em>tumbuhkan</em>imajinasi.</h1>
<p class="hero-description">Ruang nyaman untuk membaca, belajar, dan menjelajahi pengetahuan. Setiap halaman punya cerita, setiap cerita membuka dunia.</p>
<form class="sbar" method="get" action="hasil.php" role="search">
<input name="q" value="<?php echo h($qs); ?>" placeholder="Cari judul buku, pengarang, atau topik..." aria-label="Cari koleksi">
<select name="kat" aria-label="Kategori"><option value="">Semua kategori</option>
<?php foreach($CATS as $c=>$i){ echo '<option'.($kat===$c?' selected':'').'>'.h($c).'</option>'; } ?>
</select><button class="button button-primary" type="submit">Cari koleksi</button></form>
<div class="chips">
<?php foreach($CATS as $c=>$i){ echo '<a class="chip'.($kat===$c?' on':'').'" href="hasil.php?kat='.urlencode($c).'"><span>'.(isset($glyph[$c])?$glyph[$c]:'•').'</span>'.h($c).'</a>'; } ?>
</div>
<div class="hero-note"><span class="dots"><i>✿</i><i>☘</i><i>✦</i></span><span><strong>Tempat ide bertumbuh</strong><small>Belajar dengan cara yang menyenangkan</small></span></div>
</div>
<div class="hero-art" aria-hidden="true">
<svg viewBox="0 0 540 440" xmlns="http://www.w3.org/2000/svg">
<path d="M0 440V270C0 120 120 0 270 0s270 120 270 270v170z" fill="#E6EFD4"/>
<path d="M20 440V270C20 132 132 20 270 20s250 112 250 250v170" fill="none" stroke="#CFE0B8" stroke-width="1.2"/>
<rect x="0" y="385" width="540" height="55" fill="#C7DDAE" rx="0"/>
<circle cx="425" cy="95" r="52" fill="#F4C542"/>
<g fill="#F4C542"><path d="M262 38l3 8 8 3-8 3-3 8-3-8-8-3 8-3z" transform="translate(0 0) scale(.9)"/><path d="M505 190l3 7 7 3-7 3-3 7-3-7-7-3 7-3z"/></g>
<g font-family="Newsreader,Georgia,serif" font-style="italic" font-size="19" fill="#4A5A3C"><text x="22" y="72">a little place</text><text x="22" y="98">for big ideas</text></g>
<g transform="rotate(-4 280 270)"><rect x="168" y="188" width="215" height="248" rx="8" fill="#F28C38"/></g>
<g transform="rotate(8 330 300)">
<rect x="185" y="140" width="225" height="290" rx="14" fill="#6CA24A"/>
<rect x="185" y="140" width="22" height="290" rx="10" fill="#4F8438"/>
<g transform="translate(300 215)" fill="#F4C542"><circle cx="0" cy="-10" r="7"/><circle cx="10" cy="0" r="7"/><circle cx="0" cy="10" r="7"/><circle cx="-10" cy="0" r="7"/><circle cx="0" cy="0" r="5" fill="#F28C38"/></g>
<g font-family="Newsreader,Georgia,serif" font-weight="500" text-anchor="middle" fill="#FFF1B8"><text x="302" y="300" font-size="34" letter-spacing="2">LIBLOOM</text></g>
<text x="302" y="352" text-anchor="middle" font-family="Figtree,Arial,sans-serif" font-size="9" letter-spacing="3" fill="#FFF1B8">read · learn · grow</text>
</g>
<g><circle cx="132" cy="332" r="19" fill="#E9B98C"/><path d="M112 330c0-16 9-24 21-24s21 8 21 24c-9-8-33-8-42 0z" fill="#2E3D2B"/>
<rect x="100" y="348" width="64" height="62" rx="22" fill="#F4C542"/><rect x="104" y="368" width="22" height="46" rx="10" fill="#F4C542"/>
<rect x="113" y="400" width="15" height="34" rx="6" fill="#2E3D2B"/><rect x="138" y="400" width="15" height="34" rx="6" fill="#2E3D2B"/></g>
<g fill="#6CA24A"><path d="M30 392C12 360 34 336 66 340c4 30-12 50-36 52z"/><path d="M62 372c4-22 24-36 48-30-2 22-20 36-48 30z" fill="#5B9040"/>
<path d="M510 392c18-32-4-56-36-52-4 30 12 50 36 52z"/><path d="M478 372c-4-22-24-36-48-30 2 22 20 36 48 30z" fill="#5B9040"/></g>
<g fill="none" stroke="#6CA24A" stroke-width="6" stroke-linecap="round"><path d="M28 392c-8 14 4 28 20 28"/><path d="M512 392c8 14-4 28-20 28"/></g>
</svg></div>
</div></section>

<section class="band" id="info"><div class="container band-grid">
<div class="stats" aria-label="Ringkasan koleksi">
<div class="stat"><b><?php echo number_format($n_judul,0,',','.'); ?></b><span>judul buku</span></div>
<div class="stat"><b><?php echo number_format($n_eks,0,',','.'); ?></b><span>total eksemplar</span></div>
<div class="stat"><b><?php echo number_format($n_ada,0,',','.'); ?></b><span>ada di rak</span></div>
<div class="stat"><b><?php echo number_format($n_pinjam,0,',','.'); ?></b><span>sedang dipinjam</span></div>
</div>
<div class="open-card">
<small>Hari ini, <?php echo h($hr[0]); ?></small>
<div class="open-state"><span class="dot<?php echo $buka_skrg?' on':''; ?>"></span><?php echo h($status_txt); ?></div>
<?php if($status_sub!==''){ ?><p><?php echo h($status_sub); ?></p><?php } ?>
<a class="more" href="tentang.php#jam">Jadwal lengkap &rarr;</a>
</div>
</div></section>

<section class="books opac-sec" id="koleksi"><div class="container">
<div class="sec-top"><div><h2>Koleksi pilihan</h2><p class="sec-sub">Klik sebuah buku untuk melihat detail, nomor panggil, dan status peminjamannya.</p></div>
<a class="more" href="koleksiopac.php">Lihat semua koleksi &rarr;</a></div>
<?php ob_row('Buku populer','rowPop',$populer); ob_row('Baru ditambahkan','rowBaru',$baru); ?>
</div></section>

<section class="access" id="layanan"><div class="container access-grid">
<div><span class="eyebrow">MUDAH DIAKSES</span><h2>Semua kebutuhan literasimu, <em>di sini.</em></h2>
<p>Manfaatkan layanan perpustakaan untuk menemukan informasi dan mendukung kegiatan belajar sehari-hari.</p></div>
<ul class="access-list">
<li><div class="al"><span class="no">01</span><span><strong>Pencarian katalog</strong><small>Telusuri koleksi berdasarkan judul atau penulis.</small></span></div></li>
<li><div class="al"><span class="no">02</span><span><strong>Peminjaman &amp; pengembalian</strong><small>Akses layanan sirkulasi koleksi perpustakaan.</small></span></div></li>
<li><div class="al"><span class="no">03</span><span><strong>Bantuan referensi</strong><small>Dapatkan bantuan menemukan sumber informasi.</small></span></div></li>
</ul></div></section>

<section class="agenda" id="agenda"><div class="container agenda-grid">
<div><span class="eyebrow">CATAT DI KALENDER</span><h2>Ada hal menarik yang <em>menanti.</em></h2></div>
<div class="agenda-list">
<?php foreach($AGENDA as $a){ echo '<div class="agenda-card"><span class="agenda-tag">✦ '.h(strtoupper($a[0])).'</span><h3>'.h($a[1]).'</h3><p>'.h($a[2]).'</p><span class="pill">'.h($a[3]).'</span></div>'; } ?>
</div></div></section>

<section class="helpsec"><div class="container">
<div class="help"><div><strong>Masih ada pertanyaan?</strong><span>Lihat aturan peminjaman atau hubungi pustakawan.</span></div>
<div class="acts"><a class="button button-light" href="tentang.php#aturan">Lihat aturan</a><a class="button button-ghost" href="tentang.php#kontak">Hubungi pustakawan</a></div></div>
</div></section>

<section class="usulan" id="usulan"><div class="container usulan-grid">
<div class="u-intro"><span class="eyebrow">PUNYA BUKU IDAMAN?</span><h2>Usulkan buku <em>untuk kami.</em></h2>
<p>Belum menemukan buku yang kamu cari? Tulis judulnya di sini, nanti pustakawan akan mempertimbangkannya untuk ditambahkan ke koleksi.</p>
<ul class="u-points"><li>Cukup isi judul buku dan namamu.</li><li>Cek dulu di <a href="koleksiopac.php" style="color:#D9741F;font-weight:700">katalog</a>, siapa tahu sudah ada.</li><li>Usulanmu dibaca langsung oleh pustakawan.</li></ul></div>
<div class="u-card">
<?php if($FORM_USULAN!==''){ ?>
<div class="u-cta"><h3>Isi formulir usulan</h3><p>Formulirnya dibuka di tab baru lewat Google Form. Cukup isi judul buku dan namamu, nanti pustakawan yang membacanya.</p>
<a class="button btn-send" href="<?php echo h($FORM_USULAN); ?>" target="_blank" rel="noopener">Buka formulir usulan ↗</a></div>
<?php } else { ?>
<div class="u-err" id="uErr" role="alert"<?php echo $us_err?'':' hidden'; ?>><ul id="uErrList"><?php foreach($us_err as $e){ echo '<li>'.h($e).'</li>'; } ?></ul></div>
<form method="post" action="usulan.php" id="formUsulan" autocomplete="off">
<input type="hidden" name="usulan_form" value="1">
<input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_usulan']); ?>">
<div class="hp" aria-hidden="true"><label>Jangan diisi<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
<div class="u-f"><label for="u_judul">Judul buku <i>*</i></label><input id="u_judul" name="judul" required maxlength="150" value="<?php echo h($us_old['judul']); ?>" placeholder="Contoh: Laskar Pelangi"></div>
<div class="u-f"><label for="u_peng">Pengarang</label><input id="u_peng" name="pengarang" maxlength="150" value="<?php echo h($us_old['pengarang']); ?>" placeholder="Kalau kamu tahu"></div>
<div class="u-row">
<div class="u-f"><label for="u_nama">Namamu <i>*</i></label><input id="u_nama" name="nama" required maxlength="150" value="<?php echo h($us_old['nama']); ?>"></div>
<div class="u-f"><label for="u_kelas">Kelas</label><input id="u_kelas" name="kelas" maxlength="150" value="<?php echo h($us_old['kelas']); ?>" placeholder="Contoh: XI IPA 2"></div>
</div>
<div class="u-f"><label for="u_alasan">Kenapa buku ini perlu ada?</label><textarea id="u_alasan" name="alasan" maxlength="400" placeholder="Boleh dikosongkan"><?php echo h($us_old['alasan']); ?></textarea></div>
<button class="button btn-send" type="submit" id="btnKirim">Kirim usulan</button>
</form>
<?php } ?>
</div>
</div></section>

</main>
<footer class="lb-footer"><div class="lb-footer-main">
  <div class="lb-footer-brand"><h2>Libloom<span>.</span></h2><div class="lb-footer-tag">Where Dreams Grow Between Pages</div><p>Ruang untuk menemukan pengetahuan, menjelajahi imajinasi, dan menumbuhkan kecintaan terhadap membaca.</p><div class="lb-footer-map"><strong style="display:block;font-size:10px;letter-spacing:.13em;margin:12px 0 7px;color:#687451">TEMUKAN LOKASI KAMI</strong><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d9766.192141529253!2d4.538814411728285!3d52.26975188292057!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47c5c20434806f1d%3A0x29417663c1c3b37d!2sStationsweg%20166A%2C%202161%20AM%20Lisse%2C%20Belanda!5e0!3m2!1sid!2sid!4v1794799412265!5m2!1sid!2sid" title="Peta lokasi Libloom" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div></div>
  <div><h3>Jelajahi</h3><div class="lb-footer-nav"><a href="?">Beranda</a><a href="tentang.php">Tentang Kami</a><a href="koleksiopac.php">Koleksi</a><a href="pages/layanan.php">Layanan</a><a href="pages/agenda.php">Agenda</a></div></div>
  <div><h3>Hubungi Kami</h3><p>Email<br><a href="mailto:libloom@gmail.com">libloom@gmail.com</a></p><p style="margin-top:10px">Telepon<br><a href="tel:+62888666777">+62 888-666-777</a></p><p style="margin-top:10px">Instagram<br><a href="https://instagram.com/libloom" target="_blank">@libloom</a></p><p style="margin-top:10px">Alamat<br>Stationsweg 166A, 2161 AM Lisse, Belanda</p></div>
</div><div class="lb-footer-bottom"><span>© <?= date('Y') ?> Perpustakaan Libloom. Where Dreams Grow Between Pages.</span><span>Read · Discover · Grow</span></div></footer>
<div class="modal" id="mConfirm" hidden role="dialog" aria-modal="true" aria-labelledby="mcT">
<div class="m-box"><div class="m-ico q">?</div><h3 id="mcT">Yakin sudah mengisi dengan benar?</h3><p>Periksa lagi judul buku dan namamu sebelum dikirim ke pustakawan.</p>
<div class="m-acts"><button type="button" class="button m-no" id="mcNo">Periksa lagi</button><button type="button" class="button m-yes" id="mcYes" data-focus>Ya, kirim</button></div></div></div>
<div class="modal" id="mThanks"<?php echo $us_ok?'':' hidden'; ?> role="dialog" aria-modal="true" aria-labelledby="mtT">
<div class="m-box"><div class="m-ico ok">&#10003;</div><h3 id="mtT">Terima kasih sudah mengirim!</h3><p>Usulan bukumu sudah diterima dan akan dipertimbangkan oleh pustakawan.</p>
<div class="m-acts"><button type="button" class="button m-yes" id="mtOk" data-focus>Oke</button></div></div></div>
<script>
(function(){
 var b=document.getElementById('menuToggle'),n=document.getElementById('mainNav');
 if(b&&n){b.addEventListener('click',function(){var o=n.classList.toggle('open');b.setAttribute('aria-expanded',o?'true':'false');});}
 var arr=document.querySelectorAll('.arr');
 for(var i=0;i<arr.length;i++){arr[i].addEventListener('click',function(){
  var r=document.getElementById(this.getAttribute('data-row'));
  if(r){r.scrollBy({left:parseInt(this.getAttribute('data-dir'),10)*360,behavior:'smooth'});}
 });}

 // ===== form usulan: konfirmasi sebelum kirim + terima kasih setelah kirim =====
 var f=document.getElementById('formUsulan'),k=document.getElementById('btnKirim'),
     mC=document.getElementById('mConfirm'),mT=document.getElementById('mThanks'),
     eBox=document.getElementById('uErr'),eList=document.getElementById('uErrList');
 function buka(m){m.hidden=false;var x=m.querySelector('[data-focus]');if(x)x.focus();}
 function tutup(m){m.hidden=true;}
 function tampilErr(list){
  eList.innerHTML='';
  for(var i=0;i<list.length;i++){var li=document.createElement('li');li.textContent=list[i];eList.appendChild(li);}
  eBox.hidden=!list.length;
  if(list.length&&eBox.scrollIntoView){eBox.scrollIntoView({behavior:'smooth',block:'center'});}
 }
 function kirim(){
  k.disabled=true;k.textContent='Mengirim...';
  fetch('usulan.php',{method:'POST',body:new FormData(f),headers:{'X-Requested-With':'fetch'},credentials:'same-origin'})
  .then(function(r){return r.json();})
  .then(function(d){
   k.disabled=false;k.textContent='Kirim usulan';
   if(d.ok){f.reset();tampilErr([]);buka(mT);}
   else{tampilErr(d.errors&&d.errors.length?d.errors:['Usulan belum bisa dikirim. Coba lagi.']);}
   if(d.csrf){f.elements['csrf'].value=d.csrf;}
  })
  .catch(function(){k.disabled=false;k.textContent='Kirim usulan';tampilErr(['Koneksi bermasalah. Coba kirim lagi sebentar lagi.']);});
 }
 if(f&&k&&mC&&mT&&window.fetch&&window.FormData){
  f.addEventListener('submit',function(e){
   e.preventDefault();
   if(f.checkValidity&&!f.checkValidity()){f.reportValidity();return;}
   buka(mC);
  });
  document.getElementById('mcNo').addEventListener('click',function(){tutup(mC);});
  document.getElementById('mcYes').addEventListener('click',function(){tutup(mC);kirim();});
  document.getElementById('mtOk').addEventListener('click',function(){tutup(mT);});
  [mC,mT].forEach(function(m){m.addEventListener('click',function(e){if(e.target===m)tutup(m);});});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'){tutup(mC);tutup(mT);}});
 }
 if(mT&&!mT.hidden){var ok=document.getElementById('mtOk');if(ok)ok.focus();}
})();
</script>
</body></html>
<?php exit;