<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Periode_model extends CI_Model {

	const TABLE_NAME = 'periode';
	const TABLE_PESERTA = 'peserta';

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function get()
	{
		$query = $this->db->query("SELECT ".self::TABLE_NAME.".*, COUNT(pes_id) AS terisi
									FROM ".self::TABLE_NAME." 
									LEFT JOIN peserta ON ".self::TABLE_NAME.".`per_id`=peserta.`per_id`
									GROUP BY ".self::TABLE_NAME.".`per_id`
									ORDER BY ".self::TABLE_NAME.".per_tahun DESC, ".self::TABLE_NAME.".per_semester DESC;");
		return $query->result_array();
	}

	public function add(
		$nama,
		$semester,
		$tahun,
		$tgl_mulai_pendaftaran,
		$tgl_selesai_pendaftaran,
		$tgl_mulai_pelaksanaan,
		$tgl_selesai_pelaksanaan,
		$tgl_selesai_pasca_pkl,
		$tgl_selesai_pasca_ujian)
	{
		$data = array(
			'per_nama' => $nama,
			'per_semester' => $semester,
			'per_tahun' => $tahun,
			'per_tgl_mulai_pendaftaran' => $tgl_mulai_pendaftaran,
			'per_tgl_selesai_pendaftaran' => $tgl_selesai_pendaftaran,
			'per_tgl_mulai' => $tgl_mulai_pelaksanaan,
			'per_tgl_selesai' => $tgl_selesai_pelaksanaan,
			'per_tgl_selesai_pasca_pkl' => $tgl_selesai_pasca_pkl,
			'per_tgl_selesai_pasca_ujian' => $tgl_selesai_pasca_ujian
			);

		$result = $this->db->insert(self::TABLE_NAME, $data);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Periode_model->add
				Add periode gagal
				- nama:{$nama}
				- semester:{$semester})
				- tahun:{$tahun}
				- tgl_mulai_pendaftaran:{$tgl_mulai_pendaftaran}
				- tgl_selesai_pendaftaran:{$tgl_selesai_pendaftaran}
				- tgl_mulai_pelaksanaan:{$tgl_mulai_pelaksanaan}
				- tgl_selesai_pelaksanaan:{$tgl_selesai_pelaksanaan}
				- tgl_selesai_pasca_pkl:{$tgl_selesai_pasca_pkl}
				- tgl_selesai_pasca_ujian:{$tgl_selesai_pasca_ujian}");
		}
		
		return TRUE;
	}

	public function edit(
		$id,
		$nama,
		$semester,
		$tahun,
		$tgl_mulai_pendaftaran,
		$tgl_selesai_pendaftaran,
		$tgl_mulai_pelaksanaan,
		$tgl_selesai_pelaksanaan,
		$tgl_selesai_pasca_pkl,
		$tgl_selesai_pasca_ujian)
	{
		$data = array(
			'per_nama' => $nama,
			'per_semester' => $semester,
			'per_tahun' => $tahun,
			'per_tgl_mulai_pendaftaran' => $tgl_mulai_pendaftaran,
			'per_tgl_selesai_pendaftaran' => $tgl_selesai_pendaftaran,
			'per_tgl_mulai' => $tgl_mulai_pelaksanaan,
			'per_tgl_selesai' => $tgl_selesai_pelaksanaan,
			'per_tgl_selesai_pasca_pkl' => $tgl_selesai_pasca_pkl,
			'per_tgl_selesai_pasca_ujian' => $tgl_selesai_pasca_ujian
			);

		$this->db->set($data);
		$this->db->where('per_id', $id);
		$result = $this->db->update(self::TABLE_NAME);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Periode_model->edit
				Edit periode gagal
				- nama:{$nama}
				- semester:{$semester})
				- tahun:{$tahun}
				- tgl_mulai_pendaftaran:{$tgl_mulai_pendaftaran}
				- tgl_selesai_pendaftaran:{$tgl_selesai_pendaftaran}
				- tgl_mulai_pelaksanaan:{$tgl_mulai_pelaksanaan}
				- tgl_selesai_pelaksanaan:{$tgl_selesai_pelaksanaan}
				- tgl_selesai_pasca_pkl:{$tgl_selesai_pasca_pkl}
				- tgl_selesai_pasca_ujian:{$tgl_selesai_pasca_ujian}");
		}
		
		return TRUE;
	}

	public function delete($id)
	{
		$result = $this->db->delete(self::TABLE_NAME, array('per_id' => $id));
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Periode_model->delete
				Delete periode gagal
				- id:{$id}");
		}
		
		return TRUE;
	}

	/**
	 * Mendapatkan list periode yang masih membuka pendaftaran.
	 * Acuan yang dipakai adalah waktu sekarang harus berada antara
	 * field per_tgl_mulai_pendaftaran dan field per_tgl_selesai_pendaftaran.
	 */
	public function get_open_register()
	{
		// $this->db->order_by('per_tahun', 'ASC');
		// $this->db->order_by('per_semester', 'ASC');
		// $result = $this->db->get_where(self::TABLE_NAME, array('per_aktif' => 1));
		// return $result->result_array();
		
		$now = date('Y-m-d H:i:s');

		$query = $this->db->query("SELECT * FROM ".self::TABLE_NAME." 
									WHERE '{$now}' BETWEEN per_tgl_mulai_pendaftaran AND DATE_ADD(per_tgl_selesai_pendaftaran, INTERVAL 1 DAY)
									ORDER BY per_tahun DESC, per_semester DESC;");
		return $query->result_array();
	}

	/**
	 * Mendapatkan list periode yang masih membuka pendaftaran
	 * sekaligus periode saat ini yang sedang diikuti peserta.
	 */
	public function get_open_register_include_current($peserta_id)
	{
		$periode_active = $this->get_open_register();

		// Dapatkan periode PKL peserta.
		$this->db->select('periode.*');
		$this->db->from(self::TABLE_NAME);
		$this->db->join(self::TABLE_PESERTA, 'periode.per_id=peserta.per_id');
		$this->db->where('pes_id', $peserta_id);
		$periode_current = $this->db->get()->row_array();

		// Tambahkan periode current ke periode aktive jika data periode belum ada.
		if ($periode_current)
		{
			$ada = FALSE;
			foreach ($periode_active as $value) 
			{
				if ($value['per_id'] === $periode_current['per_id'])
				{
					$ada = TRUE;
					break;
				}
			}

			if ( ! $ada)
			{
				array_push($periode_active, $periode_current);
			}
		}
		
		// var_dump($periode_active);
		return $periode_active;
	}

	public function get_data($id)
	{
		return $this->db->limit(1)->get_where(self::TABLE_NAME, array('per_id' => $id))->row_array();
	}
}