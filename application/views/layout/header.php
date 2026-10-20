<?php
$ikon = array(
	'dashboard' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
	'audit'     => '<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="m9 14 2 2 4-4"/>',
	'master'    => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
	'laporan'   => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/>',
	'keluar'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
);
$daftar_menu = array(
	array('dashboard', 'Dashboard', 'dashboard', array('admin', 'ipcn', 'ketua', 'perawat')),
	array('audit', 'Audit Bundle', 'audit', array('admin', 'ipcn')),
	array('master', 'Master Pertanyaan', 'master', array('admin')),
	array('laporan', 'Laporan', 'laporan', array('admin', 'ipcn', 'ketua', 'perawat')),
);
$nama_peran = array('admin' => 'Administrator', 'ipcn' => 'IPCN / IPCLN', 'ketua' => 'Ketua PPI', 'perawat' => 'Perawat ruangan');
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(isset($judul) ? $judul . ' · ' : '') . e($this->config->item('app_nama')) ?></title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap">
<style><?php readfile(APPPATH . "views/layout/app.css"); ?></style>
</head>
<body>
<div class="app">
	<aside class="side">
		<div class="who"><?= e($nama_peran[$user['role']]) ?><small><?= e($user['nama']) ?><?= !empty($user['ruangan']) ? ' · ' . e($user['ruangan']) : '' ?></small></div>
		<nav aria-label="Menu utama">
			<?php foreach ($daftar_menu as $m): if (!in_array($user['role'], $m[3], true)) continue; ?>
				<a href="<?= site_url($m[0]) ?>" class="<?= $menu === $m[0] ? 'on' : '' ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><?= $ikon[$m[2]] ?></svg><?= e($m[1]) ?></a>
			<?php endforeach; ?>
			<a href="<?= site_url('auth/logout') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><?= $ikon['keluar'] ?></svg>Keluar</a>
		</nav>
	</aside>
	<div class="main">
		<div class="top"><?= e($this->config->item('app_nama')) ?></div>
		<div class="isi">
			<?php if (!empty($pesan)): ?><div class="pesan" role="status"><?= e($pesan) ?></div><?php endif; ?>
			<?php if (!empty($galat)): ?><div class="galat" role="alert"><?= e($galat) ?></div><?php endif; ?>
