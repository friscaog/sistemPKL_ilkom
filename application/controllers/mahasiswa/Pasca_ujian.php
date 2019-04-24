<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pasca_ujian extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('url');
		$this->load->model(array('peserta_model'));

		// Periksa hak akses pengguna.
		if ($this->session->jenis_akun !== 'mahasiswa')
		{
			redirect('login');
		}

		// Periksa tahapan peserta saat ini.
		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa(
			$this->session->mahasiswa_id
			);
		if ( ! $peserta_data OR $peserta_data['pes_tahapan'] < $this->config->item('pkl_tahapan_num_pasca_ujian'))
		{
			redirect('mahasiswa');
		}
	}

	public function index()
	{
		$this->load->helper(array('form'));
		$this->load->model(array('peserta_model', 'periode_model'));
		$this->load->library(array('form_validation', 'upload'));

		// Data state form.
		$data['form_state'] = '';
		$data['submit_errors'] = array();

		// Proses edit.
		$this->_update_data($this->session->mahasiswa_id, $data['form_state'], $data['submit_errors']);
		
		// Data terbaru setelah proses insert atau edit.
		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($this->session->mahasiswa_id);
		$peserta_data_laporan_revisi = $this->peserta_model->get_data_laporan_revisi($peserta_data['pes_id']);
		$peserta_data_laporan_lembar_pengesahan = $this->peserta_model->get_data_laporan_lembar_pengesahan($peserta_data['pes_id']);
		$peserta_data_bukti_pengumpulan_laporan = $this->peserta_model->get_data_bukti_pengumpulan_laporan($peserta_data['pes_id']);		

		// Data halaman.
		$data['page'] = 'mahasiswa';
		$data['title'] = 'Pasca Ujian';
		$data['tahapan'] = $this->config->item('pkl_tahapan_num_pasca_ujian');
		$data['maks_tahapan'] = $peserta_data['pes_tahapan'];

		// Data peserta saat ini.
		$data['peserta_status'] = $this->peserta_model->get_status_pasca_ujian($peserta_data['pes_id']);
		$data['peserta_data'] = $peserta_data;
		$data['peserta_data_laporan_revisi'] = $peserta_data_laporan_revisi;
		$data['peserta_data_laporan_lembar_pengesahan'] = $peserta_data_laporan_lembar_pengesahan;
		$data['peserta_data_bukti_pengumpulan_laporan'] = $peserta_data_bukti_pengumpulan_laporan;

		// Data waktu akhir pengisian form.
		$data['batas_waktu_submit'] = '';
		if ($peserta_data)
		{
			$periode_data = $this->periode_model->get_data($peserta_data['per_id']);
			$data['batas_waktu_submit'] = $periode_data['per_tgl_selesai_pasca_ujian'];
		}

		$this->load->view('templates/header', $data);
		$this->load->view('mahasiswa/pasca_ujian', $data);
		$this->load->view('templates/footer', $data);
	}

	private function _update_data($mahasiswa_id, &$out_form_state, &$out_submit_errors)
    {
    	$this->load->model('periode_model');
    	$this->load->language('cs_pkl_error');

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

		// Upload revisi laporan
		if (isset($_FILES['laporan_revisi']) && $_FILES['laporan_revisi']['name'])
		{
			$config['file_name']			= $this->session->nim.'_laporan_revisi';
	    	$config['upload_path']          = $this->config->item('pkl_laporan_revisi_path');
	        $config['allowed_types']        = $this->config->item('pkl_laporan_revisi_allowed_types');
	        $config['max_size']             = $this->config->item('pkl_laporan_revisi_max_size');
	        $this->upload->initialize($config, TRUE);
	        if ($this->upload->do_upload('laporan_revisi'))
	        {
	        	$uploaded_files['laporan_revisi'] = $this->upload->data();
	        }
	        else
	        {
	        	$submit_errors['laporan_revisi'] ='Revisi Laporan: '.$this->upload->display_errors('','');
	        }
		}

		// Upload lembar pengesahan
		if (isset($_FILES['laporan_lembar_pengesahan']) && $_FILES['laporan_lembar_pengesahan']['name'])
		{
			$config['file_name']			= $this->session->nim.'_laporan_lembar_pengesahan';
	    	$config['upload_path']          = $this->config->item('pkl_laporan_lembar_pengesahan_path');
	        $config['allowed_types']        = $this->config->item('pkl_laporan_lembar_pengesahan_allowed_types');
	        $config['max_size']             = $this->config->item('pkl_laporan_lembar_pengesahan_max_size');
	        $this->upload->initialize($config, TRUE);
	        if ($this->upload->do_upload('laporan_lembar_pengesahan'))
	        {
	        	$uploaded_files['laporan_lembar_pengesahan'] = $this->upload->data();
	        }
	        else
	        {
	        	$submit_errors['laporan_lembar_pengesahan'] ='Lembar Pengesahan: '.$this->upload->display_errors('','');
	        }
		}

		// Upload bukti pengumpulan laporan
		if (isset($_FILES['bukti_pengumpulan_laporan']) && $_FILES['bukti_pengumpulan_laporan']['name'])
		{
			$config['file_name']			= $this->session->nim.'_bukti_pengumpulan_laporan';
	    	$config['upload_path']          = $this->config->item('pkl_bukti_pengumpulan_laporan_path');
	        $config['allowed_types']        = $this->config->item('pkl_bukti_pengumpulan_laporan_allowed_types');
	        $config['max_size']             = $this->config->item('pkl_bukti_pengumpulan_laporan_max_size');
	        $this->upload->initialize($config, TRUE);
	        if ($this->upload->do_upload('bukti_pengumpulan_laporan'))
	        {
	        	$uploaded_files['bukti_pengumpulan_laporan'] = $this->upload->data();
	        }
	        else
	        {
	        	$submit_errors['bukti_pengumpulan_laporan'] ='Bukti Pengumpulan Laporan: '.$this->upload->display_errors('','');
	        }
		}

		$data_peserta = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($mahasiswa_id);

		// Cek apakah update data masih dapat dilakukan di tanggal ini.
        $periode_data = $this->periode_model->get_data($data_peserta['per_id']);
        if (time() > strtotime($periode_data['per_tgl_selesai_pasca_ujian'])+60*60*24)
        {
        	$submit_errors['batas_waktu_submit'] = $this->lang->line('error_lewat_batas_waktu_submit');
        }

		// Jika semua file berhasil diupload, simpan data ke database.
        if (empty($submit_errors))
        {
			try
			{			
				// Proses update data.
				// Proses update file tidak menghapus file lama, namun hanya menambah versi dari file tersebut.
				$this->peserta_model->update_pasca_ujian(
					$data_peserta['pes_id'],
					(isset($uploaded_files['laporan_revisi'])) ? $uploaded_files['laporan_revisi']['file_name']:'',
					(isset($uploaded_files['laporan_lembar_pengesahan'])) ? $uploaded_files['laporan_lembar_pengesahan']['file_name']:'',
					(isset($uploaded_files['bukti_pengumpulan_laporan'])) ? $uploaded_files['bukti_pengumpulan_laporan']['file_name']:'');
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