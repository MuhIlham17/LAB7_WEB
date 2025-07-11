<?php

namespace App\Controllers;

use App\Models\UserModel;

class User extends BaseController
{
    public function index()
    {
        $title = 'Daftar User'; // Judul halaman daftar user
        $model = new UserModel();
        $users = $model->findAll(); // Ambil semua data user
        return view('user/index', compact('users', 'title')); // Kirim ke view
    }

    public function login()
    {
        helper(['form']); // Aktifkan form helper

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Jika form belum dikirim, tampilkan halaman login
        if (!$email) {
            return view('user/login');
        }

        $session = session();
        $model = new UserModel();
        $login = $model->where('useremail', $email)->first(); // Cari user

        if ($login) {
            $pass = $login['userpassword'];
            if (password_verify($password, $pass)) {
                // Jika password benar, set session
                $login_data = [
                    'user_id'    => $login['id'],
                    'user_name'  => $login['username'],
                    'user_email' => $login['useremail'],
                    'logged_in'  => true,
                ];
                $session->set($login_data);
                return redirect()->to('/admin/artikel');
            } else {
                $session->setFlashdata("flash_msg", "Password salah.");
                return redirect()->to('/user/login');
            }
        } else {
            $session->setFlashdata("flash_msg", "Email tidak terdaftar.");
            return redirect()->to('/user/login');
        }
    }

    public function logout()
    {
        // Hapus semua data sesi login
        session()->destroy();

        // Kembali ke halaman login
        return redirect()->to('/user/login');
    }
}