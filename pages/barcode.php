<?php
// HALAMAN CETAK LABEL (barcode inventaris)
// - Dibuka dari sidebar            : semua label, ada pencarian/kategori/urut
// - Dibuka dari ikon di Data Koleksi: fokus satu buku (?biblio=ID), tanpa pencarian
ini_set('display_errors', '1');
error_reporting(E_ALL);

admin_open('barcode');

// ---------- Filter dari form (GET) ----------
$cari   = trim($_GET['q'] ?? '');
$kat    = trim($_GET['kategori'] ?? '');
$filter = (int)($_GET['biblio'] ?? 0);   // dipakai tombol barcode di Data Koleksi / Scan
$urut   = $_GET['urut'] ?? 'judul';
$fokus  = $filter > 0;                  // mode satu buku
if ($fokus) { $cari = ''; $kat = ''; $urut = 'barcode'; }
$urutan = array('judul' => 'b.judul COLLATE NOCASE, i.id', 'barcode' => 'i.barcode', 'callno' => 'b.callno COLLATE NOCASE, i.id');
$orderBy = $urutan[$urut] ?? $urutan['judul'];

$where = array();
$args  = array();

if ($filter > 0) {
    $where[] = 'b.id = ?';
    $args[]  = $filter;
}

if ($cari !== '') {
    $where[] = '(b.judul LIKE ? OR b.pengarang LIKE ? OR b.callno LIKE ? OR i.barcode LIKE ?)';
    $like = '%' . $cari . '%';
    array_push($args, $like, $like, $like, $like);
}

if ($kat !== '') {
    $where[] = 'b.kategori = ?';
    $args[]  = $kat;
}

$sql = 'SELECT i.id AS item_id, i.barcode, b.judul, b.pengarang, b.callno, b.id AS biblio_id
        FROM item i JOIN biblio b ON b.id = i.biblio_id'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY $orderBy";
$items = q($sql, $args)->fetchAll();

$kategoriList = q("SELECT DISTINCT kategori FROM biblio WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori")->fetchAll();

$judulFilter = '';
$infoBuku = array();
if ($filter > 0) {
    $infoBuku = q('SELECT judul, pengarang, callno FROM biblio WHERE id = ?', array($filter))->fetch();
    $judulFilter = $infoBuku ? $infoBuku['judul'] : '';
}
?>
<style>
.bc-bar{display:flex;gap:12px;flex-wrap:wrap;align-items:end}
.bc-bar .f{display:flex;flex-direction:column;gap:6px;min-width:150px;flex:1}
.bc-bar label{font-size:13px;font-weight:500}
.bc-bar input,.bc-bar select,.bc-n input{font:400 15px Outfit;border:1px solid var(--line);border-radius:14px;padding:12px 16px;background:var(--paper);color:var(--dg);width:100%}
.bc-n{display:flex;gap:8px;align-items:center;font-size:14px}
.bc-n input{width:84px;padding:8px 12px}
.barcode-label{position:relative}
.bc-pick{position:absolute;top:8px;right:10px;display:flex;gap:5px;align-items:center;font-size:12px;cursor:pointer}
.bc-pick input{width:auto}
.skip{opacity:.35}
@media print{.skip{display:none!important}}
</style>

<div class='pagehead' style='display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:22px'>
  <div>
    <span class='eyebrow'>INVENTARIS</span>
    <h1>Cetak Label</h1>
    <p style='color:var(--mut)'><?= $fokus ? 'Pilih label eksemplar yang ingin dicetak untuk buku ini.' : 'Kode Code 128 dibuat dari kode inventaris setiap eksemplar.' ?></p>
  </div>
  <div style='display:flex;gap:10px;flex-wrap:wrap'>
    <a class='btn g' href='?page=scan'>Scan Barcode</a>
    <button class='btn' type='button' onclick='window.print()'>Cetak label / Simpan PDF</button>
  </div>
</div>

<div class='pn no-print'>
  <?php if ($fokus): ?>
  <div style='display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap'>
    <div>
      <h3 style='margin:0 0 4px'><?= h($judulFilter) ?></h3>
      <small><?= h($infoBuku['pengarang'] ?? '') ?><?= !empty($infoBuku['callno']) ? ' · ' . h($infoBuku['callno']) : '' ?> · <?= count($items) ?> eksemplar</small>
    </div>
    <div style='display:flex;gap:10px;flex-wrap:wrap'>
      <a class='btn g s' href='?page=koleksi'>← Kembali ke Data Koleksi</a>
      <a class='btn g s' href='?page=barcode'>Lihat semua label</a>
    </div>
  </div>
  <?php else: ?>
  <form method='get' class='bc-bar'>
    <input type='hidden' name='page' value='barcode'>
    <div class='f' style='flex:2'><label>Cari</label><input name='q' value='<?= h($cari) ?>' placeholder='Judul, pengarang, nomor panggil, atau kode barcode'></div>
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
        <option value='barcode' <?= $urut === 'barcode' ? 'selected' : '' ?>>Kode barcode</option>
        <option value='callno' <?= $urut === 'callno' ? 'selected' : '' ?>>Nomor panggil</option>
      </select>
    </div>
    <div class='f' style='flex:none;min-width:0;flex-direction:row;gap:8px'>
      <button class='btn'>Tampilkan</button>
      <a class='btn g' href='?page=barcode'>Reset</a>
    </div>
  </form>

  <?php endif; ?>

  <div style='display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px'>
    <div style='display:flex;gap:18px;align-items:center;flex-wrap:wrap'>
      <label style='display:flex;gap:8px;align-items:center;cursor:pointer;font-size:14px'>
        <input type='checkbox' id='bc-all' checked style='width:auto'> Pilih semua
      </label>
      <div class='bc-n'>
        <span>Atau cetak</span>
        <input type='number' id='bc-first' min='1' max='<?= max(1, count($items)) ?>' value='<?= min(12, max(1, count($items))) ?>'>
        <span>label pertama</span>
        <button type='button' class='btn g s' id='bc-first-btn'>Pilih</button>
      </div>
    </div>
    <span class='pill'><span id='bc-count'><?= count($items) ?></span> dari <?= count($items) ?> label akan dicetak</span>
  </div>
  <p style='color:var(--mut);font-size:13px;margin:10px 0 0'>Hilangkan centang pada label yang tidak ingin dicetak, lalu klik tombol cetak dan pilih “Save as PDF” bila ingin menyimpan sebagai PDF.</p>
</div>

<div class='barcode-grid' id='labels'>
<?php if ($items): foreach ($items as $it): ?>
  <div class='barcode-label' data-bc>
    <label class='bc-pick no-print'><input type='checkbox' checked> cetak</label>
    <b class='label-brand'>Libloom</b>
    <div class='label-title'><?= h($it['judul']) ?></div>
    <small><?= h($it['callno']) ?></small>
    <svg class='barcode-svg' data-code='<?= h($it['barcode']) ?>'></svg>
    <small class='label-code'><?= h($it['barcode']) ?></small>
  </div>
<?php endforeach; else: ?>
  <div class='pn'>Tidak ada eksemplar yang cocok. Ubah pencarian, atau tambahkan bibliografi beserta jumlah eksemplarnya di Data Koleksi.</div>
<?php endif; ?>
</div>

<script src='https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js'></script>
<script>
document.querySelectorAll('.barcode-svg').forEach(function (el) {
  try {
    if (window.JsBarcode) { JsBarcode(el, el.dataset.code, {format: 'CODE128', displayValue: false, margin: 2, height: 42, lineColor: '#111'}); }
    else { el.outerHTML = '<small>' + el.dataset.code + '</small>'; }
  } catch (e) { el.outerHTML = '<small>' + el.dataset.code + '</small>'; }
});

(function () {
  var boxes = document.querySelectorAll('.bc-pick input');
  var all = document.getElementById('bc-all');
  var count = document.getElementById('bc-count');
  var first = document.getElementById('bc-first');

  function sync() {
    var n = 0;
    boxes.forEach(function (c) {
      var el = c.closest('[data-bc]');
      if (el) el.classList.toggle('skip', !c.checked);
      if (c.checked) n++;
    });
    count.textContent = n;
    all.checked = (boxes.length > 0 && n === boxes.length);
  }

  boxes.forEach(function (c) { c.addEventListener('change', sync); });

  all.addEventListener('change', function () {
    boxes.forEach(function (c) { c.checked = all.checked; });
    sync();
  });

  document.getElementById('bc-first-btn').addEventListener('click', function () {
    var n = parseInt(first.value, 10);
    if (isNaN(n) || n < 0) n = 0;
    boxes.forEach(function (c, i) { c.checked = i < n; });
    sync();
  });
})();
</script>
<?php admin_close();