<?php
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$rows = q("SELECT s.*, p.nama, m.merk, m.model, m.plat_nomor FROM sewa s
           JOIN pelanggan p ON p.id = s.pelanggan_id JOIN mobil m ON m.id = s.mobil_id
           WHERE s.status = 'selesai' AND s.tgl_kembali BETWEEN ? AND ? ORDER BY s.tgl_kembali", [$dari, $sampai])->fetchAll();

$totalPendapatan = array_sum(array_column($rows, 'total'));
$totalDenda = array_sum(array_column($rows, 'denda'));
$totalSopir = array_sum(array_column($rows, 'biaya_sopir'));
$piutang = array_sum(array_map(fn ($r) => $r['total'] - $r['dibayar'], $rows));

$perMobil = q("SELECT m.merk, m.model, m.plat_nomor, COUNT(s.id) n, SUM(s.lama_hari) hari, SUM(s.total) t
               FROM sewa s JOIN mobil m ON m.id = s.mobil_id
               WHERE s.status = 'selesai' AND s.tgl_kembali BETWEEN ? AND ?
               GROUP BY m.id ORDER BY t DESC", [$dari, $sampai])->fetchAll();

// Ekspor CSV (bisa dibuka di Excel)
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan-rental-' . $dari . '-sd-' . $sampai . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Kode', 'Pelanggan', 'Mobil', 'Plat', 'Mulai', 'Kembali', 'Hari', 'Sopir', 'Denda', 'Total', 'Dibayar'], ';', '"', '');
    foreach ($rows as $r) {
        fputcsv($out, [$r['kode'], $r['nama'], $r['merk'] . ' ' . $r['model'], $r['plat_nomor'], $r['tgl_mulai'], $r['tgl_kembali'],
            $r['lama_hari'], $r['biaya_sopir'], $r['denda'], $r['total'], $r['dibayar']], ';', '"', '');
    }
    exit;
}

$title = 'Laporan Pendapatan';
$actions = '<a class="btn btn-light" href="' . url('laporan', ['dari' => $dari, 'sampai' => $sampai, 'export' => 'csv']) . '">Ekspor CSV</a>'
    . '<button onclick="window.print()" class="btn btn-primary">' . icon('printer', 16) . ' Cetak</button>';
require __DIR__ . '/../includes/header.php';
?>
<form class="card no-print" method="get" style="margin-bottom:20px">
    <div class="card-body filters" style="align-items:end">
        <input type="hidden" name="page" value="laporan">
        <div><label>Dari tanggal</label><input type="date" name="dari" value="<?= e($dari) ?>"></div>
        <div><label>Sampai tanggal</label><input type="date" name="sampai" value="<?= e($sampai) ?>"></div>
        <button class="btn btn-primary">Tampilkan</button>
    </div>
</form>

<div class="stats">
    <div class="card stat"><div class="stat-icon green"><?= icon('wallet', 22) ?></div><div><small>Total Pendapatan</small><strong><?= rupiah($totalPendapatan) ?></strong></div></div>
    <div class="card stat"><div class="stat-icon blue"><?= icon('key', 22) ?></div><div><small>Transaksi Selesai</small><strong><?= count($rows) ?></strong></div></div>
    <div class="card stat"><div class="stat-icon amber"><?= icon('alert', 22) ?></div><div><small>Pendapatan Denda</small><strong><?= rupiah($totalDenda) ?></strong></div></div>
    <div class="card stat"><div class="stat-icon red"><?= icon('wallet', 22) ?></div><div><small>Belum Terbayar</small><strong><?= rupiah($piutang) ?></strong></div></div>
</div>

<div class="card">
    <div class="card-head"><h2>Performa per Mobil</h2><span class="muted"><?= tgl($dari) ?> – <?= tgl($sampai) ?></span></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Mobil</th><th>Transaksi</th><th>Hari Disewa</th><th class="text-right">Pendapatan</th></tr></thead>
        <tbody>
        <?php foreach ($perMobil as $m): ?>
            <tr><td><strong><?= e($m['merk'] . ' ' . $m['model']) ?></strong><small><?= e($m['plat_nomor']) ?></small></td>
                <td><?= (int) $m['n'] ?>x</td><td><?= (int) $m['hari'] ?> hari</td><td class="text-right"><strong><?= rupiah($m['t']) ?></strong></td></tr>
        <?php endforeach; ?>
        <?php if (!$perMobil): ?><tr><td colspan="4" class="empty">Tidak ada transaksi selesai di periode ini.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>

<div class="card mt">
    <div class="card-head"><h2>Rincian Transaksi</h2><span class="muted">Termasuk biaya sopir <?= rupiah($totalSopir) ?></span></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Kode</th><th>Pelanggan</th><th>Mobil</th><th>Dikembalikan</th><th>Denda</th><th class="text-right">Total</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr><td><?= e($r['kode']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['merk'] . ' ' . $r['model']) ?></td>
                <td><?= tgl($r['tgl_kembali']) ?></td><td><?= $r['denda'] ? rupiah($r['denda']) : '-' ?></td><td class="text-right"><?= rupiah($r['total']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6" class="empty">Tidak ada data.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
