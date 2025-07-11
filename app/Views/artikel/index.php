<?= $this->include('template/header'); ?>

<?php if ($artikel): foreach ($artikel as $row): ?>
    <article class="entry">
        <h2>
            <a href="<?= base_url('/artikel/' . $row['slug']); ?>">
                <?= esc($row['judul']); ?>
            </a>
        </h2>
        <p><strong>Kategori:</strong> <?= esc($row['nama_kategori']); ?></p>
        
        <?php if (!empty($row['gambar'])): ?>
            <img src="<?= base_url('/gambar/' . $row['gambar']); ?>" alt="<?= esc($row['judul']); ?>" style="max-width:100%; height:auto;">
        <?php endif; ?>

        <p><?= esc(substr($row['isi'], 0, 200)); ?>...</p>
    </article>
    <hr class="divider" />
<?php endforeach; else: ?>
    <article class="entry">
        <h2>Belum ada data.</h2>
    </article>
<?php endif; ?>

<?= $this->include('template/footer'); ?>
