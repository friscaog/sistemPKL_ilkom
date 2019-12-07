<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Include the main TCPDF library.
require_once(APPPATH.'third_party'.DIRECTORY_SEPARATOR.'tcpdf'.DIRECTORY_SEPARATOR.'tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES.'logo_unud.jpg';
        // Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
        $this->Image($image_file, 20, 10, 30, 30, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        // Set font
        $this->SetFont('times', 'B', 10);
        
        // Title
        $table = "
        <style>
        	table{
        		font-size: 12px;
        		text-align:center;
        	}
        </style>
        <table>
        <tr>
        	<td>KEMENTERIAN RISET, TEKNOLOGI DAN PENDIDIKAN TINGGI</td>
        </tr>
        <tr>
        	<td>UNIVERSITAS UDAYANA</td>
        </tr>
        <tr>
        	<td>FAKULTAS MIPA JURUSAN ILMU KOMPUTER</td>
        </tr>
        <tr>
        	<td>PROGRAM STUDI TEKNIK INFORMATIKA</td>
        </tr>
        <tr>
        	<td></td>
        </tr>
        <tr>
        	<td><b>KOMISI PRAKTEK KERJA LAPANGAN</b></td>
        </tr>
		<tr>
        	<td>Sekretariat : Jurusan Ilmu Komputer FMIPA UNUD, Gedung BF Kampus Bukit Jimbaran</td>
        </tr>
        <tr>
        	<td>Telp. (0361) 701805, Email: pkl@cs.unud.ac.id</td>
        </tr>
        </table>
        ";
        $this->writeHTML($table);

        //membuat garis horizontal 
        $this->writeHTML("<hr>", true, false, false, false, '');
    }

}

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
		$this->load->helper('url');
		$this->load->helper(array('form'));
		$this->load->model(array('periode_model', 'tempat_model', 'dosen_model', 'mahasiswa_model', 'peserta_model'));
		$this->load->library(array('form_validation', 'upload'));
		$this->load->language('cs_pkl_error');

		//cek apakah sudah terdaftar di tabel peserta atau belum
		if($this->mahasiswa_model->get_status() == 0){
			$this->session->set_flashdata('msg','Isi periode, institusi tempat PKL, dan dosen pembimbing');
			return redirect('mahasiswa/profil');
		}

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
		// $this->form_validation->set_rules('periode', 'Periode', 'required', 
		// 	array('required' => 'Anda harus memilih %s yang akan diikuti')
		// 	);
		// $this->form_validation->set_rules('tempat', 'Institusi Tempat PKL', 'required',
		// 	array('required' => 'Anda harus memilih %s')
		// 	);
		// $this->form_validation->set_rules('pembimbing', 'Dosen Pembimbing', 'required',
		// 	array('required' => 'Anda harus memilih %s')
		// 	);

		// Validasi form.
		// if ($this->form_validation->run() == FALSE)
		// {
		// 	// Menentukan apakah form dalam kondisi submit error atau dalam kondisi tidak disubmit.
		// 	$out_form_state = validation_errors() ? 'error' : '';
		// } 
		// else
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


    public function pernyataan(){
    	$this->load->model('Peserta_model');
    	$data = $this->Peserta_model->laporan_pernyataan($this->session->mahasiswa_id);

    	// create new PDF document
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('CS PKL');
		$pdf->SetAuthor('Jurusan Ilmu Komputer Universitas Udayana');
		$pdf->SetTitle('Surat Pernyataan Memenuhi Syarat');
		$pdf->SetSubject('Surat Pernyataan Memenuhi Syarat');
		$pdf->SetKeywords('Surat, Pernyataan, Syarat, PKL');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// remove default header/footer
		$pdf->setPrintFooter(false);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//SetMargins($left,$top,$right = -1,$keepmargins = false)
		$pdf->SetMargins(20, 20, 20, true);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		    require_once(dirname(__FILE__).'/lang/eng.php');
		    $pdf->setLanguageArray($l);
		}

		// ---------------------------------------------------------

		// set font
		$pdf->SetFont('times', '', 12);

		// add a page
		$pdf->AddPage();

		//mengubah tanggal ke bahasa indonesia
		$bulan = array (
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$tanggal = date('d-m-Y');
		$pecahkan = explode('-', $tanggal);
		$date =  $pecahkan[0] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[2];

		$html = '<br><br><br><br><br>		
		<h4 style="text-align: center;">FORM PERNYATAAN</h4>
		<i>Saya yang bertanda tangan dibawah ini:</i><br>
		<table border="0" style="width:800px">
			<tr>
				<td style="width:50px"></td>
				<td>Nama Mahasiwa</td>
				<td style="width:20px">:</td>
				<td>'.$data[0]['mhs_nama'].'</td>
			</tr>
			<tr>
				<td></td>
				<td>NIM</td>
				<td style="width:20px">:</td>
				<td>'.$data[0]['mhs_nim'].'</td>
			</tr>
			<tr>
				<td></td>
				<td>Program Studi</td>
				<td style="width:20px">:</td>
				<td>Teknik Informatika</td>
			</tr>
			<tr>
				<td></td>
				<td>Jurusan</td>
				<td style="width:20px">:</td>
				<td>Ilmu Komputer</td>
			</tr>
			<tr>
				<td></td>
				<td>Semester</td>
				<td style="width:20px">:</td>
				<td>'.$data[0]['mhs_smt'].' &nbsp; Tahun Ajaran: '.$data[0]['per_tahun'].'</td>
			</tr>
			<br><br><br>
			<i style="text-align: justify">Dengan ini menyatakan bahwa telah memenuhi persyaratan untuk mengikuti Praktek Kerja Lapangan Jurusan Ilmu Komputer FMIPA Universitas Udayana Periode '.$data[0]['per_nama'].' pada Semester '.$data[0]['per_semester'].' '.$data[0]['per_tahun'].' dengan melampirkan fotokopi transkrip terakhir.</i>
		<br><br><br><br>
			<tr>
				<td style="width:50px"></td>
				<td colspan="2" style="width:40%">
					Mengetahui			
				</td>
				<td colspan="2" style="width:50%">
					Bukit Jimbaran, '.$date.'
				</td>
			</tr>

			<tr>
				<td style="width:50px"></td>
				<td colspan="2" style="width:40%">
					Dosen Pembiming Akademik
				</td>
				<td colspan="2" style="width:50%">			
					Pemohon,
				</td>					
			</tr>
		<br><br><br><br>
			<tr>
				<td style="width:50px"></td>
				<td colspan="2">
					('.$data[0]['dos_nama'].')		
				</td>
				<td colspan="2">
					('.$data[0]['mhs_nama'].')		
				</td>
			</tr>

			<tr>
				<td style="width:50px"></td>
				<td colspan="2">		
					NIP. '.$data[0]['dos_nip'].'
				</td>
				<td colspan="2">		
					NIM. '.$data[0]['mhs_nim'].'
				</td>
			</tr>
		</table>
		';		

		// Tampilkan konten
		$pdf->writeHTML($html, true, false, true, false, '');

		//Close and output PDF document
		$pdf->Output('Form Penyataan.pdf', 'I');
    }

    public function permohonan(){
    	//ambil data dari database
    	$this->load->model('Peserta_model');
    	$data = $this->Peserta_model->laporan_pernyataan($this->session->mahasiswa_id);

    	// create new PDF document
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('CS PKL');
		$pdf->SetAuthor('Jurusan Ilmu Komputer Universitas Udayana');
		$pdf->SetTitle('Surat Permohonan PKL');
		$pdf->SetSubject('Surat Permohonan PKL');
		$pdf->SetKeywords('Surat, Permohonan, PKL');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// remove default header/footer
		$pdf->setPrintFooter(false);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		//SetMargins($left,$top,$right = -1,$keepmargins = false)
		$pdf->SetMargins(20, 20, 20, true);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		    require_once(dirname(__FILE__).'/lang/eng.php');
		    $pdf->setLanguageArray($l);
		}

		// ---------------------------------------------------------

		// set font
		$pdf->SetFont('times', '', 12);

		// add a page
		$pdf->AddPage();

		$html = '<br><br><br><br><br>		
		<h4 style="text-align: center;">FORM PERMOHONAN PRAKTEK KERJA LAPANGAN</h4>
		<i>Saya yang bertanda tangan dibawah ini:</i><br>
		<table border="0" style="width:800px">
			<tr>
				<td style="width:50px"></td>
				<td style="width:250px">Nama Mahasiwa</td>
				<td style="width:20px">:</td>
				<td>'.$data[0]['mhs_nama'].'</td>
			</tr>
			<tr>
				<td></td>
				<td>NIM</td>
				<td style="width:20px">:</td>
				<td>'.$data[0]['mhs_nim'].'</td>
			</tr>
			<tr>
				<td></td>
				<td>Program Studi</td>
				<td style="width:20px">:</td>
				<td>Teknik Informatika</td>
			</tr>
			<tr>
				<td></td>
				<td>Jurusan</td>
				<td style="width:20px">:</td>
				<td>Ilmu Komputer</td>
			</tr>
			<tr>
				<td></td>
				<td>Semester</td>
				<td style="width:20px">:</td>
				<td>'.$data[0]['mhs_smt'].' &nbsp; Tahun Ajaran: '.$data[0]['per_tahun'].'</td>
			</tr>
		</table>
		';		

		// Tampilkan konten
		$pdf->writeHTML($html, true, false, true, false, '');

		$html = '<i>Memohon untuk melakukan Praktek Kerja Lapangan pada:</i><br>
		<table border="0" style="width:800px">
			<tr>
				<td style="width:50px"></td>
				<td style="width:250px">Nama Instansi/Perusahaan</td>
				<td style="width:20px">:</td>
				<td>'.$data[0]['tem_nama'].'</td>
			</tr>
			<tr>
				<td></td>
				<td>Alamat</td>
				<td style="width:20px">:</td>
				<td>'.$data[0]['tem_alamat'].'</td>
			</tr>
			<tr>
				<td></td>
				<td>Nomor Telepon</td>
				<td style="width:20px">:</td>
				<td>'.$data[0]['tem_telepon'].'</td>
			</tr>
		</table>
		';

		// output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');

		$html = '<i>Dengan perincian sebagai berikut:</i><br>
		<table border="0" style="width:800px">
			<tr>
				<td style="width:50px"></td>
				<td style="width:250px">Judul Praktek Kerja (Jika telah ada)</td>
				<td style="width:20px">:</td>
				<td style="width:280px"></td>
			</tr>
			<tr>
				<td></td>
				<td>Lama Praktek Kerja</td>
				<td style="width:20px">:</td>
				<td>(2) bulan</td>
			</tr>
			<tr>
				<td></td>
				<td>Mulai Tanggal</td>
				<td style="width:20px">:</td>
				<td>'.$data[0]['per_tgl_mulai'].', Selesai tanggal: '.$data[0]['per_tgl_selesai'].'</td>
			</tr>
		</table>
		';
		
		// output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');

		$bulan = array (
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$tanggal = date('d-m-Y');
		$pecahkan = explode('-', $tanggal);
		$date =  $pecahkan[0] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[2];


		$html = '<i>Dan menyatakan bersedia:</i>
		<ol>
			<li>Menaati semua pedoman Praktek Kerja Lapangan yang telah ditetapkan oleh Program Studi dan peraturan Perusahaan/Instansi tempat pelaksanaan Praktek Kerja Lapangan.</li>
			<li>Tidak akan melakukan hal-hal yang dapat merugikan pihak lain serta mencemarkan nama baik diri sendiri, keluarga, pihak Program Studi serta Perusahaan/Institusi tempat melakukan Praktek Kerja Lapangan.</li>
			<li>Tidak akan menuntut atau meminta ganti rugi kepada pihak Program Studi dan Perusahaan/Institusi apabila terjadi hal-hal yang tidak diinginkan saat Praktek kerja (kehilangan, kecelakaan, dsb.) yang disebabkan oleh kecerobohan saya sendiri.</li>
		</ol>
		<table border="0" style="width:800px">
		<br><br><br><br>
			<tr>
				<td style="width:50px"></td>
				<td style="width:40%">
					Mengetahui			
				</td>
				<td style="width:50%">
					Bukit Jimbaran, '.$date.'
				</td>
			</tr>

			<tr>
				<td style="width:50px"></td>
				<td style="width:40%">
					Dosen Pembiming Akademik
				</td>
				<td style="width:50%">			
					Pemohon,
				</td>					
			</tr>
		<br><br><br>
			<tr>
				<td style="width:50px"></td>
				<td>
					('.$data[0]['dos_nama'].')		
				</td>
				<td>
					('.$data[0]['mhs_nama'].')		
				</td>
			</tr>

			<tr>
				<td style="width:50px"></td>
				<td>		
					NIP. '.$data[0]['dos_nip'].'
				</td>
				<td>		
					NIM. '.$data[0]['mhs_nim'].'
				</td>
			</tr>
		</table>
		';
		// output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');

		//Close and output PDF document
		$pdf->Output('Form Permohonan PKL.pdf', 'I');
    }

}