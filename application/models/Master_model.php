<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Master bundle, tahap, pertanyaan, dan ruangan. */
class Master_model extends CI_Model
{
	public function bundles()
	{
		return $this->db->where('aktif', 1)->order_by('id')->get('bundle')->result_array();
	}

	public function bundle($id)
	{
		return $this->db->where('id', (int) $id)->get('bundle')->row_array();
	}

	public function ruangan()
	{
		return $this->db->order_by('nama')->get('ruangan')->result_array();
	}

	public function tahap_bundle($bundle_id)
	{
		return $this->db->where('bundle_id', (int) $bundle_id)->order_by('urutan')->get('tahap')->result_array();
	}

	public function tahap($id)
	{
		return $this->db->where('id', (int) $id)->get('tahap')->row_array();
	}

	public function tahap_by_kode($bundle_id, $kode)
	{
		return $this->db->where(array('bundle_id' => (int) $bundle_id, 'kode' => $kode))->get('tahap')->row_array();
	}

	/** Semua pertanyaan satu tahap (untuk halaman master). */
	public function pertanyaan($tahap_id)
	{
		return $this->db->where('tahap_id', (int) $tahap_id)->order_by('urutan')->order_by('id')->get('pertanyaan')->result_array();
	}

	/** Hanya yang aktif (untuk form audit). */
	public function pertanyaan_aktif($tahap_id)
	{
		return $this->db->where(array('tahap_id' => (int) $tahap_id, 'aktif' => 1))->order_by('urutan')->order_by('id')->get('pertanyaan')->result_array();
	}

	public function satu_pertanyaan($id)
	{
		return $this->db->where('id', (int) $id)->get('pertanyaan')->row_array();
	}

	/** Tambah (id kosong) atau ubah pertanyaan. */
	public function simpan_pertanyaan(array $data, $id = 0)
	{
		$now = date('Y-m-d H:i:s');
		if ($id) {
			$data['updated_at'] = $now;
			$this->db->where('id', (int) $id)->update('pertanyaan', $data);
			return (int) $id;
		}
		$data['created_at'] = $now;
		$data['updated_at'] = $now;
		$this->db->insert('pertanyaan', $data);
		return $this->db->insert_id();
	}

	/** Pertanyaan tidak dihapus, hanya diaktifkan atau dinonaktifkan. */
	public function ubah_aktif($id)
	{
		$this->db->query('UPDATE pertanyaan SET aktif = 1 - aktif, updated_at = ? WHERE id = ?', array(date('Y-m-d H:i:s'), (int) $id));
	}
}
