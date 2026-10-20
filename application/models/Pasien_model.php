<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Akses data pasien dari SIMRS (hanya baca).
 * Sementara membaca tabel simrs_pasien di MySQL. Untuk pindah ke SQL Server cukup
 * ubah grup 'simrs' di config/database.php dan pemetaan di config/ppi.php.
 */
class Pasien_model extends CI_Model
{
	private $sdb;
	private $tabel;
	private $kolom;

	public function __construct()
	{
		parent::__construct();
		$this->sdb   = $this->load->database($this->config->item('simrs_group'), TRUE);
		$this->tabel = $this->config->item('simrs_table');
		$this->kolom = $this->config->item('simrs_kolom');
	}

	private function dasar()
	{
		$pilih = array();
		foreach ($this->kolom as $alias => $kolom) {
			$pilih[] = $this->sdb->protect_identifiers($kolom) . ' AS ' . $alias;
		}
		$this->sdb->select(implode(', ', $pilih), FALSE)->from($this->tabel);
	}

	/** Pasien yang masih dirawat, dengan pencarian nomor RM / nama dan batas ruangan. */
	public function aktif($cari = '', $ruangan = null, $batas = 50)
	{
		$this->dasar();
		$this->sdb->where($this->kolom['tgl_keluar'] . ' IS NULL', null, FALSE);
		if ($ruangan) {
			$this->sdb->where($this->kolom['ruangan'], $ruangan);
		}
		if ($cari !== '') {
			$this->sdb->group_start()
				->like($this->kolom['no_rm'], $cari)
				->or_like($this->kolom['nama'], $cari)
				->group_end();
		}
		return $this->sdb->order_by($this->kolom['tgl_masuk'], 'DESC')->limit($batas)->get()->result_array();
	}

	public function by_registrasi($no_registrasi)
	{
		$this->dasar();
		$baris = $this->sdb->where($this->kolom['no_registrasi'], $no_registrasi)->get()->row_array();
		return $baris ?: null;
	}

	/** Ambil banyak registrasi sekaligus: [no_registrasi => baris]. */
	public function by_registrasi_list(array $daftar)
	{
		if (!$daftar) {
			return array();
		}
		$this->dasar();
		$hasil = array();
		foreach ($this->sdb->where_in($this->kolom['no_registrasi'], $daftar)->get()->result_array() as $b) {
			$hasil[$b['no_registrasi']] = $b;
		}
		return $hasil;
	}
}
