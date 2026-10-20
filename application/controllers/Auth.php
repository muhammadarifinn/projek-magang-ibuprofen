<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model');
	}

	public function login()
	{
		if ($this->session->userdata('user')) {
			redirect('dashboard');
		}
		$galat = '';
		if ($this->input->method() === 'post') {
			$user = $this->User_model->masuk(trim((string) $this->input->post('username')), (string) $this->input->post('password'));
			if ($user) {
				$this->session->sess_regenerate(TRUE);
				$this->session->set_userdata('user', $user);
				redirect('dashboard');
			}
			$galat = 'Nama pengguna atau kata sandi salah.';
		}
		$this->load->view('auth/login', array('galat' => $galat));
	}

	public function logout()
	{
		$this->session->unset_userdata('user');
		$this->session->sess_destroy();
		redirect('auth/login');
	}
}
