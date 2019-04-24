<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Berkas_model extends CI_Model {

	const TABLE_NAME = 'berkas';
	const TABLE_FILE = 'berkas_file';
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
		$this->db->join(self::TABLE_ADMIN, 'berkas.adm_id=admin.adm_id', 'left');
		$data = $this->db->get()->result_array();
		for ($i=0; $i<count($data); $i++)
		{
			$data[$i]['files'] = $this->db->get_where(self::TABLE_FILE, array('ber_id' => $data[$i]['ber_id']))->result_array();
		}
		return $data;
	}

	public function get_active($offset=NULL, $limit=NULL)
	{
		$this->db->select('*');
		$this->db->from(self::TABLE_NAME);
		$this->db->join(self::TABLE_ADMIN, 'berkas.adm_id=admin.adm_id', 'left');
		$this->db->where('berkas.ber_aktif', 1);
		$this->db->order_by('ber_tgl_publikasi', 'DESC');
		$this->db->order_by('ber_last_modified', 'DESC');

		if ($offset!==NULL &&
			$limit!==NULL)
		{
			$this->db->limit($limit, $offset);
		}

		$data = $this->db->get()->result_array();
		for ($i=0; $i<count($data); $i++)
		{
			$data[$i]['files'] = $this->db->get_where(self::TABLE_FILE, array('ber_id' => $data[$i]['ber_id']))->result_array();
		}
		return $data;
	}

	public function get_active_num()
	{
		$this->db->select('*');
		$this->db->from(self::TABLE_NAME);
		$this->db->join(self::TABLE_ADMIN, 'berkas.adm_id=admin.adm_id', 'left');
		$this->db->where('berkas.ber_aktif', 1);
		return $this->db->get()->num_rows();
	}

	public function get_id($id)
	{
		$data = $this->db->limit(1)->get_where(self::TABLE_NAME, array('ber_id' => $id))->row_array();
		if (is_array($data))
		{
			$data['files'] = $this->db->get_where(self::TABLE_FILE, array('ber_id' => $id))->result_array();	
		}		
		return $data;
	}

	public function add(
		$nama,
		$admin_id,
		$tgl_publikasi,
		$aktif=1,
		$file=NULL)
	{
		$now = date('Y-m-d H:i:s');

		$this->db->trans_start();

		// Tambahkan record berkas.
		$data = array(
			'ber_nama' => $nama,
			'adm_id' => $admin_id,
			'ber_tgl_publikasi' => $tgl_publikasi,
			'ber_last_modified' => $now,
			'ber_aktif' => $aktif
			);
		$this->db->insert(self::TABLE_NAME, $data);

		if ($file)
		{
			// Tambahkan record file.
			$berkas_id = $this->db->insert_id();	
			$data2 = array(
				'ber_id' => $berkas_id, 
				'bf_file' => $file,
				'bf_date' => $now
				);	
			$this->db->insert(self::TABLE_FILE, $data2);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			throw new Exception(__FILE__." Berkas_model->add
				Transaction add berkas gagal
				- nama:{$nama}
				- admin_id:{$admin_id}
				- tgl_publikasi:{$tgl_publikasi}
				- aktif:{$aktif}
				- file:{$file}");
		}		
		
		return TRUE;
	}

	public function edit(
		$id,
		$nama,
		$admin_id,
		$tgl_publikasi,
		$aktif=1,
		$file=NULL)
	{
		$now = date('Y-m-d H:i:s');

		$this->db->trans_start();

		// Edit record berkas.
		$data = array(
			'ber_nama' => $nama,
			'adm_id' => $admin_id,
			'ber_tgl_publikasi' => $tgl_publikasi,
			'ber_last_modified' => $now,
			'ber_aktif' => $aktif
			);
		$this->db->set($data);
		$this->db->where('ber_id', $id);
		$this->db->update(self::TABLE_NAME);

		if ($file)
		{
			// Tambahkan record file.
			$data2 = array(
				'ber_id' => $id, 
				'bf_file' => $file,
				'bf_date' => $now
				);	
			$this->db->insert(self::TABLE_FILE, $data2);
		}		

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			throw new Exception(__FILE__." Berkas_model->edit
				Transaction edit berkas gagal
				- id:{$id}
				- nama:{$nama}
				- admin_id:{$admin_id}
				- tgl_publikasi:{$tgl_publikasi}
				- aktif:{$aktif}
				- file:{$file}");
		}	
		
		return TRUE;
	}

	public function delete($id)
	{
		$result = $this->db->delete(self::TABLE_NAME, array('ber_id' => $id));
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Berkas_model->delete
				Delete berkas gagal
				- id:{$id}");
		}
		
		return TRUE;
	}
}