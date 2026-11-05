<?php
$id = (int) ($_POST['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('sewa');

$s = q("SELECT * FROM sewa WHERE id = ? AND status = 'berjalan'", [$id])->fetch();
if ($s) {
    db()->beginTransaction();
    q("UPDATE sewa SET status = 'batal' WHERE id = ?", [$id]);
    q("UPDATE mobil SET status = 'tersedia' WHERE id = ?", [$s['mobil_id']]);
    db()->commit();
    flash("Transaksi {$s['kode']} dibatalkan.");
}
redirect('sewa');
