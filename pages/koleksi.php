
<?php
// ==========================================
// HALAMAN KOLEKSI BIBLIOGRAFI - LIBLOOM
// ==========================================

admin_open('koleksi');

// ==========================================
// CSS
// ==========================================

echo <<<'CSS'
<style>

.biblio-table-wrap {
    width:100%;
    max-width:100%;
    overflow-x:auto;
}

.biblio-table {
    width:100%;
    min-width:1000px;
    table-layout:fixed;
    border-collapse:collapse;
}

.biblio-table th,
.biblio-table td {
    padding:13px 8px;
    text-align:left;
    vertical-align:middle;
    overflow-wrap:anywhere;
    font-size:13px;
    line-height:1.5;
}

.biblio-table th {
    background:#fff7e6;
    color:var(--forest);
}

.biblio-table th:nth-child(1),
.biblio-table td:nth-child(1) {
    width:4%;
    text-align:center;
}

.biblio-table th:nth-child(2),
.biblio-table td:nth-child(2) {
    width:9%;
    text-align:center;
}

.biblio-table th:nth-child(3),
.biblio-table td:nth-child(3) {
    width:19%;
}

.biblio-table th:nth-child(4),
.biblio-table td:nth-child(4) {
    width:14%;
}

.biblio-table th:nth-child(5),
.biblio-table td:nth-child(5) {
    width:12%;
}

.biblio-table th:nth-child(6),
.biblio-table td:nth-child(6) {
    width:12%;
    text-align:center;
}

.biblio-table th:nth-child(7),
.biblio-table td:nth-child(7) {
    width:12%;
}

.biblio-table th:nth-child(8),
.biblio-table td:nth-child(8) {
    width:8%;
}

.biblio-table th:nth-child(9),
.biblio-table td:nth-child(9) {
    width:14%;
}

.book-title {
    font-weight:700;
    color:var(--forest);
}

.biblio-cover {
    display:flex;
    justify-content:center;
    align-items:center;
}

.biblio-cover .cv {
    width:54px;
    height:76px;
    min-width:54px;
    border-radius:5px;
    overflow:hidden;
    box-shadow:0 4px 10px #0002;
}

.biblio-cover .cv b {
    width:32px;
    height:32px;
    font-size:17px;
}

.biblio-cover .cv small {
    font-size:6px;
    margin-top:5px;
}

.cv img {
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.callno-format {
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:2px;
    font-weight:600;
    color:var(--forest);
    text-align:center;
}

.biblio-actions {
    display:flex;
    align-items:center;
    gap:5px;
    white-space:nowrap;
}

.biblio-actions a {
    position:relative;
    width:31px;
    height:31px;
    min-width:31px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:9px;
    text-decoration:none;
    transition:all .2s ease;
}

.biblio-actions svg {
    width:16px;
    height:16px;
    stroke:currentColor;
    fill:none;
    stroke-width:1.8;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.action-edit,
.action-label {
    background:#e7eedb;
    color:#477d46;
}

.action-edit:hover,
.action-label:hover {
    background:#cbdcb9;
    transform:translateY(-2px);
}

.action-delete {
    background:#f8e4dd;
    color:#c95f4b;
}

.action-delete:hover {
    background:#e9c1b5;
    transform:translateY(-2px);
}

.biblio-actions a[title]:hover::after {
    content:attr(title);
    position:absolute;
    bottom:calc(100% + 8px);
    left:50%;
    transform:translateX(-50%);
    background:#315d3b;
    color:white;
    font-size:11px;
    font-weight:600;
    padding:6px 9px;
    border-radius:7px;
    white-space:nowrap;
    z-index:20;
}

.cover-upload {
    background:#fff;
    border:1px dashed #b7c99f;
    border-radius:18px;
    padding:22px;
    margin-top:8px;
}

.cover-upload input {
    width:100%;
    padding:12px;
    border:1px solid var(--line);
    border-radius:12px;
    background:var(--paper);
    font:14px Outfit;
}

.cover-preview {
    display:flex;
    gap:20px;
    align-items:center;
    flex-wrap:wrap;
    margin-top:15px;
}

.cover-preview .cv {
    width:100px;
    height:140px;
    flex:none;
}

.cover-preview .cv b {
    width:60px;
    height:60px;
}

.cover-preview .cv small {
    font-size:8px;
}

.cover-help {
    font-size:12px;
    color:var(--mut);
    line-height:1.7;
}

@media(max-width:768px) {
    .biblio-table-wrap {
        overflow-x:auto;
    }

    .biblio-table {
        min-width:1000px;
    }
}

</style>
CSS;


// ==========================================
// FORM TAMBAH / EDIT BIBLIOGRAFI
// ==========================================

if (isset($_GET['add']) || isset($_GET['edit'])) {

    $e = isset($_GET['edit'])
        ? q(
            'SELECT * FROM biblio WHERE id=?',
            array((int)$_GET['edit'])
        )->fetch()
        : array();

    if (!$e) {
        $e = array();
    }

    $g = function($k) use ($e) {
        return h($e[$k] ?? '');
    };

    $isEdit = !empty($e['id']);

    echo "
    <h1>" . ($isEdit ? 'Edit' : 'Tambah') . " Bibliografi</h1>

    <div class='pn'>

        <div class='top'>
            <div>
                <small>LIBLOOM COLLECTION</small>
                <h3 style='color:var(--forest)'>
                    " . ($isEdit ? 'Perbarui Data Buku' : 'Tambah Buku Baru') . "
                </h3>
            </div>
        </div>

        <form
            class='fm'
            method='post'
            action='?act=biblio'
            enctype='multipart/form-data'
        >

            <input type='hidden' name='id' value='" . $g('id') . "'>

            <div class='w'>
                <label>Judul Buku *</label>
                <input
                    type='text'
                    name='judul'
                    required
                    placeholder='Masukkan judul buku'
                    value='" . $g('judul') . "'
                >
            </div>

            <div>
                <label>Pengarang *</label>
                <input
                    type='text'
                    name='pengarang'
                    required
                    placeholder='Nama pengarang'
                    value='" . $g('pengarang') . "'
                >
            </div>

            <div>
                <label>Kolasi</label>
                <input
                    type='text'
                    name='kolasi'
                    placeholder='xii, 320 hlm; 26 cm'
                    value='" . $g('kolasi') . "'
                >
            </div>

            <div>
                <label>Judul Series</label>
                <input
                    type='text'
                    name='series'
                    value='" . $g('series') . "'
                >
            </div>

            <div>
                <label>Nomor Panggil</label>
                <input
                    type='text'
                    name='callno'
                    placeholder='813 HIR l'
                    value='" . $g('callno') . "'
                >
            </div>

            <div>
                <label>Kategori</label>
                <select name='kategori'>
    ";

    foreach ($CATS as $c => $icon) {

        $selected = (($e['kategori'] ?? 'Buku Teks') === $c)
            ? 'selected'
            : '';

        echo "<option value='" . h($c) . "' $selected>"
            . h($icon . ' ' . $c)
            . "</option>";
    }

    echo "
                </select>
            </div>

            <div>
                <label>Tipe Koleksi</label>
                <select name='tipe'>
    ";

    foreach (array('Tercetak','Elektronik','Audio-Visual') as $t) {

        $selected = (($e['tipe'] ?? 'Tercetak') === $t)
            ? 'selected'
            : '';

        echo "<option value='" . h($t) . "' $selected>"
            . h($t)
            . "</option>";
    }

    echo "
                </select>
            </div>

            <div>
                <label>Bahasa</label>
                <input
                    type='text'
                    name='bahasa'
                    value='" . ($isEdit ? $g('bahasa') : 'Indonesia') . "'
                >
            </div>

            <div>
                <label>" . ($isEdit ? 'Tambah Eksemplar Baru' : 'Jumlah Eksemplar') . "</label>
                <input
                    type='number'
                    min='0'
                    max='500'
                    name='jml'
                    value='0'
                >
            </div>

            <!-- UPLOAD COVER -->

            <div class='w'>

                <label>Cover Buku</label>

                <div class='cover-upload'>

                    <input
                        type='file'
                        name='cover'
                        accept='.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp'
                        id='coverInput'
                    >

                    <div class='cover-help' style='margin-top:10px'>
                        Format: JPG, JPEG, PNG, atau WEBP.<br>
                        Ukuran maksimal: 2 MB.<br>
                        Kosongkan jika tidak ingin mengganti cover lama.
                    </div>

                    <div class='cover-preview'>

                        <div id='coverPreview'>
                            " . (
                                $isEdit
                                ? cover($e['judul'], $e['id'], $e['cover'] ?? '')
                                : "<div class='cv' style='background:#e5ecd6'>
                                    <b style='color:#4a7c45'>+</b>
                                    <small style='color:#4a7c45'>LIBLOOM</small>
                                  </div>"
                            ) . "
                        </div>

                        <div>
                            <strong>Pratinjau Cover</strong>
                            <p class='cover-help'>
                                " . (
                                    $isEdit
                                    ? 'Cover buku saat ini. Pilih file baru jika ingin menggantinya.'
                                    : 'Cover yang kamu unggah akan ditampilkan pada daftar bibliografi.'
                                ) . "
                            </p>
                        </div>

                    </div>

                </div>

            </div>

            <div class='w' style='margin-top:20px;display:flex;gap:10px;flex-wrap:wrap'>

                <button type='submit' class='btn'>
                    Simpan Bibliografi
                </button>

                <a class='btn g' href='?page=koleksi'>
                    Batal
                </a>

            </div>

        </form>

    </div>

    <script>
    (function() {
        const input = document.getElementById('coverInput');
        const preview = document.getElementById('coverPreview');

        if (!input || !preview) return;

        input.addEventListener('change', function() {

            const file = this.files[0];

            if (!file) return;

            const allowed = ['image/jpeg','image/png','image/webp'];

            if (!allowed.includes(file.type)) {
                alert('Format cover harus JPG, PNG, atau WEBP.');
                this.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran cover maksimal 2 MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function(event) {
                preview.innerHTML =
                    '<div class=\"cv cover-image\">' +
                    '<img src=\"' + event.target.result + '\" alt=\"Pratinjau cover\">' +
                    '</div>';
            };

            reader.readAsDataURL(file);
        });
    })();
    </script>
    ";


// ==========================================
// DAFTAR BIBLIOGRAFI
// ==========================================

} else {

    $s = trim($_GET['s'] ?? '');

    $r = q(
        "$BQ WHERE judul LIKE ? OR pengarang LIKE ? OR callno LIKE ? ORDER BY id DESC",
        array("%$s%", "%$s%", "%$s%")
    )->fetchAll();

    // ======================================
    // HEADER
    // ======================================

    echo "

    <div class='hd'>

        <small>✦ KOLEKSI PERPUSTAKAAN</small>

        <h2>Daftar <em>Bibliografi</em></h2>

        <p style='margin-bottom:22px'>
            Kelola dan temukan informasi koleksi buku dengan mudah.
        </p>

        <div style='display:flex;gap:10px;flex-wrap:wrap'>

            <a class='btn' href='?page=koleksi&add=1'>
                + Tambah Bibliografi
            </a>

        </div>

    </div>

    $fl

    <div class='pn'>

        <div class='top'>

            <div>
                <small>THE BOOKSHELF</small>

                <h3 style='color:var(--forest)'>
                    Koleksi Bibliografi
                </h3>
            </div>

            <span class='pill'>
                " . count($r) . " koleksi
            </span>

        </div>

        <form class='sr' method='get'>

            <input type='hidden' name='page' value='koleksi'>

            <input
                type='text'
                name='s'
                value='" . h($s) . "'
                placeholder='Cari judul, pengarang, atau nomor panggil...'
            >

            <button type='submit' class='btn y'>
                Cari
            </button>

        </form>

        <div class='biblio-table-wrap'>

            <table class='biblio-table'>

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Cover</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Kolasi</th>
                        <th>Nomor Panggil</th>
                        <th>Tipe Koleksi</th>
                        <th>Bahasa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
    ";

    // ======================================
    // ISI TABEL
    // ======================================

    foreach ($r as $n => $b) {

        $id = (int)$b['id'];

        $callno = trim((string)$b['callno']);

        $callnoParts = preg_split('/\s+/', $callno, 2);

        $callnoFirst = $callnoParts[0] ?? '';
        $callnoSecond = $callnoParts[1] ?? '';

        echo "

        <tr>

            <td>" . ($n + 1) . "</td>

            <td>
                <div class='biblio-cover'>
                    " . cover($b['judul'], $id, $b['cover'] ?? '') . "
                </div>
            </td>

            <td>
                <span class='book-title'>
                    " . h($b['judul']) . "
                </span>
            </td>

            <td>" . h($b['pengarang']) . "</td>

            <td>" . h($b['kolasi']) . "</td>

            <td>
                <div class='callno-format'>
                    <span>" . h($callnoFirst) . "</span>
                    <span>" . h($callnoSecond) . "</span>
                </div>
            </td>

            <td>" . h($b['tipe']) . "</td>

            <td>" . h($b['bahasa']) . "</td>

            <td>

                <div class='biblio-actions'>

                    <!-- EDIT -->

                    <a
                        class='action-edit'
                        href='?page=koleksi&edit=" . $id . "'
                        title='Edit Bibliografi'
                        aria-label='Edit Bibliografi'
                    >
                        <svg viewBox='0 0 24 24'>
                            <path d='M12 20h9'/>
                            <path d='M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z'/>
                        </svg>
                    </a>

                    <!-- BARCODE -->

                    <a
                        class='action-label'
                        href='?page=barcode&biblio=" . $id . "'
                        title='Cetak Label'
                        aria-label='Cetak Label'
                    >
                        <svg viewBox='0 0 24 24'>
                            <path d='M3 7V4a1 1 0 0 1 1-1h3'/>
                            <path d='M17 3h3a1 1 0 0 1 1 1v3'/>
                            <path d='M21 17v3a1 1 0 0 1-1 1h-3'/>
                            <path d='M7 21H4a1 1 0 0 1-1-1v-3'/>
                            <path d='M7 8v8'/>
                            <path d='M11 8v8'/>
                            <path d='M15 8v8'/>
                            <path d='M18 8v8'/>
                        </svg>
                    </a>

                    <!-- HAPUS -->

                    <a
                        class='action-delete'
                        href='?page=koleksi&act=del&id=" . $id . "'
                        title='Hapus Bibliografi'
                        aria-label='Hapus Bibliografi'
                        onclick=\"return confirm('Hapus bibliografi ini?')\"
                    >
                        <svg viewBox='0 0 24 24'>
                            <path d='M3 6h18'/>
                            <path d='M8 6V4h8v2'/>
                            <path d='m19 6-1 14H6L5 6'/>
                            <path d='M10 11v5'/>
                            <path d='M14 11v5'/>
                        </svg>
                    </a>

                </div>

            </td>

        </tr>
        ";
    }

    if (count($r) === 0) {

        echo "
        <tr>
            <td colspan='9' style='text-align:center;padding:35px;color:var(--mut)'>
                Belum ada bibliografi yang ditemukan.
            </td>
        </tr>
        ";
    }

    echo "

                </tbody>

            </table>

        </div>

    </div>

    ";
}

admin_close();
