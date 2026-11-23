<?php
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';

$adm = isLoggedIn();
$act = $_GET['act'] ?? '';
$page = $_GET['page'] ?? 'home';

require __DIR__ . '/actions.php';

$routes = [
    'home'       => 'opac',
    'login'      => 'login',
    'dashboard'  => 'dashboard',
    'koleksi'    => 'koleksi',
    'katalog'    => 'katalog',
    'barcode'    => 'barcode',
    'scan'       => 'scan',
    'anggota'    => 'anggota',
    'sirkulasi'  => 'sirkulasi',
    'riwayat'    => 'riwayat',
    'notifikasi' => 'notifikasi',
    'admin'      => 'admin'
];

if (!isset($routes[$page])) {
    go('index.php');
}

$adminPages = [
    'dashboard',
    'koleksi',
    'katalog',
    'barcode',
    'scan',
    'anggota',
    'sirkulasi',
    'riwayat',
    'notifikasi',
    'admin'
];

if (in_array($page, $adminPages, true) && !$adm) {
    go('index.php?page=login');
}

$fl = '';

if (!empty($_SESSION['f'])) {
    $fl = "<div class='msg'>" . h($_SESSION['f']) . "</div>";
    unset($_SESSION['f']);
}

require __DIR__ . '/pages/' . $routes[$page] . '.php';