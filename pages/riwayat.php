<?php
admin_open('riwayat');
$rows=q("SELECT l.*,m.nama,b.judul FROM loan l JOIN member m ON m.id=l.member_id JOIN item i ON i.id=l.item_id JOIN biblio b ON b.id=i.biblio_id ORDER BY l.id DESC")->fetchAll();
echo "<h1>Riwayat Transaksi</h1>$fl<div class='pn'><div class='top'><h3>Seluruh transaksi</h3><span class='pill'>".count($rows)." transaksi</span></div><table><tr><th>No.</th><th>Anggota</th><th>Judul</th><th>Tanggal pinjam</th><th>Jatuh tempo</th><th>Tanggal kembali</th><th>Denda</th><th>Status</th></tr>";
foreach($rows as $n=>$r){$status=$r['tgl_kembali']?"<span class='bd b3'>Kembali</span>":(overdueDays($r['jatuh_tempo'])>0?"<span class='bd b2'>Terlambat</span>":"<span class='bd b1'>Dipinjam</span>");echo "<tr><td>".($n+1)."</td><td>".h($r['nama'])."</td><td>".h($r['judul'])."</td><td>".h($r['tgl_pinjam'])."</td><td>".h($r['jatuh_tempo'])."</td><td>".h($r['tgl_kembali']?:'—')."</td><td>Rp".number_format((int)$r['denda'],0,',','.')."</td><td>$status</td></tr>";}
if(!$rows)echo "<tr><td colspan='8'>Belum ada transaksi.</td></tr>";
echo '</table></div>';admin_close();
