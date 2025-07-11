<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// =========================
// 📌 ROUTE PUBLIK
// =========================
$routes->get('/', 'Home::index');
$routes->get('about', 'Page::about');
$routes->get('contact', 'Page::contact');
$routes->get('faqs', 'Page::faqs');
$routes->get('tos', 'Page::tos');

// Artikel publik
$routes->get('artikel', 'Artikel::index');
$routes->get('artikel/(:segment)', 'Artikel::view/$1');

// =========================
// 🔐 ROUTE ADMIN (PROTECTED)
// =========================
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Artikel::admin_index'); // /admin
    $routes->get('artikel', 'Artikel::admin_index');
    $routes->add('artikel/add', 'Artikel::add');
    $routes->add('artikel/edit/(:num)', 'Artikel::edit/$1');
    $routes->get('artikel/delete/(:num)', 'Artikel::delete/$1');
});

// =========================
// 🔐 ROUTE AUTH USER
// =========================
$routes->get('user/login', 'User::login');   // GET untuk form
$routes->post('user/login', 'User::login');  // POST untuk proses login
$routes->get('user/logout', 'User::logout'); // logout
$routes->get('/kategori/(:num)', 'Artikel::kategori/$1');

$routes->get('ajax', 'AjaxController::index');
$routes->get('ajax/getData', 'AjaxController::getData');
$routes->delete('artikel/delete/(:num)', 'AjaxController::delete/$1');
$routes->get('artikel/edit/(:num)', 'Artikel::edit/$1');
$routes->post('artikel/edit/(:num)', 'Artikel::edit/$1');
$routes->match(['get', 'post'], 'ajax/edit/(:num)', 'AjaxController::edit/$1');
$routes->get('admin/artikel/edit/(:num)', 'Artikel::edit/$1');
$routes->post('admin/artikel/edit/(:num)', 'Artikel::edit/$1');
$routes->resource('post');