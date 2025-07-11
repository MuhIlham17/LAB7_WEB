<?php
namespace App\Controllers;

use App\Models\ArtikelModel;
use App\Models\KategoriModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Artikel extends BaseController
{
    /* ===================== PUBLIC ===================== */

    public function index()
    {
        $title   = 'Daftar Artikel';
        $model   = new ArtikelModel();
        $artikel = $model->getArtikelDenganKategori(); // Menampilkan artikel + kategori
        return view('artikel/index', compact('artikel', 'title'));
    }

    public function view($slug)
    {
        $model   = new ArtikelModel();
        $artikel = $model->where('slug', $slug)->first();

        if (!$artikel) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('artikel/detail', [
            'title'   => $artikel['judul'],
            'artikel' => $artikel,
        ]);
    }

    /* ===================== ADMIN ===================== */

    public function admin_index()
{
    $title = 'Daftar Artikel (Admin)';
    $model = new ArtikelModel();

    // Ambil parameter pencarian
    $q = $this->request->getVar('q') ?? '';
    $kategori_id = $this->request->getVar('kategori_id') ?? '';
    $page = $this->request->getVar('page') ?? 1;

    // Query builder untuk join artikel dan kategori
    $builder = $model->table('artikel')
        ->select('artikel.*, kategori.nama_kategori')
        ->join('kategori', 'kategori.id_kategori = artikel.id_kategori');

    // Filter pencarian jika ada
    if ($q != '') {
        $builder->like('artikel.judul', $q);
    }

    // Filter berdasarkan kategori jika dipilih
    if ($kategori_id != '') {
        $builder->where('artikel.id_kategori', $kategori_id);
    }

    // Paginasi
    $artikel = $builder->paginate(10, 'default', $page);
    $pager = $model->pager;

    // Susun data untuk response
    $data = [
        'title' => $title,
        'q' => $q,
        'kategori_id' => $kategori_id,
        'artikel' => $artikel,
        'pager' => $pager
    ];

    // Jika request via AJAX, kembalikan dalam bentuk JSON
    if ($this->request->isAJAX()) {
        return $this->response->setJSON($data);
    } else {
        // Jika bukan AJAX, tampilkan view biasa
        $kategoriModel = new KategoriModel();
        $data['kategori'] = $kategoriModel->findAll();
        return view('artikel/admin_index', $data);
    }
}

    public function add()
    {
    // Validasi input
    if ($this->request->getMethod() == 'post' && $this->validate([
        'judul'       => 'required',
        'isi'         => 'required',
        'id_kategori' => 'required|integer' // Pastikan kategori wajib
    ])) {
        $model = new ArtikelModel();

        $model->insert([
            'judul'       => $this->request->getPost('judul'),
            'isi'         => $this->request->getPost('isi'),
            'slug'        => url_title($this->request->getPost('judul'), '-', true),
            'id_kategori' => $this->request->getPost('id_kategori'),
            'status'      => 'aktif', // ✅ WAJIB DITAMBAHKAN agar muncul di halaman /artikel
        ]);

        return redirect()->to('/admin/artikel');
        } 
        else 
        {
        $kategoriModel = new KategoriModel();
        $data['kategori'] = $kategoriModel->findAll(); // Untuk dropdown
        $data['title'] = "Tambah Artikel";

        return view('artikel/form_add', $data);
        }
    }

    public function edit($id)
{
    $model = new \App\Models\ArtikelModel();
    $kategoriModel = new \App\Models\KategoriModel();

    // Cek apakah artikel ditemukan
    $artikel = $model->find($id);
    if (!$artikel) {
        throw new \CodeIgniter\Exceptions\PageNotFoundException("Artikel tidak ditemukan");
    }

    // Cek jika method POST dan validasi lolos
    if ($this->request->getMethod() === 'post' && $this->validate([
        'judul' => 'required',
        'isi' => 'required',
        'status' => 'required',
        'id_kategori' => 'required|integer'
    ])) {
        $model->update($id, [
            'judul' => $this->request->getPost('judul'),
            'isi' => $this->request->getPost('isi'),
            'status' => $this->request->getPost('status'),
            'id_kategori' => $this->request->getPost('id_kategori')
        ]);

        // ✅ Redirect agar tidak mentok
        return redirect()->to('/admin/artikel');
    }

    // Tampilkan form dengan data
    return view('artikel/form_edit', [
        'title' => 'Edit Artikel',
        'artikel' => $artikel,
        'kategori' => $kategoriModel->findAll()
    ]);
}

    public function delete($id)
    {
        $model = new ArtikelModel();
        $model->delete($id);
        return redirect()->to('/admin/artikel');
    }

    public function kategori($id_kategori)
    {
    $model = new ArtikelModel();
    $kategoriModel = new KategoriModel();

    $kategori = $kategoriModel->find($id_kategori);
    if (!$kategori) {
        throw new \CodeIgniter\Exceptions\PageNotFoundException('Kategori tidak ditemukan.');
    }

    $artikel = $model->table('artikel')
        ->select('artikel.*, kategori.nama_kategori')
        ->join('kategori', 'kategori.id_kategori = artikel.id_kategori')
        ->where('artikel.id_kategori', $id_kategori)
        ->get()
        ->getResultArray();

    $data = [
        'title' => 'Artikel Kategori: ' . $kategori['nama_kategori'],
        'artikel' => $artikel,
    ];

    return view('artikel/index', $data);
    }

}
