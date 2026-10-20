<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['app_nama']      = 'Aplikasi PPI RSI Siti Aisyah';
$config['app_footer']    = 'Copyright ©2026 Tim PDE RSI Madiun';
$config['rs_nama']       = 'RSI Siti Aisyah Madiun';

// Sumber data pasien SIMRS (dipakai Pasien_model).
$config['simrs_group']   = 'simrs';          // grup koneksi di config/database.php
$config['simrs_table']   = 'simrs_pasien';   // tabel atau view

// Pemetaan kolom SIMRS -> nama yang dipakai aplikasi.
// Jika SIMRS sungguhan memakai nama kolom lain, cukup ubah sisi kanan.
$config['simrs_kolom'] = array(
	'no_registrasi' => 'no_registrasi',
	'no_rm'         => 'no_rm',
	'nama'          => 'nama',
	'ruangan'       => 'ruangan',
	'tgl_masuk'     => 'tgl_masuk',
	'tgl_masuk_ri'  => 'tgl_masuk_ri',
	'tgl_keluar'    => 'tgl_keluar',
);
