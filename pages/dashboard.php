<?php
// HALAMAN DASHBOARD
admin_open('dashboard');
 $tot=q("SELECT COUNT(*) c FROM item")->fetch()['c'];$out=q("SELECT COUNT(*) c FROM loan WHERE tgl_kembali IS NULL")->fetch()['c'];
 $mem=q("SELECT COUNT(*) c FROM member")->fetch()['c'];$lt=q("SELECT COUNT(*) c FROM loan WHERE tgl_kembali IS NULL AND $late")->fetch()['c'];
 echo "<h1>Dashboard</h1>$fl<div class=wel><small>SELAMAT DATANG KEMBALI</small><h2>Kelola perpustakaan,<br><em>lebih mudah setiap hari.</em></h2><p>Pantau koleksi, kelola anggota, dan pastikan setiap transaksi sirkulasi tercatat dengan baik.</p><a class=btn href='?page=sirkulasi'>+ Transaksi Peminjaman</a></div>
 <div class=stats><div class=st><span>Total eksemplar</span><b>$tot</b><small>Seluruh koleksi tercatat</small></div><div class=st><span>Tersedia dipinjam</span><b>".($tot-$out)."</b><small>Eksemplar tersedia saat ini</small></div><div class=st><span>Anggota terdaftar</span><b>$mem</b><small>Data anggota perpustakaan</small></div><div class=st><span>Sedang dipinjam</span><b>$out</b><small>$lt transaksi terlambat</small></div></div>
 <div class=two><div class=pn><div class=top><div><h3>Transaksi terbaru</h3><small>Aktivitas peminjaman dan pengembalian terakhir</small></div><a class='btn g s' href='?page=riwayat'>Lihat semua →</a></div><table><tr><th>Anggota<th>Judul<th>Tanggal<th>Status</tr>";
 foreach(q("SELECT l.*,m.nama,b.judul FROM loan l JOIN member m ON m.id=l.member_id JOIN item i ON i.id=l.item_id JOIN biblio b ON b.id=i.biblio_id ORDER BY l.id DESC LIMIT 5")->fetchAll() as $l)
  echo "<tr><td>".h($l['nama'])."<td>".h($l['judul'])."<td>".h($l['tgl_pinjam'])."<td>".($l['tgl_kembali']?"<span class='bd b3'>Kembali</span>":"<span class='bd b1'>Dipinjam</span>");
 echo "</table></div><div class='pn qa'><h3>Akses cepat</h3><small>Pintasan pengelolaan data</small><a href='?page=koleksi&add=1'>＋ Tambah bibliografi</a><a href='?page=anggota&add=1'>＋ Tambah anggota</a><a href='?page=sirkulasi'>⇄ Peminjaman &amp; pengembalian</a><a href='?page=barcode'>▥ Cetak barcode koleksi</a><a href='?page=scan'>⌕ Scan barcode inventaris</a><a href='?'>↗ Lihat OPAC publik</a></div></div>";
admin_close();
