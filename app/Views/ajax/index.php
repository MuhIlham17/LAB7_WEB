<?= $this->include('template/header'); ?>

<h1>Data Artikel</h1>

<table class="table-data" id="artikelTable" border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<!-- FORM TAMBAH ARTIKEL -->
<h3>Tambah Artikel</h3>
<form id="formAdd">
    <input type="text" name="judul" placeholder="Judul" required>
    <input type="text" name="status" placeholder="Status" required>
    <button type="submit">Tambah</button>
</form>

<!-- jQuery + Script AJAX -->
<script src="<?= base_url('assets/js/jquery-3.7.1.min.js') ?>"></script>
<script>
$(document).ready(function () {
    function showLoadingMessage() {
        $('#artikelTable tbody').html('<tr><td colspan="4">Loading data...</td></tr>');
    }

    function loadData() {
        showLoadingMessage();

        $.ajax({
            url: "<?= base_url('ajax/getData') ?>",
            method: "GET",
            dataType: "json",
            success: function (data) {
                var tableBody = "";
                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    tableBody += "<tr>";
                    tableBody += "<td>" + row.id + "</td>";
                    tableBody += "<td>" + row.judul + "</td>";
                    tableBody += "<td><span class='status'>" + (row.status ?? '---') + "</span></td>";
                    tableBody += "<td>";
                    tableBody += "<a href='<?= base_url('artikel/edit/') ?>" + row.id + "' class='btn btn-primary'>Edit</a> ";
                    tableBody += "<a href='#' class='btn btn-danger btn-delete' data-id='" + row.id + "'>Delete</a>";
                    tableBody += "</td>";
                    tableBody += "</tr>";
                }
                $('#artikelTable tbody').html(tableBody);
            },
            error: function () {
                $('#artikelTable tbody').html('<tr><td colspan="4">Gagal memuat data.</td></tr>');
            }
        });
    }

    // Muat data saat halaman dibuka
    loadData();

    // Tombol hapus
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        if (confirm('Apakah Anda yakin ingin menghapus artikel ini?')) {
            $.ajax({
                url: "<?= base_url('artikel/delete/') ?>" + id,
                method: "DELETE",
                success: function () {
                    loadData(); // Refresh table
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('Gagal menghapus artikel: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }
    });

    // Submit form tambah
    $('#formAdd').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= base_url("ajax/add") ?>',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert('Artikel berhasil ditambahkan!');
                $('#formAdd')[0].reset();
                loadData();
            },
            error: function(err) {
                alert('Gagal menambahkan artikel.');
            }
        });
    });
});
</script>

<?= $this->include('template/footer'); ?>
