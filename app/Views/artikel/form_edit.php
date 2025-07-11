<form action="" method="post">
    <p>
        <label>Judul</label>
        <input type="text" name="judul" value="<?= esc($artikel['judul']) ?>" required>
    </p>
    <p>
        <label>Isi</label>
        <textarea name="isi" required><?= esc($artikel['isi']) ?></textarea>
    </p>
    <p>
        <label>Status</label>
        <input type="text" name="status" value="<?= esc($artikel['status']) ?>" required>
    </p>
    <p>
        <label>Kategori</label>
        <select name="id_kategori" required>
            <?php foreach ($kategori as $k): ?>
                <option value="<?= $k['id_kategori'] ?>" <?= $artikel['id_kategori'] == $k['id_kategori'] ? 'selected' : '' ?>>
                    <?= esc($k['nama_kategori']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <button type="submit">Simpan</button>
    </p>
</form>