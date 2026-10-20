<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dasar semua controller yang butuh login.
 * Peran: admin, ipcn (IPCN/IPCLN), ketua (Ketua PPI), perawat.
 */
class MY_Controller extends CI_Controller
{
	/** @var array data pengguna dari sesi */
	protected $user;

	public function __construct()
	{
		parent::__construct();
		$this->user = $this->session->userdata('user');
		if (!$this->user) {
			redirect('auth/login');
		}
	}

	/** Hentikan jika peran pengguna tidak termasuk daftar. */
	protected function izin(array $peran)
	{
		if (!in_array($this->user['role'], $peran, true)) {
			show_error('Anda tidak punya akses ke halaman ini.', 403, 'Akses ditolak');
		}
	}

	/** IPCN dan perawat yang dipetakan ke satu ruangan hanya boleh melihat ruangannya. */
	protected function batas_ruangan()
	{
		if (in_array($this->user['role'], array('ipcn', 'perawat'), true) && !empty($this->user['ruangan'])) {
			return $this->user['ruangan'];
		}
		return null;
	}

	protected function render($view, array $data = array())
	{
		$data['user']   = $this->user;
		$data['menu']   = $this->uri->segment(1) ?: 'dashboard';
		$data['pesan']  = $this->session->flashdata('pesan');
		$data['galat']  = $this->session->flashdata('galat');
		$this->load->view('layout/header', $data);
		$this->load->view($view, $data);
		$this->load->view('layout/footer', $data);
	}
}
