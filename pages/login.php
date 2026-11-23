<?php
$mode=($_GET['mode']??'')==='register'?'register':'login';
head($mode==='register'?'Daftar Akun':'Login');
?>
<div class='lg'><div class='lgc'><a href='?' class='logo'><i><img src='assets/logolibloom.jpg' alt='L'></i><span><b>Libloom</b><small>WHERE DREAMS GROW BETWEEN PAGES</small></span></a>
<div class='auth-switch'><a class='<?=$mode==='login'?'active':''?>' href='?page=login'>Login</a><a class='<?=$mode==='register'?'active':''?>' href='?page=login&mode=register'>Register</a></div>
<?php if(!empty($fl)) echo $fl; ?>
<?php if($mode==='login'): ?>
<h2>Selamat datang</h2><p>Masuk untuk mengelola perpustakaan.</p>
<form method='post' action='?act=login'><div class='fm' style='display:block'><label>Username</label><input name='u' required autofocus placeholder='Masukkan username'><label>Password</label><input type='password' name='p' required placeholder='Masukkan password'></div><button class='btn' style='width:100%;justify-content:center;margin-top:24px'>Masuk ke Back Office</button></form>
<?php else: ?>
<h2>Buat akun baru</h2><p>Daftarkan akun untuk mulai mengelola Libloom.</p>
<form method='post' action='?act=register'><div class='fm' style='display:block'><label>Username</label><input name='u' required minlength='3' placeholder='Minimal 3 karakter'><label>Password</label><input type='password' name='p' required minlength='6' placeholder='Minimal 6 karakter'><label>Konfirmasi password</label><input type='password' name='p2' required minlength='6' placeholder='Ulangi password'></div><button class='btn' style='width:100%;justify-content:center;margin-top:24px'>Daftar &amp; Masuk</button><p class='auth-note'>Akun pertama yang didaftarkan otomatis menjadi administrator. Pendaftaran berikutnya menjadi petugas.</p></form>
<?php endif; ?>
<p style='text-align:center;margin:18px 0 0'><a href='?' style='color:var(--forest)'>← Kembali ke beranda</a></p></div></div></body></html>