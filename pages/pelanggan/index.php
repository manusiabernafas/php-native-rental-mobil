<?php
$title = 'Pelanggan';
$actions = '<a href="' . url('pelanggan.form') . '" class="btn btn-primary">' . icon('plus', 16) . ' Tambah Pelanggan</a>';

$cari = trim($_GET['cari'] ?? '');
$params = [];
$sql = "SELECT p.*, COUNT(s.id) jml_sewa, COALESCE(SUM(CASE WHEN s.status='selesai' THEN s.total END),0) total_belanja
        FROM pelanggan p LEFT JOIN sewa s ON s.pelanggan_id = p.id";
if ($cari !== '') {
    $sql .= ' WHERE p.nama LIKE ? OR p.nik LIKE ? OR p.telepon LIKE ?';
    $params = ["%$cari%", "%$cari%", "%$cari%"];
}
$pelanggan = q($sql . ' GROUP BY p.id ORDER BY p.nama', $params)->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>
<div class="card">
    <div class="card-head">
        <h2><?= count($pelanggan) ?> pelanggan</h2>
        <form class="filters" method="get">
            <input type="hidden" name="page" value="pelanggan">
            <input type="search" name="cari" placeholder="Cari nama, NIK, telepon..." value="<?= e($cari) ?>">
            <button class="btn btn-light"><?= icon('search', 16) ?></button>
        </form>
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>Nama</th><th>NIK</th><th>Telepon</th><th>No. SIM</th><th>Riwayat</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($pelanggan as $p): ?>
            <tr>
                <td><strong><?= e($p['nama']) ?></strong><small><?= e($p['alamat']) ?></small></td>
                <td><?= e($p['nik']) ?></td>
                <td><?= e($p['telepon']) ?></td>
                <td><?= e($p['no_sim'] ?: '-') ?></td>
                <td><strong><?= (int) $p['jml_sewa'] ?>x sewa</strong><small><?= rupiah($p['total_belanja']) ?></small></td>
                <td>
                    <div class="actions">
                        <a class="icon-btn" title="Riwayat sewa" href="<?= url('sewa', ['pelanggan' => $p['id']]) ?>"><?= icon('eye', 16) ?></a>
                        <a class="icon-btn" title="Ubah" href="<?= url('pelanggan.form', ['id' => $p['id']]) ?>"><?= icon('edit', 16) ?></a>
                        <form method="post" action="<?= url('pelanggan.hapus') ?>" data-confirm="Hapus pelanggan <?= e($p['nama']) ?>?">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button class="icon-btn danger" title="Hapus"><?= icon('trash', 16) ?></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$pelanggan): ?><tr><td colspan="6" class="empty">Belum ada pelanggan.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
