<?php
// HALAMAN CETAK KATALOG (kartu katalog & daftar katalog)
ini_set('display_errors', '1');
error_reporting(E_ALL);

admin_open('katalog');

// ---------- Filter dari form (GET) ----------
$cari  = trim($_GET['q'] ?? '');
$kat   = trim($_GET['kategori'] ?? '');
$mode  = ($_GET['mode'] ?? 'kartu') === 'daftar' ? 'daftar' : 'kartu';
$urut  = $_GET['urut'] ?? 'judul';
$urutan = array('judul' => 'b.judul', 'pengarang' => 'b.pengarang', 'callno' => 'b.callno');
$orderBy = $urutan[$urut] ?? 'b.judul';

$where = array();
$args  = array();

if ($cari !== '') {
    $where[] = '(b.judul LIKE ? OR b.pengarang LIKE ? OR b.callno LIKE ? OR b.series LIKE ?)';
    $like = '%' . $cari . '%';
    array_push($args, $like, $like, $like, $like);
}

if ($kat !== '') {
    $where[] = 'b.kategori = ?';
    $args[]  = $kat;
}

$sql = 'SELECT b.*, (SELECT COUNT(*) FROM item i WHERE i.biblio_id=b.id) AS tot FROM biblio b' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY $orderBy COLLATE NOCASE, b.id";
$books = q($sql, $args)->fetchAll();

// Daftar kategori untuk dropdown
$kategoriList = q("SELECT DISTINCT kategori FROM biblio WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori")->fetchAll();

// Kode inventaris (barcode) per bibliografi
$barcodes = array();
foreach (q('SELECT biblio_id, barcode FROM item ORDER BY id')->fetchAll() as $it) {
    $barcodes[$it['biblio_id']][] = $it['barcode'];
}
?>
<style>
.kat-bar{display:flex;gap:12px;flex-wrap:wrap;align-items:end}
.kat-bar .f{display:flex;flex-direction:column;gap:6px;min-width:150px;flex:1}
.kat-bar label{font-size:13px;font-weight:500}
.kat-bar input,.kat-bar select{font:400 15px Outfit;border:1px solid var(--line);border-radius:14px;padding:12px 16px;background:var(--paper);color:var(--dg);width:100%}
.kat-head{text-align:center;margin:6px 0 18px}
.kat-head h2{font:700 24px 'Playfair Display',serif;margin:0}
.kat-head p{color:var(--mut);margin:4px 0 0;font-size:13px}
.kat-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.kat-card{position:relative;background:#fff;border:1px dashed #9aa58c;border-radius:12px;padding:16px 18px;break-inside:avoid;page-break-inside:avoid}
.kat-card .kc-brand{font-size:10px;letter-spacing:.14em;color:var(--mut);margin-bottom:10px}
.kat-card .kc-body{display:flex;gap:16px}
.kat-card .kc-call{flex:none;width:64px;font:600 13px/1.5 monospace;color:var(--forest)}
.kat-card .kc-main{flex:1;font-size:13px;line-height:1.55;min-width:0}
.kat-card .kc-author{font-weight:700;text-transform:uppercase;font-size:12px;letter-spacing:.04em}
.kat-card .kc-title{font:700 15px/1.35 'Playfair Display',serif;margin:2px 0 4px}
.kat-card .kc-meta{margin-top:8px;font-size:11px;color:var(--mut)}
.kat-card .kc-bc{margin-top:6px;font:500 11px monospace;word-break:break-word}
.kat-pick{position:absolute;top:10px;right:12px;display:flex;gap:6px;align-items:center;font-size:12px;cursor:pointer}
.kat-pick input{width:auto}
.kat-table{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden}
.kat-table th{text-align:left;background:var(--cream);padding:10px 12px;font-size:12px}
.kat-table td{padding:9px 12px;border-top:1px solid var(--line);vertical-align:top}
.skip{opacity:.35}
.kat-print-only{display:none}
@media(max-width:700px){.kat-grid{grid-template-columns:1fr}}
@media print{
  .skip{display:none!important}
  .kat-print-only{display:block}
  .kat-grid{gap:6mm}
  .kat-card{border:1px dashed #777;border-radius:3px;padding:4mm 5mm;box-shadow:none}
  .kat-table{border:0;border-radius:0}
  .kat-table th{background:#eee!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .kat-table td,.kat-table th{padding:2mm 2.5mm;font-size:10pt;border-top:1px solid #bbb}
  .kat-table tr{break-inside:avoid;page-break-inside:avoid}
}
</style>

<div class='pagehead' style='display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:22px'>
  <div>
    <span class='eyebrow'>KATALOG</span>
    <h1>Cetak Katalog</h1>
    <p style='color:var(--mut)'>Cetak kartu katalog atau daftar katalog dari koleksi Libloom.</p>
  </div>
</div>

<div class='pn no-print'>
  <form method='get' class='kat-bar'>
    <input type='hidden' name='page' value='katalog'>
    <div class='f' style='flex:2'><label>Cari</label><input name='q' value='<?= h($cari) ?>' placeholder='Judul, pengarang, nomor panggil, atau seri'></div>
    <div class='f'>
      <label>Kategori</label>
      <select name='kategori'>
        <option value=''>Semua kategori</option>
        <?php foreach ($kategoriList as $k): ?>
          <option value='<?= h($k['kategori']) ?>' <?= $kat === $k['kategori'] ? 'selected' : '' ?>><?= h($k['kategori']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class='f'>
      <label>Urutkan</label>
      <select name='urut'>
        <option value='judul' <?= $urut === 'judul' ? 'selected' : '' ?>>Judul</option>
        <option value='pengarang' <?= $urut === 'pengarang' ? 'selected' : '' ?>>Pengarang</option>
        <option value='callno' <?= $urut === 'callno' ? 'selected' : '' ?>>Nomor panggil</option>
      </select>
    </div>
    <div class='f'>
      <label>Format cetak</label>
      <select name='mode'>
        <option value='kartu' <?= $mode === 'kartu' ? 'selected' : '' ?>>Kartu katalog</option>
        <option value='daftar' <?= $mode === 'daftar' ? 'selected' : '' ?>>Daftar katalog (tabel)</option>
      </select>
    </div>
    <div class='f' style='flex:none;min-width:0'><button class='btn'>Tampilkan</button></div>
  </form>
  <div style='display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px'>
    <label style='display:flex;gap:8px;align-items:center;cursor:pointer;font-size:14px'>
      <input type='checkbox' id='kat-all' checked style='width:auto'> Pilih semua
    </label>
    <div style='display:flex;gap:10px;align-items:center;flex-wrap:wrap'>
      <span class='pill'><span id='kat-n'><?= count($books) ?></span> dari <?= count($books) ?> bibliografi akan dicetak</span>
      <button class='btn' type='button' onclick='window.print()'>🖨 Cetak / Simpan PDF</button>
    </div>
  </div>
  <p style='color:var(--mut);font-size:13px;margin:10px 0 0'>Hilangkan centang pada buku yang tidak ingin dicetak, lalu klik tombol cetak dan pilih “Save as PDF” bila ingin menyimpan sebagai PDF.</p>
</div>

<?php if (!$books): ?>
  <div class='pn'>Tidak ada koleksi yang cocok. Ubah filter pencarian atau tambahkan bibliografi di menu Data Koleksi.</div>
<?php else: ?>

<div class='kat-print-only kat-head'>
  <h2>Katalog Perpustakaan Libloom</h2>
  <p><?= $mode === 'kartu' ? 'Kartu katalog' : 'Daftar katalog' ?> · dicetak <?= date('d M Y') ?></p>
</div>

<?php if ($mode === 'kartu'): ?>
<div class='kat-grid'>
  <?php foreach ($books as $b):
    $inv = $barcodes[$b['id']] ?? array();
    $call = trim((string)$b['callno']);
  ?>
  <article class='kat-card' data-kat>
    <label class='kat-pick no-print'><input type='checkbox' checked> cetak</label>
    <div class='kc-brand'>LIBLOOM · KARTU KATALOG</div>
    <div class='kc-body'>
      <div class='kc-call'><?= $call !== '' ? str_replace(' ', '<br>', h($call)) : '—' ?></div>
      <div class='kc-main'>
        <div class='kc-author'><?= h($b['pengarang']) ?></div>
        <div class='kc-title'><?= h($b['judul']) ?> / <?= h($b['pengarang']) ?>.</div>
        <?php if (trim((string)$b['kolasi']) !== ''): ?><div><?= h($b['kolasi']) ?>.</div><?php endif; ?>
        <?php if (trim((string)$b['series']) !== ''): ?><div>(<?= h($b['series']) ?>)</div><?php endif; ?>
        <div class='kc-meta'><?= h($b['kategori']) ?> · <?= h($b['bahasa']) ?> · <?= h($b['tipe']) ?></div>
        <div class='kc-bc'>No. inventaris: <?= $inv ? h(implode(', ', $inv)) : '—' ?></div>
      </div>
    </div>
  </article>
  <?php endforeach; ?>
</div>

<?php else: ?>
<div class='kat-table'>
  <table>
    <tr>
      <th class='no-print' style='width:44px'>Cetak</th>
      <th style='width:40px'>No.</th>
      <th>Judul</th>
      <th>Pengarang</th>
      <th>No. panggil</th>
      <th>Kategori</th>
      <th>Bahasa</th>
      <th>Eks.</th>
    </tr>
    <?php foreach ($books as $n => $b): ?>
    <tr data-kat>
      <td class='kat-pick no-print' style='position:static'><input type='checkbox' checked></td>
      <td><?= $n + 1 ?></td>
      <td><b><?= h($b['judul']) ?></b><?php if (trim((string)$b['series']) !== ''): ?><br><small>(<?= h($b['series']) ?>)</small><?php endif; ?></td>
      <td><?= h($b['pengarang']) ?></td>
      <td><?= h($b['callno']) ?></td>
      <td><?= h($b['kategori']) ?></td>
      <td><?= h($b['bahasa']) ?></td>
      <td><?= (int)$b['tot'] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

<script>
(function () {
  var boxes = document.querySelectorAll('.kat-pick input');
  var all = document.getElementById('kat-all');
  var count = document.getElementById('kat-n');

  function sync() {
    var n = 0;
    boxes.forEach(function (c) {
      var el = c.closest('[data-kat]');
      if (el) el.classList.toggle('skip', !c.checked);
      if (c.checked) n++;
    });
    count.textContent = n;
    all.checked = (n === boxes.length);
  }

  boxes.forEach(function (c) { c.addEventListener('change', sync); });
  all.addEventListener('change', function () {
    boxes.forEach(function (c) { c.checked = all.checked; });
    sync();
  });
})();
</script>
<?php endif; ?>
<?php admin_close();