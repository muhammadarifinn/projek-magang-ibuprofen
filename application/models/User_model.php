<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
	/** Kembalikan data sesi pengguna jika username + kata sandi cocok, selain itu null. */
	public function masuk($username, $password)
	{
		$u = $this->db->select('u.id, u.username, u.password, u.nama, u.role, r.nama AS ruangan', FALSE)
			->from('users u')
			->join('ruangan r', 'r.id = u.ruangan_id', 'left')
			->where('u.username', $username)
			->where('u.aktif', 1)
			->get()->row_array();
		if (!$u || !password_verify($password, $u['password'])) {
			return null;
		}
		unset($u['password']);
		return $u;
	}
}
