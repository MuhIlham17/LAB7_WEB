<?php helper('url'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/style.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('css/admin.css'); ?>">
</head>
<body>
<div id="container">

    <!-- Header -->
    <header class="admin-header">
        <h1>Admin Portal Berita</h1>
    </header>

    <!-- Navbar -->
    <nav class="admin-nav">
        <a href="<?= base_url('/admin') ?>" class="<?= uri_string() == 'admin' ? 'active' : '' ?>">Dashboard</a>
        <a href="<?= base_url('/admin/artikel') ?>" class="<?= uri_string() == 'admin/artikel' ? 'active' : '' ?>">Artikel</a>
        <a href="<?= base_url('/admin/artikel/add') ?>" class="<?= uri_string() == 'admin/artikel/add' ? 'active' : '' ?>">Tambah Artikel</a>
    </nav>

    <!-- Wrapper -->
    <section id="wrapper">
        <section id="main">
