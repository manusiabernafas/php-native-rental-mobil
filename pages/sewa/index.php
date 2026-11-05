<?php
$title = 'Transaksi Sewa';
$actions = '<a href="' . url('sewa.baru') . '" class="btn btn-primary">' . icon('plus', 16) . ' Sewa Baru</a>';

$status = $_GET['status'] ?? '';
$cari = trim($_GET['cari'] ?? '');
$pelangganId = (int) ($_GET['pelanggan'] ?? 0);

$where = ['1=1'];
$params = [];
if (in_array($status, ['berjalan', 'selesai', 'batal'], true)) {
    $where[] = 's.status = ?';
    $params[] = $status;
}
if ($cari !== '') {
    $where[] = '(s.kode LIKE ? OR p.nama LIKE ? OR m.plat_nomor LIKE ?)';
    array_push($params, "%$cari%", "%$cari%", "%$cari%");
}
if ($pelangganId) {
    $where[] = 's.pelanggan_id = ?';
    $params[] = $pelangganId;
}
$sewa = q('SELECT s.*, p.nama, p.telepon, m.merk, m.model, m.plat_nomor FROM sewa s
           JOIN pelanggan p ON p.id = s.pelanggan_id JOIN mobil m ON m.id = s.mobil_id
           WHERE ' . implode(' AND ', $where) . ' ORDER BY s.id DESC', $params)->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>
<div class="card">
    <div class="card-head">
        <h2><?= count($sewa) ?> transaksi</h2>
        <form class="filters" method="get">
            <input type="hidden" name="page" value="sewa">
            <input type="search" name="cari" placeholder="Kode, pelanggan, plat..." value="<?= e($cari) ?>">
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <?php foreach (['berjalan', 'selesai', 'batal'] as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-light"><?= icon('search', 16) ?></button>
        </form>
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>Kode</th><th>Pelanggan</th><th>Mobil</th><th>Periode</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($sewa as $s):
            $telat = $s['status'] === 'berjalan' && $s['tgl_selesai'] < date('Y-m-d'); ?>
            <tr>
                <td><strong><?= e($s['kode']) ?></strong><small><?= tgl($s['created_at']) ?></small></td>
                <td><strong><?= e($s['nama']) ?></strong><small><?= e($s['telepon']) ?></small></td>
                <td><?= e($s['merk'] . ' ' . $s['model']) ?><br><small><?= e($s['plat_nomor']) ?><?= $s['pakai_sopir'] ? ' · dengan sopir' : '' ?></small></td>
                <td><?= tgl($s['tgl_mulai']) ?> – <?= tgl($s['tgl_selesai']) ?><br><small><?= (int) $s['lama_hari'] ?> hari</small></td>
                <td><strong><?= rupiah($s['total']) ?></strong>
                    <?php if ($s['dibayar'] < $s['total'] && $s['status'] !== 'batal'): ?><small>Kurang <?= rupiah($s['total'] - $s['dibayar']) ?></small><?php endif; ?>
                </td>
                <td><?= status_badge($telat ? 'terlambat' : $s['status']) ?></td>
                <td>
                    <div class="actions">
                        <?php if ($s['status'] === 'berjalan'): ?>
                            <a class="btn btn-light btn-sm" href="<?= url('sewa.kembali', ['id' => $s['id']]) ?>"><?= icon('undo', 14) ?> Kembali</a>
                        <?php endif; ?>
                        <a class="icon-btn" title="Invoice" href="<?= url('sewa.invoice', ['id' => $s['id']]) ?>"><?= icon('printer', 16) ?></a>
                        <?php if ($s['status'] === 'berjalan'): ?>
                            <form method="post" action="<?= url('sewa.batal') ?>" data-confirm="Batalkan transaksi <?= e($s['kode']) ?>?">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button class="icon-btn danger" title="Batalkan"><?= icon('trash', 16) ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$sewa): ?><tr><td colspan="7" class="empty">Belum ada transaksi.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
