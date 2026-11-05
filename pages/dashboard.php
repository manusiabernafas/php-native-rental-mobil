<?php
$title = 'Dashboard';
$actions = '<a href="' . url('sewa.baru') . '" class="btn btn-primary">' . icon('plus', 16) . ' Sewa Baru</a>';

$armada = q("SELECT status, COUNT(*) n FROM mobil GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$berjalan = (int) q("SELECT COUNT(*) FROM sewa WHERE status = 'berjalan'")->fetchColumn();
$terlambat = q("SELECT s.*, p.nama, p.telepon, m.merk, m.model, m.plat_nomor
                FROM sewa s JOIN pelanggan p ON p.id = s.pelanggan_id JOIN mobil m ON m.id = s.mobil_id
                WHERE s.status = 'berjalan' AND s.tgl_selesai < CURDATE() ORDER BY s.tgl_selesai")->fetchAll();
$pendapatanBulan = (int) q("SELECT COALESCE(SUM(total),0) FROM sewa WHERE status = 'selesai'
                            AND DATE_FORMAT(tgl_kembali, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')")->fetchColumn();

// Pendapatan 6 bulan terakhir
$grafik = [];
for ($i = 5; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("first day of -$i month"));
    $grafik[$ym] = 0;
}
$rows = q("SELECT DATE_FORMAT(tgl_kembali, '%Y-%m') ym, SUM(total) t FROM sewa
           WHERE status = 'selesai' AND tgl_kembali >= ? GROUP BY ym", [array_key_first($grafik) . '-01'])->fetchAll();
foreach ($rows as $r) if (isset($grafik[$r['ym']])) $grafik[$r['ym']] = (int) $r['t'];
$max = max(1, max($grafik));
$namaBulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

$aktif = q("SELECT s.*, p.nama, m.merk, m.model, m.plat_nomor FROM sewa s
            JOIN pelanggan p ON p.id = s.pelanggan_id JOIN mobil m ON m.id = s.mobil_id
            WHERE s.status = 'berjalan' ORDER BY s.tgl_selesai LIMIT 6")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="stats">
    <div class="card stat">
        <div class="stat-icon green"><?= icon('car', 22) ?></div>
        <div><small>Mobil Tersedia</small><strong><?= (int) ($armada['tersedia'] ?? 0) ?></strong> <span class="muted">/ <?= array_sum($armada) ?></span></div>
    </div>
    <div class="card stat">
        <div class="stat-icon blue"><?= icon('key', 22) ?></div>
        <div><small>Sewa Berjalan</small><strong><?= $berjalan ?></strong></div>
    </div>
    <div class="card stat">
        <div class="stat-icon red"><?= icon('alert', 22) ?></div>
        <div><small>Terlambat Kembali</small><strong><?= count($terlambat) ?></strong></div>
    </div>
    <div class="card stat">
        <div class="stat-icon amber"><?= icon('wallet', 22) ?></div>
        <div><small>Pendapatan Bulan Ini</small><strong><?= rupiah($pendapatanBulan) ?></strong></div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-head"><h2>Pendapatan 6 Bulan Terakhir</h2></div>
        <div class="card-body">
            <div class="bars">
                <?php foreach ($grafik as $ym => $v): ?>
                    <div class="bar-col" title="<?= rupiah($v) ?>">
                        <small><?= $v ? number_format($v / 1000000, 1, ',', '.') . 'jt' : '0' ?></small>
                        <div class="bar" style="height: <?= round($v / $max * 100) ?>%"></div>
                        <small><?= $namaBulan[(int) substr($ym, 5)] ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head"><h2>Status Armada</h2></div>
        <div class="card-body summary">
            <div><span><?= status_badge('tersedia') ?></span><strong><?= (int) ($armada['tersedia'] ?? 0) ?> unit</strong></div>
            <div><span><?= status_badge('disewa') ?></span><strong><?= (int) ($armada['disewa'] ?? 0) ?> unit</strong></div>
            <div><span><?= status_badge('servis') ?></span><strong><?= (int) ($armada['servis'] ?? 0) ?> unit</strong></div>
            <div class="total"><span>Total armada</span><span><?= array_sum($armada) ?> unit</span></div>
        </div>
    </div>
</div>

<?php if ($terlambat): ?>
<div class="card mt">
    <div class="card-head"><h2>Perlu Ditindaklanjuti: Terlambat Kembali</h2></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Kode</th><th>Pelanggan</th><th>Mobil</th><th>Jatuh Tempo</th><th>Telat</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($terlambat as $s): [$telat] = hitung_denda($s['tgl_selesai'], date('Y-m-d'), 0); ?>
            <tr>
                <td><strong><?= e($s['kode']) ?></strong></td>
                <td><strong><?= e($s['nama']) ?></strong><small><?= e($s['telepon']) ?></small></td>
                <td><?= e($s['merk'] . ' ' . $s['model']) ?><br><small><?= e($s['plat_nomor']) ?></small></td>
                <td><?= tgl($s['tgl_selesai']) ?></td>
                <td><?= status_badge('terlambat') ?> <small><?= $telat ?> hari</small></td>
                <td class="text-right"><a class="btn btn-light btn-sm" href="<?= url('sewa.kembali', ['id' => $s['id']]) ?>"><?= icon('undo', 14) ?> Proses Kembali</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<div class="card mt">
    <div class="card-head"><h2>Sewa Sedang Berjalan</h2><a href="<?= url('sewa', ['status' => 'berjalan']) ?>">Lihat semua</a></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Kode</th><th>Pelanggan</th><th>Mobil</th><th>Periode</th><th>Sisa Tagihan</th></tr></thead>
        <tbody>
        <?php foreach ($aktif as $s): ?>
            <tr>
                <td><a href="<?= url('sewa.invoice', ['id' => $s['id']]) ?>"><strong><?= e($s['kode']) ?></strong></a></td>
                <td><?= e($s['nama']) ?></td>
                <td><?= e($s['merk'] . ' ' . $s['model']) ?> <small>· <?= e($s['plat_nomor']) ?></small></td>
                <td><?= tgl($s['tgl_mulai']) ?> – <?= tgl($s['tgl_selesai']) ?></td>
                <td><?= $s['total'] - $s['dibayar'] > 0 ? rupiah($s['total'] - $s['dibayar']) : '<span class="badge badge-green">Lunas</span>' ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$aktif): ?><tr><td colspan="5" class="empty">Tidak ada sewa yang sedang berjalan.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
