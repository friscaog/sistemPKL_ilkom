<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Download extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('url');
	}

	public function index($page=1)
	{
		$this->load->model('berkas_model');
		$this->load->library('pagination_basic');

		$config = array(
			'base_url' => site_url('download/page'),
			'total_rows' => $this->berkas_model->get_active_num(),
			'per_page' => $this->config->item('pkl_download_num_per_page'),
			'current_page' => $page
			);
		$this->pagination_basic->initialize($config);

		$data['berkas_data'] = $this->_get_berkas_data(
			$this->pagination_basic->get_record_offset(), 
			$this->pagination_basic->get_per_page()
			);
		$data['prev_url'] = $this->pagination_basic->get_prev_url();
		$data['next_url'] = $this->pagination_basic->get_next_url();

		$data['title'] = 'Download';
		$data['page'] = 'download';
		$this->load->view('templates/header', $data);
		$this->load->view('download', $data);
		$this->load->view('templates/footer', $data);
	}

	private function _get_berkas_data($offset, $num_per_page)
	{
		$this->load->model('berkas_model');

		$berkas_data = $this->berkas_model->get_active($offset, $num_per_page);

		setlocale (LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');

		$file_path = $this->config->item('pkl_berkas_path');
		foreach ($berkas_data as &$berkas) 
		{
			// Ubah format tanggal publikasi.
			$berkas['ber_tgl_publikasi'] = strftime("%d %B %Y", strtotime($berkas['ber_tgl_publikasi']));

			foreach ($berkas['files'] as &$value)
			{
				// Ubah URL file .
				$value['bf_file'] = base_url().$file_path.$value['bf_file'];
			}
		}

		return $berkas_data;
	}
}