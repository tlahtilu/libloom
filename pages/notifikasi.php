
<?php
admin_open('notifikasi');

$items = notificationData();

echo "
<h1>Notifikasi &amp; Pengingat</h1>
$fl

<div class='pn'>
    <div class='top'>
        <div>
            <h3>Pengingat sirkulasi</h3>
            <small>Pengingat otomatis berdasarkan tanggal jatuh tempo.</small>
        </div>

        <span class='pill'>" . count($items) . " pengingat</span>
    </div>
";

if (!$items) {
    echo "
    <div class='notice-row'>
        <b>Semua aman!</b>
        <p>Tidak ada peminjaman yang mendekati jatuh tempo atau terlambat saat ini.</p>
    </div>
    ";
}

foreach ($items as $item) {

    $badge = $item['type'] === 'late'
        ? "<span class='bd b2'>Terlambat</span>"
        : ($item['type'] === 'today'
            ? "<span class='bd b1'>Hari ini</span>"
            : "<span class='bd b3'>Segera</span>");

    echo "
    <div class='notice-row'>
        <div style='display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap'>
            <b>" . h($item['title']) . "</b>
            $badge
        </div>

        <p>" . h($item['detail']) . "</p>

        <small>
            Jatuh tempo: " . h($item['date']) . " · " . h($item['extra']) . "
        </small>
    </div>
    ";
}

echo "
<p style='color:var(--mut);font-size:13px;margin-top:18px'>
    Catatan: notifikasi tampil di dalam Back Office Libloom.
    Sistem belum mengirim pesan melalui WhatsApp, email, atau push notification.
</p>

</div>
";

admin_close();
?>

<style>
/* Tombol jumlah pengingat */
.pill {
    background: #f8dce0 !important;
    color: #c94b5b !important;
    padding: 10px 24px;
    border-radius: 30px;
    font-size: 15px;
    font-weight: 600;
    display: inline-block;
}
</style>
