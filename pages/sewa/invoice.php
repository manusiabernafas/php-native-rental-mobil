<?php
$id = (int) ($_GET['id'] ?? 0);
$s = q("SELECT s.*, p.nama, p.nik, p.telepon, p.alamat, m.merk, m.model, m.plat_nomor, m.tahun, u.nama petugas
        FROM sewa s JOIN pelanggan p ON p.id = s.pelanggan_id JOIN mobil m ON m.id = s.mobil_id
        LEFT JOIN users u ON u.id = s.user_id WHERE s.id = ?", [$id])->fetch();
if (!$s) redirect('sewa');

$set = setting();
$sisa = $s['total'] - $s['dibayar'];
$title = 'Invoice ' . $s['kode'];
$actions = '<a href="' . url('sewa') . '" class="btn btn-light">Kembali</a>'
    . '<button onclick="window.print()" class="btn btn-primary">' . icon('printer', 16) . ' Cetak</button>';
require __DIR__ . '/../../includes/header.php';
?>
<div class="card invoice">
    <div class="invoice-head">
        <div>
            <h2><?= e($set['nama_usaha']) ?></h2>
            <div class="muted"><?= e($set['alamat']) ?><br>Telp. <?= e($set['telepon']) ?></div>
        </div>
        <div class="text-right">
            <h2>INVOICE</h2>
            <div><strong><?= e($s['kode']) ?></strong></div>
            <div class="muted"><?= tgl($s['created_at']) ?></div>
            <div style="margin-top:6px"><?= status_badge($s['status']) ?></div>
        </div>
    </div>

    <div class="invoice-meta">
        <div><small>Penyewa</small><strong><?= e($s['nama']) ?></strong><br><?= e($s['telepon']) ?><br><span class="muted"><?= e($s['alamat']) ?></span></div>
        <div><small>Kendaraan</small><strong><?= e($s['merk'] . ' ' . $s['model']) ?> (<?= e($s['tahun']) ?>)</strong><br><?= e($s['plat_nomor']) ?></div>
        <div><small>Periode Sewa</small><?= tgl($s['tgl_mulai']) ?> – <?= tgl($s['tgl_selesai']) ?></div>
        <div><small>Tanggal Dikembalikan</small><?= tgl($s['tgl_kembali']) ?></div>
    </div>

    <table>
        <thead><tr><th>Keterangan</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            <tr><td>Sewa mobil <?= $s['lama_hari'] ?> hari × <?= rupiah($s['harga_per_hari']) ?></td><td class="text-right"><?= rupiah($s['lama_hari'] * $s['harga_per_hari']) ?></td></tr>
            <?php if ($s['pakai_sopir']): ?>
                <tr><td>Jasa sopir <?= $s['lama_hari'] ?> hari</td><td class="text-right"><?= rupiah($s['biaya_sopir']) ?></td></tr>
            <?php endif; ?>
            <?php if ($s['denda'] > 0): ?>
                <tr><td>Denda keterlambatan</td><td class="text-right"><?= rupiah($s['denda']) ?></td></tr>
            <?php endif; ?>
            <tr><td><strong>Total</strong></td><td class="text-right"><strong><?= rupiah($s['total']) ?></strong></td></tr>
            <tr><td>Dibayar</td><td class="text-right"><?= rupiah($s['dibayar']) ?></td></tr>
            <tr><td><strong><?= $sisa > 0 ? 'Sisa Tagihan' : 'Status Pembayaran' ?></strong></td>
                <td class="text-right"><strong><?= $sisa > 0 ? rupiah($sisa) : 'LUNAS' ?></strong></td></tr>
        </tbody>
    </table>

    <?php if ($s['catatan']): ?><p class="muted" style="white-space:pre-line;margin-top:20px">Catatan: <?= e($s['catatan']) ?></p><?php endif; ?>
    <p class="muted" style="margin-top:30px">Petugas: <?= e($s['petugas'] ?? '-') ?>. Terima kasih telah menggunakan layanan kami.</p>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
