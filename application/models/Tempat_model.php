<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tempat_model extends CI_Model {

	const TABLE_NAME = 'tempat_pkl';
	const TABLE_PESERTA = 'peserta';
	const TABLE_PERIODE = 'periode';

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function get()
	{
		$query = $this->db->query("SELECT tempat_pkl.*, COUNT(pes_id) AS terisi
									FROM tempat_pkl 
									LEFT JOIN peserta ON tempat_pkl.`tem_id`=peserta.`tem_id`
									GROUP BY tempat_pkl.`tem_id`;");
		return $query->result_array();
	}

	public function get_id($id)
	{
		return $this->db->limit(1)->get_where(self::TABLE_NAME, array('tem_id' => $id))->row_array();
	}

	public function add(
		$nama, 
		$alamat, 
		$telepon, 
		$kapasitas_mhs, 
		$user, 
		$password,
		$aktif=1)
	{
		$data = array(
			'tem_nama' => $nama,
			'tem_alamat' => $alamat,
			'tem_telepon' => $telepon,
			'tem_kapasitas_mhs' => $kapasitas_mhs,
			'tem_user' => $user,
			'tem_password' => ($password) ? md5($password) : NULL,
			'tem_aktif' => $aktif
			);

		$result = $this->db->insert(self::TABLE_NAME, $data);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Tempat_model->add
				Add tempat gagal
				- nama:{$nama}
				- alamat:{$alamat}
				- telepon:{$telepon}
				- kapasitas_mhs:{$kapasitas_mhs}
				- user:{$user}
				- password:{$password}
				- aktif:{$aktif}");
		}
		
		return TRUE;
	}

	public function edit(
		$id,
		$nama, 
		$alamat, 
		$telepon, 
		$kapasitas_mhs, 
		$user, 
		$password,
		$aktif=1)
	{
		$data = array(
			'tem_nama' => $nama,
			'tem_alamat' => $alamat,
			'tem_telepon' => $telepon,
			'tem_kapasitas_mhs' => $kapasitas_mhs,
			'tem_user' => $user,
			'tem_aktif' => $aktif
			);
		// Password diganti.
		if ($password){
			$data['tem_password'] = md5($password);
		}

		$this->db->set($data);
		$this->db->where('tem_id', $id);
		$result = $this->db->update(self::TABLE_NAME);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Tempat_model->edit
				Edit tempat gagal
				- id:{$id}
				- nama:{$nama}
				- alamat:{$alamat}
				- telepon:{$telepon}
				- kapasitas_mhs:{$kapasitas_mhs}
				- user:{$user}
				- password:{$password}
				- aktif:{$aktif}");
		}
		
		return TRUE;
	}

	public function delete($id)
	{
		$result = $this->db->delete(self::TABLE_NAME, array('tem_id' => $id));
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Tempat_model->delete
				Delete tempat gagal
				- id:{$id}");
		}
		
		return TRUE;
	}

	public function getActive()
	{
		$result = $this->db->get_where(self::TABLE_NAME, array('tem_aktif' => 1));
		return $result->result_array();
	}

	public function get_available_include_current($peserta_id)
	{
		// Dapatkan tempat PKL yang tersedia.
		$query = $this->db->query("SELECT tempat_pkl.*
									FROM tempat_pkl 
									LEFT JOIN peserta ON tempat_pkl.`tem_id`=peserta.`tem_id`
									WHERE tempat_pkl.`tem_aktif`=1 
									GROUP BY tempat_pkl.`tem_id`
									HAVING COUNT(pes_id) < tem_kapasitas_mhs
									ORDER BY tempat_pkl.tem_nama ASC");
		$result1 = $query->result_array();

		// Dapatkan tempat PKL yang sedang digunakan 
		$this->db->select('tempat_pkl.*');
		$this->db->from(self::TABLE_NAME);
		$this->db->join(self::TABLE_PESERTA, 'tempat_pkl.tem_id=peserta.tem_id');
		$this->db->where(array('pes_id' => $peserta_id));
		// $this->db->join(self::TABLE_PERIODE, 'peserta.per_id=periode.per_id');
		// $this->db->where(array('pes_id' => $peserta_id, 'per_aktif' => 1));
		$result2 = $this->db->get()->row_array();

		// Jika tempat pKL yang sedang digunakan tidak terdapat dalam tempat PKL yang tersedia, tambahkan.
		if ($result2)
		{
			$ada = FALSE;
			foreach ($result1 as $value) {
				if ($value['tem_id'] === $result2['tem_id'])
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
	 * Mendapatkan daftar tempat PKL yang aktif dan belum memenuhi kuota mahasiswa pada periode tertentu
	 * serta tempat PKL yang sedang dipilih saat ini.
	 * @param  string $periode_id id periode tertentu
	 * @return array              daftar tempat PKL yang aktif dan belum memenuhi kuta mahasiswa
	 */
	public function get_available_in_periode_include_current($periode_id, $peserta_id)
	{
		if ( ! $periode_id)
		{
			return array();
		}

		// Ambil list semua tempat PKL yang aktif.
		$result1 = array();
		$query1 = $this->db->query("SELECT *
									FROM tempat_pkl
									WHERE tem_aktif=1
									ORDER BY tem_nama");
		$result1 = $query1->result_array();

		// Ambil list tempat pkl yang kapasitasnya sudah penuh.
		$periode_id = $this->db->escape($periode_id);
		$query2 = $this->db->query("SELECT tempat_pkl.*
									FROM tempat_pkl 
									LEFT JOIN peserta ON tempat_pkl.`tem_id`=peserta.`tem_id`
									WHERE tempat_pkl.`tem_aktif`=1 AND peserta.`per_id`={$periode_id}
									GROUP BY tempat_pkl.`tem_id`
									HAVING COUNT(pes_id) >= tem_kapasitas_mhs");
		$result2 = $query2->result_array();

		// Dapatkan tempat PKL yang sedang digunakan 
		$this->db->select('tempat_pkl.*');
		$this->db->from('tempat_pkl');
		$this->db->join('peserta', 'tempat_pkl.tem_id=peserta.tem_id');
		$this->db->where(array('pes_id' => $peserta_id));
		$result3 = $this->db->get()->row_array();

		// Hapus tempat PKL jika tempat berada pada result 2.
		$result = array();
		foreach ($result1 as &$res1) {
			$valid = TRUE;
			foreach ($result2 as $res2) {
				if ($res1['tem_id'] === $res2['tem_id'])
				{
					$valid = FALSE;
					break;
				}
			}
			if ($valid)
			{
				$result[] = $res1;
			}
		}

		// Jika tempat pKL yang sedang digunakan tidak terdapat dalam tempat PKL yang tersedia, tambahkan.
		if ( ! empty($result3))
		{
			$ada = FALSE;
			foreach ($result as $value) {
				if ($value['tem_id'] === $result3['tem_id'])
				{
					$ada = TRUE;
					break;
				}
			}
			if ( ! $ada)
			{
				array_push($result, $result3);
			}	
		}

		return $result;
	}
}