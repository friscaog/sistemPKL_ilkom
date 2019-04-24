<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_informasi extends CI_Controller {

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
		$data['title'] = 'Manage Informasi';
		$data['page_admin'] = 'manage_informasi';

		$this->load->view('templates/header', $data);
		$this->load->view('admin/manage_informasi', $data);
		$this->load->view('templates/footer', $data);
	}

	public function get($id=NULL)
	{
		$this->load->helper('output_ajax');
		$this->load->model('informasi_model');

		$result = NULL;
		if ($id)
		{
			// Ambil data detail.
			$result = $this->informasi_model->get_id($id);
			// Format tanggal menjadi d-m-Y.
			$result['inf_tgl_publikasi'] = date('d-m-Y', strtotime($result['inf_tgl_publikasi']));
		}
		else
		{
			// Ambil list data.
			$result = $this->informasi_model->get();
			// Format tanggal menjadi d-m-Y.
			for($i=0; $i<count($result);$i++)
			{
				$result[$i]['inf_tgl_publikasi'] = date('d-m-Y', strtotime($result[$i]['inf_tgl_publikasi']));
				$result[$i]['inf_last_modified'] = date('d-m-Y H:i', strtotime($result[$i]['inf_last_modified']));
			}
		}
		
		send_ajax_output(200, 'Ok', $result);
		return;
	}

	public function add()
	{
		$this->load->library('form_validation');
		$this->load->model('informasi_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('judul', 'Judul', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('konten', 'Konten', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('tgl_publikasi', 'Tanggal Publikasi', 'trim|required|callback_tanggal_check', 
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
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
				$this->informasi_model->add(
					$this->input->post('judul'),
					$this->input->post('konten'),
					$this->session->admin_id,
					date('Y-m-d', strtotime($this->input->post('tgl_publikasi'))),
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
		$this->load->model('informasi_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('id', 'ID', 'required', 
				array('required' => 'Form tidak valid')
				);
		$this->form_validation->set_rules('judul', 'Judul', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('konten', 'Konten', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('tgl_publikasi', 'Tanggal Publikasi', 'trim|required|callback_tanggal_check', 
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
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
				$this->informasi_model->edit(
					$this->input->post('id'),
					$this->input->post('judul'),
					$this->input->post('konten'),
					$this->session->admin_id,
					date('Y-m-d', strtotime($this->input->post('tgl_publikasi'))),
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
		$this->load->model('informasi_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');
		
		try
		{
			$this->informasi_model->delete($this->input->post('id'));

			$result = $this->informasi_model->get();
			// Format tanggal menjadi d-m-Y.
			for($i=0; $i<count($result);$i++)
			{
				$result[$i]['inf_tgl_publikasi'] = date('d-m-Y', strtotime($result[$i]['inf_tgl_publikasi']));
				$result[$i]['inf_last_modified'] = date('d-m-Y H:i', strtotime($result[$i]['inf_last_modified']));
			}

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
	
	/**
	 * Mengecek apakah tanggal valid.
	 * Tanggal dalam format dd-mm-YYYY
	 */
	public function tanggal_check($tanggal)
	{
		$date = explode('-', $tanggal);
		return checkdate($date[1], $date[0], $date[2]);
	}
}