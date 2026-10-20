<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('Master_model', 'Audit_model', 'Pasien_model'));
	}

	public function index()
	{
		$periode = (string) $this->input->get('periode');
		if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
			$periode = date('Y-m');
		}
		$ruangan   = $this->batas_ruangan() ?: ($this->input->get('ruangan') ?: null);
		$bundle_id = (int) $this->input->get('bundle');

		$this->Audit_model->sinkron_aktif($this->Pasien_model);

		$awal  = $periode . '-01';
		$akhir = date('Y-m-t', strtotime($awal));

		$daftar_periode = array();
		for ($i = 0; $i < 12; $i++) {
			$daftar_periode[] = date('Y-m', strtotime('first day of -' . $i . ' month'));
		}

		$this->render('dashboard/index', array(
			'periode'        => $periode,
			'daftar_periode' => $daftar_periode,
			'ruangan'        => $ruangan,
			'terkunci'       => (bool) $this->batas_ruangan(),
			'bundle_id'      => $bundle_id,
			'bundles'        => $this->Master_model->bundles(),
			'daftar_ruangan' => $this->Master_model->ruangan(),
			'kpi'            => $this->Audit_model->kpi($awal, $akhir, $ruangan, $bundle_id),
			'pasien'         => $this->Audit_model->daftar_aktif($ruangan, $bundle_id),
		));
	}
}
