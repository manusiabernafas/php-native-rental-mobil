<?php
$id = (int) ($_GET['id'] ?? 0);
$s = q("SELECT s.*, p.nama, m.merk, m.model, m.plat_nomor FROM sewa s
        JOIN pelanggan p ON p.id = s.pelanggan_id JOIN mobil m ON m.id = s.mobil_id
        WHERE s.id = ? AND s.status = 'berjalan'", [$id])->fetch();
if (!$s) {
    flash('Transaksi tidak ditemukan atau sudah selesai.', 'error');
    redirect('sewa');
}

$tglKembali = $_POST['tgl_kembali'] ?? date('Y-m-d');
if ($tglKembali < $s['tgl_mulai']) $tglKembali = $s['tgl_mulai'];
[$telat, $denda] = hitung_denda($s['tgl_selesai'], $tglKembali, (int) $s['harga_per_hari']);
$totalAkhir = $s['subtotal'] + $denda;
$sisa = $totalAkhir - $s['dibayar'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi'])) {
    $bayar = max(0, (int) ($_POST['bayar'] ?? 0));
    $kondisi = $_POST['kondisi'] === 'servis' ? 'servis' : 'tersedia';

    db()->beginTransaction();
    q("UPDATE sewa SET tgl_kembali = ?, denda = ?, total = ?, dibayar = LEAST(dibayar + ?, ?), status = 'selesai',
       catatan = CONCAT(COALESCE(catatan,''), ?) WHERE id = ?", [
        $tglKembali, $denda, $totalAkhir, $bayar, $totalAkhir,
        trim($_POST['catatan'] ?? '') !== '' ? "\n[Pengembalian] " . trim($_POST['catatan']) : '', $id,
    ]);
    q('UPDATE mobil SET status = ? WHERE id = ?', [$kondisi, $s['mobil_id']]);
    db()->commit();

    flash("Mobil {$s['plat_nomor']} sudah dikembalikan.");
    redirect('sewa.invoice', ['id' => $id]);
}

$title = 'Pengembalian Mobil';
require __DIR__ . '/../../includes/header.php';
?>
<div class="grid-2">
    <div class="card">
        <div class="card-head"><h2><?= e($s['kode']) ?> · <?= e($s['nama']) ?></h2></div>
        <div class="card-body">
            <div class="invoice-meta">
                <div><small>Mobil</small><strong><?= e($s['merk'] . ' ' . $s['model']) ?></strong> · <?= e($s['plat_nomor']) ?></div>
                <div><small>Periode sewa</small><?= tgl($s['tgl_mulai']) ?> – <?= tgl($s['tgl_selesai']) ?> (<?= $s['lama_hari'] ?> hari)</div>
            </div>
            <form method="post" class="form-grid">
                <?= csrf_field() ?>
                <div><label>Tanggal Dikembalikan</label>
                    <input type="date" name="tgl_kembali" value="<?= e($tglKembali) ?>" onchange="this.form.submit()">
                </div>
                <div><label>Kondisi Mobil</label>
                    <select name="kondisi">
                        <option value="tersedia">Baik, siap disewa lagi</option>
                        <option value="servis">Perlu servis / perbaikan</option>
                    </select>
                </div>
                <div class="full"><label>Pembayaran Saat Ini (Rp)</label>
                    <input type="number" name="bayar" min="0" step="1000" value="<?= max(0, $sisa) ?>">
                </div>
                <div class="full"><label>Catatan Pengembalian</label>
                    <textarea name="catatan" rows="2" placeholder="Kondisi bodi, BBM, kilometer, dll."></textarea>
                </div>
                <div class="full form-actions">
                    <button class="btn btn-primary" name="konfirmasi" value="1"><?= icon('undo', 16) ?> Konfirmasi Pengembalian</button>
                    <a href="<?= url('sewa') ?>" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
    <div class="card" style="align-self:start">
        <div class="card-head"><h2>Rincian Tagihan</h2></div>
        <div class="card-body summary">
            <div><span class="muted">Biaya sewa + sopir</span><span><?= rupiah($s['subtotal']) ?></span></div>
            <div><span class="muted">Terlambat</span><span><?= $telat ?> hari</span></div>
            <div><span class="muted">Denda (<?= (int) setting()['denda_per_hari_persen'] ?>%/hari)</span><span><?= rupiah($denda) ?></span></div>
            <div><span class="muted">Sudah dibayar</span><span>- <?= rupiah($s['dibayar']) ?></span></div>
            <div class="total"><span>Sisa tagihan</span><span><?= rupiah(max(0, $sisa)) ?></span></div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
