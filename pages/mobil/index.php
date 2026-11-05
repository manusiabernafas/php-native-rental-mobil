<?php
$title = 'Data Mobil';
$actions = '<a href="' . url('mobil.form') . '" class="btn btn-primary">' . icon('plus', 16) . ' Tambah Mobil</a>';

$cari = trim($_GET['cari'] ?? '');
$status = $_GET['status'] ?? '';
$where = ['1=1'];
$params = [];
if ($cari !== '') {
    $where[] = '(merk LIKE ? OR model LIKE ? OR plat_nomor LIKE ?)';
    array_push($params, "%$cari%", "%$cari%", "%$cari%");
}
if (in_array($status, ['tersedia', 'disewa', 'servis'], true)) {
    $where[] = 'status = ?';
    $params[] = $status;
}
$mobil = q('SELECT * FROM mobil WHERE ' . implode(' AND ', $where) . ' ORDER BY merk, model', $params)->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>
<div class="card">
    <div class="card-head">
        <h2><?= count($mobil) ?> mobil</h2>
        <form class="filters" method="get">
            <input type="hidden" name="page" value="mobil">
            <input type="search" name="cari" placeholder="Cari merk, model, plat..." value="<?= e($cari) ?>">
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <?php foreach (['tersedia', 'disewa', 'servis'] as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-light"><?= icon('search', 16) ?></button>
        </form>
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>Mobil</th><th>Plat Nomor</th><th>Spesifikasi</th><th>Harga / Hari</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($mobil as $m): ?>
            <tr>
                <td>
                    <div style="display:flex;gap:12px;align-items:center">
                        <?php if ($m['foto']): ?>
                            <img class="car-thumb" src="assets/uploads/<?= e($m['foto']) ?>" alt="">
                        <?php else: ?>
                            <span class="car-thumb"><?= icon('car', 20) ?></span>
                        <?php endif; ?>
                        <div><strong><?= e($m['merk'] . ' ' . $m['model']) ?></strong><small><?= e($m['tahun']) ?> · <?= e($m['warna']) ?></small></div>
                    </div>
                </td>
                <td><?= e($m['plat_nomor']) ?></td>
                <td><?= (int) $m['kursi'] ?> kursi · <?= e($m['transmisi']) ?> · <?= e($m['bahan_bakar']) ?></td>
                <td><strong><?= rupiah($m['harga_per_hari']) ?></strong></td>
                <td><?= status_badge($m['status']) ?></td>
                <td>
                    <div class="actions">
                        <a class="icon-btn" title="Ubah" href="<?= url('mobil.form', ['id' => $m['id']]) ?>"><?= icon('edit', 16) ?></a>
                        <form method="post" action="<?= url('mobil.hapus') ?>" data-confirm="Hapus mobil <?= e($m['plat_nomor']) ?>?">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button class="icon-btn danger" title="Hapus"><?= icon('trash', 16) ?></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$mobil): ?><tr><td colspan="6" class="empty">Belum ada data mobil.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
