<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('url');

		// Periksa hak akses pengguna.
		if ($this->session->jenis_akun !== 'mahasiswa'){
			redirect('login');
		}
	}

	public function index()
	{
		$this->load->database();
		$this->load->model(array('mahasiswa_model', 'peserta_model'));

		// Redirect ke halaman inisialisasi jika mahasiswa baru pertama kali login.
		if ( ! $this->mahasiswa_model->is_email_exist($this->session->email))
		{
			redirect('mahasiswa/inisialisasi');
			return;
		}		

		// Redirect sesuai dengan posisi tahapan peserta.
		$mahasiswa_data = $this->mahasiswa_model->get_data($this->session->email);
		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($mahasiswa_data['mhs_id']);
		switch ($peserta_data['pes_tahapan']) {
			case $this->config->item('pkl_tahapan_num_pendaftaran'):
				redirect('mahasiswa/pendaftaran');
				break;			
			case $this->config->item('pkl_tahapan_num_pelaksanaan'):
				redirect('mahasiswa/pelaksanaan');
				break;			
			case $this->config->item('pkl_tahapan_num_pasca_pkl'):
				redirect('mahasiswa/pasca_pkl');
				break;	
			case $this->config->item('pkl_tahapan_num_pasca_ujian'):
				redirect('mahasiswa/pasca_ujian');
				break;	
			case $this->config->item('pkl_tahapan_num_selesai'):
				redirect('mahasiswa/selesai');
				break;	
			default:
				redirect('mahasiswa/pendaftaran');
				break;
		}
	}

}