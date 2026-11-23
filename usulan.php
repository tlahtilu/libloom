<?php
// =====================================================================
// usulan.php - PENERIMA FORM USULAN BUKU (dipanggil dari form di beranda)
// Simpan di folder utama libloom/ (sejajar dengan index.php & hasil.php).
// Dibuat terpisah supaya kiriman form tidak lewat index.php (yang bisa
// mengarahkan POST ke halaman login). Data disimpan di tabel `usulan_buku`
// dan dikabarkan ke WhatsApp pustakawan lewat Fonnte (kalau token diisi di bawah).
// =====================================================================
@ini_set('display_errors','0');   // peringatan PHP tidak boleh ikut tercetak ke balasan form
require_once __DIR__.'/config.php';
if(function_exists('session_status') && session_status()===PHP_SESSION_NONE){ @session_start(); }

// >>>>>>>>>>  NOTIFIKASI WHATSAPP KE PUSTAKAWAN (opsional)  <<<<<<<<<<
// Memakai layanan Fonnte (fonnte.com). Isi $WA_TOKEN dengan token perangkat dari akun Fonnte.
// Biarkan kosong kalau tidak mau memakai notifikasi WhatsApp.
// $WA_TARGET = nomor WhatsApp pustakawan yang menerima pesan (format 08xx atau 628xx).
$WA_TOKEN='NmC6CNmuH6PQ2WxCCBGP';
$WA_TARGET='085711765997';
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

function us_clean($v,$max){
 $v=trim(preg_replace('/\s+/u',' ',(string)$v));
 return function_exists('mb_substr')?mb_substr($v,0,$max,'UTF-8'):substr($v,0,$max);
}
// kirim notifikasi ke WhatsApp pustakawan lewat Fonnte. Hasilnya dicatat di usulan_wa.log
// (tanpa isi pesan dan tanpa token); kalau gagal, usulan tetap tersimpan di database.
function us_wa($token,$target,$pesan){
 $r=array('ok'=>false,'http'=>0,'err'=>'','body'=>'');
 if($token===''){ $r['err']='Token belum diisi di $WA_TOKEN.'; return $r; }
 if($target===''){ $r['err']='Nomor tujuan belum diisi di $WA_TARGET.'; return $r; }
 if(!function_exists('curl_init')){ $r['err']='Ekstensi cURL PHP belum aktif (aktifkan extension=curl di php.ini lalu restart Apache).'; }
 else{
  try{
   $ch=curl_init('https://api.fonnte.com/send');
curl_setopt_array($ch,array(
    CURLOPT_SSL_VERIFYPEER=>false,
    CURLOPT_SSL_VERIFYHOST=>0,
    CURLOPT_POST=>true,
    CURLOPT_POSTFIELDS=>array('target'=>(string)$target,'message'=>$pesan,'countryCode'=>'62'),
    CURLOPT_HTTPHEADER=>array('Authorization: '.$token),
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_CONNECTTIMEOUT=>4,
    CURLOPT_TIMEOUT=>8
   ));
   $body=curl_exec($ch);
   $r['http']=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);
   $r['err']=curl_error($ch);
   curl_close($ch);
   $r['body']=is_string($body)?$body:'';
   $j=json_decode($r['body'],true);
   $r['ok']=(is_array($j) && !empty($j['status']));
  }catch(Exception $e){ $r['err']=$e->getMessage(); }
  catch(Throwable $e){ $r['err']=$e->getMessage(); }
 }
 @file_put_contents(__DIR__.'/usulan_wa.log',date('Y-m-d H:i:s').' | ok='.($r['ok']?'ya':'tidak').' | http='.$r['http'].' | err='.$r['err'].' | respon='.substr($r['body'],0,300)."\n",FILE_APPEND);
 return $r;
}
$ajax=(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='fetch');

// TES WHATSAPP: login dulu, lalu buka usulan.php?tes_wa
if(isset($_GET['tes_wa'])){
 header('Content-Type: text/html; charset=utf-8');
 echo '<!doctype html><meta charset="utf-8"><title>Tes WhatsApp</title><body style="font:15px/1.6 system-ui,Arial,sans-serif;margin:28px;max-width:720px">';
 if(empty($_SESSION['adm']) && !(function_exists('isLoggedIn') && isLoggedIn())){ echo '<p>Login dulu sebagai petugas, lalu buka alamat ini lagi.</p>'; exit; }
 $r=us_wa($WA_TOKEN,$WA_TARGET,'Tes notifikasi Libloom: kalau pesan ini masuk, integrasi WhatsApp sudah berfungsi.');
 echo '<h2>'.($r['ok']?'Berhasil dikirim ke Fonnte':'Gagal').'</h2><ul>';
 echo '<li>Tujuan: '.h($WA_TARGET).'</li><li>Kode HTTP: '.(int)$r['http'].'</li>';
 echo '<li>Error cURL: '.h($r['err']!==''?$r['err']:'-').'</li><li>Respon Fonnte: <code>'.h($r['body']!==''?$r['body']:'-').'</code></li></ul>';
 $t=strtolower($r['err'].' '.$r['body']);
 if(!$r['ok']){
  echo '<p><b>Kemungkinan penyebab:</b></p><ul>';
  if(strpos($t,'ssl')!==false || strpos($t,'certificate')!==false) echo '<li>Sertifikat SSL PHP belum diatur (umum di XAMPP). Unduh cacert.pem dari curl.se/ca/cacert.pem, simpan di folder PHP, isi <code>curl.cainfo="path\\cacert.pem"</code> di php.ini, lalu restart Apache.</li>';
  if(strpos($t,'token invalid')!==false) echo '<li>Token tidak dikenali. Pakai <b>token perangkat (device)</b> dari menu Device di Fonnte, bukan token akun, dan pastikan tidak ada spasi.</li>';
  if(strpos($t,'disconnected')!==false) echo '<li>Perangkat di Fonnte belum tersambung. Buka dashboard Fonnte dan scan ulang QR sampai statusnya connect.</li>';
  if(strpos($t,'target')!==false) echo '<li>Nomor tujuan tidak valid. Pakai format 08xxxxxxxxxx atau 628xxxxxxxxxx tanpa spasi atau tanda minus.</li>';
  if(strpos($t,'resolve')!==false || strpos($t,'connect')!==false || strpos($t,'timed out')!==false) echo '<li>Server tidak bisa menjangkau api.fonnte.com. Cek koneksi internet atau firewall.</li>';
  echo '<li>Kuota atau paket Fonnte habis, atau nomor pengirim sama dengan nomor tujuan. Cek di dashboard Fonnte.</li></ul>';
 }
 exit;
}

// hanya menerima POST
if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD']!=='POST'){ header('Location: index.php#usulan'); exit; }

$old=array('judul'=>'','pengarang'=>'','nama'=>'','kelas'=>'','alasan'=>'');
foreach($old as $k=>$v){ $old[$k]=us_clean(isset($_POST[$k])?$_POST[$k]:'',$k==='alasan'?400:150); }
$err=array(); $ok=false;

if(!empty($_POST['website'])){
 $ok=true;                                   // bot (kolom jebakan terisi): pura-pura berhasil
} else {
 $tok=isset($_POST['csrf'])?$_POST['csrf']:'';
 if(empty($_SESSION['csrf_usulan']) || !is_string($tok) || $tok!==$_SESSION['csrf_usulan']) $err[]='Formulir kedaluwarsa. Muat ulang halaman lalu kirim lagi.';
 if(strlen($old['judul'])<3) $err[]='Judul buku wajib diisi (minimal 3 huruf).';
 if($old['nama']==='') $err[]='Namamu wajib diisi.';
 if(isset($_SESSION['usulan_t']) && time()-(int)$_SESSION['usulan_t']<20) $err[]='Tunggu sebentar sebelum mengirim usulan lagi.';

 if(!$err){
  try{
   $ada=q("SELECT id FROM biblio WHERE LOWER(judul)=LOWER(?) LIMIT 1",array($old['judul']))->fetch();
   if($ada) $err[]='Buku ini sudah ada di koleksi. Coba cari di katalog.';
  }catch(Exception $e){}catch(Throwable $e){}
 }
 if(!$err){
  try{
   $tgl=date('Y-m-d H:i:s');
   q("CREATE TABLE IF NOT EXISTS usulan_buku(id INTEGER PRIMARY KEY AUTOINCREMENT, judul TEXT, pengarang TEXT, alasan TEXT, nama TEXT, kelas TEXT, tanggal TEXT, status TEXT DEFAULT 'baru')");
   q("INSERT INTO usulan_buku(judul,pengarang,alasan,nama,kelas,tanggal,status) VALUES(?,?,?,?,?,?,?)",
     array($old['judul'],$old['pengarang'],$old['alasan'],$old['nama'],$old['kelas'],$tgl,'baru'));
   $_SESSION['usulan_t']=time();
   $ok=true;
   // notifikasi WhatsApp ke pustakawan
   $pesan="*Usulan buku baru - Libloom*\n\nJudul: ".$old['judul'];
   if($old['pengarang']!=='') $pesan.="\nPengarang: ".$old['pengarang'];
   $pesan.="\nDiusulkan oleh: ".$old['nama'].($old['kelas']!==''?' ('.$old['kelas'].')':'');
   if($old['alasan']!=='') $pesan.="\nAlasan: ".$old['alasan'];
   $pesan.="\nWaktu: ".$tgl;
   us_wa($WA_TOKEN,$WA_TARGET,$pesan);
  }catch(Exception $e){ $err[]='Maaf, usulan belum bisa disimpan. Coba lagi nanti.'; }
  catch(Throwable $e){ $err[]='Maaf, usulan belum bisa disimpan. Coba lagi nanti.'; }
 }
}
if($ok){ $_SESSION['csrf_usulan']=md5(uniqid(mt_rand(),true)); }
if(empty($_SESSION['csrf_usulan'])){ $_SESSION['csrf_usulan']=md5(uniqid(mt_rand(),true)); }

if($ajax){
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(array('ok'=>$ok,'errors'=>$err,'csrf'=>$_SESSION['csrf_usulan']));
 exit;
}
// tanpa JavaScript: kembali ke beranda membawa hasilnya lewat sesi
if($ok){ $_SESSION['usulan_flash']=1; } else { $_SESSION['usulan_err']=$err; $_SESSION['usulan_old']=$old; }
header('Location: index.php#usulan');
exit;