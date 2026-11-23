
<?php
admin_open('scan');

// Ambil kode barcode dari URL
$code = strtoupper(trim($_GET['code'] ?? ''));
$found = null;

if ($code !== '') {
    $found = q("
        SELECT
            i.id AS item_id,
            i.barcode,
            b.judul,
            b.pengarang,
            b.callno,
            b.id AS biblio_id,
            m.nama AS peminjam,
            l.id AS loan_id,
            l.tgl_pinjam,
            l.jatuh_tempo
        FROM item i
        JOIN biblio b ON b.id = i.biblio_id
        LEFT JOIN loan l
            ON l.item_id = i.id
            AND l.tgl_kembali IS NULL
        LEFT JOIN member m ON m.id = l.member_id
        WHERE UPPER(TRIM(i.barcode)) = ?
        LIMIT 1
    ", [$code])->fetch();
}
?>

<div class="pagehead" style="margin-bottom:22px">
    <span class="eyebrow">PEMINDAIAN INVENTARIS</span>
    <h1>Scan Barcode</h1>
    <p style="color:var(--mut)">
        Gunakan scanner USB atau kamera perangkat untuk mencari data eksemplar.
    </p>
</div>

<div class="two">

    <!-- SCANNER USB -->
    <section class="pn">
        <h3>1. Pindai dengan scanner USB</h3>

        <p style="color:var(--mut);font-size:13px;margin:8px 0 16px">
            Klik kolom, lalu arahkan scanner ke barcode.
            Scanner akan mengirimkan kode secara otomatis.
        </p>

        <form method="GET" action="" class="scan-form">
            <input type="hidden" name="page" value="scan">

            <input
                type="text"
                id="barcodeInput"
                name="code"
                placeholder="Klik di sini, lalu scan barcode..."
                autocomplete="off"
                autocapitalize="characters"
                spellcheck="false"
                autofocus
                required
            >

            <button type="submit" class="btn">
                Cari kode
            </button>
        </form>
    </section>

    <!-- SCANNER KAMERA -->
    <section class="pn">
        <h3>2. Pindai dengan kamera</h3>

        <div id="reader" class="video-box"></div>

        <button
            type="button"
            class="btn g"
            id="startCamera"
            style="margin-top:14px"
        >
            Aktifkan kamera
        </button>

        <p
            id="cameraStatus"
            style="color:var(--mut);font-size:13px;margin-top:10px"
        >
            Kamera memerlukan izin browser dan koneksi aman (HTTPS) atau localhost.
        </p>
    </section>

</div>

<!-- HASIL PEMINDAIAN -->
<?php if ($code !== ''): ?>

<section class="pn scan-result">

    <div class="top">
        <h3>Hasil pemindaian</h3>
        <span class="pill"><?= h($code) ?></span>
    </div>

    <?php if ($found): ?>

        <h2 style="font:700 24px 'Playfair Display';margin:8px 0">
            <?= h($found['judul']) ?>
        </h2>

        <p style="color:var(--mut)">
            <?= h($found['pengarang']) ?>
            ·
            <?= h($found['callno']) ?>
        </p>

        <p style="margin-top:16px">
            <b>Barcode:</b> <?= h($found['barcode']) ?>
        </p>

        <p>
            <b>Nomor inventaris:</b> <?= h($found['item_id']) ?>
        </p>

        <?php if ($found['loan_id']): ?>

            <p style="margin-top:10px">
                Status:
                <span class="bd b1">Sedang dipinjam</span>
            </p>

            <p>
                Peminjam:
                <b><?= h($found['peminjam'] ?? '-') ?></b>
            </p>

            <p>
                Tanggal pinjam:
                <b><?= h($found['tgl_pinjam'] ?? '-') ?></b>
            </p>

            <p>
                Jatuh tempo:
                <b><?= h($found['jatuh_tempo'] ?? '-') ?></b>
            </p>

        <?php else: ?>

            <p style="margin-top:10px">
                Status:
                <span class="bd b3">Tersedia</span>
            </p>

        <?php endif; ?>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:18px">

            <a
                class="btn g"
                href="?page=barcode&amp;biblio=<?= (int)$found['biblio_id'] ?>"
            >
                Lihat label
            </a>

            <a class="btn" href="?page=sirkulasi">
                Buka sirkulasi
            </a>

        </div>

    <?php else: ?>

        <p>
            Kode <b><?= h($code) ?></b> tidak ditemukan.
            Pastikan barcode sudah terdaftar pada inventaris Libloom.
        </p>

        <p style="color:var(--mut);font-size:13px;margin-top:8px">
            Periksa kembali nomor barcode pada data eksemplar.
        </p>

    <?php endif; ?>

</section>

<?php endif; ?>

<!-- LIBRARY PEMINDAI KAMERA -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
(function () {

    const startBtn = document.getElementById('startCamera');
    const statusEl = document.getElementById('cameraStatus');

    if (!startBtn) return;

    let scanner = null;

    startBtn.addEventListener('click', async function () {

        if (!window.Html5Qrcode) {
            statusEl.textContent =
                'Library pemindai tidak dapat dimuat. Periksa koneksi internet.';
            return;
        }

        try {

            startBtn.disabled = true;
            statusEl.textContent = 'Meminta izin kamera...';

            scanner = new Html5Qrcode('reader');

            await scanner.start(
                { facingMode: 'environment' },
                {
                    fps: 10,

                    qrbox: {
                        width: 260,
                        height: 120
                    },

                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.QR_CODE
                    ]
                },

                async function (decoded) {

                    statusEl.textContent =
                        'Kode berhasil dibaca: ' + decoded;

                    try {
                        await scanner.stop();
                    } catch (e) {}

                    window.location.href =
                        '?page=scan&code=' +
                        encodeURIComponent(decoded.trim());

                },

                function () {}

            );

            statusEl.textContent =
                'Kamera aktif. Arahkan kamera ke barcode.';

        } catch (e) {

            startBtn.disabled = false;

            statusEl.textContent =
                'Kamera tidak dapat dibuka. Izinkan akses kamera dan gunakan localhost atau HTTPS.';

            console.error(e);

        }

    });

})();
</script>

<?php admin_close(); ?>
