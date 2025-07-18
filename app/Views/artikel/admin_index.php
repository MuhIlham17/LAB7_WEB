<?= $this->include('template/admin_header'); ?>

<h2><?= $title; ?></h2>

<div class="row mb-3">
    <div class="col-md-6">
        <form id="search-form" class="form-inline">
            <input type="text" name="q" id="search-box" value="<?= $q; ?>" placeholder="Cari judul artikel" class="form-control mr-2">

            <select name="kategori_id" id="category-filter" class="form-control mr-2">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategori as $k): ?>
                    <option value="<?= $k['id_kategori']; ?>" <?= ($kategori_id == $k['id_kategori']) ? 'selected' : ''; ?>>
                        <?= $k['nama_kategori']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" value="Cari" class="btn btn-primary">
        </form>
    </div>
</div>

<!-- Container untuk menampilkan hasil AJAX -->
<div id="article-container"></div>
<div id="pagination-container"></div>

<!-- jQuery dari CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    const articleContainer = $('#article-container');
    const paginationContainer = $('#pagination-container');
    const searchForm = $('#search-form');
    const searchBox = $('#search-box');
    const categoryFilter = $('#category-filter');

    // Fungsi untuk mengambil data dengan AJAX
    const fetchData = (url) => {
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {
                renderArticles(data.artikel);
                renderPagination(data.pager, data.q, data.kategori_id);
            }
        });
    };

    // Fungsi untuk merender data artikel ke dalam tabel
    const renderArticles = (articles) => {
        let html = '<table class="table">';
        html += '<thead><tr><th>ID</th><th>Judul</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';

        if (articles.length > 0) {
            articles.forEach(article => {
                html += `
                    <tr>
                        <td>${article.id}</td>
                        <td>
                            <b>${article.judul}</b>
                            <p><small>${article.isi.substring(0, 50)}...</small></p>
                        </td>
                        <td>${article.nama_kategori}</td>
                        <td>${article.status}</td>
                        <td>
                            <a class="btn btn-sm btn-info" href="/admin/artikel/edit/${article.id}">Ubah</a>
                            <a class="btn btn-sm btn-danger" onclick="return confirm('Yakin menghapus data?');" href="/admin/artikel/delete/${article.id}">Hapus</a>
                        </td>
                    </tr>
                `;
            });
        } else {
            html += '<tr><td colspan="5">Tidak ada data.</td></tr>';
        }

        html += '</tbody></table>';
        articleContainer.html(html);
    };

    // Fungsi untuk merender pagination
    const renderPagination = (pager, q, kategori_id) => {
        let html = '<nav><ul class="pagination">';
        if (pager && pager.links) {
            pager.links.forEach(link => {
                let url = link.url ? `${link.url}&q=${q}&kategori_id=${kategori_id}` : '#';
                html += `<li class="page-item ${link.active ? 'active' : ''}">
                            <a class="page-link" href="${url}">${link.title}</a>
                         </li>`;
            });
        }
        html += '</ul></nav>';
        paginationContainer.html(html);
    };

    // Event: saat form cari dikirim
    searchForm.on('submit', function(e) {
        e.preventDefault();
        const q = searchBox.val();
        const kategori_id = categoryFilter.val();
        fetchData(`/admin/artikel?q=${q}&kategori_id=${kategori_id}`);
    });

    // Event: saat ganti kategori
    categoryFilter.on('change', function() {
        searchForm.trigger('submit');
    });

    // Event: klik pagination (delegasi karena pagination dinamis)
    paginationContainer.on('click', '.page-link', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url && url !== '#') {
            fetchData(url);
        }
    });

    // Load data awal
    fetchData('/admin/artikel');
});
</script>

<?= $this->include('template/admin_footer'); ?>
