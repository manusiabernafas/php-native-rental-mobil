<?php
$id = (int) ($_POST['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('mobil');

if (q('SELECT COUNT(*) FROM sewa WHERE mobil_id = ?', [$id])->fetchColumn() > 0) {
    flash('Mobil ini punya riwayat sewa sehingga tidak bisa dihapus. Ubah statusnya saja.', 'error');
} else {
    $foto = q('SELECT foto FROM mobil WHERE id = ?', [$id])->fetchColumn();
    q('DELETE FROM mobil WHERE id = ?', [$id]);
    if ($foto) @unlink(__DIR__ . '/../../assets/uploads/' . $foto);
    flash('Mobil dihapus.');
}
redirect('mobil');
