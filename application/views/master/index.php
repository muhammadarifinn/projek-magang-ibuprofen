<?php $url_dasar = 'master?bundle=' . (int) $bundle['id']; ?>
<div>
	<h1>Master Pertanyaan Bundle</h1>
	<p class="sub">Pertanyaan dikelola per bundle dan per tahap. Perubahan teks berlaku untuk audit berikutnya, audit yang sudah tersimpan tidak berubah.</p>
</div>

<div class="dua">
	<div class="kotak daftar">
		<h2>Bundle</h2>
		<?php foreach ($bundles as $b): ?>
			<a href="<?= site_url('master?bundle=' . (int) $b['id']) ?>" class="<?= (int) $b['id'] === (int) $bundle['id'] ? 'on' : '' ?>"><b><?= e($b['kode']) ?></b><small><?= e($b['nama']) ?></small></a>
		<?php endforeach; ?>
	</div>

	<div class="kanan">
		<div class="kotak">
			<div class="kepala"><h2>Pertanyaan <?= e($bundle['kode']) ?></h2></div>
			<div class="tab" role="tablist">
				<?php foreach ($tahap_list as $t): ?>
					<a role="tab" href="<?= site_url($url_dasar . '&tahap=' . (int) $t['id']) ?>" class="<?= (int) $t['id'] === (int) $tahap['id'] ? 'on' : '' ?>"><b><?= e($t['nama']) ?></b> <span><?= e($t['keterangan']) ?></span></a>
				<?php endforeach; ?>
			</div>
			<div class="geser">
				<table class="tb">
					<thead><tr><th>Urut</th><th>Teks pertanyaan</th><th>Wajib</th><th>Status</th><th>Aksi</th></tr></thead>
					<tbody>
					<?php foreach ($pertanyaan as $p): ?>
						<tr>
							<td><?= (int) $p['urutan'] ?></td>
							<td><strong><?= e($p['teks']) ?></strong></td>
							<td><?= $p['wajib'] ? 'Ya' : 'Tidak' ?></td>
							<td><?= $p['aktif'] ? '<span class="chip ok">Aktif</span>' : '<span class="chip mati">Nonaktif</span>' ?></td>
							<td style="white-space:nowrap">
								<a class="btn pol kecil" href="<?= site_url($url_dasar . '&tahap=' . (int) $tahap['id'] . '&ubah=' . (int) $p['id']) ?>#form">Ubah</a>
								<?= form_open('master/aktif/' . (int) $p['id'], array('style' => 'display:inline')) ?>
									<button type="submit" class="btn pol kecil"><?= $p['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
								<?= form_close() ?>
							</td>
						</tr>
					<?php endforeach; ?>
					<?php if (!$pertanyaan): ?><tr><td colspan="5" class="catatan">Belum ada pertanyaan di tahap ini.</td></tr><?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="kotak" id="form">
			<div class="kepala"><h2><?= $ubah ? 'Ubah pertanyaan' : 'Tambah pertanyaan' ?></h2></div>
			<div class="badan">
				<?= form_open('master/simpan', array('style' => 'display:flex;flex-direction:column;gap:16px')) ?>
					<input type="hidden" name="id" value="<?= $ubah ? (int) $ubah['id'] : 0 ?>">
					<input type="hidden" name="tahap_id" value="<?= (int) $tahap['id'] ?>">
					<div style="display:flex;flex-wrap:wrap;gap:16px">
						<div class="f" style="flex:1 1 200px"><span class="catatan">Bundle</span><div class="catatan" style="color:var(--teks);padding:9px 0"><?= e($bundle['kode']) ?></div></div>
						<div class="f" style="flex:1 1 200px"><span class="catatan">Tahap</span><div class="catatan" style="color:var(--teks);padding:9px 0"><?= e($tahap['nama']) ?> (<?= e($tahap['keterangan']) ?>)</div></div>
						<label class="f" style="flex:0 1 120px">Urutan<input type="number" name="urutan" min="1" value="<?= $ubah ? (int) $ubah['urutan'] : count($pertanyaan) + 1 ?>"></label>
					</div>
					<label class="f">Teks pertanyaan<textarea name="teks" rows="3" required><?= $ubah ? e($ubah['teks']) : '' ?></textarea></label>
					<div class="baris" style="align-items:center">
						<div style="display:flex;flex-wrap:wrap;gap:24px">
							<label style="display:flex;align-items:center;gap:8px;min-height:44px"><input type="checkbox" name="wajib" value="1" <?= (!$ubah || $ubah['wajib']) ? 'checked' : '' ?>> Wajib diisi</label>
							<label style="display:flex;align-items:center;gap:8px;min-height:44px"><input type="checkbox" name="aktif" value="1" <?= (!$ubah || $ubah['aktif']) ? 'checked' : '' ?>> Aktif</label>
						</div>
						<div style="display:flex;gap:8px">
							<?php if ($ubah): ?><a class="btn pol" href="<?= site_url($url_dasar . '&tahap=' . (int) $tahap['id']) ?>">Batal</a><?php endif; ?>
							<button type="submit" class="btn">Simpan</button>
						</div>
					</div>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
