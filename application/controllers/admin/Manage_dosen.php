<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_dosen extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('url');

		// Periksa hak akses pengguna.
		if ( ! $this->session->is_admin){
			redirect('login');
		}
	}

	public function index()
	{
		// Data halaman.
		$data['page'] = 'admin';
		$data['title'] = 'Manage Dosen';
		$data['page_admin'] = 'manage_dosen';

		$this->load->view('templates/header', $data);
		$this->load->view('admin/manage_dosen', $data);
		$this->load->view('templates/footer', $data);
	}

	public function get($id=NULL)
	{
		$this->load->helper('output_ajax');
		$this->load->model('dosen_model');

		$result = NULL;
		if ($id)
		{
			// Ambil data detail.
			$result = $this->dosen_model->get_id($id);
		}
		else
		{
			// Ambil list.
			$periode = ($this->input->get('periode')) ? $this->input->get('periode') : NULL;
			$result = $this->dosen_model->get_with_num_bimbingan($periode);
		}
		
		send_ajax_output(200, 'Ok', $result);
		return;
	}

	public function get_periode()
	{
		$this->load->helper('output_ajax');
		$this->load->model('periode_model');

		$result = $this->periode_model->get();
		foreach ($result as &$value) {
			$value['nama'] = $value['per_nama'].' (TA '.$value['per_tahun'].' semester '.$value['per_semester'].')';
		}

		send_ajax_output(200, 'Ok', $result);
		return;
	}

	public function add()
	{
		$this->load->library('form_validation');
		$this->load->model('dosen_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[dosen.dos_email_cs]', 
				array(
					'required' => '%s tidak boleh kosong',
					'valid_email' => '%s tidak valid',
					'is_unique' => '%s sudah terdaftar'
					)
				);
		$this->form_validation->set_rules('nip', 'NIP', 'trim|required|regex_match[/^[\d ]+$/i]', 
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('nama', 'Nama', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('telepon', 'Telepon', 'trim|required|regex_match[/^[\d\+ ]+$/i]', 
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('alamat', 'Alamat', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('kapasitas', 'Kapasitas', 'trim|required|regex_match[/^\d+$/i]', 
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('aktif', 'Status Aktif', 'trim|required|in_list[1,0]', 
				array(
					'required' => '%s tidak boleh kosong',
					'in_list' => '%s tidak valid'
					)
				);

		if ($this->form_validation->run() === FALSE)
		{
			$errors = validation_errors('|', ' ');
			$errors = explode('|', $errors);
			$errors = array_slice($errors, 1);
			send_ajax_output(400, $errors);
			return;
		}
		else
		{
			try
			{
				// Proses add.
				$this->dosen_model->add(
					$this->input->post('email'),
					$this->input->post('nip'),
					$this->input->post('nama'),
					$this->input->post('telepon'),
					$this->input->post('alamat'),
					$this->input->post('kapasitas'),
					$this->input->post('aktif')
					);	

				send_ajax_output(200, 'Ok');
				return;
			}
			catch (Exception $e)
			{
				log_message('error', $e->getMessage());
				send_ajax_output(500, $this->lang->line('error_operasi_data'));
				return;
			}
		}
	}

	public function edit()
	{
		$this->load->library('form_validation');
		$this->load->model('dosen_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('id', 'ID', 'required', 
				array('required' => 'Form tidak valid')
				);
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email', 
				array(
					'required' => '%s tidak boleh kosong',
					'valid_email' => '%s tidak valid',
					'is_unique' => '%s sudah terdaftar'
					)
				);
		$this->form_validation->set_rules('nip', 'NIP', 'trim|required|regex_match[/^[\d ]+$/i]', 
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('nama', 'Nama', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('telepon', 'Telepon', 'trim|required|regex_match[/^[\d\+ ]+$/i]', 
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('alamat', 'Alamat', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('kapasitas', 'Kapasitas', 'trim|required|regex_match[/^\d+$/i]', 
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('aktif', 'Status Aktif', 'trim|required|in_list[1,0]', 
				array(
					'required' => '%s tidak boleh kosong',
					'in_list' => '%s tidak valid'
					)
				);

		if ($this->form_validation->run() === FALSE)
		{
			$errors = validation_errors('|', ' ');
			$errors = explode('|', $errors);
			$errors = array_slice($errors, 1);
			send_ajax_output(400, $errors);
			return;
		}
		else
		{
			try
			{
				// Proses edit.
				$this->dosen_model->edit(
					$this->input->post('id'),
					$this->input->post('email'),
					$this->input->post('nip'),
					$this->input->post('nama'),
					$this->input->post('telepon'),
					$this->input->post('alamat'),
					$this->input->post('kapasitas'),
					$this->input->post('aktif')
					);	

				send_ajax_output(200, 'Ok');
				return;
			}
			catch (Exception $e)
			{
				log_message('error', $e->getMessage());
				send_ajax_output(500, $this->lang->line('error_operasi_data'));
				return;
			}
		}
	}

	public function delete()
	{
		$this->load->model('dosen_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');
		
		try
		{
			$this->dosen_model->delete($this->input->post('id'));

			$result = $this->dosen_model->get();
			send_ajax_output(200, 'Ok', $result);
			return;
		}
		catch (Exception $e)
		{
			log_message('error', $e->getMessage());
			send_ajax_output(500, $this->lang->line('error_operasi_data'));
			return;
		}
	}


}