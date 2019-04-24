<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mahasiswa_model extends CI_Model {

	const TABLE_NAME = 'mahasiswa';

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function is_email_exist($email)
	{
		return ($this->db->limit(1)->get_where(self::TABLE_NAME, array('mhs_email_cs' => $email))->num_rows() > 0);
	}

	public function is_nim_exist($nim)
	{
		return ($this->db->limit(1)->get_where(self::TABLE_NAME, array('mhs_nim' => $nim))->num_rows() > 0);
	}

	public function get_data($email)
	{
		$result = $this->db->get_where(self::TABLE_NAME, array('mhs_email_cs' => $email));
		return $result->row_array();
	}

	public function add($email_cs, $nim, $nama, $telepon, $alamat)
	{
		$data = array(
			'mhs_email_cs' => $email_cs,
			'mhs_nim' => $nim,
			'mhs_nama' => $nama,
			'mhs_telepon' => $telepon,
			'mhs_alamat' => $alamat);

		if( ! $this->db->insert(self::TABLE_NAME, $data))
		{
			$db_error = $this->db->error();
			throw new Exception('Query error code: '.$db_error['code'].' - Query error message: '.$db_error['message'].' - Invalid query: '.$this->db->last_query());
		}
		return $this->db->insert_id();
	}

	public function edit($id, $nim, $nama, $telepon, $alamat)
	{
		$data = array(
			'mhs_nim' => $nim,
			'mhs_nama' => $nama,
			'mhs_telepon' => $telepon,
			'mhs_alamat' => $alamat);

		$this->db->set($data);
		$this->db->where('mhs_id', $id);
		$result = $this->db->update(self::TABLE_NAME);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Mahasiswa_model->edit
				Edit mahasiswa gagal
				- id:{$id}
				- nim:{$nim}
				- nama:{$nama}
				- telepon:{$telepon})
				- alamat:{$alamat}");
		}
		
		return TRUE;
	}

}