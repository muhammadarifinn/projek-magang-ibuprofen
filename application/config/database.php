<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

// Database aplikasi PPI (MySQL, dikelola lewat phpMyAdmin)
$db['default'] = array(
	'dsn'      => '',
	'hostname' => 'localhost',
	'username' => 'root',
	'password' => '',
	'database' => 'ppi_rs',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_unicode_ci',
	'swap_pre' => '',
	'encrypt'  => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

// Data pasien SIMRS. SEMENTARA memakai database yang sama dengan di atas
// (tabel simrs_pasien). Nanti, saat SIMRS dihubungkan, ganti isi grup ini:
//   'dbdriver' => 'sqlsrv', 'hostname' => '<server>', 'database' => '<db SIMRS>', dst.
// lalu sesuaikan nama tabel/kolom di application/config/ppi.php.
$db['simrs'] = $db['default'];
