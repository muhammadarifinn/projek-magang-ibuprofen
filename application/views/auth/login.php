<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · <?= e($this->config->item('app_nama')) ?></title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap">
<style><?php readfile(APPPATH . "views/layout/app.css"); ?></style>
</head>
<body>
<div class="login">
	<div class="kotak">
		<div>
			<h1 style="font-size:22px"><?= e($this->config->item('app_nama')) ?></h1>
			<p class="sub">Masuk untuk melanjutkan.</p>
		</div>
		<?php if ($galat): ?><div class="galat" role="alert"><?= e($galat) ?></div><?php endif; ?>
		<?= form_open('auth/login', array('style' => 'display:flex;flex-direction:column;gap:16px')) ?>
			<label class="f">Nama pengguna<input type="text" name="username" autocomplete="username" required autofocus></label>
			<label class="f">Kata sandi<input type="password" name="password" autocomplete="current-password" required></label>
			<button type="submit" class="btn">Masuk</button>
		<?= form_close() ?>
	</div>
</div>
</body>
</html>
