<?php
$id = (int) ($_GET['id'] ?? 0);
$m = $id ? q('SELECT * FROM mobil WHERE id = ?', [$id])->fetch() : null;
if ($id && !$m) redirect('mobil');

$data = $m ?: ['plat_nomor' => '', 'merk' => '', 'model' => '', 'tahun' => date('Y'), 'warna' => '', 'kursi' => 7,
    'transmisi' => 'Manual', 'bahan_bakar' => 'Bensin', 'harga_per_hari' => '', 'status' => 'tersedia', 'foto' => null];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['plat_nomor', 'merk', 'model', 'tahun', 'warna', 'kursi', 'transmisi', 'bahan_bakar', 'harga_per_hari', 'status'] as $f) {
        $data[$f] = trim($_POST[$f] ?? '');
    }
    $data['plat_nomor'] = strtoupper($data['plat_nomor']);

    if ($data['plat_nomor'] === '' || $data['merk'] === '' || $data['model'] === '' || (int) $data['harga_per_hari'] <= 0) {
        $error = 'Plat nomor, merk, model, dan harga wajib diisi.';
    } elseif (q('SELECT id FROM mobil WHERE plat_nomor = ? AND id <> ?', [$data['plat_nomor'], $id])->fetch()) {
        $error = 'Plat nomor sudah terdaftar.';
    }

    // Upload foto (opsional)
    if (!$error && !empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || $_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $error = 'Foto harus JPG/PNG/WEBP maksimal 2 MB.';
        } elseif (!getimagesize($_FILES['foto']['tmp_name'])) {
            $error = 'File foto tidak valid.';
        } else {
            $nama = 'mobil_' . bin2hex(random_bytes(6)) . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/../../assets/uploads/' . $nama);
            if ($data['foto']) @unlink(__DIR__ . '/../../assets/uploads/' . $data['foto']);
            $data['foto'] = $nama;
        }
    }

    if (!$error) {
        $vals = [$data['plat_nomor'], $data['merk'], $data['model'], (int) $data['tahun'], $data['warna'], (int) $data['kursi'],
            $data['transmisi'], $data['bahan_bakar'], (int) $data['harga_per_hari'], $data['status'], $data['foto']];
        if ($id) {
            q('UPDATE mobil SET plat_nomor=?, merk=?, model=?, tahun=?, warna=?, kursi=?, transmisi=?, bahan_bakar=?, harga_per_hari=?, status=?, foto=? WHERE id=?', [...$vals, $id]);
            flash('Data mobil diperbarui.');
        } else {
            q('INSERT INTO mobil (plat_nomor, merk, model, tahun, warna, kursi, transmisi, bahan_bakar, harga_per_hari, status, foto) VALUES (?,?,?,?,?,?,?,?,?,?,?)', $vals);
            flash('Mobil baru ditambahkan.');
        }
        redirect('mobil');
    }
}

$title = $id ? 'Ubah Mobil' : 'Tambah Mobil';
require __DIR__ . '/../../includes/header.php';
?>
<form method="post" enctype="multipart/form-data" class="card" style="max-width:820px">
    <?= csrf_field() ?>
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <div class="form-grid">
            <div><label>Plat Nomor</label><input type="text" name="plat_nomor" value="<?= e($data['plat_nomor']) ?>" placeholder="AB 1234 XY" required></div>
            <div><label>Harga Sewa per Hari (Rp)</label><input type="number" name="harga_per_hari" value="<?= e($data['harga_per_hari']) ?>" min="0" step="5000" required></div>
            <div><label>Merk</label><input type="text" name="merk" value="<?= e($data['merk']) ?>" placeholder="Toyota" required></div>
            <div><label>Model</label><input type="text" name="model" value="<?= e($data['model']) ?>" placeholder="Avanza" required></div>
            <div><label>Tahun</label><input type="number" name="tahun" value="<?= e($data['tahun']) ?>" min="1990" max="<?= date('Y') + 1 ?>"></div>
            <div><label>Warna</label><input type="text" name="warna" value="<?= e($data['warna']) ?>"></div>
            <div><label>Jumlah Kursi</label><input type="number" name="kursi" value="<?= e($data['kursi']) ?>" min="2" max="60"></div>
            <div><label>Transmisi</label>
                <select name="transmisi"><?php foreach (['Manual', 'Matic'] as $o): ?><option <?= $data['transmisi'] === $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select>
            </div>
            <div><label>Bahan Bakar</label>
                <select name="bahan_bakar"><?php foreach (['Bensin', 'Solar', 'Listrik', 'Hybrid'] as $o): ?><option <?= $data['bahan_bakar'] === $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select>
            </div>
            <div><label>Status</label>
                <select name="status"><?php foreach (['tersedia', 'disewa', 'servis'] as $o): ?><option value="<?= $o ?>" <?= $data['status'] === $o ? 'selected' : '' ?>><?= ucfirst($o) ?></option><?php endforeach; ?></select>
            </div>
            <div class="full"><label>Foto Mobil (opsional)</label><input type="file" name="foto" accept="image/*"></div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary">Simpan</button>
            <a href="<?= url('mobil') ?>" class="btn btn-light">Batal</a>
        </div>
    </div>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
