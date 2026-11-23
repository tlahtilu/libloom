<?php
function head($title, $class='') {
  echo "<!doctype html><html lang='id'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>".h($title)." · Libloom</title>
  <link href='https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700;800&family=Lora:ital,wght@1,500&family=Outfit:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,700;1,700&display=swap' rel='stylesheet'>
  <link rel='icon' type='image/jpeg' href='assets/logolibloom.jpg'><link rel='stylesheet' href='assets/style.css'><style>.notice-link{position:relative}.notice-count{display:inline-grid;place-items:center;min-width:20px;height:20px;padding:0 5px;border-radius:20px;background:#f2b84b;color:#3a4630;font-size:11px;margin-left:5px}.notice-row{padding:18px 20px;background:#fff;border:1px solid var(--line);border-radius:18px;margin:12px 0}.notice-row p{color:var(--mut);margin:5px 0}.notice-row small{color:var(--forest)}.auth-switch{display:flex;gap:8px;background:var(--cream);padding:6px;border-radius:999px;margin:18px 0}.auth-switch a{flex:1;text-align:center;padding:10px;border-radius:999px;font-size:13px;font-weight:600;color:var(--forest)}.auth-switch a.active{background:var(--green);color:#fff}.auth-note{font-size:12px!important;margin-top:12px!important}.home-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.menu a{white-space:nowrap}.logo i{background:none!important;overflow:hidden;padding:0;width:52px!important;height:52px!important;border-radius:12px}.logo i img{width:100%;height:100%;object-fit:cover;border-radius:12px;display:block}.me .prof{display:flex;align-items:center;gap:10px;padding:10px 12px;margin:0 -8px 10px;border-radius:16px;color:inherit;transition:.2s}.me .prof:hover,.me .prof.on{background:var(--mint)}.me .prof i{flex:none;width:38px;height:38px;border-radius:50%;background:var(--green);color:#fff;display:grid;place-items:center;font:700 15px Outfit;font-style:normal}.me .prof b{display:block;line-height:1.2}.me .prof small{display:block;color:var(--mut)}.me .prof em{margin-left:auto;font-style:normal;color:var(--forest);font-size:12px}@media(max-width:900px){.menu{display:none}.hero{padding-top:36px}.sb{flex-wrap:wrap}}</style></head><body class='$class'>";
}
function admin_open($page) {
  global $titles,$icons;
  $user=currentUser(); $count=count(notificationData());

  // Menu sidebar: sisipkan "Cetak Katalog" dan "Cetak Label" tepat setelah Data Koleksi
  $menu=array();
  foreach($titles as $key=>$title){
    if($key==='barcode' || $key==='katalog') continue;
    $menu[$key]=$title;
    if($key==='koleksi'){ $menu['katalog']='Cetak Katalog'; $menu['barcode']='Cetak Label'; }
  }
  if(!isset($menu['katalog'])) $menu['katalog']='Cetak Katalog';
  if(!isset($menu['barcode'])) $menu['barcode']='Cetak Label';
  $ic=$icons+array('katalog'=>'▦');

  if($page==='admin') $judul='Pengaturan Akun';
  elseif($page==='katalog') $judul='Cetak Katalog';
  elseif($page==='barcode') $judul='Cetak Label';
  else $judul=$titles[$page] ?? 'Back Office';
  head($judul);

  echo "<div class='adm'><aside class='sd'><a href='?' class='logo'><i><img src='assets/logolibloom.jpg' alt='L'></i><span><b>Libloom</b><small>LIBRARY MANAGEMENT</small></span></a><h6>MENU UTAMA</h6>";
  foreach($menu as $key=>$title){
    $extra=$key==='notifikasi' && $count ? "<span class='notice-count'>$count</span>" : '';
    echo "<a class='m ".($key===$page?'on':'')."' href='?page=$key'><span>".($ic[$key] ?? '•')."</span>$title $extra</a>";
  }
  $ini=strtoupper(function_exists('mb_substr') ? mb_substr($user['nama'],0,1) : substr($user['nama'],0,1));
  echo "<div class='me'><a class='prof ".($page==='admin'?'on':'')."' href='?page=admin' title='Edit akun'><i>".h($ini)."</i><span><b>".h($user['nama'])."</b><small>".h(ucfirst($user['role']))." · @".h($user['username'])."</small></span><em>✎</em></a><a href='?act=logout' style='color:#a4442e'>↗ Keluar</a></div></aside><main class='mn'>";
  echo "<div class='bc'><span>LIBLOOM / ADMINISTRASI</span><span>".date('d M Y')." <span style='color:var(--green)'>●</span></span></div>";
}
function admin_close(){echo '</main></div></body></html>';}