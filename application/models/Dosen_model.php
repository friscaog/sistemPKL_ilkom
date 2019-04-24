<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dosen_model extends CI_Model {

	const TABLE_DOSEN = 'dosen';
	const TABLE_PESERTA = 'peserta';
	const TABLE_PERIODE = 'periode';

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function get()
	{
		$query = $this->db->query("SELECT * FROM dosen;");
		return $query->result_array();
	}

	public function get_id($id)
	{
		return $this->db->limit(1)->get_where(self::TABLE_DOSEN, array('dos_id' => $id))->row_array();
	}

	public function get_by_email($email)
	{
		return $this->db->limit(1)->get_where(self::TABLE_DOSEN, array('dos_email_cs' => $email))->row_array();
	}

	public function is_dosen($email)
	{
		return $this->db->limit(1)->get_where(self::TABLE_DOSEN, array('dos_email_cs' => $email))->num_rows() > 0;
	}

	public function add(
		$email_cs,
		$nip,
		$nama,
		$telepon,
		$alamat,
		$kapasitas,
		$aktif=1)
	{
		$data = array(
			'dos_email_cs' => $email_cs,
			'dos_nip' => $nip,
			'dos_nama' => $nama,
			'dos_telepon' => $telepon,
			'dos_alamat' => $alamat,
			'dos_kapasitas_mhs' => $kapasitas,
			'dos_aktif' => $aktif
			);

		$result = $this->db->insert(self::TABLE_DOSEN, $data);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Dosen_model->add
				Add dosen gagal
				- email_cs:{$email_cs}
				- nip:{$nip}
				- nama:{$nama}
				- telepon:{$telepon}
				- alamat:{$alamat}
				- kapasitas:{$kapasitas}				
				- aktif:{$aktif}");
		}
		
		return TRUE;
	}

	public function edit(
		$id,
		$email_cs,
		$nip,
		$nama,
		$telepon,
		$alamat,
		$kapasitas,
		$aktif=1)
	{
		$data = array(
			'dos_email_cs' => $email_cs,
			'dos_nip' => $nip,
			'dos_nama' => $nama,
			'dos_telepon' => $telepon,
			'dos_alamat' => $alamat,
			'dos_kapasitas_mhs' => $kapasitas,
			'dos_aktif' => $aktif
			);

		$this->db->set($data);
		$this->db->where('dos_id', $id);
		$result = $this->db->update(self::TABLE_DOSEN);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Dosen_model->edit
				Edit dosen gagal
				- id:{$id}
				- email_cs:{$email_cs}
				- nip:{$nip}
				- nama:{$nama}
				- telepon:{$telepon}
				- alamat:{$alamat}
				- kapasitas:{$kapasitas}				
				- aktif:{$aktif}");
		}
		
		return TRUE;
	}

	public function delete($id)
	{
		$result = $this->db->delete(self::TABLE_DOSEN, array('dos_id' => $id));
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Dosen_model->delete
				Delete dosen gagal
				- id:{$id}");
		}
		
		return TRUE;
	}

	public function getActive()
	{
		$result = $this->db->get_where('dosen', array('dos_aktif' => 1));
		return $result->result_array();
	}

	public function get_available_include_current($peserta_id)
	{
		// Dapatkan tempat PKL yang tersedia.
		$query = $this->db->query("SELECT dosen.*
									FROM dosen 
									LEFT JOIN peserta ON dosen.`dos_id`=peserta.`pes_pembimbing`
									WHERE dosen.`dos_aktif`=1 
									GROUP BY dosen.`dos_id`
									HAVING COUNT(pes_id) < dos_kapasitas_mhs
									ORDER BY dosen.dos_nama");
		$result1 = $query->result_array();

		// Dapatkan tempat PKL yang sedang digunakan.
		// Tidak dilakukan filter periode aktif. 
		$this->db->select('dosen.*');
		$this->db->from(self::TABLE_DOSEN);
		$this->db->join(self::TABLE_PESERTA, 'dosen.dos_id=peserta.pes_pembimbing');
		$this->db->where(array('pes_id' => $peserta_id));
		// $this->db->join(self::TABLE_PERIODE, 'peserta.per_id=periode.per_id');
		// $this->db->where(array('pes_id' => $peserta_id, 'per_aktif' => 1));
		$result2 = $this->db->get()->row_array();

		// Jika tempat pKL yang sedang digunakan tidak terdapat dalam tempat PKL yang tersedia, tambahkan.
		if ($result2)
		{
			$ada = FALSE;
			foreach ($result1 as $value) {
				if ($value['dos_id'] === $result2['dos_id'])
				{
					$ada = TRUE;
					break;
				}
			}
			if ( ! $ada)
			{
				array_push($result1, $result2);
			}	
		}		

		return $result1;
	}

	/**
	 * Mendapatkan daftar dosen dengan tambahan informasi jumlah mahasiswa bimbingan di suatu periode tertentu
	 * @param  string $periode_id id periode
	 * @return array              Daftar dosen dengan tambahan informasi jumlah mahasiswa bimbingan.
	 */
	public function get_with_num_bimbingan($periode_id=NULL)
	{
		// Ambil list semua dosen yang aktif.
		$result1 = array();
		$query1 = $this->db->query("SELECT dosen.*, '0' AS bimbingan
									FROM dosen
									WHERE dos_aktif=1
									ORDER BY dos_nama");
		$result1 = $query1->result_array();

		// Ambil list semua dosen sekaligus menghitung jumlah mahasiswa bimbingan di periode tertentu.		
		$and_where_periode = '';
		if ($periode_id)
		{
			$and_where_periode = " AND peserta.`per_id`=".$this->db->escape($periode_id)." ";
		}
		
		$query2 = $this->db->query("SELECT dosen.*, COUNT(pes_id) AS bimbingan
									FROM dosen 
									LEFT JOIN peserta ON dosen.`dos_id`=peserta.`pes_pembimbing`
									WHERE dosen.`dos_aktif`=1 {$and_where_periode}										
									GROUP BY dosen.`dos_id`
									ORDER BY dosen.dos_nama");
		$result2 = $query2->result_array();

		// Gabungkan result 1 dan result 2;
		// Jika dosen ada pada result 2, gunakan data dosen pada result 2.
		foreach ($result1 as &$res1) {
			foreach ($result2 as $res2) {
				if ($res1['dos_id'] === $res2['dos_id'])
				{
					$res1 = $res2;
					break;
				}
			}
		}

		return $result1;
	}

}