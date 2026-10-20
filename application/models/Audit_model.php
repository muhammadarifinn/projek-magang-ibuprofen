<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Audit bundle per registrasi pasien, jawaban harian, rekap dashboard, dan data laporan. */
class Audit_model extends CI_Model
{
	public function cari($no_registrasi, $bundle_id)
	{
		return $this->db->where(array('no_registrasi' => $no_registrasi, 'bundle_id' => (int) $bundle_id))->get('audit')->row_array();
	}

	public function satu($id)
	{
		return $this->db->select('a.*, b.kode AS bundle_kode, b.nama AS bundle_nama', FALSE)
			->from('audit a')->join('bundle b', 'b.id = a.bundle_id')
			->where('a.id', (int) $id)->get()->row_array();
	}

	/** Ambil audit milik pasien+bundle; dibuat (dengan salinan data pasien) jika belum ada. */
	public function ambil_atau_buat(array $pasien, $bundle_id, $user_id)
	{
		$ada = $this->cari($pasien['no_registrasi'], $bundle_id);
		if ($ada) {
			$this->perbarui_dari_simrs($ada['id'], $pasien);
			return $ada['id'];
		}
		$this->db->insert('audit', array(
			'no_registrasi' => $pasien['no_registrasi'],
			'bundle_id'     => (int) $bundle_id,
			'no_rm'         => $pasien['no_rm'],
			'nama_pasien'   => $pasien['nama'],
			'ruangan'       => $pasien['ruangan'],
			'tgl_masuk'     => $pasien['tgl_masuk'],
			'tgl_masuk_ri'  => $pasien['tgl_masuk_ri'],
			'tgl_keluar'    => $pasien['tgl_keluar'],
			'status'        => $pasien['tgl_keluar'] ? 'selesai' : 'aktif',
			'created_by'    => (int) $user_id,
			'created_at'    => date('Y-m-d H:i:s'),
		));
		return $this->db->insert_id();
	}

	private function perbarui_dari_simrs($audit_id, array $pasien)
	{
		$this->db->where('id', (int) $audit_id)->update('audit', array(
			'ruangan'      => $pasien['ruangan'],
			'tgl_masuk_ri' => $pasien['tgl_masuk_ri'],
			'tgl_keluar'   => $pasien['tgl_keluar'],
			'status'       => $pasien['tgl_keluar'] ? 'selesai' : 'aktif',
		));
	}

	/** Samakan status audit aktif dengan SIMRS (pasien yang sudah pulang jadi selesai). */
	public function sinkron_aktif(Pasien_model $pasien_model)
	{
		$aktif = $this->db->select('id, no_registrasi')->where('status', 'aktif')->get('audit')->result_array();
		if (!$aktif) {
			return;
		}
		$regs = array_values(array_unique(array_column($aktif, 'no_registrasi')));
		$simrs = $pasien_model->by_registrasi_list($regs);
		foreach ($aktif as $a) {
			if (isset($simrs[$a['no_registrasi']])) {
				$this->perbarui_dari_simrs($a['id'], $simrs[$a['no_registrasi']]);
			}
		}
	}

	/** Peta jawaban: [pertanyaan_id][tanggal] = Y|T|N */
	public function jawaban_map($audit_id)
	{
		$peta = array();
		foreach ($this->db->where('audit_id', (int) $audit_id)->get('audit_jawaban')->result_array() as $j) {
			$peta[$j['pertanyaan_id']][$j['tanggal']] = $j['jawaban'];
		}
		return $peta;
	}

	/**
	 * Simpan jawaban dari form. $post = [pertanyaan_id => [tanggal => Y|T|N|'']].
	 * Hanya pertanyaan aktif tahap ini dan tanggal yang diizinkan yang diterima.
	 */
	public function simpan($audit_id, $tahap_id, array $pertanyaan, array $tanggal_ok, array $post, $user_id)
	{
		$teks = array();
		foreach ($pertanyaan as $p) {
			$teks[(int) $p['id']] = $p['teks'];
		}
		$now = date('Y-m-d H:i:s');
		$this->db->trans_start();
		foreach ($post as $qid => $per_tanggal) {
			$qid = (int) $qid;
			if (!isset($teks[$qid]) || !is_array($per_tanggal)) {
				continue;
			}
			foreach ($per_tanggal as $tgl => $nilai) {
				if (!in_array($tgl, $tanggal_ok, true)) {
					continue;
				}
				if ($nilai === '') {
					$this->db->where(array('audit_id' => (int) $audit_id, 'pertanyaan_id' => $qid, 'tanggal' => $tgl))->delete('audit_jawaban');
					continue;
				}
				if (!in_array($nilai, array('Y', 'T', 'N'), true)) {
					continue;
				}
				$this->db->query(
					'INSERT INTO audit_jawaban (audit_id, pertanyaan_id, tahap_id, tanggal, jawaban, teks_pertanyaan, updated_by, updated_at)
					 VALUES (?, ?, ?, ?, ?, ?, ?, ?)
					 ON DUPLICATE KEY UPDATE jawaban = VALUES(jawaban), updated_by = VALUES(updated_by), updated_at = VALUES(updated_at)',
					array((int) $audit_id, $qid, (int) $tahap_id, $tgl, $nilai, $teks[$qid], (int) $user_id, $now)
				);
			}
		}
		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/** Rekap kepatuhan per bundle untuk satu rentang tanggal. */
	public function kpi($awal, $akhir, $ruangan = null, $bundle_id = 0)
	{
		$sql = "SELECT b.id, b.kode, b.nama,
				COALESCE(SUM(j.jawaban = 'Y'), 0) AS ya,
				COALESCE(SUM(j.jawaban = 'T'), 0) AS tidak
			FROM bundle b
			LEFT JOIN audit a ON a.bundle_id = b.id" . ($ruangan ? ' AND a.ruangan = ?' : '') . "
			LEFT JOIN audit_jawaban j ON j.audit_id = a.id AND j.tanggal BETWEEN ? AND ?
			WHERE b.aktif = 1" . ($bundle_id ? ' AND b.id = ?' : '') . '
			GROUP BY b.id, b.kode, b.nama ORDER BY b.id';
		$param = array();
		if ($ruangan) {
			$param[] = $ruangan;
		}
		$param[] = $awal;
		$param[] = $akhir;
		if ($bundle_id) {
			$param[] = (int) $bundle_id;
		}
		return $this->db->query($sql, $param)->result_array();
	}

	/** Audit yang masih aktif beserta status pengisian hari ini. */
	public function daftar_aktif($ruangan = null, $bundle_id = 0)
	{
		$sql = "SELECT a.id, a.no_registrasi, a.no_rm, a.nama_pasien, a.ruangan, a.tgl_masuk, a.tgl_masuk_ri, b.kode AS bundle,
				(SELECT COUNT(*) FROM audit_jawaban j WHERE j.audit_id = a.id AND j.tanggal = ?) AS terisi_hari_ini
			FROM audit a JOIN bundle b ON b.id = a.bundle_id
			WHERE a.status = 'aktif'" . ($ruangan ? ' AND a.ruangan = ?' : '') . ($bundle_id ? ' AND a.bundle_id = ?' : '') . '
			ORDER BY a.tgl_masuk DESC, a.nama_pasien';
		$param = array(date('Y-m-d'));
		if ($ruangan) {
			$param[] = $ruangan;
		}
		if ($bundle_id) {
			$param[] = (int) $bundle_id;
		}
		return $this->db->query($sql, $param)->result_array();
	}

	/** Daftar audit untuk halaman laporan. */
	public function daftar($bundle_id, $cari = '', $ruangan = null)
	{
		$this->db->from('audit')->where('bundle_id', (int) $bundle_id);
		if ($ruangan) {
			$this->db->where('ruangan', $ruangan);
		}
		if ($cari !== '') {
			$this->db->group_start()->like('no_rm', $cari)->or_like('nama_pasien', $cari)->group_end();
		}
		return $this->db->order_by('tgl_masuk', 'DESC')->limit(100)->get()->result_array();
	}

	/**
	 * Susun data laporan cetak satu audit.
	 * Kolom tanggal HANYA hari pasien dirawat: tgl_masuk sampai tgl_keluar (atau hari ini bila masih dirawat).
	 */
	public function laporan(array $audit, Master_model $master)
	{
		$akhir   = $audit['tgl_keluar'] ?: date('Y-m-d');
		$tanggal = ppi_rentang($audit['tgl_masuk'], $akhir);
		$jawaban = $this->db->where('audit_id', (int) $audit['id'])->get('audit_jawaban')->result_array();

		$peta = array();   // [pertanyaan_id][tanggal] = Y|T|N
		$teks = array();   // teks saat pertama dijawab (salinan)
		foreach ($jawaban as $j) {
			$peta[$j['pertanyaan_id']][$j['tanggal']] = $j['jawaban'];
			if (!isset($teks[$j['pertanyaan_id']])) {
				$teks[$j['pertanyaan_id']] = $j['teks_pertanyaan'];
			}
		}

		$kelompok = array();
		foreach ($master->tahap_bundle($audit['bundle_id']) as $t) {
			$baris = array();
			foreach ($master->pertanyaan($t['id']) as $p) {
				$dijawab = isset($peta[$p['id']]);
				if (!$p['aktif'] && !$dijawab) {
					continue;
				}
				$baris[] = array(
					'id'    => (int) $p['id'],
					'teks'  => $dijawab ? $teks[$p['id']] : $p['teks'],
					'nilai' => $dijawab ? $peta[$p['id']] : array(),
				);
			}
			$kelompok[] = array('tahap' => $t, 'baris' => $baris);
		}

		$ya_hari = array();
		$tidak_hari = array();
		$ya = 0;
		$tidak = 0;
		foreach ($jawaban as $j) {
			if ($j['jawaban'] === 'Y') {
				$ya++;
				$ya_hari[$j['tanggal']] = (isset($ya_hari[$j['tanggal']]) ? $ya_hari[$j['tanggal']] : 0) + 1;
			} elseif ($j['jawaban'] === 'T') {
				$tidak++;
				$tidak_hari[$j['tanggal']] = (isset($tidak_hari[$j['tanggal']]) ? $tidak_hari[$j['tanggal']] : 0) + 1;
			}
		}
		$persen_hari = array();
		foreach ($tanggal as $tgl) {
			$persen_hari[$tgl] = ppi_pct(isset($ya_hari[$tgl]) ? $ya_hari[$tgl] : 0, isset($tidak_hari[$tgl]) ? $tidak_hari[$tgl] : 0);
		}
		return array(
			'tanggal'     => $tanggal,
			'kelompok'    => $kelompok,
			'persen_hari' => $persen_hari,
			'persen_total' => ppi_pct($ya, $tidak),
		);
	}
}
