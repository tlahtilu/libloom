<?php
// Gabungkan bibliografi duplikat (judul & pengarang sama) menjadi satu; eksemplar & riwayat pinjam dipindahkan.
// Jalankan sekali via browser saat login sebagai admin: /Libloom/merge_duplikat.php
require __DIR__.'/config.php';
if(!isLoggedIn()){http_response_code(403);exit('Login dulu sebagai admin.');}
$norm=function($t){return mb_strtolower(preg_replace('/\s+/u',' ',trim((string)$t)),'UTF-8');};
$grp=[];
foreach(q('SELECT id,judul,pengarang FROM biblio ORDER BY id ASC')->fetchAll() as $r){$grp[$norm($r['judul']).'|'.$norm($r['pengarang'])][]=$r;}
$dups=array_filter($grp,function($g){return count($g)>1;});
$go=($_SERVER['REQUEST_METHOD']==='POST');
echo "<meta charset=utf-8><body style='font-family:sans-serif;max-width:640px;margin:30px auto'><h2>Gabungkan Bibliografi Duplikat</h2>";
if(!$dups){echo "<p>Tidak ada duplikat. 🎉</p><a href='index.php?page=koleksi'>Kembali</a>";exit;}
foreach($dups as $g){
  $keep=$g[0];echo "<p><b>".h($keep['judul'])."</b> — dipertahankan ID {$keep['id']} ({$keep['pengarang']})<br>";
  for($i=1;$i<count($g);$i++){
    $d=$g[$i];echo "&nbsp;&nbsp;↳ digabung dari ID {$d['id']} ({$d['pengarang']})<br>";
    if($go){q('UPDATE item SET biblio_id=? WHERE biblio_id=?',[$keep['id'],$d['id']]);q('DELETE FROM biblio WHERE id=?',[$d['id']]);}
  }
  echo "</p>";
}
if($go)echo "<p><b>Selesai digabung.</b></p><a href='index.php?page=koleksi'>Ke Data Koleksi</a>";
else echo "<form method=post><button style='padding:10px 18px'>Gabungkan sekarang</button></form><p><small>Backup libloom.db dulu.</small></p>";