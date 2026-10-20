<?php $dasar = 'audit?bundle=' . (int) $bundle['id'] . '&reg=' . rawurlencode($pasien['no_registrasi']); ?>
<div class="baris">
	<div>
		<h1>Audit Bundle <?= e($bundle['kode']) ?></h1>
		<p class="sub">Ketuk sel untuk mengganti jawaban: Ya, Tidak, N/A.</p>
	</div>
	<div class="filter">
		<form method="get" action="<?= site_url('audit') ?>" class="filter">
			<input type="hidden" name="reg" value="<?= e($pasien['no_registrasi']) ?>">
			<label class="f">Bundle
				<select name="bundle" onchange="this.form.submit()">
					<?php foreach ($bundles as $b): ?><option value="<?= (int) $b['id'] ?>" <?= (int) $b['id'] === (int) $bundle['id'] ? 'selected' : '' ?>><?= e($b['kode']) ?></option><?php endforeach; ?>
				</select>
			</label>
		</form>
		<a class="btn pol" href="<?= site_url('audit?bundle=' . (int) $bundle['id']) ?>">Ganti pasien</a>
	</div>
</div>

<div class="kotak">
	<div class="kepala"><h2>Data pasien</h2><span class="chip info">Dari SIMRS, hanya baca</span></div>
	<div class="badan pasien">
		<div><small>No. RM</small><b><?= e($pasien['no_rm']) ?></b></div>
		<div><small>Nama pasien</small><b><?= e($pasien['nama']) ?></b></div>
		<div><small>Ruangan</small><b><?= e($pasien['ruangan']) ?></b></div>
		<div><small>Masuk IGD</small><b><?= e(ppi_tgl($pasien['tgl_masuk'])) ?></b></div>
		<div><small>Masuk rawat inap</small><b><?= e(ppi_tgl($pasien['tgl_masuk_ri'])) ?></b></div>
		<div><small>Keluar</small><b><?= $pasien['tgl_keluar'] ? e(ppi_tgl($pasien['tgl_keluar'])) : 'Masih dirawat' ?></b></div>
	</div>
</div>

<div class="kotak">
	<div class="tab" role="tablist">
		<?php foreach ($tahap_list as $t): ?>
			<a role="tab" href="<?= site_url($dasar . '&tahap=' . e($t['kode'])) ?>" class="<?= (int) $t['id'] === (int) $tahap['id'] ? 'on' : '' ?>"><b><?= e($t['nama']) ?></b> <span><?= (int) $t['harian'] === 1 ? 'diisi setiap hari rawat' : 'diisi saat pasien masuk' ?></span></a>
		<?php endforeach; ?>
	</div>

	<?php if (!$tanggal): ?>
		<div class="badan catatan">Pasien belum tercatat masuk rawat inap di SIMRS, jadi tahap ini belum bisa diisi.</div>
	<?php elseif (!$pertanyaan): ?>
		<div class="badan catatan">Belum ada pertanyaan aktif di tahap ini. Tambahkan lewat menu Master Pertanyaan.</div>
	<?php else: ?>
	<?= form_open('audit/simpan') ?>
		<input type="hidden" name="reg" value="<?= e($pasien['no_registrasi']) ?>">
		<input type="hidden" name="bundle" value="<?= (int) $bundle['id'] ?>">
		<input type="hidden" name="tahap" value="<?= e($tahap['kode']) ?>">
		<div class="geser">
			<table class="grid">
				<thead>
					<tr>
						<th class="kiri" style="width:40px">No</th>
						<th class="kiri">Pertanyaan</th>
						<?php foreach ($tanggal as $i => $tgl): ?>
							<th style="min-width:84px"><?= (int) $tahap['harian'] === 1 ? 'Hari ' . ($i + 1) : 'Saat masuk' ?><small><?= e(ppi_tgl($tgl, 'pendek')) ?></small></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($pertanyaan as $n => $p): ?>
					<tr>
						<td class="kiri"><?= $n + 1 ?></td>
						<td class="kiri"><?= e($p['teks']) ?></td>
						<?php foreach ($tanggal as $tgl):
							$v = isset($peta[$p['id']][$tgl]) ? $peta[$p['id']][$tgl] : '';
							$lab = array('' => '–', 'Y' => 'Ya', 'T' => 'Tidak', 'N' => 'N/A'); ?>
							<td>
								<input type="hidden" name="j[<?= (int) $p['id'] ?>][<?= e($tgl) ?>]" value="<?= e($v) ?>">
								<button type="button" class="sel" data-v="<?= e($v) ?>" aria-label="<?= e($p['teks']) ?>, <?= e(ppi_tgl($tgl)) ?>"><?= $lab[$v] ?></button>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td></td><td class="kiri">Kepatuhan harian</td>
						<?php foreach ($tanggal as $tgl): ?><td><?= e(ppi_pct_teks($persen[$tgl])) ?></td><?php endforeach; ?>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="kepala" style="border-top:1px solid var(--garis);border-bottom:0">
			<span class="catatan">Kepatuhan = jawaban Ya dibagi jawaban Ya dan Tidak. N/A tidak dihitung.</span>
			<button type="submit" class="btn">Simpan audit</button>
		</div>
	<?= form_close() ?>
	<?php endif; ?>
</div>
