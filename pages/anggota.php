<?php
// ==========================================
// HALAMAN ANGGOTA LIBLOOM
// ==========================================

// ==========================================
// 1. PENGATURAN FOTO
// ==========================================

$folderFoto = __DIR__ . '/../assets/images/anggota/';
$urlFoto = 'assets/images/anggota/';

if (!is_dir($folderFoto)) {
    mkdir($folderFoto, 0755, true);
}

// ==========================================
// 2. PROSES SIMPAN ANGGOTA
// ==========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['simpan_anggota'])
) {

    $id = $_POST['id'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $telp = trim($_POST['telp'] ?? '');

    if ($nama === '') {
        die('Nama anggota wajib diisi.');
    }

    // Ambil foto lama
    $fotoLama = '';

    if ($id !== '') {

        $dataLama = q(
            "SELECT * FROM member WHERE id=?",
            [$id]
        )->fetch();

        if ($dataLama) {
            $fotoLama = $dataLama['foto'] ?? '';
        }
    }

    $namaFoto = $fotoLama;

    // Proses foto baru
    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            die('Upload foto gagal. Silakan coba kembali.');
        }

        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            die('Ukuran foto maksimal 2 MB.');
        }

        $infoFoto = getimagesize($_FILES['foto']['tmp_name']);

        if (!$infoFoto) {
            die('File bukan gambar yang valid.');
        }

        $formatFoto = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];

        $tipeFoto = $infoFoto['mime'];

        if (!isset($formatFoto[$tipeFoto])) {
            die('Format foto harus JPG, JPEG, atau PNG.');
        }

        $ekstensi = $formatFoto[$tipeFoto];

        $namaFoto = 'anggota_' .
            bin2hex(random_bytes(8)) . '.' . $ekstensi;

        $tujuanFoto = $folderFoto . $namaFoto;

        if (!move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            $tujuanFoto
        )) {
            die('Foto gagal disimpan.');
        }

        // Hapus foto lama setelah foto baru berhasil disimpan
        if (
            $fotoLama !== '' &&
            is_file($folderFoto . basename($fotoLama))
        ) {
            unlink($folderFoto . basename($fotoLama));
        }
    }

    // Simpan atau update anggota
    if ($id !== '') {

        q(
            "UPDATE member
             SET nama=?, kelas=?, telp=?, foto=?
             WHERE id=?",
            [$nama, $kelas, $telp, $namaFoto, $id]
        );

    } else {

        q(
            "INSERT INTO member (nama, kelas, telp, foto)
             VALUES (?, ?, ?, ?)",
            [$nama, $kelas, $telp, $namaFoto]
        );
    }

    header("Location: ?page=anggota");
    exit;
}

// ==========================================
// 3. BUKA HALAMAN ADMIN
// ==========================================

admin_open('anggota');

// ==========================================
// 4. CETAK KARTU ANGGOTA
// ==========================================

if (isset($_GET['cetak'])) {

    $m = q(
        "SELECT * FROM member WHERE id=?",
        [(int)$_GET['cetak']]
    )->fetch();

    if ($m) {

        $fotoCetak = '';

        if (!empty($m['foto'])) {
            $fotoCetak = $urlFoto .
                rawurlencode(basename($m['foto']));
        }

        ?>

        <div class="pn halaman-cetak">

            <h2>Preview Kartu Anggota LIBLOOM</h2>

            <div id="print-area">

                <!-- SISI DEPAN -->
                <div class="kartu kartu-depan">

                    <div class="header-depan">
                        <div class="ikon-bunga">✿</div>
                        <div class="teks-header">
                            <div class="judul-depan">KARTU ANGGOTA</div>
                            <div class="subjudul-depan">PERPUSTAKAAN LIBLOOM</div>
                        </div>
                    </div>

                    <div class="rules-title">
                        TATA TERTIB KEANGGOTAAN
                    </div>

                    <ol class="rules">
                        <li>
                            Kartu anggota merupakan identitas resmi
                            pemustaka dan hanya boleh digunakan oleh
                            pemiliknya.
                        </li>

                        <li>
                            Setiap anggota wajib menunjukkan kartu
                            kepada petugas ketika melakukan transaksi
                            peminjaman maupun pengembalian koleksi.
                        </li>

                        <li>
                            Seluruh anggota wajib menjaga kebersihan,
                            ketenangan, dan kenyamanan perpustakaan.
                        </li>

                        <li>
                            Anggota bertanggung jawab atas koleksi
                            yang dipinjam serta wajib menjaga kondisi buku.
                        </li>

                        <li>
                            Seluruh anggota wajib mengikuti peraturan
                            perpustakaan.
                        </li>
                    </ol>

                </div>

                <!-- SISI BELAKANG -->
                <div class="kartu kartu-belakang">

                    <div class="header-belakang">
                        <h2>KARTU ANGGOTA</h2>
                        <p>PERPUSTAKAAN LIBLOOM</p>
                    </div>

                    <div class="identitas-content">

                        <div class="foto-anggota">

                            <?php if ($fotoCetak !== ''): ?>

                                <img
                                    src="<?= h($fotoCetak) ?>"
                                    alt="Foto Anggota">

                            <?php else: ?>

                                👤

                            <?php endif; ?>

                        </div>

                        <table class="tabel-identitas">

                            <tr>
                                <td>ID Anggota</td>
                                <td>: <?= h($m['id']) ?></td>
                            </tr>

                            <tr>
                                <td>Nama</td>
                                <td>: <?= h($m['nama']) ?></td>
                            </tr>

                            <tr>
                                <td>Kelas</td>
                                <td>: <?= h($m['kelas']) ?></td>
                            </tr>

                            <tr>
                                <td>Telepon</td>
                                <td>: <?= h($m['telp']) ?></td>
                            </tr>

                        </table>

                    </div>

                    <div class="footer-belakang">
                        Kartu ini merupakan identitas resmi anggota perpustakaan.
                    </div>

                </div>

            </div>

            <div class="aksi-cetak">

                <button class="btn" onclick="window.print()">
                    Cetak Kartu
                </button>

                <a class="btn g" href="?page=anggota">
                    Kembali
                </a>

            </div>

        </div>

        <style>

        /* ========== TAMPILAN LAYAR ========== */

        #print-area {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 6mm;
            margin: 30px auto;
        }

        .kartu {
            width: 85.6mm;
            height: 54mm;
            box-sizing: border-box;
            overflow: hidden;
            border: 0.3mm solid #829568;
            border-radius: 3mm;
            background: #FFF9E9;
            color: #536B3D;
            font-family: Arial, Helvetica, sans-serif;
            flex-shrink: 0;
            text-align: left;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        /* ----- Sisi depan ----- */

        .kartu-depan {
            padding: 2.8mm 4mm 2.5mm;
            display: flex;
            flex-direction: column;
        }

        .header-depan {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2.5mm;
            margin-bottom: 1.8mm;
        }

        .ikon-bunga {
            font-size: 16pt;
            line-height: 1;
            color: #71864E;
        }

        .teks-header {
            text-align: left;
        }

        .judul-depan {
            font-size: 10.5pt;
            font-weight: bold;
            letter-spacing: .5pt;
            line-height: 1.1;
        }

        .subjudul-depan {
            font-size: 6.5pt;
            letter-spacing: .5pt;
            line-height: 1.2;
            margin-top: .6mm;
        }

        .rules-title {
            width: 100%;
            box-sizing: border-box;
            font-size: 6.5pt;
            font-weight: bold;
            border-top: 0.25mm solid #9BAA7D;
            border-bottom: 0.25mm solid #9BAA7D;
            padding: .9mm 0;
            margin-bottom: 1.2mm;
        }

        .rules {
            box-sizing: border-box;
            width: 100%;
            padding-left: 3.6mm;
            margin: 0;
            font-size: 6pt;
            line-height: 1.28;
        }

        .rules li {
            margin-bottom: .7mm;
        }

        /* ----- Sisi belakang ----- */

        .kartu-belakang {
            padding: 3.5mm 4mm;
            display: flex;
            flex-direction: column;
        }

        .header-belakang {
            text-align: center;
            border-bottom: 0.4mm solid #829568;
            padding-bottom: 1.8mm;
        }

        .header-belakang h2 {
            font-size: 10.5pt;
            margin: 0;
            line-height: 1.1;
        }

        .header-belakang p {
            font-size: 6.5pt;
            margin: .8mm 0 0;
        }

        .identitas-content {
            flex: 1;
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4mm;
        }

        .foto-anggota {
            width: 15mm;
            height: 20mm;
            border: 0.25mm solid #829568;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 20pt;
        }

        .foto-anggota img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tabel-identitas {
            width: auto;
            max-width: 50mm;
            border-collapse: collapse;
            font-size: 7pt;
        }

        .tabel-identitas td {
            padding: 1.1mm 1mm;
            vertical-align: top;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .tabel-identitas td:first-child {
            width: 15mm;
            font-weight: bold;
            white-space: nowrap;
        }

        .footer-belakang {
            border-top: 0.25mm solid #9BAA7D;
            text-align: center;
            font-size: 5.3pt;
            padding-top: 1.5mm;
        }

        .aksi-cetak {
            margin-top: 20px;
        }

        /* ========== TAMPILAN CETAK ========== */
        /* Catatan: style.css global menyembunyikan .pn saat print
           (untuk cetak label/katalog). Di sini kita override khusus
           halaman kartu, dan hanya menyembunyikan elemen non-kartu. */

        @media print {

            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            html, body {
                background: #fff !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .sd,
            .bc,
            .pagehead,
            .halaman-cetak > h2,
            .aksi-cetak {
                display: none !important;
            }

            .adm {
                display: block !important;
                min-height: 0 !important;
                background: #fff !important;
            }

            .mn {
                padding: 0 !important;
                width: auto !important;
            }

            .pn.halaman-cetak {
                display: block !important;
                border: 0 !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
                overflow: visible !important;
            }

            #print-area {
                display: flex;
                flex-wrap: nowrap;
                justify-content: flex-start;
                align-items: flex-start;
                gap: 6mm;
                margin: 0;
            }

            .kartu {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }

        </style>

        <?php

    } else {

        echo "
            <div class='pn'>
                Data anggota tidak ditemukan.
                <a href='?page=anggota'>Kembali</a>
            </div>
        ";

    }

// ==========================================
// 5. FORM TAMBAH DAN EDIT ANGGOTA
// ==========================================

} elseif (isset($_GET['add']) || isset($_GET['edit'])) {

    $e = isset($_GET['edit'])
        ? q(
            "SELECT * FROM member WHERE id=?",
            [(int)$_GET['edit']]
        )->fetch()
        : [];

    $e = $e ?: [];

    $fotoLamaUrl = '';

    if (!empty($e['foto'])) {
        $fotoLamaUrl = $urlFoto .
            rawurlencode(basename($e['foto']));
    }

    ?>

    <h1><?= $e ? 'Edit' : 'Tambah' ?> Anggota</h1>

    <div class="pn">

        <form
            class="fm"
            id="formAnggota"
            method="post"
            action="?page=anggota"
            enctype="multipart/form-data">

            <input
                type="hidden"
                name="id"
                value="<?= h($e['id'] ?? '') ?>">

            <!-- NAMA -->
            <div class="w">
                <label>Nama lengkap</label>

                <input
                    type="text"
                    name="nama"
                    required
                    value="<?= h($e['nama'] ?? '') ?>"
                    placeholder="Masukkan nama lengkap">
            </div>

            <!-- KELAS -->
            <div>
                <label>Kelas</label>

                <input
                    type="text"
                    name="kelas"
                    value="<?= h($e['kelas'] ?? '') ?>"
                    placeholder="Contoh: XI IPA 1">
            </div>

            <!-- TELEPON -->
            <div>
                <label>No. telepon</label>

                <input
                    type="text"
                    name="telp"
                    value="<?= h($e['telp'] ?? '') ?>"
                    placeholder="Masukkan nomor telepon">
            </div>

            <!-- FOTO ANGGOTA -->
            <div class="w foto-section">

                <label class="judul-foto">
                    Foto Anggota
                </label>

                <input
                    type="file"
                    name="foto"
                    id="inputFoto"
                    accept="image/jpeg,image/png"
                    hidden>

                <div class="pilihan-foto">

                    <button
                        type="button"
                        class="foto-action-btn"
                        id="btnPilihFoto">

                        <span class="foto-icon">📁</span>
                        <span>Pilih Foto</span>

                    </button>

                    <button
                        type="button"
                        class="foto-action-btn"
                        id="btnBukaKamera">

                        <span class="foto-icon">📷</span>
                        <span>Buka Kamera</span>

                    </button>

                </div>

                <small class="petunjuk-foto">
                    Format JPG, JPEG, atau PNG. Maksimal 2 MB.
                </small>

                <!-- KAMERA -->
                <div id="kameraBox" hidden>

                    <p class="kamera-title">
                        Ambil Foto Anggota
                    </p>

                    <video
                        id="videoKamera"
                        autoplay
                        playsinline>
                    </video>

                    <canvas id="canvasKamera" hidden></canvas>

                    <div class="aksi-kamera">

                        <button
                            type="button"
                            class="foto-action-btn tombol-ambil"
                            id="btnAmbilFoto">

                            📸 Ambil Foto

                        </button>

                        <button
                            type="button"
                            class="foto-action-btn tombol-tutup"
                            id="btnTutupKamera">

                            Tutup Kamera

                        </button>

                    </div>

                    <small id="statusKamera"></small>

                </div>

                <!-- PREVIEW FOTO BARU -->
                <div id="previewBox" hidden>

                    <p class="preview-title">
                        Preview Foto
                    </p>

                    <img
                        id="previewFoto"
                        src=""
                        alt="Preview foto anggota">

                    <button
                        type="button"
                        class="hapus-foto-btn"
                        id="btnHapusPreview">

                        ✕ Hapus Foto Pilihan

                    </button>

                </div>

                <!-- FOTO LAMA -->
                <?php if ($fotoLamaUrl !== ''): ?>

                    <div id="fotoLamaBox">

                        <p class="preview-title">
                            Foto saat ini:
                        </p>

                        <img
                            src="<?= h($fotoLamaUrl) ?>"
                            id="fotoLama"
                            alt="Foto anggota saat ini">

                    </div>

                <?php endif; ?>

            </div>

            <!-- TOMBOL FORM -->
            <div class="w tombol-form">

                <button
                    class="btn"
                    type="submit"
                    name="simpan_anggota">

                    Simpan

                </button>

                <a
                    class="btn g"
                    href="?page=anggota">

                    Batal

                </a>

            </div>

        </form>

    </div>

    <style>

    /* ==================================
       FOTO ANGGOTA - LIBLOOM
    ================================== */

    .foto-section {
        width: 100%;
        margin-top: 12px;
    }

    .judul-foto {
        display: block;
        margin-bottom: 14px;
        color: #3a4630;
        font-size: 16px;
        font-weight: 600;
    }

    /* BARIS TOMBOL FOTO */

    .pilihan-foto {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* RESET DAN UKURAN SERAGAM */

    .foto-action-btn {
        appearance: none;
        -webkit-appearance: none;
        box-sizing: border-box;

        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-direction: row !important;
        gap: 9px !important;

        width: 185px !important;
        min-width: 185px !important;
        max-width: 185px !important;

        height: 48px !important;
        min-height: 48px !important;
        max-height: 48px !important;

        margin: 0 !important;
        padding: 0 14px !important;

        border: 1px solid #6a9b47 !important;
        border-radius: 12px !important;

        background: #6a9b47 !important;
        color: #ffffff !important;

        font-family: inherit !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        line-height: 1 !important;

        text-align: center !important;
        white-space: nowrap !important;

        cursor: pointer;
        box-shadow: none !important;
        transition: background .2s ease,
                    transform .2s ease !important;
    }

    .foto-action-btn:hover {
        background: #527e37 !important;
        border-color: #527e37 !important;
        transform: translateY(-2px);
    }

    .foto-action-btn:active {
        transform: scale(.98);
    }

    .foto-icon {
        font-size: 16px;
        line-height: 1;
    }

    /* PETUNJUK */

    .petunjuk-foto {
        display: block;
        margin-top: 12px;
        color: #858575;
        font-size: 12px;
        line-height: 1.5;
    }

    /* AREA KAMERA */

    #kameraBox {
        box-sizing: border-box;
        width: 100%;
        max-width: 420px;
        padding: 16px;
        margin-top: 20px;

        background: #fffdf7;
        border: 1px solid #e5ecd6;
        border-radius: 12px;
    }

    #kameraBox[hidden],
    #previewBox[hidden] {
        display: none !important;
    }

    .kamera-title,
    .preview-title {
        color: #3a4630;
        font-size: 14px;
        font-weight: 600;
        margin: 0 0 12px;
    }

    #videoKamera {
        display: block;
        width: 100%;
        max-height: 320px;
        object-fit: cover;
        border-radius: 10px;
        background: #222;
    }

    .aksi-kamera {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .aksi-kamera .foto-action-btn {
        width: auto !important;
        min-width: 125px !important;
        max-width: none !important;
        padding: 0 14px !important;
    }

    .aksi-kamera .tombol-tutup {
        background: #fffdf7 !important;
        color: #527e37 !important;
        border-color: #a8bb95 !important;
    }

    #statusKamera {
        display: block;
        margin-top: 10px;
        color: #858575;
        font-size: 12px;
    }

    /* PREVIEW FOTO */

    #previewBox,
    #fotoLamaBox {
        margin-top: 20px;
    }

    #previewFoto,
    #fotoLama {
        display: block;
        width: 120px;
        height: 145px;
        object-fit: cover;
        border: 2px solid #e5ecd6;
        border-radius: 10px;
        margin: 10px 0;
    }

    .hapus-foto-btn {
        border: none;
        background: transparent;
        color: #bd5c4c;
        padding: 5px 0;
        font-family: inherit;
        font-size: 12px;
        cursor: pointer;
    }

    /* TOMBOL SIMPAN DAN BATAL */

    .tombol-form {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
    }

    /* RESPONSIVE */

    @media (max-width: 600px) {

        .pilihan-foto {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .pilihan-foto .foto-action-btn {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }

        .aksi-kamera {
            flex-direction: column;
        }

        .aksi-kamera .foto-action-btn {
            width: 100% !important;
            max-width: 100% !important;
        }

    }

    </style>

    <script>

    document.addEventListener('DOMContentLoaded', function () {

        const inputFoto = document.getElementById('inputFoto');

        const btnPilihFoto = document.getElementById('btnPilihFoto');
        const btnBukaKamera = document.getElementById('btnBukaKamera');

        const kameraBox = document.getElementById('kameraBox');
        const video = document.getElementById('videoKamera');
        const canvas = document.getElementById('canvasKamera');

        const btnAmbilFoto = document.getElementById('btnAmbilFoto');
        const btnTutupKamera = document.getElementById('btnTutupKamera');

        const previewBox = document.getElementById('previewBox');
        const previewFoto = document.getElementById('previewFoto');
        const btnHapusPreview = document.getElementById('btnHapusPreview');

        const statusKamera = document.getElementById('statusKamera');

        let streamKamera = null;
        let fotoObjectURL = null;

        // Buka galeri
        btnPilihFoto.addEventListener('click', function () {
            inputFoto.click();
        });

        // Tampilkan preview
        function tampilkanPreview(file) {

            if (fotoObjectURL) {
                URL.revokeObjectURL(fotoObjectURL);
            }

            fotoObjectURL = URL.createObjectURL(file);

            previewFoto.src = fotoObjectURL;
            previewBox.hidden = false;

            const fotoLamaBox = document.getElementById('fotoLamaBox');

            if (fotoLamaBox) {
                fotoLamaBox.style.display = 'none';
            }
        }

        // Pilih foto dari galeri
        inputFoto.addEventListener('change', function () {

            const file = inputFoto.files[0];

            if (!file) return;

            if (!['image/jpeg', 'image/png'].includes(file.type)) {
                alert('Format foto harus JPG, JPEG, atau PNG.');
                inputFoto.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran foto maksimal 2 MB.');
                inputFoto.value = '';
                return;
            }

            tampilkanPreview(file);
        });

        // Tutup kamera
        function tutupKamera() {

            if (streamKamera) {

                streamKamera.getTracks().forEach(function (track) {
                    track.stop();
                });

                streamKamera = null;
            }

            video.srcObject = null;
            kameraBox.hidden = true;
        }

        // Buka kamera
        btnBukaKamera.addEventListener('click', async function () {

            if (!navigator.mediaDevices ||
                !navigator.mediaDevices.getUserMedia) {

                alert(
                    'Browser tidak mendukung kamera. ' +
                    'Gunakan localhost atau koneksi HTTPS.'
                );

                return;
            }

            try {

                statusKamera.textContent = 'Mengaktifkan kamera...';

                kameraBox.hidden = false;

                streamKamera = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user'
                    },
                    audio: false
                });

                video.srcObject = streamKamera;

                await video.play();

                statusKamera.textContent =
                    'Posisikan wajah di depan kamera, lalu ambil foto.';

            } catch (error) {

                tutupKamera();

                alert(
                    'Kamera tidak dapat dibuka. ' +
                    'Pastikan izin kamera sudah diberikan kepada browser.'
                );

                console.error(error);
            }

        });

        // Ambil foto dari kamera
        btnAmbilFoto.addEventListener('click', function () {

            if (!video.videoWidth) {
                alert('Kamera belum siap.');
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const context = canvas.getContext('2d');

            context.drawImage(
                video,
                0,
                0,
                canvas.width,
                canvas.height
            );

            canvas.toBlob(function (blob) {

                if (!blob) {
                    alert('Gagal mengambil foto.');
                    return;
                }

                const file = new File(
                    [blob],
                    'foto_anggota.jpg',
                    {
                        type: 'image/jpeg'
                    }
                );

                const dataTransfer = new DataTransfer();

                dataTransfer.items.add(file);

                inputFoto.files = dataTransfer.files;

                tampilkanPreview(file);

                tutupKamera();

            }, 'image/jpeg', 0.90);

        });

        // Tutup kamera
        btnTutupKamera.addEventListener('click', function () {
            tutupKamera();
        });

        // Hapus foto pilihan
        btnHapusPreview.addEventListener('click', function () {

            inputFoto.value = '';

            previewBox.hidden = true;
            previewFoto.src = '';

            if (fotoObjectURL) {
                URL.revokeObjectURL(fotoObjectURL);
                fotoObjectURL = null;
            }

            const fotoLamaBox = document.getElementById('fotoLamaBox');

            if (fotoLamaBox) {
                fotoLamaBox.style.display = 'block';
            }

        });

        // Matikan kamera ketika halaman ditinggalkan
        window.addEventListener('beforeunload', function () {
            tutupKamera();

            if (fotoObjectURL) {
                URL.revokeObjectURL(fotoObjectURL);
            }
        });

    });

    </script>

    <?php

// ==========================================
// 6. TABEL DATA ANGGOTA
// ==========================================

} else {

    $fl = $fl ?? '';

    ?>

    <h1>Anggota</h1>

    <?= $fl ?>

    <div class="pn">

        <div class="top">

            <h3>Data anggota</h3>

            <a class="btn" href="?page=anggota&add=1">
                + Tambah Anggota
            </a>

        </div>

        <div class="anggota-table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Telepon</th>
                        <th>Dipinjam</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                <?php

                $dataAnggota = q("
                    SELECT m.*,

                    (SELECT COUNT(*)
                     FROM loan
                     WHERE member_id=m.id
                     AND tgl_kembali IS NULL) AS p

                    FROM member m
                    ORDER BY nama
                ")->fetchAll();

                if ($dataAnggota):

                    foreach ($dataAnggota as $n => $m):

                ?>

                    <tr>

                        <td><?= $n + 1 ?></td>

                        <td>
                            <b><?= h($m['nama']) ?></b>
                        </td>

                        <td><?= h($m['kelas']) ?></td>

                        <td><?= h($m['telp']) ?></td>

                        <td><?= (int)$m['p'] ?></td>

                        <td class="aksi-anggota">

                            <a
                                class="btn s g"
                                href="?page=anggota&edit=<?= (int)$m['id'] ?>">
                                Edit
                            </a>

                            <a
                                class="btn s"
                                href="?page=anggota&cetak=<?= (int)$m['id'] ?>">
                                Cetak Kartu
                            </a>

                            <a
                                class="btn s r"
                                onclick="return confirm('Hapus anggota?')"
                                href="?page=anggota&act=del&id=<?= (int)$m['id'] ?>">
                                Hapus
                            </a>

                        </td>

                    </tr>

                <?php

                    endforeach;

                else:

                ?>

                    <tr>
                        <td colspan="6" style="text-align:center;padding:25px">
                            Belum ada data anggota.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <style>

    .anggota-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .anggota-table-wrap table {
        min-width: 750px;
        width: 100%;
    }

    .aksi-anggota {
        white-space: nowrap;
    }

    </style>

    <?php
}

// ==========================================
// 7. TUTUP HALAMAN ADMIN
// ==========================================

admin_close();