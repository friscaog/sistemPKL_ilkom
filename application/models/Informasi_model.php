<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Informasi_model extends CI_Model {

	const TABLE_NAME = 'informasi';
	const TABLE_ADMIN = 'admin';

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function get()
	{
		$this->db->select('*');
		$this->db->from(self::TABLE_NAME);
		$this->db->join(self::TABLE_ADMIN, 'informasi.adm_id=admin.adm_id', 'left');
		$this->db->order_by('inf_tgl_publikasi', 'DESC');
		return $this->db->get()->result_array();
	}

	public function get_active($offset=NULL, $limit=NULL)
	{
		$this->db->select('*');
		$this->db->from(self::TABLE_NAME);
		$this->db->join(self::TABLE_ADMIN, 'informasi.adm_id=admin.adm_id', 'left');
		$this->db->order_by('inf_tgl_publikasi', 'DESC');
		$this->db->order_by('inf_last_modified', 'DESC');
		$this->db->where('inf_aktif', 1);
		
		if ($offset!==NULL &&
			$limit!==NULL)
		{
			$this->db->limit($limit, $offset);
		}	

		return $this->db->get()->result_array();
	}

	public function get_active_num()
	{
		$this->db->select('*');
		$this->db->from(self::TABLE_NAME);
		$this->db->join(self::TABLE_ADMIN, 'informasi.adm_id=admin.adm_id', 'left');
		$this->db->order_by('inf_tgl_publikasi', 'DESC');
		$this->db->where('inf_aktif', 1);
		return $this->db->get()->num_rows();
	}

	public function get_id($id)
	{
		return $this->db->limit(1)->get_where(self::TABLE_NAME, array('inf_id' => $id))->row_array();
	}

	public function add(
		$judul, 
		$konten,
		$admin_id,
		$tgl_publikasi,
		$aktif=1)
	{
		$data = array(
			'inf_judul' => $judul,
			'inf_konten' => $konten,
			'adm_id' => $admin_id,
			'inf_tgl_publikasi' => $tgl_publikasi,
			'inf_last_modified' => date('Y-m-d H:i:s'),
			'inf_aktif' => $aktif
			);

		$result = $this->db->insert(self::TABLE_NAME, $data);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Informasi_model->add
				Add informasi gagal
				- judul:{$judul}
				- konten:{$konten}
				- admin_id:{$admin_id}
				- tgl_publikasi:{$tgl_publikasi}
				- aktif:{$aktif}");
		}
		
		return TRUE;
	}

	public function edit(
		$id,
		$judul, 
		$konten,
		$admin_id,
		$tgl_publikasi,
		$aktif=1)
	{
		$data = array(
			'inf_judul' => $judul,
			'inf_konten' => $konten,
			'adm_id' => $admin_id,
			'inf_tgl_publikasi' => $tgl_publikasi,
			'inf_last_modified' => date('Y-m-d H:i:s'),
			'inf_aktif' => $aktif
			);

		$this->db->set($data);
		$this->db->where('inf_id', $id);
		$result = $this->db->update(self::TABLE_NAME);
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Informasi_model->edit
				Edit informasi gagal
				- id:{$id}
				- judul:{$judul}
				- konten:{$konten}
				- admin_id:{$admin_id}
				- tgl_publikasi:{$tgl_publikasi}
				- aktif:{$aktif}");
		}
		
		return TRUE;
	}

	public function delete($id)
	{
		$result = $this->db->delete(self::TABLE_NAME, array('inf_id' => $id));
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Informasi_model->delete
				Delete informasi gagal
				- id:{$id}");
		}
		
		return TRUE;
	}
}