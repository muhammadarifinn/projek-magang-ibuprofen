<div class="baris">
	<div>
		<h1>Dashboard Kepatuhan Bundle</h1>
		<p class="sub">Rekap audit pasien dari IGD dan rawat inap, periode <?= e(ppi_bulan($periode)) ?>.</p>
	</div>
	<form method="get" action="<?= site_url('dashboard') ?>" class="filter">
		<label class="f">Periode
			<select name="periode" onchange="this.form.submit()">
				<?php foreach ($daftar_periode as $p): ?><option value="<?= e($p) ?>" <?= $p === $periode ? 'selected' : '' ?>><?= e(ppi_bulan($p)) ?></option><?php endforeach; ?>
			</select>
		</label>
		<label class="f">Ruangan
			<select name="ruangan" onchange="this.form.submit()" <?= $terkunci ? 'disabled' : '' ?>>
				<option value="">Semua ruangan</option>
				<?php foreach ($daftar_ruangan as $r): ?><option value="<?= e($r['nama']) ?>" <?= $r['nama'] === $ruangan ? 'selected' : '' ?>><?= e($r['nama']) ?></option><?php endforeach; ?>
			</select>
		</label>
		<label class="f">Bundle
			<select name="bundle" onchange="this.form.submit()">
				<option value="0">Semua bundle</option>
				<?php foreach ($bundles as $b): ?><option value="<?= (int) $b['id'] ?>" <?= (int) $b['id'] === $bundle_id ? 'selected' : '' ?>><?= e($b['kode']) ?></option><?php endforeach; ?>
			</select>
		</label>
	</form>
</div>

<div class="kpi">
	<?php foreach ($kpi as $k): $p = ppi_pct($k['ya'], $k['tidak']); ?>
		<div class="kotak k">
			<div class="atas"><span class="kode"><?= e($k['kode']) ?></span><span class="nilai"><?= e(ppi_pct_teks($p)) ?></span></div>
			<span class="nama"><?= e($k['nama']) ?></span>
			<div class="bar"><i style="width:<?= (int) $p ?>%"></i></div>
		</div>
	<?php endforeach; ?>
</div>

<div class="kotak">
	<div class="kepala">
		<h2>Pasien yang sedang diaudit</h2>
		<?php if (in_array($user['role'], array('admin', 'ipcn'), true)): ?><a class="btn" href="<?= site_url('audit') ?>">Isi audit hari ini</a><?php endif; ?>
	</div>
	<div class="geser">
		<table class="tb">
			<thead><tr><th>No. RM</th><th>Nama pasien</th><th>Ruangan</th><th>Bundle</th><th>Hari rawat</th><th>Audit hari ini</th></tr></thead>
			<tbody>
			<?php foreach ($pasien as $p): ?>
				<tr>
					<td><?= e($p['no_rm']) ?></td>
					<td><strong><?= e($p['nama_pasien']) ?></strong></td>
					<td><?= e($p['ruangan']) ?></td>
					<td><?= e($p['bundle']) ?></td>
					<td>Hari ke-<?= (int) ppi_hari_ke($p['tgl_masuk']) ?></td>
					<td><?= (int) $p['terisi_hari_ini'] > 0 ? '<span class="chip ok">Sudah diisi</span>' : '<span class="chip tunda">Belum diisi</span>' ?></td>
				</tr>
			<?php endforeach; ?>
			<?php if (!$pasien): ?><tr><td colspan="6" class="catatan">Belum ada pasien yang diaudit.</td></tr><?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
