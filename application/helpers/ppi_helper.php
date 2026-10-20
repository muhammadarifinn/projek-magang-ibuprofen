<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ppi_rentang')) {
	/** Daftar tanggal Y-m-d dari $mulai sampai $akhir (inklusif). */
	function ppi_rentang($mulai, $akhir)
	{
		$hasil = array();
		if (!$mulai || !$akhir) {
			return $hasil;
		}
		$t = strtotime($mulai);
		$a = strtotime($akhir);
		for (; $t <= $a && count($hasil) < 120; $t = strtotime('+1 day', $t)) {
			$hasil[] = date('Y-m-d', $t);
		}
		return $hasil;
	}
}

if (!function_exists('ppi_tgl')) {
	/** Format tanggal Indonesia. $pola: 'pendek' = 15 Okt, 'panjang' = 15 Okt 2026. */
	function ppi_tgl($tanggal, $pola = 'panjang')
	{
		if (!$tanggal) {
			return '-';
		}
		$bulan = array('', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
		$t = strtotime($tanggal);
		$s = (int) date('j', $t) . ' ' . $bulan[(int) date('n', $t)];
		return $pola === 'panjang' ? $s . ' ' . date('Y', $t) : $s;
	}
}

if (!function_exists('ppi_bulan')) {
	/** 2026-10 -> Oktober 2026 */
	function ppi_bulan($periode)
	{
		$nama = array('', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
		list($y, $m) = explode('-', $periode);
		return $nama[(int) $m] . ' ' . $y;
	}
}

if (!function_exists('ppi_pct')) {
	/** Kepatuhan = Ya / (Ya + Tidak). N/A tidak dihitung. Mengembalikan null jika belum ada data. */
	function ppi_pct($ya, $tidak)
	{
		$ya = (int) $ya;
		$tidak = (int) $tidak;
		return ($ya + $tidak) === 0 ? null : (int) round($ya / ($ya + $tidak) * 100);
	}
}

if (!function_exists('ppi_pct_teks')) {
	function ppi_pct_teks($nilai)
	{
		return $nilai === null ? '–' : $nilai . '%';
	}
}

if (!function_exists('ppi_hari_ke')) {
	/** Hari rawat ke-n, dihitung dari tanggal masuk sampai hari ini (atau tanggal keluar). */
	function ppi_hari_ke($masuk, $keluar = null)
	{
		if (!$masuk) {
			return 0;
		}
		$akhir = $keluar ? strtotime($keluar) : strtotime(date('Y-m-d'));
		return max(1, (int) floor(($akhir - strtotime($masuk)) / 86400) + 1);
	}
}

if (!function_exists('e')) {
	function e($teks)
	{
		return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
	}
}
