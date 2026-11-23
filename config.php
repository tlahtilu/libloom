<?php
// Konfigurasi dan koneksi database Libloom
session_start();
date_default_timezone_set('Asia/Jakarta');

$db = new PDO('sqlite:' . __DIR__ . '/libloom.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$db->exec("CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL UNIQUE,
  password TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'petugas',
  created_at TEXT DEFAULT CURRENT_TIMESTAMP
)");

// Kolom profil tambahan untuk tabel users (aman dijalankan berulang kali)
$userCols = array();

foreach ($db->query("PRAGMA table_info(users)")->fetchAll() as $col) {
    $userCols[] = $col['name'];
}

foreach (array('nama', 'email', 'telp') as $newCol) {
    if (!in_array($newCol, $userCols, true)) {
        $db->exec("ALTER TABLE users ADD COLUMN $newCol TEXT");
    }
}

$db->exec("CREATE TABLE IF NOT EXISTS biblio(id INTEGER PRIMARY KEY AUTOINCREMENT,judul,pengarang,kolasi,series,callno,tipe,bahasa,kategori);
CREATE TABLE IF NOT EXISTS item(id INTEGER PRIMARY KEY AUTOINCREMENT,biblio_id INT,barcode);
CREATE TABLE IF NOT EXISTS member(id INTEGER PRIMARY KEY AUTOINCREMENT,nama,kelas,telp);
CREATE TABLE IF NOT EXISTS loan(id INTEGER PRIMARY KEY AUTOINCREMENT,item_id INT,member_id INT,tgl_pinjam,jatuh_tempo,tgl_kembali)");

// Tambahkan kolom cover jika belum tersedia
$colsBiblio = $db->query("PRAGMA table_info(biblio)")->fetchAll();

$hasCover = false;

foreach ($colsBiblio as $col) {
    if ($col['name'] === 'cover') {
        $hasCover = true;
        break;
    }
}

if (!$hasCover) {
    $db->exec("ALTER TABLE biblio ADD COLUMN cover TEXT");
}


$cols = $db->query("PRAGMA table_info(loan)")->fetchAll();
$columnNames = array();

foreach ($cols as $col) {
    $columnNames[] = $col['name'];
}

if (!in_array('denda', $columnNames, true)) {
    $db->exec("ALTER TABLE loan ADD COLUMN denda INTEGER NOT NULL DEFAULT 0");
}

function q($sql, $params = array()) {
    global $db;
    $st = $db->prepare($sql);
    $st->execute($params);
    return $st;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function go($url) {
    header('Location: ' . $url);
    exit;
}

function flash($message) {
    $_SESSION['f'] = $message;
}

function addItems($biblioId, $count) {
    global $db;

    for ($i = 0; $i < $count; $i++) {
        q('INSERT INTO item(biblio_id,barcode) VALUES(?,?)', array($biblioId, 'TEMP-' . uniqid()));
        $id = (int)$db->lastInsertId();
        q('UPDATE item SET barcode=? WHERE id=?', array('LB' . str_pad((string)$id, 5, '0', STR_PAD_LEFT), $id));
    }
}

// Ambil data akun yang sedang login langsung dari database
function loggedUser() {
    static $cache = array();

    $id = (int)($_SESSION['user_id'] ?? 0);

    if ($id <= 0) {
        return null;
    }

    if (!array_key_exists($id, $cache)) {
        $row = q('SELECT * FROM users WHERE id=?', array($id))->fetch();
        $cache[$id] = $row ? $row : null;
    }

    return $cache[$id];
}

function isLoggedIn() {
    // Kalau akun sudah dihapus dari database, sesi dianggap tidak valid
    return loggedUser() !== null;
}

function currentUser() {
    $u = loggedUser();

    if ($u) {
        $nama = trim((string)($u['nama'] ?? ''));

        return array(
            'id'       => (int)$u['id'],
            'username' => $u['username'],
            'nama'     => $nama !== '' ? $nama : $u['username'],
            'role'     => $u['role']
        );
    }

    return array('id' => 0, 'username' => 'Petugas', 'nama' => 'Petugas', 'role' => 'petugas');
}

function overdueDays($dueDate, $today = null) {
    $today = $today ? $today : date('Y-m-d');
    $due = new DateTime($dueDate);
    $now = new DateTime($today);

    return max(0, (int)$due->diff($now)->format('%r%a'));
}

function notificationData() {
    $rows = q("SELECT l.id,l.tgl_pinjam,l.jatuh_tempo,l.denda,m.nama,b.judul
        FROM loan l JOIN member m ON m.id=l.member_id JOIN item i ON i.id=l.item_id
        JOIN biblio b ON b.id=i.biblio_id WHERE l.tgl_kembali IS NULL ORDER BY l.jatuh_tempo ASC")->fetchAll();

    $items = array();

    foreach ($rows as $row) {
        $days = overdueDays($row['jatuh_tempo']);
        $diff = (int)(new DateTime(date('Y-m-d')))->diff(new DateTime($row['jatuh_tempo']))->format('%r%a');

        if ($days > 0) {
            $items[] = array(
                'type'=>'late',
                'title'=>'Peminjaman terlambat',
                'detail'=>$row['nama'].' — '.$row['judul'],
                'date'=>$row['jatuh_tempo'],
                'extra'=>'Terlambat '.$days.' hari · Estimasi denda Rp'.number_format($days*1000,0,',','.')
            );
        } elseif ($diff === 0) {
            $items[] = array(
                'type'=>'today',
                'title'=>'Jatuh tempo hari ini',
                'detail'=>$row['nama'].' — '.$row['judul'],
                'date'=>$row['jatuh_tempo'],
                'extra'=>'Mohon ingatkan peminjam untuk mengembalikan buku hari ini'
            );
        } elseif ($diff <= 2) {
            $items[] = array(
                'type'=>'soon',
                'title'=>'Jatuh tempo dalam '.$diff.' hari',
                'detail'=>$row['nama'].' — '.$row['judul'],
                'date'=>$row['jatuh_tempo'],
                'extra'=>'Pengingat pengembalian buku'
            );
        }
    }

    return $items;
}

$CATS = array('Buku Teks'=>'📖','Fiksi'=>'📚','Nonfiksi'=>'🌿','Referensi'=>'🎨');

$titles = array(
    'dashboard'=>'Dashboard',
    'koleksi'=>'Data Koleksi',
    'barcode'=>'Barcode & PDF',
    'scan'=>'Scan Barcode',
    'anggota'=>'Anggota',
    'sirkulasi'=>'Sirkulasi',
    'riwayat'=>'Riwayat Transaksi',
    'notifikasi'=>'Notifikasi'
);

$icons = array(
    'dashboard'=>'▥',
    'koleksi'=>'▤',
    'barcode'=>'▥',
    'scan'=>'⌕',
    'anggota'=>'👤',
    'sirkulasi'=>'⇄',
    'riwayat'=>'◷',
    'notifikasi'=>'♧'
);

$late = "date(jatuh_tempo)<date('now','localtime')";

$BQ = "SELECT b.*,(SELECT COUNT(*) FROM item i WHERE i.biblio_id=b.id) tot,
(SELECT COUNT(*) FROM item i WHERE i.biblio_id=b.id AND NOT EXISTS(SELECT 1 FROM loan l WHERE l.item_id=i.id AND l.tgl_kembali IS NULL)) av,
(SELECT COUNT(*) FROM loan l JOIN item i ON i.id=l.item_id WHERE i.biblio_id=b.id) pop FROM biblio b";

// ===== COVER MANUAL =====
// Taruh gambar di assets/covers/ dengan nama <id>.jpg atau <judul-buku>.jpg
// (huruf kecil, spasi dan tanda baca jadi -). Ekstensi: jpg, jpeg, png, webp.
// Contoh: 6.jpg atau bumi-manusia.jpg. Daftar nama tiap buku: koleksiopac.php?cvnama
// Buku tanpa file memakai cover bawaan (kotak berwarna dengan inisial).

function cover_folders() {
    return array('assets/image/covers','assets/image','assets/covers','assets/images','covers');
}

function cover_slug($t) {
    $t = strtolower(trim((string)$t));
    return trim(preg_replace('/[^a-z0-9]+/', '-', $t), '-');
}

function cover_nama($title, $id) {
    $n = array((string)(int)$id);
    $s = cover_slug($title);

    if ($s !== '') {
        $n[] = $s;
    }

    return $n;
}

function cover_base() {
    $dir = str_replace('\\', '/', __DIR__);
    $root = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;

    if ($root !== false) {
        $root = rtrim(str_replace('\\', '/', $root), '/');

        if ($root !== '' && strpos($dir, $root) === 0) {
            return substr($dir, strlen($root));
        }
    }

    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';

    return rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
}

// alamat gambar cover manual kalau ada, kalau tidak ''
function cover_file($title, $id) {
    foreach (cover_nama($title, $id) as $nm) {
        foreach (cover_folders() as $fd) {
            foreach (array('jpg','jpeg','png','webp') as $x) {
                $f = __DIR__.'/'.$fd.'/'.$nm.'.'.$x;

                if (is_file($f)) {
                    return cover_base().'/'.$fd.'/'.$nm.'.'.$x.'?v='.filemtime($f);
                }
            }
        }
    }

    return '';
}

function cover($title, $id) {
    $img = cover_file($title, $id);

    if ($img !== '') {
        return "<div class='cv' role='img' aria-label='Cover ".h($title)."' style=\"background:#EFE2C4 url('".h($img)."') center/cover no-repeat\"></div>";
    }

    $colors = array(
        array('#e9a24f','#cf7c3d'),
        array('#a284c0','#6e4a8c'),
        array('#f0cf5f','#d9a93f'),
        array('#8fbccb','#4f8497'),
        array('#e89a9a','#c96b6b'),
        array('#9bbb6e','#6a9a45')
    );

    $c = $colors[$id%6];
    $words = preg_split('/\s+/',trim($title));
    $initial = strtoupper(substr($words[0],0,1).(isset($words[1]) ? substr($words[1],0,1) : substr($words[0],1,1)));

    return "<div class='cv' style='background:linear-gradient(145deg,{$c[0]},{$c[1]})'><b>$initial</b><small>LIBLOOM</small></div>";
}

function card($book) {
    return "<div class='bk'>".cover($book['judul'],$book['id'])."<div class='bi'><small class='kt'>".h(strtoupper($book['kategori']))."</small><h4>".h($book['judul'])."</h4><p>".h($book['pengarang'])."</p><div class='ft'><span class='".($book['av']>0?'ok':'no')."'>".($book['av']>0?'Tersedia':'Dipinjam')."</span><span>".h($book['callno'])."</span></div></div></div>";
}

// Data contoh hanya dimasukkan jika database benar-benar masih kosong.
if ((int)q('SELECT COUNT(*) c FROM biblio')->fetch()['c'] === 0) {
    $seed = array(
        array('Ensiklopedia Sains untuk Pelajar','Tim Redaksi','xii, 320 hlm; 26 cm','','503 TIM e','Tercetak','Indonesia','Referensi'),
        array('Kamus Besar Bahasa Indonesia','Badan Pengembangan dan Pembinaan Bahasa','xxxv, 1.791 hlm; 25 cm','','499.221 3 BAD k','Tercetak','Indonesia','Referensi'),
        array('Biologi untuk SMA/MA Kelas XI','Tim Kemendikbud','x, 280 hlm; 28 cm','Kurikulum Merdeka','570 TIM b','Tercetak','Indonesia','Buku Teks'),
        array('Matematika untuk SMA/MA Kelas X','Tim Kemendikbud','x, 260 hlm; 28 cm','Kurikulum Merdeka','510 TIM m','Tercetak','Indonesia','Buku Teks'),
        array('Laskar Pelangi','Andrea Hirata','xii, 529 hlm; 20 cm','Tetralogi Laskar Pelangi','813 HIR l','Tercetak','Indonesia','Fiksi'),
        array('Bumi Manusia','Pramoedya Ananta Toer','535 hlm; 21 cm','Tetralogi Buru','813 PRA b','Tercetak','Indonesia','Fiksi'),
        array('Sapiens: Riwayat Singkat Umat Manusia','Yuval Noah Harari','xiv, 528 hlm; 23 cm','','909 HAR s','Tercetak','Indonesia','Nonfiksi'),
        array('Atomic Habits','James Clear','352 hlm; 21 cm','','158.1 CLE a','Tercetak','Inggris','Nonfiksi')
    );

    foreach ($seed as $row) {
        q('INSERT INTO biblio(judul,pengarang,kolasi,series,callno,tipe,bahasa,kategori) VALUES(?,?,?,?,?,?,?,?)',$row);
        addItems((int)$GLOBALS['db']->lastInsertId(),1);
    }

    q("INSERT INTO member(nama,kelas,telp) VALUES('Budi Santoso','XI IPA 1','081234567890')");
    q('INSERT INTO loan(item_id,member_id,tgl_pinjam,jatuh_tempo) VALUES(1,1,?,?)',array(date('Y-m-d'),date('Y-m-d',strtotime('+7 days'))));
}