<div class="baris">
	<div>
		<h1>Laporan Cetak per Bundle</h1>
		<p class="sub">Kolom tanggal hanya memuat hari pasien dirawat.</p>
	</div>
	<form method="get" action="<?= site_url('laporan') ?>" class="filter">
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
	<div class="kepala"><h2>Audit <?= e($bundle['kode']) ?></h2></div>
	<div class="geser">
		<table class="tb">
			<thead><tr><th>No. RM</th><th>Nama pasien</th><th>Ruangan</th><th>Masuk</th><th>Keluar</th><th>Status</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($daftar as $a): ?>
				<tr>
					<td><?= e($a['no_rm']) ?></td>
					<td><strong><?= e($a['nama_pasien']) ?></strong></td>
					<td><?= e($a['ruangan']) ?></td>
					<td><?= e(ppi_tgl($a['tgl_masuk'])) ?></td>
					<td><?= $a['tgl_keluar'] ? e(ppi_tgl($a['tgl_keluar'])) : '-' ?></td>
					<td><?= $a['status'] === 'aktif' ? '<span class="chip tunda">Dirawat</span>' : '<span class="chip mati">Selesai</span>' ?></td>
					<td><a class="btn kecil" target="_blank" rel="noopener" href="<?= site_url('laporan/cetak/' . (int) $a['id']) ?>">Cetak</a></td>
				</tr>
			<?php endforeach; ?>
			<?php if (!$daftar): ?><tr><td colspan="7" class="catatan">Belum ada audit untuk bundle ini.</td></tr><?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
