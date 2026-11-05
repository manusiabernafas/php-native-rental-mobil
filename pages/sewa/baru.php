<?php
$mobilList = q("SELECT * FROM mobil WHERE status = 'tersedia' ORDER BY merk, model")->fetchAll();
$pelangganList = q('SELECT id, nama, nik FROM pelanggan ORDER BY nama')->fetchAll();
$biayaSopir = (int) (setting()['biaya_sopir_per_hari'] ?? 0);

$data = ['pelanggan_id' => '', 'mobil_id' => $_GET['mobil'] ?? '', 'tgl_mulai' => date('Y-m-d'),
    'tgl_selesai' => date('Y-m-d', strtotime('+1 day')), 'pakai_sopir' => 0, 'dibayar' => '', 'catatan' => ''];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $k => $_) $data[$k] = trim($_POST[$k] ?? '');
    $data['pakai_sopir'] = isset($_POST['pakai_sopir']) ? 1 : 0;

    $lama = (int) round((strtotime($data['tgl_selesai']) - strtotime($data['tgl_mulai'])) / 86400);

    try {
        db()->beginTransaction();
        // Kunci baris mobil agar tidak bisa disewa dua kali bersamaan
        $mobil = q("SELECT * FROM mobil WHERE id = ? FOR UPDATE", [(int) $data['mobil_id']])->fetch();

        if (!$data['pelanggan_id'] || !$mobil) {
            throw new RuntimeException('Pilih pelanggan dan mobil.');
        }
        if ($mobil['status'] !== 'tersedia') {
            throw new RuntimeException('Mobil sudah tidak tersedia.');
        }
        if ($lama < 1) {
            throw new RuntimeException('Tanggal selesai minimal 1 hari setelah tanggal mulai.');
        }

        $subtotalSewa = $lama * (int) $mobil['harga_per_hari'];
        $totalSopir = $data['pakai_sopir'] ? $lama * $biayaSopir : 0;
        $total = $subtotalSewa + $totalSopir;
        $dibayar = min(max(0, (int) $data['dibayar']), $total);

        $last = (int) q("SELECT COALESCE(MAX(id),0) FROM sewa")->fetchColumn();
        $kode = 'RM-' . str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);

        q('INSERT INTO sewa (kode, pelanggan_id, mobil_id, user_id, tgl_mulai, tgl_selesai, lama_hari, harga_per_hari,
             pakai_sopir, biaya_sopir, subtotal, total, dibayar, catatan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            $kode, (int) $data['pelanggan_id'], $mobil['id'], user()['id'], $data['tgl_mulai'], $data['tgl_selesai'], $lama,
            $mobil['harga_per_hari'], $data['pakai_sopir'], $totalSopir, $total, $total, $dibayar, $data['catatan'],
        ]);
        $newId = (int) db()->lastInsertId();
        q("UPDATE mobil SET status = 'disewa' WHERE id = ?", [$mobil['id']]);
        db()->commit();

        flash("Transaksi $kode berhasil dibuat.");
        redirect('sewa.invoice', ['id' => $newId]);
    } catch (RuntimeException $ex) {
        db()->rollBack();
        $error = $ex->getMessage();
    }
}

$title = 'Sewa Baru';
require __DIR__ . '/../../includes/header.php';
?>
<form method="post" id="form-sewa" data-biaya-sopir="<?= $biayaSopir ?>">
    <?= csrf_field() ?>
    <div class="grid-2">
        <div class="card">
            <div class="card-head"><h2>Detail Penyewaan</h2></div>
            <div class="card-body">
                <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
                <?php if (!$mobilList): ?><div class="alert alert-error">Tidak ada mobil yang tersedia saat ini.</div><?php endif; ?>
                <div class="form-grid">
                    <div class="full"><label>Pelanggan</label>
                        <select name="pelanggan_id" required>
                            <option value="">Pilih pelanggan</option>
                            <?php foreach ($pelangganList as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $data['pelanggan_id'] == $p['id'] ? 'selected' : '' ?>><?= e($p['nama']) ?> · <?= e($p['nik']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="muted">Pelanggan baru? <a href="<?= url('pelanggan.form') ?>">Daftarkan dulu</a></small>
                    </div>
                    <div class="full"><label>Mobil</label>
                        <select name="mobil_id" required>
                            <option value="" data-harga="0">Pilih mobil yang tersedia</option>
                            <?php foreach ($mobilList as $m): ?>
                                <option value="<?= $m['id'] ?>" data-harga="<?= $m['harga_per_hari'] ?>" <?= $data['mobil_id'] == $m['id'] ? 'selected' : '' ?>>
                                    <?= e($m['merk'] . ' ' . $m['model']) ?> · <?= e($m['plat_nomor']) ?> · <?= rupiah($m['harga_per_hari']) ?>/hari
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label>Tanggal Ambil</label><input type="date" name="tgl_mulai" value="<?= e($data['tgl_mulai']) ?>" required></div>
                    <div><label>Tanggal Kembali</label><input type="date" name="tgl_selesai" value="<?= e($data['tgl_selesai']) ?>" required></div>
                    <div class="full"><label class="check"><input type="checkbox" name="pakai_sopir" <?= $data['pakai_sopir'] ? 'checked' : '' ?>> Pakai sopir (<?= rupiah($biayaSopir) ?>/hari)</label></div>
                    <div class="full"><label>Catatan</label><textarea name="catatan" rows="2" placeholder="Tujuan perjalanan, jaminan, dll."><?= e($data['catatan']) ?></textarea></div>
                </div>
            </div>
        </div>
        <div class="card" style="align-self:start">
            <div class="card-head"><h2>Ringkasan Biaya</h2></div>
            <div class="card-body">
                <div class="summary">
                    <div><span class="muted">Lama sewa</span><strong id="r-hari">0 hari</strong></div>
                    <div><span class="muted">Biaya mobil</span><span id="r-sewa">Rp 0</span></div>
                    <div><span class="muted">Biaya sopir</span><span id="r-sopir">Rp 0</span></div>
                    <div class="total"><span>Total</span><span id="r-total">Rp 0</span></div>
                </div>
                <div class="mt"><label>Dibayar Sekarang (DP / Lunas)</label><input type="number" name="dibayar" min="0" step="1000" value="<?= e($data['dibayar']) ?>" placeholder="0"></div>
                <button class="btn btn-primary btn-block mt" <?= $mobilList ? '' : 'disabled' ?>>Simpan Transaksi</button>
            </div>
        </div>
    </div>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
