<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inisialisasi extends CI_Controller {

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
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('mahasiswa_model');		
		$this->load->database();

		// Jika mahasiswa telah terdaftar, lemparkan ke homepage mahasiswa.
		if ($this->mahasiswa_model->is_email_exist($this->session->email))
		{
			redirect('mahasiswa');
			return;
		}

		// Rule validasi form.
		$this->form_validation->set_rules('nim', 'NIM', 'trim|required|numeric|is_unique[mahasiswa.mhs_nim]', 
			array(
				'required' => 'Anda harus memasukkan %s ',
				'numeric' => '%s tidak valid, silakan periksa kembali',
				'is_unique' => '%s sudah terdaftar, jika NIM yang Anda masukkan sudah benar mohon hubungi administrator')
			);
		$this->form_validation->set_rules('nama', 'Nama Lengkap', 'trim|required|regex_match[/^[a-zA-Z ]*$/]', 
			array(
				'required' => 'Anda harus memasukkan %s ',
				'regex_match' => '%s tidak valid, hanya diijinkan menggunakan karakter alfabet atau spasi'
				)
			);
		$this->form_validation->set_rules('telepon', 'Nomor Telepon', 'trim|required|numeric', 
			array(
				'required' => 'Anda harus memasukkan %s ',
				'numeric' => '%s tidak valid, hanya diijinkan menggunakan angka')
			);
		$this->form_validation->set_rules('alamat', 'Alamat Asal', 'trim|required', 
			array(
				'required' => 'Anda harus memasukkan %s ')
			);

		// Validasi form.
		if ($this->form_validation->run() == FALSE)
		{
			// Menentukan apakah form dalam kondisi submit error atau dalam kondisi tidak disubmit.
			$data['form_state'] = validation_errors() ? 'error' : '';
		} 
		else
		{
			// Submit data.
			try
			{
				$mahasiswa_id = $this->mahasiswa_model->add(
					$this->session->email, 
					$this->input->post('nim'), 
					$this->input->post('nama'), 
					$this->input->post('telepon'), 
					$this->input->post('alamat'));

				// Set cookie.
				$akun_data = $this->mahasiswa_model->get_data($this->session->email);
				$this->session->nama = $akun_data['mhs_nama'];	
				$this->session->nim = $akun_data['mhs_nim'];
				$this->session->mahasiswa_id = $akun_data['mhs_id'];
				
				// Proses sukses, ucapkan terimakasih.
				$this->session->set_flashdata('inisialisasi_sukses', TRUE);
				redirect('mahasiswa/inisialisasi/terimakasih');
				return;
			}
			catch(Exception $e)
			{
				log_message('error', $e->getMessage());
				$data['form_state'] = 'error';
				$data['submit_errors'] = 'Terjadi kegagalan saat menyimpan data, silakan coba beberapa saat lagi atau hubungi administrator';	
			}
		}

		// Display page.
		$data['page'] = 'mahasiswa';
		$data['title'] = 'Inisialisasi Mahasiswa';
		$this->load->view('templates/header', $data);
		$this->load->view('mahasiswa/inisialisasi', $data);
		$this->load->view('templates/footer', $data);
	}

	public function terimakasih()
	{
		// Halaman ini hanya tampil setelah mahasiswa selesai melakukan inisialisasi.
		if ( ! $this->session->flashdata('inisialisasi_sukses')){
			redirect('mahasiswa');
		}

		// Display page.
		$data['page'] = 'mahasiswa';
		$data['title'] = 'Selamat';
		$this->load->view('templates/header', $data);
		$this->load->view('mahasiswa/inisialisasi_terimakasih', $data);
		$this->load->view('templates/footer', $data);
	}

}