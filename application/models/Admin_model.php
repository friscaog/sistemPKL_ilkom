<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {

	const TABLE_NAME = 'admin';

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function get()
	{
		return $this->db->get(self::TABLE_NAME)->result_array();
	}

	public function get_id($id)
	{
		return $this->db->limit(1)->get_where(self::TABLE_NAME, array('adm_id' => $id))->row_array();
	}

	public function get_by_email($email_cs)
	{
		return $this->db->limit(1)->get_where(self::TABLE_NAME, array('adm_email_cs' => $email_cs))->row_array();
	}

	public function get_active_by_email($email_cs)
	{
		return $this->db->limit(1)->get_where(self::TABLE_NAME, array('adm_email_cs' => $email_cs, 'adm_aktif' => 1))->row_array();
	}

	public function is_admin($email_cs)
	{
		return $this->db->limit(1)->get_where(self::TABLE_NAME, array('adm_email_cs' => $email_cs, 'adm_aktif' => 1))->num_rows() > 0;
	}

	public function add($email_cs, $aktif=1)
	{
		$data = array(
			'adm_email_cs' => $email_cs,
			'adm_aktif' => $aktif
			);

		$result = $this->db->insert(self::TABLE_NAME, $data);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Admin_model->add
				Add admin gagal
				- email_cs:{$email_cs}
				- aktif:{$aktif}");
		}
		
		return TRUE;
	}

	public function edit($id, $email_cs, $aktif=1)
	{
		$data = array(
			'adm_email_cs' => $email_cs,
			'adm_aktif' => $aktif
			);

		$this->db->set($data);
		$this->db->where('adm_id', $id);
		$result = $this->db->update(self::TABLE_NAME);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Admin_model->edit
				Edit admin gagal
				- id:{$id}
				- email_cs:{$email_cs}
				- aktif:{$aktif}");
		}
		
		return TRUE;
	}

	public function delete($id)
	{
		$result = $this->db->delete(self::TABLE_NAME, array('adm_id' => $id));
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Admin_model->delete
				Delete admin gagal
				- id:{$id}");
		}
		
		return TRUE;
	}
	
}