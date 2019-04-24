<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_berkas extends CI_Controller {

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
		$data['title'] = 'Manage Berkas';
		$data['page_admin'] = 'manage_berkas';

		$this->load->view('templates/header', $data);
		$this->load->view('admin/manage_berkas', $data);
		$this->load->view('templates/footer', $data);
	}

	public function get($id=NULL)
	{
		$this->load->helper('output_ajax');
		$this->load->model('berkas_model');

		$result = NULL;
		if ($id)
		{
			// Ambil data detail.
			$result = $this->berkas_model->get_id($id);
			$this->_prepare_data_berkas($result);
		}
		else
		{
			// Ambil list data.
			$result = $this->berkas_model->get();
			for ($i=0; $i<count($result); $i++)
			{
				$this->_prepare_data_berkas($result[$i]);
			}
		}
		
		send_ajax_output(200, 'Ok', $result);
		return;
	}

	public function add()
	{
		$this->load->library(array('form_validation', 'upload'));
		$this->load->model('berkas_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('nama', 'Nama', 'trim|required', 
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
			// Proses upload file.	
			$uploaded_files = array();
			$submit_errors = array();
			$file_name = strtolower(url_title($this->input->post('nama')));

			if (isset($_FILES['file']) && $_FILES['file']['name'])
			{
				$config['file_name']			= $file_name;
		    	$config['upload_path']          = $this->config->item('pkl_berkas_path');
		    	$config['allowed_types']        = $this->config->item('pkl_berkas_allowed_types');
		    	$config['allowed_types']        = $this->config->item('pkl_berkas_allowed_types');
		        $this->upload->initialize($config, TRUE);
		        if ($this->upload->do_upload('file'))
		        {
		        	$uploaded_files['file'] = $this->upload->data();
		        }
		        else
		        {
		        	$submit_errors['file'] ='File Berkas:'.$this->upload->display_errors(' ',' ');
		        }
			}
			else
			{
				$submit_errors['file'] ='File Berkas tidak boleh kosong';
			}

			// Jika semua file berhasil diupload, simpan data ke database.
	        if (empty($submit_errors))
	        {
	        	try
				{
					// Proses add.
					$this->berkas_model->add(
						$this->input->post('nama'),
						$this->session->admin_id,
						date('Y-m-d', strtotime($this->input->post('tgl_publikasi'))),
						$this->input->post('aktif'),
						(isset($uploaded_files['file'])) ? $uploaded_files['file']['file_name']:''
						);
				}
				catch (Exception $e)
				{
					log_message('error', $e->getMessage());
					$submit_errors['insert_db'] = $this->lang->line('error_operasi_data');
					
				}
	        }

	        if ($submit_errors)
	        {
	        	// Proses upload atau proses submit gagal. Hapus file-file yang sudah terupload.
	        	foreach ($uploaded_files as $file) {
	        		$this->_unlink($file['full_path']);
	        	}

	        	$errors = array();
	        	foreach($submit_errors as $key => $value)
	        	{
	        		$errors[] = $value;
	        	}
	        	send_ajax_output(500, $errors);
				return;
	        }
	        else 
	        {
	        	// Seluruh proses sukses.
	        	send_ajax_output(200, 'Ok');
				return;
	        }			
		}
	}

	public function edit()
	{
		$this->load->library(array('form_validation', 'upload'));
		$this->load->model('berkas_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		$this->form_validation->set_rules('id', 'ID', 'required', 
				array('required' => 'Form tidak valid')
				);
		$this->form_validation->set_rules('nama', 'Nama', 'trim|required', 
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
			// Proses upload file.	
			$uploaded_files = array();
			$submit_errors = array();
			$file_name = strtolower(url_title($this->input->post('nama')));

			if (isset($_FILES['file']) && $_FILES['file']['name'])
			{
				$config['file_name']			= $file_name;
		    	$config['upload_path']          = $this->config->item('pkl_berkas_path');
		    	$config['allowed_types']        = $this->config->item('pkl_berkas_allowed_types');
		        $this->upload->initialize($config, TRUE);
		        if ($this->upload->do_upload('file'))
		        {
		        	$uploaded_files['file'] = $this->upload->data();
		        }
		        else
		        {
		        	$submit_errors['file'] = 'File Berkas:'.$this->upload->display_errors(' ',' ');
		        }
			}

			// Jika semua file berhasil diupload, simpan data ke database.
	        if (empty($submit_errors))
	        {
	        	try
				{
					// Proses edit.
					$this->berkas_model->edit(
						$this->input->post('id'),
						$this->input->post('nama'),
						$this->session->admin_id,
						date('Y-m-d', strtotime($this->input->post('tgl_publikasi'))),
						$this->input->post('aktif'),
						(isset($uploaded_files['file'])) ? $uploaded_files['file']['file_name']:''
						);
				}
				catch (Exception $e)
				{
					log_message('error', $e->getMessage());
					$submit_errors['insert_db'] = $this->lang->line('error_operasi_data');					
				}
	        }

	        if ($submit_errors)
	        {
	        	// Proses upload atau proses submit gagal. Hapus file-file yang sudah terupload.
	        	foreach ($uploaded_files as $file) {
	        		$this->_unlink($file['full_path']);
	        	}

	        	$errors = array();
	        	foreach($submit_errors as $key => $value)
	        	{
	        		$errors[] = $value;
	        	}
	        	send_ajax_output(500, $errors);
				return;
	        }
	        else 
	        {
	        	// Ambil data detail.
				$result = $this->berkas_model->get_id($this->input->post('id'));
				$this->_prepare_data_berkas($result);

	        	// Seluruh proses sukses.
	        	send_ajax_output(200, 'Ok', $result);
				return;
	        }			
		}
	}

	public function delete()
	{
		$this->load->model('berkas_model');
		$this->load->helper('output_ajax');
		$this->load->language('cs_pkl_error');

		// Ambil data berkas untuk digunakan dalam proses hapus file.
		$data_berkas = $this->berkas_model->get_id($this->input->post('id'));
		
		try
		{
			$this->berkas_model->delete($this->input->post('id'));

			// Hapus file-file berkas.
			$file_path = $this->config->item('pkl_berkas_path');
			foreach ($data_berkas['files'] as $file)
			{
				$this->_unlink($file_path.$file['bf_file']);
			}

			// Kirim result.
			$result = $this->berkas_model->get();
			for ($i=0; $i<count($result); $i++)
			{
				$this->_prepare_data_berkas($result[$i]);
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
	 * Melakukan perubahan format data berkas sebelum dikirim ke client.
	 */
	private function _prepare_data_berkas(&$data)
	{
		// Ubah format tanggal.
		$data['ber_tgl_publikasi'] = date('d-m-Y', strtotime($data['ber_tgl_publikasi']));
		$data['ber_last_modified'] = date('d-m-Y H:i', strtotime($data['ber_last_modified']));

		foreach ($data['files'] as &$value)
		{
			// Ubah URL file dan format tanggal.
			$file_path = $this->config->item('pkl_berkas_path');
			$value['bf_file'] = base_url().$file_path.$value['bf_file'];
			$value['bf_date'] = date('d-m-Y H:i', strtotime($value['bf_date']));
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

	private function _unlink($path)
	{
		if (file_exists($path))
		{
			unlink($path);
		}
	}
}