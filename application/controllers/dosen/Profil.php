<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('url');

		// Periksa hak akses pengguna.
		if ($this->session->jenis_akun !== 'dosen')
		{
			redirect('login');
		}
	}

	public function index()
	{
		$this->load->model('dosen_model');

		// Data halaman.
		$data['page'] = 'dosen';
		$data['title'] = 'Profil';
		$data['profil'] = $this->dosen_model->get_id($this->session->dosen_id);
		$this->load->view('templates/header', $data);
		$this->load->view('dosen/profil', $data);
		$this->load->view('templates/footer', $data);
	}
}