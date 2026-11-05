<?php
$menu = [
    ['dashboard', 'Dashboard', 'home'],
    ['sewa', 'Transaksi Sewa', 'key'],
    ['mobil', 'Data Mobil', 'car'],
    ['pelanggan', 'Pelanggan', 'users'],
    ['laporan', 'Laporan', 'chart'],
];
if (user()['role'] === 'admin') $menu[] = ['pengaturan', 'Pengaturan', 'settings'];
$current = explode('.', $_GET['page'] ?? 'dashboard')[0];
$flash = flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Dashboard') ?> · <?= e(setting()['nama_usaha'] ?? 'Rental Mobil') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <span class="brand-mark"><?= icon('car', 20) ?></span>
            <div>
                <strong><?= e(setting()['nama_usaha'] ?? 'Rental Mobil') ?></strong>
                <small>Sistem Rental Mobil</small>
            </div>
        </div>
        <nav>
            <?php foreach ($menu as [$key, $label, $ic]): ?>
                <a href="<?= url($key) ?>" class="<?= $current === $key ? 'active' : '' ?>"><?= icon($ic) ?> <?= $label ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-user">
            <div class="avatar"><?= e(strtoupper(substr(user()['nama'], 0, 1))) ?></div>
            <div class="grow">
                <strong><?= e(user()['nama']) ?></strong>
                <small><?= e(ucfirst(user()['role'])) ?></small>
            </div>
            <a href="<?= url('logout') ?>" title="Keluar" class="icon-btn"><?= icon('logout') ?></a>
        </div>
    </aside>
    <main class="main">
        <header class="topbar">
            <button class="icon-btn menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><?= icon('menu') ?></button>
            <h1><?= e($title ?? 'Dashboard') ?></h1>
            <div class="topbar-actions"><?= $actions ?? '' ?></div>
        </header>
        <div class="content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
            <?php endif; ?>
