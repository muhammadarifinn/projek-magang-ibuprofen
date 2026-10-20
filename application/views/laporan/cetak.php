<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan <?= e($audit['bundle_kode']) ?> · <?= e($audit['nama_pasien']) ?></title>
<style>
	@page { size: A4 <?= $lanskap ? 'landscape' : 'portrait' ?>; margin: 12mm; }
	* { box-sizing: border-box; }
	body { margin: 0; font-family: "Times New Roman", Georgia, serif; color: #111; background: #ddd; }
	.bar { padding: 10px 16px; background: #fff; border-bottom: 1px solid #bbb; display: flex; gap: 12px; align-items: center; font-family: system-ui, sans-serif; font-size: 14px; }
	.bar button { min-height: 40px; padding: 0 18px; border: 0; border-radius: 6px; background: #7B4F06; color: #fff; font: inherit; font-weight: 600; cursor: pointer; }
	.kertas { background: #fff; margin: 16px auto; padding: 28px; width: <?= $lanskap ? '1040px' : '760px' ?>; max-width: 100%; }
	.kop { text-align: center; border-bottom: 2px solid #111; padding-bottom: 10px; }
	.kop .rs { font-size: 15px; font-weight: 700; }
	.kop .judul { font-size: 18px; font-weight: 700; margin-top: 2px; }
	.kop .sub { font-size: 13px; margin-top: 2px; }
	.info { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 32px; font-size: 14px; margin: 16px 0; }
	.info div { display: flex; gap: 8px; }
	.info span { width: 120px; color: #444; flex: none; }
	.info b { font-weight: 600; }
	table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
	th, td { border: 1px solid #555; padding: 5px 4px; }
	th { background: #EFEFEF; }
	td.t, th.t { text-align: center; }
	td.kiri { text-align: left; padding-left: 6px; }
	tr.tahap td { background: #F6F1E6; font-weight: 700; }
	.Y { color: #1B6B3A; font-weight: 600; }
	.T { color: #A12B1F; font-weight: 700; background: #FBE4E1; }
	.N { color: #555; }
	tfoot td { background: #EFEFEF; font-weight: 700; }
	.total { margin: 12px 0; font-size: 14px; font-weight: 700; }
	.ttd { display: flex; justify-content: space-between; gap: 32px; margin-top: 24px; font-size: 13px; }
	.ttd div { width: 220px; text-align: center; }
	.ttd .gap { height: 64px; }
	.ttd .garis { border-top: 1px solid #111; padding-top: 4px; }
	@media print { body { background: #fff; } .bar { display: none; } .kertas { margin: 0; padding: 0; width: auto; } }
</style>
</head>
<body>
<div class="bar"><button type="button" onclick="window.print()">Cetak</button><span>Kolom tanggal hanya hari rawat pasien (<?= count($tanggal) ?> hari).</span></div>
<div class="kertas">
	<div class="kop">
		<div class="rs"><?= e($this->config->item('rs_nama')) ?></div>
		<div class="judul">LAPORAN AUDIT KEPATUHAN BUNDLE <?= e(strtoupper($audit['bundle_kode'])) ?></div>
		<div class="sub">Pencegahan dan Pengendalian Infeksi Rumah Sakit</div>
	</div>

	<div class="info">
		<div><span>No. RM</span><b>: <?= e($audit['no_rm']) ?></b></div>
		<div><span>Tanggal masuk</span><b>: <?= e(ppi_tgl($audit['tgl_masuk'])) ?></b></div>
		<div><span>Nama pasien</span><b>: <?= e($audit['nama_pasien']) ?></b></div>
		<div><span>Tanggal keluar</span><b>: <?= $audit['tgl_keluar'] ? e(ppi_tgl($audit['tgl_keluar'])) : 'masih dirawat' ?></b></div>
		<div><span>Ruangan</span><b>: <?= e($audit['ruangan']) ?></b></div>
		<div><span>Lama rawat</span><b>: <?= count($tanggal) ?> hari</b></div>
	</div>

	<table>
		<thead>
			<tr>
				<th class="t" style="width:30px">No</th>
				<th>Pertanyaan</th>
				<?php foreach ($tanggal as $i => $tgl): ?>
					<th class="t" style="width:<?= $lanskap ? 44 : 60 ?>px">H<?= $i + 1 ?><br><small style="font-weight:400"><?= e(ppi_tgl($tgl, 'pendek')) ?></small></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($kelompok as $g): if (!$g['baris']) continue; ?>
			<tr class="tahap"><td colspan="<?= 2 + count($tanggal) ?>">Tahap <?= e($g['tahap']['nama']) ?></td></tr>
			<?php foreach ($g['baris'] as $n => $b): ?>
				<tr>
					<td class="t"><?= $n + 1 ?></td>
					<td class="kiri"><?= e($b['teks']) ?></td>
					<?php foreach ($tanggal as $tgl): $v = isset($b['nilai'][$tgl]) ? $b['nilai'][$tgl] : ''; ?>
						<td class="t <?= e($v) ?>"><?= $v === 'Y' ? 'Ya' : ($v === 'T' ? 'Tidak' : ($v === 'N' ? 'N/A' : '')) ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td></td><td class="kiri">Kepatuhan harian</td>
				<?php foreach ($tanggal as $tgl): ?><td class="t"><?= $persen_hari[$tgl] === null ? '' : (int) $persen_hari[$tgl] . '%' ?></td><?php endforeach; ?>
			</tr>
		</tfoot>
	</table>

	<div class="total">Kepatuhan selama dirawat: <?= e(ppi_pct_teks($persen_total)) ?></div>

	<div class="ttd">
		<div>Petugas audit<div class="gap"></div><div class="garis">( ............................ )</div></div>
		<div>Ketua PPI<div class="gap"></div><div class="garis">( ............................ )</div></div>
	</div>
</div>
</body>
</html>
