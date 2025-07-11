<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\ArtikelModel;

class AjaxController extends Controller
{
    public function index()
    {
        $data['title'] = 'Manajemen Artikel AJAX';
        return view('ajax/index', $data);
    }

    public function getData()
    {
        $model = new ArtikelModel();
        $data = $model->findAll();

        // Kirim data dalam format JSON
        return $this->response->setJSON($data);
    }

    public function delete($id)
    {
        $model = new ArtikelModel();
        $model->delete($id);

        // Kirim respon dalam format JSON
        return $this->response->setJSON(['status' => 'OK']);
    }

    public function add()
    {
    $model = new ArtikelModel();
    $judul = $this->request->getPost('judul');
    $status = $this->request->getPost('status');

    $data = [
        'judul' => $judul,
        'slug'  => url_title($judul, '-', true),
        'isi'   => '-', // sementara kosong
        'status'=> $status,
        'id_kategori' => 1 // sesuaikan ID kategori yang valid
    ];

    $model->insert($data);
    return $this->response->setJSON(['status' => 'OK']);
    }

    public function edit($id)
    {
    $model = new ArtikelModel();
    $artikel = $model->find($id);

    if ($this->request->getMethod() == 'post') {
        $data = [
            'judul' => $this->request->getPost('judul'),
            'status' => $this->request->getPost('status')
        ];
        $model->update($id, $data);
        return $this->response->setJSON(['status' => 'OK']);
    }

    return $this->response->setJSON($artikel);
    }


}
