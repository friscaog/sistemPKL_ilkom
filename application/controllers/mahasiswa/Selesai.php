<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Selesai extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('url');
		$this->load->model(array('peserta_model'));

		// Periksa hak akses pengguna.
		if ($this->session->jenis_akun !== 'mahasiswa')
		{
			redirect('login');
		}

		// Periksa tahapan peserta saat ini.
		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa(
			$this->session->mahasiswa_id
			);
		if ( ! $peserta_data || $peserta_data['pes_tahapan'] < $this->config->item('pkl_tahapan_num_selesai'))
		{
			redirect('mahasiswa');
		}
	}

	public function index()
	{
		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($this->session->mahasiswa_id);

		// Data halaman.
		$data['page'] = 'mahasiswa';
		$data['title'] = 'Selesai';
		$data['tahapan'] = 4;
		$data['maks_tahapan'] = $peserta_data['pes_tahapan'];

		$this->load->view('templates/header', $data);
		$this->load->view('mahasiswa/selesai', $data);
		$this->load->view('templates/footer', $data);
	}

}