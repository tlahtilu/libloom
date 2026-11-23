<?php
// HALAMAN KOLEKSI
admin_open('koleksi');
 if(isset($_GET['add'])||isset($_GET['edit'])){
  $e=isset($_GET['edit'])?q("SELECT * FROM biblio WHERE id=?",[$_GET['edit']])->fetch():[];$g=function($k)use($e){return h(isset($e[$k])?$e[$k]:'');};
  echo "<h1>".($e?'Edit':'Tambah')." Bibliografi</h1><div class=pn><form class=fm method=post action='?act=biblio'><input type=hidden name=id value='".$g('id')."'>
  <div class=w><label>Judul</label><input name=judul required value='".$g('judul')."'></div><div><label>Pengarang</label><input name=pengarang required value='".$g('pengarang')."'></div><div><label>Kolasi</label><input name=kolasi placeholder='xii, 320 hlm; 26 cm' value='".$g('kolasi')."'></div>
  <div><label>Judul series</label><input name=series value='".$g('series')."'></div><div><label>Nomor panggil</label><input name=callno value='".$g('callno')."'></div>
  <div><label>Kategori</label><select name=kategori>";foreach($CATS as $c=>$i)echo "<option ".(((isset($e['kategori'])?$e['kategori']:''))===$c?'selected':'').">$c";
  echo "</select></div><div><label>Tipe koleksi</label><select name=tipe>";foreach(['Tercetak','Elektronik','Audio-Visual'] as $t)echo "<option ".(((isset($e['tipe'])?$e['tipe']:''))===$t?'selected':'').">$t";
  echo "</select></div><div><label>Bahasa</label><input name=bahasa value='".($e?$g('bahasa'):'Indonesia')."'></div><div><label>".($e?'Tambah eksemplar baru':'Jumlah eksemplar')."</label><input type=number min=0 name=jml value='".($e?0:1)."'></div>
  <div class=w style='margin-top:22px'><button class=btn>Simpan</button> <a class='btn g' href='?page=koleksi'>Batal</a></div></form></div>";
 }else{
  $s=trim((isset($_GET['s'])?$_GET['s']:''));$r=q("$BQ WHERE judul LIKE ? OR pengarang LIKE ? OR callno LIKE ? ORDER BY id DESC",["%$s%","%$s%","%$s%"])->fetchAll();
  echo "<div class=hd><small>✦ KOLEKSI PERPUSTAKAAN</small><h2>Daftar <em>Bibliografi</em></h2><p style='margin-bottom:22px'>Kelola dan temukan informasi koleksi buku dengan mudah.</p><div style='display:flex;gap:10px;flex-wrap:wrap'><a class='btn g' href='?page=barcode'>Barcode &amp; PDF</a><a class='btn g' href='?page=scan'>Scan Barcode</a><a class=btn href='?page=koleksi&add=1'>+ Tambah Bibliografi</a></div></div>$fl
  <div class=pn><div class=top><div><small>THE BOOKSHELF</small><h3 style='color:var(--forest)'>Koleksi Bibliografi</h3></div><span class=pill>".count($r)." koleksi</span></div>
  <form class=sr><input type=hidden name=page value=koleksi><input name=s value='".h($s)."' placeholder='Cari judul, pengarang, atau nomor panggil...'><button class='btn y'>Cari</button></form>
  <table><tr><th>No.<th>Judul<th>Pengarang<th>Kolasi<th>Judul series<th>Nomor panggil<th>Tipe koleksi<th>Bahasa<th>Eksemplar<th>Aksi</tr>";
  foreach($r as $n=>$b)echo "<tr><td>".($n+1)."<td><b>".h($b['judul'])."</b><td>".h($b['pengarang'])."<td>".h($b['kolasi'])."<td>".h($b['series'])."<td>".h($b['callno'])."<td>".h($b['tipe'])."<td>".h($b['bahasa'])."<td>{$b['av']}/{$b['tot']} tersedia<td style='white-space:nowrap'><a class='btn s g' href='?page=koleksi&edit={$b['id']}'>Edit</a> <a class='btn s g' href='?page=barcode&biblio={$b['id']}'>Label</a> <a class='btn s r' onclick=\"return confirm('Hapus bibliografi ini?')\" href='?page=koleksi&act=del&id={$b['id']}'>Hapus</a>";
  echo "</table></div>";
 }
admin_close();
