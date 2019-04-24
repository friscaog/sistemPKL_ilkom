<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_admin extends CI_Controller {

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
		$data['title'] = 'Manage Admin';
		$data['page_admin'] = 'manage_admin';

		$this->load->view('templates/header', $data);
		$this->load->view('admin/manage_admin', $data);
		$this->load->view('templates/footer', $data);
	}

	public function get($id=NULL)
	{
		$this->load->helper('output_ajax');
		$this->load->model('admin_model');

		$result = NULL;
		if ($id)
		{
			// Ambil data detail tempat PKL.
			$result = $this->admin_model->get_id($id);
		}
		else
		{
			// Ambil list tempat PKL.
			$result = $this->admin_model->get();
		}
		send_ajax_output(200, 'Ok', $result);
		return;
	}

	public function add()
	{
		$this->load->library('form_validation');
		$this->load->model('admin_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[admin.adm_email_cs]', 
				array(
					'required' => '%s tidak boleh kosong',
					'valid_email' => '%s tidak valid',
					'is_unique' => '%s sudah terdaftar'
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
				$this->admin_model->add($this->input->post('email'), $this->input->post('aktif'));

				$result = $this->admin_model->get();
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

	public function edit()
	{
		$this->load->library('form_validation');
		$this->load->model('admin_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('id', 'ID', 'required', 
				array('required' => 'Form tidak valid')
				);
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email', 
				array(
					'required' => '%s tidak boleh kosong',
					'valid_email' => '%s tidak valid'
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
				$this->admin_model->edit($this->input->post('id'), $this->input->post('email'), $this->input->post('aktif'));

				$result = $this->admin_model->get();
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

	public function delete()
	{
		$this->load->model('admin_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');
		
		try
		{
			$this->admin_model->delete($this->input->post('id'));

			$result = $this->admin_model->get();
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