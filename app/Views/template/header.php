<?php helper('url'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">

</head>
<body>
    <div id="container">

        <!-- Header -->
        <header>
            <h1>Layout Sederhana</h1>
        </header>

        <!-- Navigasi -->
        <nav>
            <a href="<?= base_url('/') ?>" class="active">Home</a>
            <a href="<?= base_url('/artikel') ?>">Artikel</a>
            <a href="<?= base_url('/about') ?>">About</a>
            <a href="<?= base_url('/contact') ?>">Kontak</a>
        </nav>

        <!-- Wrapper & Main -->
        <section id="wrapper">
            <section id="main">
