<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_periode extends CI_Controller {

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
		$data['title'] = 'Manage Periode';
		$data['page_admin'] = 'manage_periode';

		$this->load->view('templates/header', $data);
		$this->load->view('admin/manage_periode', $data);
		$this->load->view('templates/footer', $data);
	}

	public function get($id=NULL)
	{
		$this->load->helper('output_ajax');
		$this->load->model('periode_model');

		$result = NULL;
		if ($id)
		{
			// Ambil data detail.
			$result = $this->periode_model->get_data($id);

			// Transformasi format tanggal.
			$result['per_tgl_mulai_pendaftaran'] = date('d-m-Y', strtotime($result['per_tgl_mulai_pendaftaran']));
			$result['per_tgl_selesai_pendaftaran'] = date('d-m-Y', strtotime($result['per_tgl_selesai_pendaftaran']));
			$result['per_tgl_mulai'] = date('d-m-Y', strtotime($result['per_tgl_mulai']));
			$result['per_tgl_selesai'] = date('d-m-Y', strtotime($result['per_tgl_selesai']));
			$result['per_tgl_selesai_pasca_pkl'] = date('d-m-Y', strtotime($result['per_tgl_selesai_pasca_pkl']));
			$result['per_tgl_selesai_pasca_ujian'] = date('d-m-Y', strtotime($result['per_tgl_selesai_pasca_ujian']));
		}
		else
		{
			// Ambil list data.
			$result = $this->periode_model->get();
			// Transformasi format tanggal.
			for($i=0; $i<count($result); $i++)
			{
				$result[$i]['per_tgl_mulai_pendaftaran'] = date('d-m-Y', strtotime($result[$i]['per_tgl_mulai_pendaftaran']));
				$result[$i]['per_tgl_selesai_pendaftaran'] = date('d-m-Y', strtotime($result[$i]['per_tgl_selesai_pendaftaran']));
				$result[$i]['per_tgl_mulai'] = date('d-m-Y', strtotime($result[$i]['per_tgl_mulai']));
				$result[$i]['per_tgl_selesai'] = date('d-m-Y', strtotime($result[$i]['per_tgl_selesai']));
				$result[$i]['per_tgl_selesai_pasca_pkl'] = date('d-m-Y', strtotime($result[$i]['per_tgl_selesai_pasca_pkl']));
				$result[$i]['per_tgl_selesai_pasca_ujian'] = date('d-m-Y', strtotime($result[$i]['per_tgl_selesai_pasca_ujian']));
			}
		}
		
		send_ajax_output(200, 'Ok', $result);
		return;
	}

	public function add()
	{
		$this->load->library('form_validation');
		$this->load->model('periode_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('nama', 'Nama', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('semester', 'Semester', 'trim|required|in_list[ganjil,genap]',
				array(
					'required' => '%s tidak boleh kosong',
					'in_list' => '%s tidak valid, nilai yang diijinkan adalah ganjil/genap'
					)
				);
		$this->form_validation->set_rules('tahun', 'Tahun Akademik', 'trim|required|regex_match[/^[\d]{4}\/[\d]{4}$/i]',
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid, contoh nilai valid: 2015/2016'
					)
				);
		$this->form_validation->set_rules('tgl_mulai_pendaftaran', 'Tanggal Mulai Pendaftaran', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('tgl_selesai_pendaftaran', 'Tanggal Selesai Pendaftaran', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('tgl_mulai_pelaksanaan', 'Tanggal Mulai Pelaksanaan', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('tgl_selesai_pelaksanaan', 'Tanggal Selesai Pelaksanaan', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('tgl_selesai_pasca_pkl', 'Tanggal Selesai Pasca PKL', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('tgl_selesai_pasca_ujian', 'Tanggal Selesai Pasca Ujian', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
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
				$this->periode_model->add(
					$this->input->post('nama'),
					$this->input->post('semester'),
					$this->input->post('tahun'),
					date('Y-m-d', strtotime($this->input->post('tgl_mulai_pendaftaran'))),
					date('Y-m-d', strtotime($this->input->post('tgl_selesai_pendaftaran'))),
					date('Y-m-d', strtotime($this->input->post('tgl_mulai_pelaksanaan'))),
					date('Y-m-d', strtotime($this->input->post('tgl_selesai_pelaksanaan'))),
					date('Y-m-d', strtotime($this->input->post('tgl_selesai_pasca_pkl'))),
					date('Y-m-d', strtotime($this->input->post('tgl_selesai_pasca_ujian')))
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
		$this->load->model('periode_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('id', 'ID', 'required', 
				array('required' => 'Form tidak valid')
				);
		$this->form_validation->set_rules('nama', 'Nama', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
		$this->form_validation->set_rules('semester', 'Semester', 'trim|required|in_list[ganjil,genap]',
				array(
					'required' => '%s tidak boleh kosong',
					'in_list' => '%s tidak valid, nilai yang diijinkan adalah ganjil/genap'
					)
				);
		$this->form_validation->set_rules('tahun', 'Tahun Akademik', 'trim|required|regex_match[/^[\d]{4}\/[\d]{4}$/i]',
				array(
					'required' => '%s tidak boleh kosong',
					'regex_match' => '%s tidak valid, contoh nilai valid: 2015/2016'
					)
				);
		$this->form_validation->set_rules('tgl_mulai_pendaftaran', 'Tanggal Mulai Pendaftaran', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('tgl_selesai_pendaftaran', 'Tanggal Selesai Pendaftaran', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('tgl_mulai_pelaksanaan', 'Tanggal Mulai Pelaksanaan', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('tgl_selesai_pelaksanaan', 'Tanggal Selesai Pelaksanaan', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('tgl_selesai_pasca_pkl', 'Tanggal Selesai Pasca PKL', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
					)
				);
		$this->form_validation->set_rules('tgl_selesai_pasca_ujian', 'Tanggal Selesai Pasca Ujian', 'trim|required|callback_tanggal_check',
				array(
					'required' => '%s tidak boleh kosong',
					'tanggal_check' => '%s tidak valid'
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
				$this->periode_model->edit(
					$this->input->post('id'),
					$this->input->post('nama'),
					$this->input->post('semester'),
					$this->input->post('tahun'),
					date('Y-m-d', strtotime($this->input->post('tgl_mulai_pendaftaran'))),
					date('Y-m-d', strtotime($this->input->post('tgl_selesai_pendaftaran'))),
					date('Y-m-d', strtotime($this->input->post('tgl_mulai_pelaksanaan'))),
					date('Y-m-d', strtotime($this->input->post('tgl_selesai_pelaksanaan'))),
					date('Y-m-d', strtotime($this->input->post('tgl_selesai_pasca_pkl'))),
					date('Y-m-d', strtotime($this->input->post('tgl_selesai_pasca_ujian')))
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
		$this->load->model('periode_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');
		
		try
		{
			$this->periode_model->delete($this->input->post('id'));

			// Ambil list data.
			$result = $this->periode_model->get();
			// Transformasi format tanggal.
			for($i=0; $i<count($result); $i++)
			{
				$result[$i]['per_tgl_mulai_pendaftaran'] = date('d-m-Y', strtotime($result[$i]['per_tgl_mulai_pendaftaran']));
				$result[$i]['per_tgl_selesai_pendaftaran'] = date('d-m-Y', strtotime($result[$i]['per_tgl_selesai_pendaftaran']));
				$result[$i]['per_tgl_mulai'] = date('d-m-Y', strtotime($result[$i]['per_tgl_mulai']));
				$result[$i]['per_tgl_selesai'] = date('d-m-Y', strtotime($result[$i]['per_tgl_selesai']));
				$result[$i]['per_tgl_selesai_pasca_pkl'] = date('d-m-Y', strtotime($result[$i]['per_tgl_selesai_pasca_pkl']));
				$result[$i]['per_tgl_selesai_pasca_ujian'] = date('d-m-Y', strtotime($result[$i]['per_tgl_selesai_pasca_ujian']));
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