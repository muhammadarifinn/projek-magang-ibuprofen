<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Master pertanyaan bundle (hanya admin). */
class Master extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->izin(array('admin'));
		$this->load->model('Master_model');
	}

	public function index()
	{
		$bundles = $this->Master_model->bundles();
		if (!$bundles) {
			show_error('Belum ada bundle. Isi tabel bundle lewat phpMyAdmin.', 500);
		}
		$bundle_id = (int) $this->input->get('bundle') ?: (int) $bundles[0]['id'];
		$bundle    = $this->Master_model->bundle($bundle_id) ?: $bundles[0];
		$tahap_list = $this->Master_model->tahap_bundle($bundle['id']);

		$tahap = null;
		$tahap_id = (int) $this->input->get('tahap');
		foreach ($tahap_list as $t) {
			if ((int) $t['id'] === $tahap_id) {
				$tahap = $t;
			}
		}
		$tahap = $tahap ?: $tahap_list[0];

		$ubah = null;
		if ($this->input->get('ubah')) {
			$ubah = $this->Master_model->satu_pertanyaan((int) $this->input->get('ubah'));
			if ($ubah && (int) $ubah['tahap_id'] !== (int) $tahap['id']) {
				$ubah = null;
			}
		}

		$this->render('master/index', array(
			'bundles'    => $bundles,
			'bundle'     => $bundle,
			'tahap_list' => $tahap_list,
			'tahap'      => $tahap,
			'pertanyaan' => $this->Master_model->pertanyaan($tahap['id']),
			'ubah'       => $ubah,
		));
	}

	public function simpan()
	{
		if ($this->input->method() !== 'post') {
			redirect('master');
		}
		$tahap = $this->Master_model->tahap((int) $this->input->post('tahap_id'));
		$teks  = trim((string) $this->input->post('teks'));
		if (!$tahap) {
			show_error('Tahap tidak ditemukan.', 400);
		}
		$balik = 'master?bundle=' . $tahap['bundle_id'] . '&tahap=' . $tahap['id'];
		if ($teks === '') {
			$this->session->set_flashdata('galat', 'Teks pertanyaan wajib diisi.');
			redirect($balik);
		}
		$id = (int) $this->input->post('id');
		if ($id) {
			$lama = $this->Master_model->satu_pertanyaan($id);
			if (!$lama || (int) $lama['tahap_id'] !== (int) $tahap['id']) {
				show_error('Pertanyaan tidak ditemukan.', 400);
			}
		}
		$urutan = (int) $this->input->post('urutan');
		if ($urutan < 1) {
			$urutan = count($this->Master_model->pertanyaan($tahap['id'])) + 1;
		}
		$this->Master_model->simpan_pertanyaan(array(
			'tahap_id' => (int) $tahap['id'],
			'teks'     => $teks,
			'urutan'   => $urutan,
			'wajib'    => $this->input->post('wajib') ? 1 : 0,
			'aktif'    => $this->input->post('aktif') ? 1 : 0,
		), $id);
		$this->session->set_flashdata('pesan', $id ? 'Pertanyaan diperbarui. Berlaku untuk audit berikutnya.' : 'Pertanyaan ditambahkan.');
		redirect($balik);
	}

	public function aktif($id = 0)
	{
		if ($this->input->method() !== 'post') {
			redirect('master');
		}
		$p = $this->Master_model->satu_pertanyaan((int) $id);
		if (!$p) {
			show_404();
		}
		$this->Master_model->ubah_aktif($p['id']);
		$tahap = $this->Master_model->tahap($p['tahap_id']);
		redirect('master?bundle=' . $tahap['bundle_id'] . '&tahap=' . $tahap['id']);
	}
}
