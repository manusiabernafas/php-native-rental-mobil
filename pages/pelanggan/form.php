<?php
$id = (int) ($_GET['id'] ?? 0);
$p = $id ? q('SELECT * FROM pelanggan WHERE id = ?', [$id])->fetch() : null;
if ($id && !$p) redirect('pelanggan');

$data = $p ?: ['nik' => '', 'nama' => '', 'telepon' => '', 'alamat' => '', 'no_sim' => ''];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nik', 'nama', 'telepon', 'alamat', 'no_sim'] as $f) $data[$f] = trim($_POST[$f] ?? '');

    if (!preg_match('/^\d{16}$/', $data['nik'])) {
        $error = 'NIK harus 16 digit angka.';
    } elseif ($data['nama'] === '' || $data['telepon'] === '') {
        $error = 'Nama dan telepon wajib diisi.';
    } elseif (q('SELECT id FROM pelanggan WHERE nik = ? AND id <> ?', [$data['nik'], $id])->fetch()) {
        $error = 'NIK sudah terdaftar.';
    }

    if (!$error) {
        $vals = [$data['nik'], $data['nama'], $data['telepon'], $data['alamat'], $data['no_sim']];
        if ($id) {
            q('UPDATE pelanggan SET nik=?, nama=?, telepon=?, alamat=?, no_sim=? WHERE id=?', [...$vals, $id]);
            flash('Data pelanggan diperbarui.');
        } else {
            q('INSERT INTO pelanggan (nik, nama, telepon, alamat, no_sim) VALUES (?,?,?,?,?)', $vals);
            flash('Pelanggan baru ditambahkan.');
        }
        redirect('pelanggan');
    }
}

$title = $id ? 'Ubah Pelanggan' : 'Tambah Pelanggan';
require __DIR__ . '/../../includes/header.php';
?>
<form method="post" class="card" style="max-width:820px">
    <?= csrf_field() ?>
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <div class="form-grid">
            <div><label>NIK (KTP)</label><input type="text" name="nik" value="<?= e($data['nik']) ?>" maxlength="16" inputmode="numeric" required></div>
            <div><label>Nama Lengkap</label><input type="text" name="nama" value="<?= e($data['nama']) ?>" required></div>
            <div><label>No. Telepon / WA</label><input type="text" name="telepon" value="<?= e($data['telepon']) ?>" required></div>
            <div><label>No. SIM</label><input type="text" name="no_sim" value="<?= e($data['no_sim']) ?>"></div>
            <div class="full"><label>Alamat</label><textarea name="alamat" rows="3"><?= e($data['alamat']) ?></textarea></div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary">Simpan</button>
            <a href="<?= url('pelanggan') ?>" class="btn btn-light">Batal</a>
        </div>
    </div>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
