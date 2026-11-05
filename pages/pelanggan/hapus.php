<?php
$id = (int) ($_POST['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('pelanggan');

if (q('SELECT COUNT(*) FROM sewa WHERE pelanggan_id = ?', [$id])->fetchColumn() > 0) {
    flash('Pelanggan ini punya riwayat sewa sehingga tidak bisa dihapus.', 'error');
} else {
    q('DELETE FROM pelanggan WHERE id = ?', [$id]);
    flash('Pelanggan dihapus.');
}
redirect('pelanggan');
