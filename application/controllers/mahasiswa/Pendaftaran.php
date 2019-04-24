<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pendaftaran extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('url');
		$this->load->model('mahasiswa_model');

		// Periksa hak akses pengguna.
		if ($this->session->jenis_akun !== 'mahasiswa')
		{
			redirect('login');
		}

		// Periksa apakah data mahasiswa sudah ada.
		if ( ! $this->mahasiswa_model->get_data($this->session->email))
		{
			redirect('mahasiswa');
		}
	}

	public function index()
	{
		$this->load->helper(array('form'));
		$this->load->model(array('periode_model', 'tempat_model', 'dosen_model', 'mahasiswa_model', 'peserta_model'));
		$this->load->library(array('form_validation', 'upload'));
		$this->load->language('cs_pkl_error');

		// Data state form.
		$data['form_state'] = '';
		$data['submit_errors'] = array();

		// Jika tidak terdapat data peserta di periode aktif saat ini, lakukan proses add.
		if ( ! $this->peserta_model->is_mahasiswa_exist($this->session->mahasiswa_id))
		{
			log_message('debug', 'Proses add pendaftaran peserta');
			$this->_add_data($this->session->mahasiswa_id, $data['form_state'], $data['submit_errors']);
			$form_type = 'add';
		}
		// Jika data ada, lakukan proses update.
		else
		{
			log_message('debug', 'Proses update pendaftaran peserta');
			$this->_update_data($this->session->mahasiswa_id, $data['form_state'], $data['submit_errors']);
			$form_type = 'edit';
		}
		

		// Data terbaru setelah proses insert atau edit.
		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($this->session->mahasiswa_id);
		$peserta_data_transkrip = $this->peserta_model->get_data_transkrip($peserta_data['pes_id']);
		$peserta_data_surat_permohonan = $this->peserta_model->get_data_surat_permohonan($peserta_data['pes_id']);
		$peserta_data_surat_penerimaan = $this->peserta_model->get_data_surat_penerimaan($peserta_data['pes_id']);		
		$peserta_data_surat_pernyataan_memenuhi_syarat = $this->peserta_model->get_data_surat_pernyataan_memenuhi_syarat($peserta_data['pes_id']);

		// Data halaman.
		$data['page'] = 'mahasiswa';
		$data['title'] = 'Pendaftaran';
		$data['tahapan'] = 0;
		$data['maks_tahapan'] = $peserta_data['pes_tahapan'];
		
		// Data untuk dropdown periode.
		$data['periode_list'] = $this->periode_model->get_open_register_include_current($peserta_data['pes_id']);

		// Data untuk dropdown tempat.
		$data['tempat_list'] = array();
		if ($this->input->post('periode'))	// Kondisi setelah form disubmit.
		{
			$data['tempat_list'] = $this->tempat_model->get_available_in_periode_include_current($this->input->post('periode'), $peserta_data['pes_id']);
		}
		else if ( ! empty($peserta_data))	// Ada data peserta.
		{
			$data['tempat_list'] = $this->tempat_model->get_available_in_periode_include_current($peserta_data['per_id'], $peserta_data['pes_id']);	
		}	

		// Data untuk dropdown dosen pembimbing.	
		$data['dosen_list'] = array();
		if ($this->input->post('periode')) // Kondisi setelah form disubmit.
		{
			$data['dosen_list'] = $this->dosen_model->get_with_num_bimbingan($this->input->post('periode'));
		}
		else if ( ! empty($peserta_data))	// Ada data peserta.
		{
			$data['dosen_list'] = $this->dosen_model->get_with_num_bimbingan($peserta_data['per_id']);	
		}		
		
		// Data jenis form. Apakah form merupakan form add atau edit.
		$data['form_type'] = $form_type;

		// Data peserta saat ini.
		$data['peserta_status'] = $this->peserta_model->get_status_pendaftaran($peserta_data['pes_id']);
		$data['peserta_data'] = $peserta_data;
		$data['peserta_data_transkrip'] = $peserta_data_transkrip;
		$data['peserta_data_surat_permohonan'] = $peserta_data_surat_permohonan;
		$data['peserta_data_surat_penerimaan'] = $peserta_data_surat_penerimaan;
		$data['peserta_data_surat_pernyataan_memenuhi_syarat'] = $peserta_data_surat_pernyataan_memenuhi_syarat;

		// Data waktu akhir pengisian form.
		$data['batas_waktu_submit'] = '';
		if ($peserta_data)
		{
			$periode_data = $this->periode_model->get_data($peserta_data['per_id']);
			$data['batas_waktu_submit'] = $periode_data['per_tgl_selesai_pendaftaran'];
		}		

		$this->load->view('templates/header', $data);
		$this->load->view('mahasiswa/pendaftaran', $data);
		$this->load->view('templates/footer', $data);
	}

	public function get_dropdown($periode_id=NULL)
	{
		$this->load->helper('output_ajax');
		$this->load->model(array('dosen_model', 'tempat_model', 'peserta_model'));

		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($this->session->mahasiswa_id);

		$tempat = $this->tempat_model->get_available_in_periode_include_current($periode_id, $peserta_data['pes_id']);
		$pembimbing = $this->dosen_model->get_with_num_bimbingan($periode_id);

		send_ajax_output(200, 'Ok', array('pembimbing' => $pembimbing, 'tempat' => $tempat));
		return;
	}

	public function files_required($value, $field_name)
    {
        if ($_FILES[$field_name]['name'])
        {
                return TRUE;
        }
        else
        {
                return FALSE;
        }
    }

    private function _add_data($mahasiswa_id, &$out_form_state, &$out_submit_errors)
    {
    	// Rule validasi form.
		$this->form_validation->set_rules('periode', 'Periode', 'required', 
			array('required' => 'Anda harus memilih %s yang akan diikuti')
			);
		$this->form_validation->set_rules('tempat', 'Institusi Tempat PKL', 'required',
			array('required' => 'Anda harus memilih %s')
			);
		$this->form_validation->set_rules('pembimbing', 'Dosen Pembimbing', 'required',
			array('required' => 'Anda harus memilih %s')
			);

		// Validasi form.
		if ($this->form_validation->run() == FALSE)
		{
			// Menentukan apakah form dalam kondisi submit error atau dalam kondisi tidak disubmit.
			$out_form_state = validation_errors() ? 'error' : '';
		} 
		else
		{	
			// Proses upload file.	
			$uploaded_files = array();
			$submit_errors = array();

			// Upload transkrip nilai
			if ($_FILES['transkrip']['name'])
			{
				$config['file_name']			= $this->session->nim.'_transkrip';
		    	$config['upload_path']          = $this->config->item('pkl_transkrip_path');
		        $config['allowed_types']        = $this->config->item('pkl_transkrip_allowed_types');
		        $config['max_size']             = $this->config->item('pkl_transkrip_max_size');
		        $this->upload->initialize($config, TRUE);
		        if ($this->upload->do_upload('transkrip'))
		        {
		        	$uploaded_files['transkrip'] = $this->upload->data();
		        }
		        else
		        {
		        	$submit_errors['transkrip'] ='Transkrip Nilai: '.$this->upload->display_errors('','');
		        }
			}
			// else
			// {
			// 	$submit_errors['transkrip'] ='Transkrip Nilai tidak boleh kosong';
			// }
			
			// Upload surat pernyataan telah memenuhi syarat mengikuti PKL.
			if ($_FILES['surat_pernyataan_memenuhi_syarat']['name'])
			{
				$config['file_name']			= $this->session->nim.'_surat_pernyataan_memenuhi_syarat';
		    	$config['upload_path']          = $this->config->item('pkl_surat_pernyataan_memenuhi_syarat_path');
		        $config['allowed_types']        = $this->config->item('pkl_surat_pernyataan_memenuhi_syarat_allowed_types');
		        $config['max_size']             = $this->config->item('pkl_surat_pernyataan_memenuhi_syarat_max_size');
		        $this->upload->initialize($config, TRUE);
		        if ($this->upload->do_upload('surat_pernyataan_memenuhi_syarat'))
		        {
		        	$uploaded_files['surat_pernyataan_memenuhi_syarat'] = $this->upload->data();
		        }
		        else
		        {
		        	$submit_errors['surat_pernyataan_memenuhi_syarat'] ='Surat Pernyataan Memenuhi Syarat: '.$this->upload->display_errors('','');
		        }
			}
			// else
			// {
			// 	$submit_errors['surat_pernyataan_memenuhi_syarat'] ='Surat Pernyataan Memenuhi Syarat tidak boleh kosong';
			// }			

	        // Upload Surat permohonan pkl.
	        if ($_FILES['surat_permohonan']['name'])
	        {
	        	$config['file_name']			= $this->session->nim.'_surat_permohonan';
		    	$config['upload_path']          = $this->config->item('pkl_surat_permohonan_path');
		        $config['allowed_types']        = $this->config->item('pkl_surat_permohonan_allowed_types');
		        $config['max_size']             = $this->config->item('pkl_surat_permohonan_max_size');
		        $this->upload->initialize($config, TRUE);
		        if ($this->upload->do_upload('surat_permohonan'))
		        {
		        	$uploaded_files['surat_permohonan'] = $this->upload->data();
		        }
		        else
		        {
		        	$submit_errors['surat_permohonan'] = 'Surat Permohonan PKL: '.$this->upload->display_errors('','');
		        }	
	        }	        
	        // else
	        // {
	        // 	$submit_errors['surat_permohonan'] ='Surat Permohonan PKL tidak boleh kosong';
	        // }

	        // Upload Surat penerimaan tempat pkl.
	        if ($_FILES['surat_penerimaan']['name'])
	        {
	        	$config['file_name']			= $this->session->nim.'_surat_penerimaan';
		    	$config['upload_path']          = $this->config->item('pkl_surat_penerimaan_path');
		        $config['allowed_types']        = $this->config->item('pkl_surat_penerimaan_allowed_types');
		        $config['max_size']             = $this->config->item('pkl_surat_penerimaan_max_size');
		        $this->upload->initialize($config, TRUE);
		        if ($this->upload->do_upload('surat_penerimaan'))
		        {
		        	$uploaded_files['surat_penerimaan'] = $this->upload->data();
		        }
		        else
		        {
		        	$submit_errors['surat_penerimaan'] = 'Surat Penerimaan Tempat PKL: '.$this->upload->display_errors('','');
		        }
	        }
	        // else
	        // {
	        // 	$submit_errors['surat_penerimaan'] = 'Surat Penerimaan Tempat PKL tidak boleh kosong';
	        // }

	        // Jika semua file berhasil diupload, simpan data ke database.
	        if (empty($submit_errors))
	        {
				try
				{
					// Proses add data.
					$peserta_id = $this->peserta_model->add(
						$mahasiswa_id,
						$this->input->post('periode'),
						$this->input->post('tempat'),
						$this->input->post('pembimbing'),
						(isset($uploaded_files['transkrip'])) ? $uploaded_files['transkrip']['file_name']:'',
						(isset($uploaded_files['surat_permohonan'])) ? $uploaded_files['surat_permohonan']['file_name']:'',
						(isset($uploaded_files['surat_penerimaan'])) ? $uploaded_files['surat_penerimaan']['file_name']:'',
						(isset($uploaded_files['surat_pernyataan_memenuhi_syarat'])) ? $uploaded_files['surat_pernyataan_memenuhi_syarat']['file_name']:'');
				}
				catch (Exception $e)
				{
					log_message('error', $e->getMessage());
					$submit_errors['insert_db'] = 'Terjadi kegagalan saat menyimpan data, silakan coba beberapa saat lagi atau hubungi administrator';
				}
				
	        }

	        
	        if ($submit_errors)
	        {
	        	// Proses upload atau proses submit gagal. Hapus file-file yang sudah terupload.
	        	foreach ($uploaded_files as $file) {
	        		unlink($file['full_path']);
	        	}

	        	$out_form_state = 'error';
	        	$out_submit_errors = $submit_errors;
	        }
	        else 
	        {
	        	// Seluruh proses sukses.
	        	$out_form_state = 'success';
	        }

		}
    }

    private function _update_data($mahasiswa_id, &$out_form_state, &$out_submit_errors)
    {
    	$this->load->model('periode_model');

    	if ( ! $this->input->post('submit'))
    	{
    		// Form tidak disubmit.
    		$out_form_state = '';
    		return;
    	}

    	// Saat ini tanpa rule validasi

		// Proses upload file.	
		$uploaded_files = array();
		$submit_errors = array();

		// Upload transkrip nilai
		if (isset($_FILES['transkrip']) && $_FILES['transkrip']['name'])
		{
			$config['file_name']			= $this->session->nim.'_transkrip';
	    	$config['upload_path']          = $this->config->item('pkl_transkrip_path');
	        $config['allowed_types']        = $this->config->item('pkl_transkrip_allowed_types');
	        $config['max_size']             = $this->config->item('pkl_transkrip_max_size');
	        $this->upload->initialize($config, TRUE);
	        if ($this->upload->do_upload('transkrip'))
	        {
	        	$uploaded_files['transkrip'] = $this->upload->data();
	        }
	        else
	        {
	        	$submit_errors['transkrip'] ='Transkrip Nilai: '.$this->upload->display_errors('','');
	        }
		}

		// Upload surat pernyataan telah memenuhi syarat mengikuti PKL.
		if (isset($_FILES['surat_pernyataan_memenuhi_syarat']) && $_FILES['surat_pernyataan_memenuhi_syarat']['name'])
		{
			$config['file_name']			= $this->session->nim.'_surat_pernyataan_memenuhi_syarat';
	    	$config['upload_path']          = $this->config->item('pkl_surat_pernyataan_memenuhi_syarat_path');
	        $config['allowed_types']        = $this->config->item('pkl_surat_pernyataan_memenuhi_syarat_allowed_types');
	        $config['max_size']             = $this->config->item('pkl_surat_pernyataan_memenuhi_syarat_max_size');
	        $this->upload->initialize($config, TRUE);
	        if ($this->upload->do_upload('surat_pernyataan_memenuhi_syarat'))
	        {
	        	$uploaded_files['surat_pernyataan_memenuhi_syarat'] = $this->upload->data();
	        }
	        else
	        {
	        	$submit_errors['surat_pernyataan_memenuhi_syarat'] ='Surat Pernyataan Memenuhi Syarat: '.$this->upload->display_errors('','');
	        }
		}

        // Upload Surat permohonan pkl.
        if (isset($_FILES['surat_permohonan']) && $_FILES['surat_permohonan']['name'])
        {
        	$config['file_name']			= $this->session->nim.'_surat_permohonan';
	    	$config['upload_path']          = $this->config->item('pkl_surat_permohonan_path');
	        $config['allowed_types']        = $this->config->item('pkl_surat_permohonan_allowed_types');
	        $config['max_size']             = $this->config->item('pkl_surat_permohonan_max_size');
	        $this->upload->initialize($config, TRUE);
	        if ($this->upload->do_upload('surat_permohonan'))
	        {
	        	$uploaded_files['surat_permohonan'] = $this->upload->data();
	        }
	        else
	        {
	        	$submit_errors['surat_permohonan'] = 'Surat Permohonan PKL: '.$this->upload->display_errors('','');
	        }	
        }	        

        // Upload Surat penerimaan tempat pkl.
        if (isset($_FILES['surat_penerimaan']) && $_FILES['surat_penerimaan']['name'])
        {
        	$config['file_name']			= $this->session->nim.'_surat_penerimaan';
	    	$config['upload_path']          = $this->config->item('pkl_surat_penerimaan_path');
	        $config['allowed_types']        = $this->config->item('pkl_surat_penerimaan_allowed_types');
	        $config['max_size']             = $this->config->item('pkl_surat_penerimaan_max_size');
	        $this->upload->initialize($config, TRUE);
	        if ($this->upload->do_upload('surat_penerimaan'))
	        {
	        	$uploaded_files['surat_penerimaan'] = $this->upload->data();
	        }
	        else
	        {
	        	$submit_errors['surat_penerimaan'] = 'Surat Penerimaan Tempat PKL: '.$this->upload->display_errors('','');
	        }
        }

        $data_peserta = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($mahasiswa_id);

        // Cek apakah update data masih dapat dilakukan di tanggal ini.
        $periode_data = $this->periode_model->get_data($data_peserta['per_id']);
        if (time() > strtotime($periode_data['per_tgl_selesai_pendaftaran'])+60*60*24)
        {
        	$submit_errors['batas_waktu_submit'] = $this->lang->line('error_lewat_batas_waktu_submit');
        }

        // Jika semua file berhasil diupload, simpan data ke database.
        if (empty($submit_errors))
        {
			try
			{				
				// Proses update data.
				// Data yang dapat diupdate hanya file transkrip nilai, file surat permohonan PKL, dan file surat penerimaan tempat PKL.
				// Proses update file tidak menghapus file lama, namun hanya menambah versi dari file tersebut.
				$this->peserta_model->update_pendaftaran(
					$data_peserta['pes_id'],
					(isset($uploaded_files['transkrip'])) ? $uploaded_files['transkrip']['file_name']:'',
					(isset($uploaded_files['surat_permohonan'])) ? $uploaded_files['surat_permohonan']['file_name']:'',
					(isset($uploaded_files['surat_penerimaan'])) ? $uploaded_files['surat_penerimaan']['file_name']:'',
					NULL,
					NULL,
					(isset($uploaded_files['surat_pernyataan_memenuhi_syarat'])) ? $uploaded_files['surat_pernyataan_memenuhi_syarat']['file_name']:'');
			}
			catch (Exception $e)
			{
				log_message('error', $e->getMessage());
				$submit_errors['insert_db'] = 'Terjadi kegagalan saat menyimpan data, silakan coba beberapa saat lagi atau hubungi administrator';
			}
			
        }

        
        if ($submit_errors)
        {
        	// Proses upload atau proses submit gagal. Hapus file-file yang sudah terupload.
        	foreach ($uploaded_files as $file) {
        		unlink($file['full_path']);
        	}

        	$out_form_state = 'error';
        	$out_submit_errors = $submit_errors;
        }
        else 
        {
    		// Seluruh proses sukses.
    		$out_form_state = 'success';	
        }

		
    }

}