<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_tempat_pkl extends CI_Controller {

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
		$data['title'] = 'Manage Tempat PKL';
		$data['page_admin'] = 'manage_tempat_pkl';

		$this->load->view('templates/header', $data);
		$this->load->view('admin/manage_tempat_pkl', $data);
		$this->load->view('templates/footer', $data);
	}

	public function get($id=NULL)
	{
		$this->load->helper('output_ajax');
		$this->load->model('tempat_model');

		$result = NULL;
		if ($id)
		{
			// Ambil data detail.
			$result = $this->tempat_model->get_id($id);
		}
		else
		{
			// Ambil list data.
			$result = $this->tempat_model->get();
		}
		
		send_ajax_output(200, 'Ok', $result);
		return;
	}

	public function add()
	{
		$this->load->library('form_validation');
		$this->load->model('tempat_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('nama', 'Nama', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('alamat', 'Alamat', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('telepon', 'Telepon', 'trim|required|regex_match[/^[\d\+ ]+$/i]', 
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('kapasitas', 'Kapasitas', 'trim|required|regex_match[/^\d+$/i]', 
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid'
					)
				);
		// $this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[tempat_pkl.tem_user]', 
		// 		array(
		// 			'required' => '%s tidak boleh kosong',
		// 			'is_unique' => '%s sudah digunakan'
		// 			)
		// 		);
		// $this->form_validation->set_rules('password', 'Password', 'required', 
		// 		array('required' => '%s tidak boleh kosong')
		// 		);
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
				$this->tempat_model->add(
					$this->input->post('nama'),
					$this->input->post('alamat'),
					$this->input->post('telepon'),
					$this->input->post('kapasitas'),
					// $this->input->post('username'),
					// $this->input->post('password'),
					NULL,
					NULL,
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
		$this->load->model('tempat_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('id', 'ID', 'required', 
				array('required' => 'Form tidak valid')
				);
		$this->form_validation->set_rules('nama', 'Nama', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('alamat', 'Alamat', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('telepon', 'Telepon', 'trim|required|regex_match[/^[\d\+ ]+$/i]', 
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('kapasitas', 'Kapasitas', 'trim|required|regex_match[/^\d+$/i]', 
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid'
					)
				);
		// $this->form_validation->set_rules('username', 'Username', 'trim|required', 
		// 		array('required' => '%s tidak boleh kosong')
		// 		);
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
				$this->tempat_model->edit(
					$this->input->post('id'),
					$this->input->post('nama'),
					$this->input->post('alamat'),
					$this->input->post('telepon'),
					$this->input->post('kapasitas'),
					// $this->input->post('username'),
					// $this->input->post('password'),
					NULL,
					NULL,
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
		$this->load->model('tempat_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');
		
		try
		{
			$this->tempat_model->delete($this->input->post('id'));

			$result = $this->tempat_model->get();
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