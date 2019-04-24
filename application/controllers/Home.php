<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('url');
	}

	public function index($page=1)
	{
		$this->load->model('informasi_model');	
		$this->load->library('pagination_basic');

		$config = array(
			'base_url' => site_url('informasi/page'),
			'total_rows' => $this->informasi_model->get_active_num(),
			'per_page' => $this->config->item('pkl_informasi_num_per_page'),
			'current_page' => $page
			);
		$this->pagination_basic->initialize($config);

		$data['informasi_data'] = $this->_get_informasi_data(
			$this->pagination_basic->get_record_offset(), 
			$this->pagination_basic->get_per_page()
			);
		$data['prev_url'] = $this->pagination_basic->get_prev_url();
		$data['next_url'] = $this->pagination_basic->get_next_url();

		$data['title'] = 'Beranda';
		$data['page'] = 'homepage';
		$this->load->view('templates/header', $data);
		$this->load->view('home', $data);
		$this->load->view('templates/footer', $data);
	}

	private function _get_informasi_data($offset, $num_per_page)
	{
		$this->load->model('informasi_model');	
		
		$informasi_data = $this->informasi_model->get_active($offset, $num_per_page);

		setlocale (LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');

		foreach ($informasi_data as &$informasi) 
		{
			// Ubah format tanggal publikasi.
			$informasi['inf_tgl_publikasi'] = strftime("%d %B %Y", strtotime($informasi['inf_tgl_publikasi']));
		}

		return $informasi_data;
	}
}