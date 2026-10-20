<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Form audit bundle harian (admin dan IPCN/IPCLN). */
class Audit extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->izin(array('admin', 'ipcn'));
		$this->load->model(array('Master_model', 'Audit_model', 'Pasien_model'));
	}

	/** Tanggal yang boleh diisi untuk satu tahap. IGD: hari masuk. Rawat Inap: tiap hari rawat. */
	private function tanggal_tahap(array $pasien, array $tahap)
	{
		if ((int) $tahap['harian'] === 1) {
			if (!$pasien['tgl_masuk_ri']) {
				return array();
			}
			return ppi_rentang($pasien['tgl_masuk_ri'], $pasien['tgl_keluar'] ?: date('Y-m-d'));
		}
		return array($pasien['tgl_masuk']);
	}

	private function muat_pasien($no_registrasi)
	{
		$pasien = $this->Pasien_model->by_registrasi($no_registrasi);
		if (!$pasien) {
			show_error('Pasien tidak ditemukan di SIMRS.', 404);
		}
		$batas = $this->batas_ruangan();
		if ($batas && $pasien['ruangan'] !== $batas) {
			show_error('Pasien ini bukan dari ruangan Anda.', 403, 'Akses ditolak');
		}
		return $pasien;
	}

	public function index()
	{
		$bundles = $this->Master_model->bundles();
		$bundle_id = (int) $this->input->get('bundle') ?: (int) $bundles[0]['id'];
		$bundle = $this->Master_model->bundle($bundle_id) ?: $bundles[0];
		$reg = (string) $this->input->get('reg');

		if ($reg === '') {
			$cari = trim((string) $this->input->get('q'));
			return $this->render('audit/pilih', array(
				'bundles' => $bundles,
				'bundle'  => $bundle,
				'cari'    => $cari,
				'pasien'  => $this->Pasien_model->aktif($cari, $this->batas_ruangan()),
			));
		}

		$pasien    = $this->muat_pasien($reg);
		$tahap_list = $this->Master_model->tahap_bundle($bundle['id']);
		$kode = (string) $this->input->get('tahap');
		if ($kode === '') {
			$kode = $pasien['tgl_masuk_ri'] ? 'RI' : 'IGD';
		}
		$tahap = null;
		foreach ($tahap_list as $t) {
			if ($t['kode'] === $kode) {
				$tahap = $t;
			}
		}
		$tahap = $tahap ?: $tahap_list[0];

		$tanggal    = $this->tanggal_tahap($pasien, $tahap);
		$pertanyaan = $this->Master_model->pertanyaan_aktif($tahap['id']);
		$audit      = $this->Audit_model->cari($pasien['no_registrasi'], $bundle['id']);
		$peta       = $audit ? $this->Audit_model->jawaban_map($audit['id']) : array();

		$ya = array();
		$tidak = array();
		foreach ($pertanyaan as $p) {
			foreach ($tanggal as $tgl) {
				$v = isset($peta[$p['id']][$tgl]) ? $peta[$p['id']][$tgl] : '';
				if ($v === 'Y') {
					$ya[$tgl] = (isset($ya[$tgl]) ? $ya[$tgl] : 0) + 1;
				} elseif ($v === 'T') {
					$tidak[$tgl] = (isset($tidak[$tgl]) ? $tidak[$tgl] : 0) + 1;
				}
			}
		}
		$persen = array();
		foreach ($tanggal as $tgl) {
			$persen[$tgl] = ppi_pct(isset($ya[$tgl]) ? $ya[$tgl] : 0, isset($tidak[$tgl]) ? $tidak[$tgl] : 0);
		}

		$this->render('audit/form', array(
			'bundles'    => $bundles,
			'bundle'     => $bundle,
			'pasien'     => $pasien,
			'tahap_list' => $tahap_list,
			'tahap'      => $tahap,
			'tanggal'    => $tanggal,
			'pertanyaan' => $pertanyaan,
			'peta'       => $peta,
			'persen'     => $persen,
		));
	}

	public function simpan()
	{
		if ($this->input->method() !== 'post') {
			redirect('audit');
		}
		$bundle = $this->Master_model->bundle((int) $this->input->post('bundle'));
		$pasien = $this->muat_pasien((string) $this->input->post('reg'));
		$tahap  = $bundle ? $this->Master_model->tahap_by_kode($bundle['id'], (string) $this->input->post('tahap')) : null;
		if (!$bundle || !$tahap) {
			show_error('Bundle atau tahap tidak valid.', 400);
		}
		$tanggal = $this->tanggal_tahap($pasien, $tahap);
		$post    = $this->input->post('j');

		$audit_id = $this->Audit_model->ambil_atau_buat($pasien, $bundle['id'], $this->user['id']);
		$ok = $this->Audit_model->simpan(
			$audit_id,
			$tahap['id'],
			$this->Master_model->pertanyaan_aktif($tahap['id']),
			$tanggal,
			is_array($post) ? $post : array(),
			$this->user['id']
		);
		if ($ok) {
			$this->session->set_flashdata('pesan', 'Audit ' . $bundle['kode'] . ' (' . $tahap['nama'] . ') tersimpan.');
		} else {
			$this->session->set_flashdata('galat', 'Audit gagal disimpan. Coba lagi.');
		}
		redirect('audit?bundle=' . $bundle['id'] . '&reg=' . rawurlencode($pasien['no_registrasi']) . '&tahap=' . $tahap['kode']);
	}
}
