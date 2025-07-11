<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    // dipanggil sebelum controller dijalankan
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logged_in')) {
            // user belum login → redirect ke /user/login
            return redirect()->to('/user/login');
        }
    }

    // dipanggil setelah controller (tidak dipakai di sini)
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // kosong
    }
}