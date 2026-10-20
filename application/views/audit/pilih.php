<div class="baris">
	<div>
		<h1>Audit Bundle Harian</h1>
		<p class="sub">Pilih bundle, lalu pilih pasien yang sedang dirawat (data dari SIMRS).</p>
	</div>
	<form method="get" action="<?= site_url('audit') ?>" class="filter">
		<label class="f">Bundle
			<select name="bundle" onchange="this.form.submit()">
				<?php foreach ($bundles as $b): ?><option value="<?= (int) $b['id'] ?>" <?= (int) $b['id'] === (int) $bundle['id'] ? 'selected' : '' ?>><?= e($b['kode']) ?></option><?php endforeach; ?>
			</select>
		</label>
		<label class="f">Cari No. RM atau nama<input type="text" name="q" value="<?= e($cari) ?>"></label>
		<button class="btn" type="submit">Cari</button>
	</form>
</div>

<div class="kotak">
	<div class="kepala"><h2>Pasien yang sedang dirawat</h2><span class="chip info">Dari SIMRS, hanya baca</span></div>
	<div class="geser">
		<table class="tb">
			<thead><tr><th>No. RM</th><th>Nama pasien</th><th>Ruangan</th><th>Masuk</th><th>Posisi</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($pasien as $p): ?>
				<tr>
					<td><?= e($p['no_rm']) ?></td>
					<td><strong><?= e($p['nama']) ?></strong></td>
					<td><?= e($p['ruangan']) ?></td>
					<td><?= e(ppi_tgl($p['tgl_masuk'])) ?></td>
					<td><?= $p['tgl_masuk_ri'] ? 'Rawat Inap, hari ke-' . (int) ppi_hari_ke($p['tgl_masuk_ri']) : 'IGD' ?></td>
					<td><a class="btn kecil" href="<?= site_url('audit?bundle=' . (int) $bundle['id'] . '&reg=' . rawurlencode($p['no_registrasi'])) ?>">Isi audit <?= e($bundle['kode']) ?></a></td>
				</tr>
			<?php endforeach; ?>
			<?php if (!$pasien): ?><tr><td colspan="6" class="catatan">Tidak ada pasien yang cocok.</td></tr><?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
