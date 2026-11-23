<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($act === 'login') {
    $username=trim($_POST['u']??''); $password=$_POST['p']??'';
    $user=q('SELECT * FROM users WHERE username=?',[$username])->fetch();
    if($user && password_verify($password,$user['password'])){
      session_regenerate_id(true); $_SESSION['user_id']=$user['id']; $_SESSION['username']=$user['username']; $_SESSION['role']=$user['role'];
      flash('Selamat datang kembali, '.$user['username'].'!'); go('?page=dashboard');
    }
    flash('Username atau password salah.'); go('?page=login');
  }
  if ($act === 'register') {
    $username=trim($_POST['u']??''); $password=$_POST['p']??''; $confirm=$_POST['p2']??'';
    if(strlen($username)<3 || strlen($password)<6){flash('Username minimal 3 karakter dan password minimal 6 karakter.');go('?page=login&mode=register');}
    if($password!==$confirm){flash('Konfirmasi password belum sama.');go('?page=login&mode=register');}
    if(q('SELECT id FROM users WHERE username=?',[$username])->fetch()){flash('Username sudah digunakan.');go('?page=login&mode=register');}
    $role=((int)q('SELECT COUNT(*) c FROM users')->fetch()['c']===0)?'admin':'petugas';
    q('INSERT INTO users(username,password,role) VALUES(?,?,?)',[$username,password_hash($password,PASSWORD_DEFAULT),$role]);
    $user=q('SELECT * FROM users WHERE username=?',[$username])->fetch();
    session_regenerate_id(true);$_SESSION['user_id']=$user['id'];$_SESSION['username']=$user['username'];$_SESSION['role']=$user['role'];
    flash('Akun berhasil dibuat. Selamat datang di Back Office Libloom!');go('?page=dashboard');
  }
  if (!$adm) go('?page=login');
  $P=$_POST;
  if($act==='save_admin'){
    $uid=(int)$_SESSION['user_id'];
    $me=q('SELECT * FROM users WHERE id=?',[$uid])->fetch();
    if(!$me){flash('Akun tidak ditemukan. Silakan login kembali.');go('?page=login');}
    $nama=trim($P['nama']??''); $username=trim($P['username']??''); $email=trim($P['email']??''); $telp=trim($P['telp']??'');
    $old=$P['password_lama']??''; $new=$P['password_baru']??''; $conf=$P['konfirmasi_password']??'';
    if($nama===''){flash('Nama lengkap wajib diisi.');go('?page=admin');}
    if(!preg_match('/^[A-Za-z0-9_.-]{3,30}$/',$username)){flash('Username 3-30 karakter, hanya huruf, angka, titik, strip, dan underscore.');go('?page=admin');}
    if($email!=='' && !filter_var($email,FILTER_VALIDATE_EMAIL)){flash('Format email tidak valid.');go('?page=admin');}
    if(q('SELECT id FROM users WHERE username=? AND id<>?',[$username,$uid])->fetch()){flash('Username sudah dipakai akun lain.');go('?page=admin');}
    $changePass=($old!=='' || $new!=='' || $conf!=='');
    if($changePass){
      if(!password_verify($old,$me['password'])){flash('Password lama salah. Perubahan tidak disimpan.');go('?page=admin');}
      if(strlen($new)<6){flash('Password baru minimal 6 karakter.');go('?page=admin');}
      if($new!==$conf){flash('Konfirmasi password baru belum sama.');go('?page=admin');}
      if($new===$old){flash('Password baru tidak boleh sama dengan password lama.');go('?page=admin');}
      q('UPDATE users SET nama=?,username=?,email=?,telp=?,password=? WHERE id=?',[$nama,$username,$email,$telp,password_hash($new,PASSWORD_DEFAULT),$uid]);
      session_regenerate_id(true);
      $msg='Akun dan password berhasil diperbarui. Gunakan password baru saat login berikutnya.';
    }else{
      q('UPDATE users SET nama=?,username=?,email=?,telp=? WHERE id=?',[$nama,$username,$email,$telp,$uid]);
      $msg='Informasi akun berhasil diperbarui.';
    }
    $_SESSION['username']=$username;
    flash($msg);go('?page=admin');
  }
  if($act==='biblio'){
    $judul=trim($P['judul']??''); $pengarang=trim($P['pengarang']??'');
    if($judul==='' || $pengarang===''){flash('Judul dan pengarang wajib diisi.');go('?page=koleksi&add=1');}
    $f=[$P['judul']??'',$P['pengarang']??'',$P['kolasi']??'',$P['series']??'',$P['callno']??'',$P['tipe']??'Tercetak',$P['bahasa']??'Indonesia',$P['kategori']??'Buku Teks'];
    if(!empty($P['id'])){q('UPDATE biblio SET judul=?,pengarang=?,kolasi=?,series=?,callno=?,tipe=?,bahasa=?,kategori=? WHERE id=?',array_merge($f,[$P['id']]));$id=(int)$P['id'];flash('Bibliografi berhasil diperbarui.');}
    else{
      $jml=min(500,max(0,(int)($P['jml']??0)));
      // Cek apakah buku yang sama sudah ada (judul & pengarang sama, tanpa membedakan huruf besar/kecil & spasi ganda)
      $norm=function($t){return mb_strtolower(preg_replace('/\s+/u',' ',trim((string)$t)),'UTF-8');};
      $kunciBaru=$norm($judul).'|'.$norm($pengarang);$ada=null;
      foreach(q('SELECT id,judul,pengarang FROM biblio ORDER BY id ASC')->fetchAll() as $row){
        if($norm($row['judul']).'|'.$norm($row['pengarang'])===$kunciBaru){$ada=(int)$row['id'];break;}
      }
      if($ada){
        // Buku sama -> cukup tambah eksemplar pada bibliografi yang sudah ada
        $id=$ada;
        flash($jml>0?"Buku \"".$judul."\" sudah ada di koleksi (pengarang sama), jadi hanya ditambahkan $jml eksemplar baru.":"Buku \"".$judul."\" sudah ada di koleksi. Isi jumlah eksemplar minimal 1 untuk menambah eksemplar.");
      }else{
        q('INSERT INTO biblio(judul,pengarang,kolasi,series,callno,tipe,bahasa,kategori) VALUES(?,?,?,?,?,?,?,?)',$f);$id=(int)$GLOBALS['db']->lastInsertId();flash('Bibliografi berhasil ditambahkan.');
      }
      addItems($id,$jml);go('?page=koleksi');
    }
    addItems($id,min(500,max(0,(int)($P['jml']??0))));go('?page=koleksi');
  }
  if($act==='scan_lookup'){
    $barcode=trim($P['barcode']??'');
    if($barcode===''){flash('Masukkan atau pindai barcode terlebih dahulu.');go('?page=scan');}
    go('?page=scan&code='.rawurlencode($barcode));
  }
  if($act==='member'){
    if(!empty($P['id'])){q('UPDATE member SET nama=?,kelas=?,telp=? WHERE id=?',[$P['nama'],$P['kelas'],$P['telp'],$P['id']]);flash('Anggota diperbarui.');}
    else{q('INSERT INTO member(nama,kelas,telp) VALUES(?,?,?)',[$P['nama'],$P['kelas'],$P['telp']]);flash('Anggota ditambahkan.');}
    go('?page=anggota');
  }
  if($act==='pinjam'){
    $item=(int)$P['item'];$member=(int)$P['member'];$exists=q('SELECT 1 FROM loan WHERE item_id=? AND tgl_kembali IS NULL',[$item])->fetch();
    if($exists)flash('Eksemplar sedang dipinjam.');
    else{q('INSERT INTO loan(item_id,member_id,tgl_pinjam,jatuh_tempo) VALUES(?,?,?,?)',[$item,$member,date('Y-m-d'),date('Y-m-d',strtotime('+'.max(1,(int)$P['hari']).' days'))]);flash('Peminjaman berhasil dicatat.');}
    go('?page=sirkulasi');
  }
  if($act==='kembali'){
    $loan=q('SELECT * FROM loan WHERE id=? AND tgl_kembali IS NULL',[(int)$P['id']])->fetch();
    if($loan){$fine=overdueDays($loan['jatuh_tempo'])*1000;q('UPDATE loan SET tgl_kembali=?,denda=? WHERE id=?',[date('Y-m-d'),$fine,$loan['id']]);flash('Buku berhasil dikembalikan. Denda: Rp'.number_format($fine,0,',','.'));}
    go('?page=sirkulasi');
  }
}
if($act==='logout'){$_SESSION=[];if(ini_get('session.use_cookies')){$params=session_get_cookie_params();setcookie(session_name(),'',time()-42000,$params['path'],$params['domain'],$params['secure'],$params['httponly']);}session_destroy();go('?');}
if($act==='del'&&$adm){
  $id=(int)($_GET['id']??0);
  if($page==='koleksi'){q('DELETE FROM loan WHERE item_id IN (SELECT id FROM item WHERE biblio_id=?)',[$id]);q('DELETE FROM item WHERE biblio_id=?',[$id]);q('DELETE FROM biblio WHERE id=?',[$id]);flash('Bibliografi dihapus.');}
  if($page==='anggota'){q('DELETE FROM loan WHERE member_id=?',[$id]);q('DELETE FROM member WHERE id=?',[$id]);flash('Anggota dihapus.');}
  go('?page='.$page);
}