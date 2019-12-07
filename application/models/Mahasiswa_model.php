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
		// $result = $this->db->get_where(self::TABLE_NAME, array('mhs_email_cs' => $email));
		// return $result->row_array();

		$this->db->select('*');
		$this->db->from('mahasiswa');
		$this->db->join('dosen', 'mahasiswa.mhs_dos_pa=dosen.dos_id', 'left');
		$this->db->where('mhs_email_cs', $email);
		
		return $this->db->get()->row_array();
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

	public function edit($id, $nim, $semester, $nama, $telepon, $alamat)
	{
		$data = array(
			'mhs_nim' => $nim,
			'mhs_smt' => $semester,
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

	public function get_periode(){
		$query = $this->db->query('SELECT * FROM periode WHERE per_aktif=1');
        return $query->result();
	}

	public function get_tempat_pkl(){
		$query = $this->db->query('SELECT * from tempat_pkl');
 		return $query->result();
	}

	public function get_dos_pem(){
		$query = $this->db->query('SELECT * from dosen');
 		return $query->result();
	}

	public function get_status(){
		//cek apakah sudah terdaftar di tabel peserta atau belum
		$query = $this->db->query('SELECT * from peserta WHERE mhs_id = '.$this->session->mahasiswa_id.'');
		return count($query->result());
	}

	public function get_data_peserta(){
		$query = $this->db->query('
			SELECT * from peserta
			LEFT JOIN periode USING(per_id)
			LEFT JOIN tempat_pkl USING(tem_id)
			LEFT JOIN dosen ON pes_pembimbing = dos_id
			WHERE mhs_id = '.$this->session->mahasiswa_id.'
			');

 		return $query->result();
	}

}