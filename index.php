<?php
require __DIR__ . '/includes/bootstrap.php';

$routes = [
    'login'          => 'pages/login.php',
    'logout'         => 'pages/logout.php',
    'dashboard'      => 'pages/dashboard.php',
    'mobil'          => 'pages/mobil/index.php',
    'mobil.form'     => 'pages/mobil/form.php',
    'mobil.hapus'    => 'pages/mobil/hapus.php',
    'pelanggan'      => 'pages/pelanggan/index.php',
    'pelanggan.form' => 'pages/pelanggan/form.php',
    'pelanggan.hapus'=> 'pages/pelanggan/hapus.php',
    'sewa'           => 'pages/sewa/index.php',
    'sewa.baru'      => 'pages/sewa/baru.php',
    'sewa.kembali'   => 'pages/sewa/kembali.php',
    'sewa.batal'     => 'pages/sewa/batal.php',
    'sewa.invoice'   => 'pages/sewa/invoice.php',
    'laporan'        => 'pages/laporan.php',
    'pengaturan'     => 'pages/pengaturan.php',
];

$page = $_GET['page'] ?? 'dashboard';
if (!isset($routes[$page])) {
    http_response_code(404);
    $page = 'dashboard';
}
if ($page !== 'login') require_login();
verify_csrf();

require __DIR__ . '/' . $routes[$page];
