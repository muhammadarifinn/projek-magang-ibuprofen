<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Laporan cetak per bundle: kolom tanggal hanya hari rawat pasien. */
class Laporan extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('Master_model', 'Audit_model'));
	}

	public function index()
	{
		$bundles = $this->Master_model->bundles();
		$bundle_id = (int) $this->input->get('bundle') ?: (int) $bundles[0]['id'];
		$bundle = $this->Master_model->bundle($bundle_id) ?: $bundles[0];
		$cari = trim((string) $this->input->get('q'));
		$this->render('laporan/index', array(
			'bundles' => $bundles,
			'bundle'  => $bundle,
			'cari'    => $cari,
			'daftar'  => $this->Audit_model->daftar($bundle['id'], $cari, $this->batas_ruangan()),
		));
	}

	public function cetak($id = 0)
	{
		$audit = $this->Audit_model->satu((int) $id);
		if (!$audit) {
			show_404();
		}
		$batas = $this->batas_ruangan();
		if ($batas && $audit['ruangan'] !== $batas) {
			show_error('Audit ini bukan dari ruangan Anda.', 403, 'Akses ditolak');
		}
		$data = $this->Audit_model->laporan($audit, $this->Master_model);
		$data['audit'] = $audit;
		$data['lanskap'] = count($data['tanggal']) > 7;
		$this->load->view('laporan/cetak', $data);
	}
}
