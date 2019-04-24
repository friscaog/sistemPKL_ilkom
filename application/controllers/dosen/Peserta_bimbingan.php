<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Peserta_bimbingan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('url');

		// Periksa hak akses pengguna.
		if ($this->session->jenis_akun !== 'dosen')
		{
			// redirect('login');
		}
	}

	public function index()
	{
		// Data halaman.
		$data['page'] = 'dosen';
		$data['title'] = 'Peserta Bimbingan';
		$data['page_dosen'] = 'peserta_bimbingan';

		$this->load->view('templates/header', $data);
		$this->load->view('dosen/peserta_bimbingan', $data);
		$this->load->view('templates/footer', $data);
	}

	public function get()	
	{
		$this->load->helper('output_ajax');
		
		// Ambil list data.
		$result = $this->_get_list_peserta(TRUE);
		
		send_ajax_output(200, 'Ok', $result);
		return;
	}

	public function get_detail($id)
	{
		$this->load->helper('output_ajax');
		$this->load->model('peserta_model');

		$data_peserta = $this->peserta_model->get_data($id);

		// Apakah peserta adalah peserta bimbingan dari user dosen yang login.
		if ($data_peserta['pes_pembimbing'] != $this->session->dosen_id)
		{
			send_ajax_output(403, 'Anda tidak diijinkan mengakses data peserta ini');
			return;
		}

		// Ambil data detail.
		$result = $this->_get_data_detail($id);

		send_ajax_output(200, 'Ok', $result);
		return;
	}

	public function download()
	{
		$this->load->model(array('peserta_model', 'periode_model'));

		// Buat nama file excel.

		$input_tahapan = $this->input->get('tahapan');
		$file_name_tahapan = 'semua';
		if (!empty($input_tahapan) OR $input_tahapan === '0')
		{
			switch ($this->input->get('tahapan')) {
				case $this->config->item('pkl_tahapan_num_pendaftaran'):
					$file_name_tahapan = 'pendaftaran';
					break;
				case $this->config->item('pkl_tahapan_num_pelaksanaan'):
					$file_name_tahapan = 'pelaksanaan';
					break;
				case $this->config->item('pkl_tahapan_num_pasca_pkl'):
					$file_name_tahapan = 'pasca_pkl';
					break;
				case $this->config->item('pkl_tahapan_num_pasca_ujian'):
					$file_name_tahapan = 'pasca_ujian';
					break;
				case $this->config->item('pkl_tahapan_num_selesai'):
					$file_name_tahapan = 'selesai';
					break;
			}
		}		

		$input_periode = $this->input->get('periode');
		$file_name_periode = 'semua';	
		if (!empty($input_periode) OR $input_periode === '0')
		{
			$data_periode = $this->periode_model->get_data($input_periode);
			$file_name_periode = $data_periode['per_nama'];
		}

		$filename = 'result-'.$file_name_tahapan.'-'.$file_name_periode;

		// Daftar peserta.
		$data_list_peserta = $this->_get_list_peserta();

		/** PHPExcel */
		require_once(APPPATH.'third_party'.DIRECTORY_SEPARATOR.'PHPExcel-1.8'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php');

		/** PHPExcel_Writer_Excel2007 */
		include (APPPATH.'third_party'.DIRECTORY_SEPARATOR.'PHPExcel-1.8'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel/Writer/Excel2007.php');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setCreator("CS PKL");
		$objPHPExcel->getProperties()->setLastModifiedBy("CS PKL");
		$objPHPExcel->getProperties()->setTitle("Daftar Peserta PKL");
		$objPHPExcel->getProperties()->setSubject("Daftar Peserta PKL");

		$objPHPExcel->setActiveSheetIndex(0);

		// Tulis header.
		$objPHPExcel->getActiveSheet()->getStyle('A1:A3')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Periode');
		$objPHPExcel->getActiveSheet()->SetCellValue('B1', (isset($data_periode))?$data_periode['per_nama']:'-');
		$objPHPExcel->getActiveSheet()->SetCellValue('A2', 'Tahun');
		$objPHPExcel->getActiveSheet()->SetCellValue('B2', (isset($data_periode))?$data_periode['per_tahun']:'-');
		$objPHPExcel->getActiveSheet()->SetCellValue('A3', 'Semester');
		$objPHPExcel->getActiveSheet()->SetCellValue('B3', (isset($data_periode))?$data_periode['per_semester']:'-');
		
		// Tulis judul kolom.		
		$objPHPExcel->getActiveSheet()->getStyle('A5:U5')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('A5', 'No');
		$objPHPExcel->getActiveSheet()->SetCellValue('B5', 'NIM');
		$objPHPExcel->getActiveSheet()->SetCellValue('C5', 'Nama');
		$objPHPExcel->getActiveSheet()->SetCellValue('D5', 'Judul Laporan');
		$objPHPExcel->getActiveSheet()->SetCellValue('E5', 'Telepon');
		$objPHPExcel->getActiveSheet()->SetCellValue('F5', 'Alamat');
		$objPHPExcel->getActiveSheet()->SetCellValue('G5', 'Tempat PKL');
		$objPHPExcel->getActiveSheet()->SetCellValue('H5', 'Alamat Tempat PKL');
		$objPHPExcel->getActiveSheet()->SetCellValue('I5', 'Telepon Tempat PKL');
		$objPHPExcel->getActiveSheet()->SetCellValue('J5', 'NIP Pembimbing');
		$objPHPExcel->getActiveSheet()->SetCellValue('K5', 'Pembimbing');		

		// Jika menampilkan data semua periode, tampilkan kolom periode.
		if ( ! isset($data_periode))
		{
			$objPHPExcel->getActiveSheet()->SetCellValue('L5', 'Periode');
			$objPHPExcel->getActiveSheet()->SetCellValue('M5', 'Tahun');
			$objPHPExcel->getActiveSheet()->SetCellValue('N5', 'Semester');		
		}		
		// $objPHPExcel->getActiveSheet()->SetCellValue('Q5', 'Email Pembimbing');		
		// $objPHPExcel->getActiveSheet()->SetCellValue('S5', 'Telepon Pembimbing');
		// $objPHPExcel->getActiveSheet()->SetCellValue('T5', 'Alamat Pembimbing');
		// $objPHPExcel->getActiveSheet()->SetCellValue('U5', 'Kapasitas Pembimbing');				
		// $objPHPExcel->getActiveSheet()->SetCellValue('E5', 'Email');
		// $objPHPExcel->getActiveSheet()->SetCellValue('J5', 'Tahapan');
		// $objPHPExcel->getActiveSheet()->SetCellValue('K5', 'Status');
		// $objPHPExcel->getActiveSheet()->SetCellValue('O5', 'Kapasitas Tempat PKL');

		// Tulis baris-baris daftar peserta.
		
		$row = 6;
		foreach ($data_list_peserta as $key => $value) {
			// $status = '';
			// switch($value['status'])
			// {
			// 	case $this->config->item('pkl_status_belum_lengkap'):
			// 		$status = 'Belum lengkap';
			// 		break;
			// 	case $this->config->item('pkl_status_menunggu_konfirmasi_pembimbing'):
			// 		$status = 'Menunggu konfirmasi pembimbing';
			// 		break;
			// 	case $this->config->item('pkl_status_pending'):
			// 		$status = 'Menunggu verifikasi administrator';
			// 		break;
			// }

			// $tahapan = '';
			// switch ($value['pes_tahapan']) {
			// 	case $this->config->item('pkl_tahapan_num_pendaftaran'):
			// 		$tahapan = 'Pendaftaran';
			// 		break;				
			// 	case $this->config->item('pkl_tahapan_num_pelaksanaan'):
			// 		$tahapan = 'Pelaksanaan';
			// 		break;
			// 	case $this->config->item('pkl_tahapan_num_pasca_pkl'):
			// 		$tahapan = 'Pasca PKL';
			// 		break;
			// 	case $this->config->item('pkl_tahapan_num_pasca_ujian'):
			// 		$tahapan = 'Pasca Ujian';
			// 		break;
			// 	case $this->config->item('pkl_tahapan_num_selesai'):
			// 		$tahapan = 'Selesai';
			// 		break;
			// }

			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $key+1);			
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $value['mhs_nim']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $value['mhs_nama']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $value['pes_laporan_judul']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $value['mhs_telepon']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $value['mhs_alamat']);			
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $value['tem_nama']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $value['tem_alamat']);
			$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $value['tem_telepon']);			
			$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $value['dos_nip']);
			$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $value['dos_nama']);

			// Jika menampilkan data semua periode, tampilkan data kolom periode.
			if ( ! isset($data_periode))
			{
				$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $value['per_nama']);
				$objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $value['per_tahun']);
				$objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $value['per_semester']);	
			}			
			// $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $value['mhs_email_cs']);
			// $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $tahapan);
			// $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $status);
			// $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $value['tem_kapasitas_mhs']);
			// $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $value['dos_email_cs']);			
			// $objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, $value['dos_telepon']);
			// $objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, $value['dos_alamat']);
			// $objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, $value['dos_kapasitas_mhs']);	
				
			$row++;
		}

		// Set column autosize.
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);

		// Jika menampilkan data semua periode, sesuaikan juga ukuran kolom periode.
		if ( ! isset($data_periode))
		{
			$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);	
		}		
		// $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);

		// Send the file to client for download
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

		if (ob_get_length()) ob_clean();
		header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-Disposition: attachment; filename=\"".$filename.".xlsx\"");
		header("Cache-Control: max-age=0");		
		$objWriter->save("php://output");
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

	public function setujui_judul()
	{
		$this->load->helper('output_ajax');
		$this->load->model('peserta_model');
		$this->load->language('cs_pkl_error');

		$id = $this->input->post('id');

		$data_peserta = $this->peserta_model->get_data($id);

		// Apakah peserta adalah peserta bimbingan dari user dosen yang login.
		if ($data_peserta['pes_pembimbing'] != $this->session->dosen_id)
		{
			send_ajax_output(403, 'Anda tidak diijinkan melakukan operasi pada peserta ini');
			return;
		}

		try
		{
			$this->peserta_model->setujui_judul_laporan($id);

			// Kirim data judul yang baru ke client.
			$data_peserta = $this->peserta_model->get_data($id);
			send_ajax_output(200, 'Ok', array(
				'pes_laporan_judul' => $data_peserta['pes_laporan_judul'], 
				'pes_laporan_judul_konfirmasi' => $data_peserta['pes_laporan_judul_konfirmasi']));
			return;
		}
		catch(Exception $e)
		{
			log_message('error', $e->getMessage());
			send_ajax_output(500, $this->lang->line('error_operasi_data'));
			return;
		}
	}

	public function setujui_aktivitas_harian()
	{
		$this->load->helper('output_ajax');
		$this->load->model('peserta_model');
		$this->load->language('cs_pkl_error');

		$aktivitas_id = $this->input->post('aktivitas_id');

		$data_aktivitas = $this->peserta_model->get_data_aktivitas_harian($aktivitas_id);

		$data_peserta = $this->peserta_model->get_data($data_aktivitas['pes_id']);

		// Apakah peserta adalah peserta bimbingan dari user dosen yang login.
		if ($data_peserta['pes_pembimbing'] != $this->session->dosen_id)
		{
			send_ajax_output(403, 'Anda tidak diijinkan melakukan operasi pada peserta ini');
			return;
		}

		try
		{
			$this->peserta_model->setujui_aktivitas_harian($data_aktivitas['pah_id']);

			// Kirim data aktivitas harian yang baru ke client.
			$result = $this->_generate_aktivitas_harian($data_peserta['pes_id'], $data_peserta['per_id']);
			send_ajax_output(200, 'Ok', $result);
			return;
		}
		catch(Exception $e)
		{
			log_message('error', $e->getMessage());
			send_ajax_output(500, $this->lang->line('error_operasi_data'));
			return;
		}
	}

	public function komentar($action='get')	
	{
		setlocale (LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');
		
		$this->load->helper('output_ajax');
		$this->load->model('peserta_model');
		$this->load->language('cs_pkl_error');

		$id = $this->input->post('id');

		$data_peserta = $this->peserta_model->get_data($id);

		// Apakah peserta adalah peserta bimbingan dari user dosen yang login.
		if ($data_peserta['pes_pembimbing'] != $this->session->dosen_id)
		{
			send_ajax_output(403, 'Anda tidak diijinkan mengakses atau melakukan operasi pada peserta ini');
			return;
		}

		if ($action === 'get')
		{
			$result = $this->peserta_model->get_data_komentar($data_peserta['pes_id']);
			foreach ($result as &$komentar) {
				$komentar['waktu'] = strftime("%d %B %Y %H:%M", strtotime($komentar['pk_date']));
			}

			send_ajax_output(200, 'Ok', $result);
			return;
		}
		elseif ($action === 'update')
		{
			$this->load->library('form_validation');

			$this->form_validation->set_rules('komentar', 'Komentar', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
			if ($this->form_validation->run() === FALSE)
			{
				send_ajax_output(400, form_error('komentar', ' ', ' '));
				return;
			}
			else
			{
				try 
				{
					$this->peserta_model->add_komentar($data_peserta['pes_id'], $this->input->post('komentar'), $this->session->dosen_id);

					// Kirim data komentar yang baru ke client.
					$result = $this->peserta_model->get_data_komentar($data_peserta['pes_id']);
					foreach ($result as &$komentar) {
						$komentar['waktu'] = strftime("%d %B %Y %H:%M", strtotime($komentar['pk_date']));
					}

					send_ajax_output(200, 'Ok', $result);
					return;
				} 
				catch(Exception $e) 
				{
					log_message('error', $e->getMessage());
					send_ajax_output(500, $this->lang->line('error_operasi_data'));
					return;
				}
			}
		}		
	}

	public function setujui_draft_laporan()
	{
		$this->load->helper('output_ajax');
		$this->load->model('peserta_model');
		$this->load->language('cs_pkl_error');

		$id = $this->input->post('id');	
		
		$data_peserta = $this->peserta_model->get_data($id);

		// Apakah peserta adalah peserta bimbingan dari user dosen yang login.
		if ($data_peserta['pes_pembimbing'] != $this->session->dosen_id)
		{
			send_ajax_output(403, 'Anda tidak diijinkan melakukan operasi pada peserta ini');
			return;
		}

		try 
		{
			$this->peserta_model->setujui_laporan_draft_terakhir($data_peserta['pes_id']);

			// Kirim data draft laporan yang baru ke client.
			$result = $this->peserta_model->get_data_laporan_draft($data_peserta['pes_id']);
			send_ajax_output(200, 'Ok', $result);
			return;
		} 
		catch(Exception $e) 
		{
			log_message('error', $e->getMessage());
			send_ajax_output(500, $this->lang->line('error_operasi_data'));
			return;
		}
	
	}

	private function _get_list_peserta($compress=FALSE)
	{
		$this->load->model('peserta_model');

		$search_periode = $this->input->get('periode');
		$search_text = $this->input->get('text');
		$search_tahapan = $this->input->get('tahapan');
		$search_status = $this->input->get('status');

		$list_peserta = $this->peserta_model->get_detail(
			$search_periode,
			$search_text,
			$search_tahapan,
			array('mhs_nim', 'mhs_nama', 'tem_nama'),
			$this->session->dosen_id
			);		

		$result = array();
		foreach ($list_peserta as $value) {
			$status = '';
			switch ($value['pes_tahapan']) {
				case $this->config->item('pkl_tahapan_num_pendaftaran'):
					$status = $this->peserta_model->get_status_pendaftaran($value['pes_id']);
					break;
				case $this->config->item('pkl_tahapan_num_pelaksanaan'):
					$status = $this->peserta_model->get_status_pelaksanaan($value['pes_id']);
					break;
				case $this->config->item('pkl_tahapan_num_pasca_pkl'):
					$status = $this->peserta_model->get_status_pasca_pkl($value['pes_id']);
					break;
				case $this->config->item('pkl_tahapan_num_pasca_ujian'):
					$status = $this->peserta_model->get_status_pasca_ujian($value['pes_id']);
					break;
			}

			// Filter status.
			if ($search_status &&
				$status != $search_status)
			{
				continue;
			}

			if ($compress)
			{
				// Kirim data minimal.
				$result[] = array(
					'pes_id' => $value['pes_id'],
					'pes_tahapan' => $value['pes_tahapan'],
					'mhs_nim' => $value['mhs_nim'],
					'mhs_nama' => $value['mhs_nama'],
					'per_nama' => $value['per_nama'],
					'per_semester' => $value['per_semester'],
					'per_tahun' => $value['per_tahun'],
					'tem_nama' => $value['tem_nama'],
					'dos_nama' => $value['dos_nama'],
					'status' => $status
					);	
			}
			else
			{
				// Kirim data full tanpa filtering.
				$value['status'] = $status;
				$result[] = $value;
			}			
		}
		return $result;
	}

	private function _get_data_detail($id)
	{
		setlocale (LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');

		$this->load->model(array('peserta_model', 'periode_model', 'dosen_model'));

		$data_peserta_detail = $this->peserta_model->get_data_detail($id);

		$data_peserta_detail['transkrip'] = $this->peserta_model->get_data_transkrip($id);
		foreach ($data_peserta_detail['transkrip'] as &$transkrip) {
			$transkrip['waktu'] = strftime("%d %B %Y %H:%M", strtotime($transkrip['ptn_date']));
		}

		$data_peserta_detail['surat_pernyataan_memenuhi_syarat'] = $this->peserta_model->get_data_surat_pernyataan_memenuhi_syarat($id);
		foreach ($data_peserta_detail['surat_pernyataan_memenuhi_syarat'] as &$surat_pernyataan_memenuhi_syarat) {
			$surat_pernyataan_memenuhi_syarat['waktu'] = strftime("%d %B %Y %H:%M", strtotime($surat_pernyataan_memenuhi_syarat['pspms_date']));
		}

		$data_peserta_detail['surat_permohonan'] = $this->peserta_model->get_data_surat_permohonan($id);
		foreach ($data_peserta_detail['surat_permohonan'] as &$surat_permohonan) {
			$surat_permohonan['waktu'] = strftime("%d %B %Y %H:%M", strtotime($surat_permohonan['pspp_date']));
		}

		$data_peserta_detail['surat_penerimaan'] = $this->peserta_model->get_data_surat_penerimaan($id);
		foreach ($data_peserta_detail['surat_penerimaan'] as &$surat_penerimaan) {
			$surat_penerimaan['waktu'] = strftime("%d %B %Y %H:%M", strtotime($surat_penerimaan['pspt_date']));
		}

		$data_peserta_detail['aktivitas_harian'] = $this->_generate_aktivitas_harian($data_peserta_detail['pes_id'], $data_peserta_detail['per_id']);
		
		$data_peserta_detail['komentar'] = $this->peserta_model->get_data_komentar($id);
		foreach ($data_peserta_detail['komentar'] as &$komentar) {
			$komentar['waktu'] = strftime("%d %B %Y %H:%M", strtotime($komentar['pk_date']));
		}

		$data_peserta_detail['laporan_draft'] = $this->peserta_model->get_data_laporan_draft($id);
		foreach ($data_peserta_detail['laporan_draft'] as &$laporan_draft) {
			$laporan_draft['waktu'] = strftime("%d %B %Y %H:%M", strtotime($laporan_draft['pld_date']));
		}

		$data_peserta_detail['surat_selesai'] = $this->peserta_model->get_data_surat_selesai($id);
		foreach ($data_peserta_detail['surat_selesai'] as &$surat_selesai) {
			$surat_selesai['waktu'] = strftime("%d %B %Y %H:%M", strtotime($surat_selesai['pssp_date']));
		}

		$data_peserta_detail['laporan_revisi'] = $this->peserta_model->get_data_laporan_revisi($id);
		foreach ($data_peserta_detail['laporan_revisi'] as &$laporan_revisi) {
			$laporan_revisi['waktu'] = strftime("%d %B %Y %H:%M", strtotime($laporan_revisi['plr_date']));
		}

		$data_peserta_detail['laporan_lembar_pengesahan'] = $this->peserta_model->get_data_laporan_lembar_pengesahan($id);
		foreach ($data_peserta_detail['laporan_lembar_pengesahan'] as &$laporan_lembar_pengesahan) {
			$laporan_lembar_pengesahan['waktu'] = strftime("%d %B %Y %H:%M", strtotime($laporan_lembar_pengesahan['pllp_date']));
		}

		$data_peserta_detail['laporan_bukti_pengumpulan'] = $this->peserta_model->get_data_bukti_pengumpulan_laporan($id);
		foreach ($data_peserta_detail['laporan_bukti_pengumpulan'] as &$laporan_bukti_pengumpulan) {
			$laporan_bukti_pengumpulan['waktu'] = strftime("%d %B %Y %H:%M", strtotime($laporan_bukti_pengumpulan['pbpl_date']));
		}

		return array('peserta' => $data_peserta_detail);
	}

	private function _generate_aktivitas_harian($peserta_id, $periode_id)
	{
		$this->load->model(array('periode_model', 'peserta_model'));
		$this->load->helper('date');

		$result = array();
		$periode_data = $this->periode_model->get_data($periode_id);
		$aktivitas = $this->peserta_model->get_aktivitas_harian($peserta_id);			
		$range_tanggal = date_range(strtotime($periode_data['per_tgl_mulai']), strtotime($periode_data['per_tgl_selesai']), TRUE, 'd-m-Y');
		foreach ($range_tanggal as $tanggal) {
			// Cari aktivitas harian di tanggal ini.
			$aktivitas_tanggal = NULL;
			foreach($aktivitas as $item)
			{
				if (date('d-m-Y', strtotime($item['pah_date'])) == $tanggal)
				{
					$aktivitas_tanggal = $item;
					break;
				}
			}

			if ($aktivitas_tanggal)
			{
				$aktivitas_tanggal['tanggal'] = $tanggal;
			}
			else
			{
				$aktivitas_tanggal = array('tanggal' => $tanggal);
			}	

			// Apakah aktivitas di tanggal ini dapat diedit.
			if (strtotime($tanggal) < strtotime(date('Y-m-d 23:59')))
			{
				$aktivitas_tanggal['allow_edit'] = TRUE;
			}
			else
			{
				$aktivitas_tanggal['allow_edit'] = FALSE;
			}

			$result[] = $aktivitas_tanggal;			
		}

		return $result;
	}
}