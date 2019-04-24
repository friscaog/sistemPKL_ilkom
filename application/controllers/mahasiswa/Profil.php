<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil extends CI_Controller {

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

		// Rule validasi form.
		$this->form_validation->set_rules('nim', 'NIM', 'trim|required|numeric', 
			array(
				'required' => 'Anda harus memasukkan %s ',
				'numeric' => '%s tidak valid, silakan periksa kembali')
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
				$this->mahasiswa_model->edit(
					$this->session->mahasiswa_id, 
					$this->input->post('nim'), 
					$this->input->post('nama'), 
					$this->input->post('telepon'), 
					$this->input->post('alamat'));

				$data['form_state'] = 'success';
			}
			catch(Exception $e)
			{
				log_message('error', $e->getMessage());
				$data['form_state'] = 'error';
				$data['submit_errors'] = 'Terjadi kegagalan saat menyimpan data, silakan coba beberapa saat lagi atau hubungi administrator';	
			}
		}

		// Tampilkan halaman.
		$data['page'] = 'profil';
		$data['title'] = 'Profil';
		$data['profil'] = $this->mahasiswa_model->get_data($this->session->email);
		$this->load->view('templates/header', $data);
		$this->load->view('mahasiswa/profil', $data);
		$this->load->view('templates/footer', $data);
	}

}