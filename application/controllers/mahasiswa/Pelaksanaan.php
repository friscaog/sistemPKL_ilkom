<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Include the main TCPDF library.
require_once(APPPATH.'third_party'.DIRECTORY_SEPARATOR.'tcpdf'.DIRECTORY_SEPARATOR.'tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        
        // Footer text
        $this->SetFont('bookos', '', 8);        
        $this->Cell($this->getPageWidth()*0.75, 10, 'Komisi Praktek Kerja Lapangan PS. Teknik Informatika FMIPA Universitas Udayana', 'T', false, 'L', 0, '', 0, false, 'T', 'M');
        
        // Page number
        $this->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
        $this->Cell(0, 10, $this->getAliasNumPage().'/'.$this->getAliasNbPages(), 'T', false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

class Pelaksanaan extends CI_Controller {

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
		if ( ! $peserta_data || $peserta_data['pes_tahapan'] < $this->config->item('pkl_tahapan_num_pelaksanaan'))
		{
			redirect('mahasiswa');
		}
	}

	public function index()
	{
		$this->load->model(array('periode_model'));

		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($this->session->mahasiswa_id);

		// Data halaman.
		$data['page'] = 'mahasiswa';
		$data['title'] = 'Pelaksanaan';
		$data['tahapan'] = 1;
		$data['maks_tahapan'] = $peserta_data['pes_tahapan'];

		// Data waktu akhir pengisian form.
		// Waktu akhir pengisisan form pelaksanaan sama dengan waktu akhir pengisian form pasca PKL.
		$data['batas_waktu_submit'] = '';
		if ($peserta_data)
		{
			$periode_data = $this->periode_model->get_data($peserta_data['per_id']);
			$data['batas_waktu_submit'] = $periode_data['per_tgl_selesai_pasca_pkl'];
		}

		// Data peserta
		$data['peserta_status'] = $this->peserta_model->get_status_pelaksanaan($peserta_data['pes_id']);

		$this->load->view('templates/header', $data);
		$this->load->view('mahasiswa/pelaksanaan', $data);
		$this->load->view('templates/footer', $data);
	}

	public function laporan_judul($action='get')
	{
		$this->load->language('cs_pkl_error');

		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($this->session->mahasiswa_id);

		// Ambil judul PKL.
		if ($action==='get')
		{
			$result = array(
				'laporan_judul' => $peserta_data['pes_laporan_judul'], 
				'laporan_judul_konfirmasi' => $peserta_data['pes_laporan_judul_konfirmasi']);
			$this->_send_output(200, 'Ok', $result);
			return;
		}
		// Update judul PKL.
		elseif ($action==='update')
		{
			$this->load->library('form_validation');
			$this->load->model('periode_model');

			$this->form_validation->set_rules('laporan_judul', 'Judul Laporan PKL', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
			if ($this->form_validation->run() === FALSE)
			{
				$this->_send_output(400, form_error('laporan_judul', ' ', ' '));
				return;
			}
			else
			{
				// Cek apakah update data masih dapat dilakukan di tanggal ini.
		        $periode_data = $this->periode_model->get_data($peserta_data['per_id']);
		        if (time() > strtotime($periode_data['per_tgl_selesai_pasca_pkl'])+60*60*24)
		        {
		        	$this->_send_output(400, $this->lang->line('error_lewat_batas_waktu_submit'));
					return;
		        }

		        // Lakukan update data.
				try
				{
					$this->peserta_model->update_laporan_judul($peserta_data['pes_id'], $this->input->post('laporan_judul'));

					$result = array('laporan_judul' => $this->input->post('laporan_judul'), 'laporan_judul_konfirmasi' => 0);
					$this->_send_output(200, 'Ok', $result);
					return;
				}
				catch (Exception $e)
				{
					log_message('error', $e->getMessage());
					$this->_send_output(400, 'Terjadi kegagalan saat menyimpan data, silakan coba beberapa saat lagi atau hubungi administrator');
					return;
				}
			}
		}
	}

	public function komentar($action='get')
	{
		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($this->session->mahasiswa_id);

		if ($action === 'get')
		{
			$result = $this->_get_data_komentar($peserta_data['pes_id']);

			$this->_send_output(200, 'Ok', $result);
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
				$this->_send_output(400, form_error('komentar', ' ', ' '));
				return;
			}
			else
			{
				try
				{
					$this->peserta_model->add_komentar($peserta_data['pes_id'], $this->input->post('komentar'));

					$result = $this->_get_data_komentar($peserta_data['pes_id']);
					$this->_send_output(200, 'Ok', $result);
					return;
				}
				catch (Exception $e)
				{
					log_message('error', $e->getMessage());
					$this->_send_output(400, 'Terjadi kegagalan saat menyimpan data, silakan coba beberapa saat lagi atau hubungi administrator');
					return;
				}
			}

		}
	}

	private function _get_data_komentar($peserta_id)
	{
		setlocale (LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');
		
		$data_komentar = $this->peserta_model->get_data_komentar($peserta_id);
		foreach ($data_komentar as &$komentar)
		{
			$komentar['waktu'] = strftime("%d %B %Y %H:%M", strtotime($komentar['pk_date']));
		}
		return $data_komentar;
	}

	public function aktivitas_harian($action='get')
	{
		$this->load->model('periode_model');
		$this->load->helper('date');
		$this->load->language('cs_pkl_error');

		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($this->session->mahasiswa_id);

		if ($action === 'get')
		{
			$result = $this->_generate_aktivitas_harian($peserta_data);
			$this->_send_output(200, 'Ok', $result);
			return;
		}
		elseif ($action === 'download')
		{
			$this->_download_aktivitas_harian($peserta_data);
			return;
		}
		elseif ($action === 'update')
		{
			$this->load->library('form_validation');
			$this->load->model('periode_model');

			$this->form_validation->set_rules('tanggal', 'Tanggal', 'trim|required|callback_aktivitas_tanggal_check', 
				array(
					'required' => '%s tidak boleh kosong',
					'aktivitas_tanggal_check' => '%s tidak valid')
				);
			$this->form_validation->set_rules('penanggung_jawab', 'Nama Penanggung Jawab/Jabatan', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);
			$this->form_validation->set_rules('lokasi', 'Lokasi', 'trim|required',
				array('required' => '%s tidak boleh kosong')
				);
			$this->form_validation->set_rules('aktivitas', 'Aktivitas', 'trim|required', 
				array('required' => '%s tidak boleh kosong')
				);

			if ($this->form_validation->run() === FALSE)
			{
				$errors = validation_errors('|', ' ');
				$errors = explode('|', $errors);
				$errors = array_slice($errors, 1);
				$this->_send_output(400, $errors);
				return;
			}
			else
			{			
				// Cek apakah update data masih dapat dilakukan di tanggal ini.
		        $periode_data = $this->periode_model->get_data($peserta_data['per_id']);
		        if (time() > strtotime($periode_data['per_tgl_selesai_pasca_pkl'])+60*60*24)
		        {
		        	$this->_send_output(400, $this->lang->line('error_lewat_batas_waktu_submit'));
					return;
		        }

		        // Lakukan update data.
				try
				{
					$this->peserta_model->update_aktivitas_harian(
						$peserta_data['pes_id'],
						date('Y-m-d', strtotime($this->input->post('tanggal'))),
						$this->input->post('penanggung_jawab'),
						$this->input->post('lokasi'),
						$this->input->post('aktivitas')
						);

					$result = $this->_generate_aktivitas_harian($peserta_data);
					$this->_send_output(200, 'Ok', $result);
					return;
				}
				catch (Exception $e)
				{
					log_message('error', $e->getMessage());
					$this->_send_output(400, 'Terjadi kegagalan saat menyimpan data, silakan coba beberapa saat lagi atau hubungi administrator');
					return;
				}
			}
		}
	}

	private function _generate_aktivitas_harian($peserta_data)
	{
		$result = array();
		$periode_data = $this->periode_model->get_data($peserta_data['per_id']);
		$aktivitas = $this->peserta_model->get_aktivitas_harian($peserta_data['pes_id']);			
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
			// Aktivitas di tanggal tertentu hanya dapat diedit sampai 2 hari setelah tanggal tersebut.
			if (strtotime($tanggal) > strtotime(date('Y-m-d 23:59'))-60*60*24*3 &&
				strtotime($tanggal) <= strtotime(date('Y-m-d 23:59')))
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

	private function _download_aktivitas_harian($peserta_data)
	{
		// Generate konten html.
		$aktivitas = $this->_generate_aktivitas_harian($peserta_data);

		setlocale (LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');

		$html = '
		<div style="text-align:center"><h3>AKTIVITAS HARIAN PKL</h3></div>
		<table>
			<tr><td style="width:20%">Nama</td><td>: '.$peserta_data['mhs_nama'].'</td></tr>
			<tr><td style="width:20%">NIM</td><td>: '.$peserta_data['mhs_nim'].'</td></tr>
			<tr><td style="width:20%">Lokasi PKL</td><td>: '.$peserta_data['tem_nama'].'</td></tr>
			<tr><td style="width:20%">Waktu Pelaksanaan</td><td>: '.strftime("%d %B %Y", strtotime($peserta_data['per_tgl_mulai'])).' - '.strftime("%d %B %Y", strtotime($peserta_data['per_tgl_selesai'])).'</td></tr>
		</table>
		<br/>
		<br/>
		<table border="1" cellpadding="10">
			<tr>
				<th rowspan="2" align="center" width="5%">No.</th>
				<th rowspan="2" align="center" width="20%">Nama Penanggung Jawab/Jabatan</th>
				<th colspan="3" align="center" width="55%">Pelaksanaan PKL</th>
				<th rowspan="2" align="center" width="20%">Keterangan</th>
			</tr>
			<tr>
				<th align="center" width="10%">Tanggal</th>
				<th align="center" width="15%">Lokasi</th>
				<th align="center" width="30%">Aktivitas</th>
			</tr>
			';

		$i=1;
		foreach ($aktivitas as $value) {
			$penanggung_jawab = (isset($value['pah_penanggung_jawab'])) ? $value['pah_penanggung_jawab'] : '';
			$lokasi = (isset($value['pah_lokasi'])) ? $value['pah_lokasi'] : '';
			$aktivitas = (isset($value['pah_aktivitas'])) ? $value['pah_aktivitas'] : '';
			$html .= '
			<tr>
				<td align="center" style="height:50px">'.$i.'</td>
				<td style="height:50px">'.$penanggung_jawab.'</td>
				<td style="height:50px">'.$value['tanggal'].'</td>
				<td style="height:50px">'.$lokasi.'</td>
				<td style="height:50px">'.$aktivitas.'</td>
				<td style="height:50px"></td>
			</tr>';
			$i++;
		}

		$html .= '</table>';

		// ---------------------------------------------------------

		// create new PDF document
		$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('CS PKL');
		$pdf->SetAuthor('Jurusan Ilmu Komputer Universitas Udayana');
		$pdf->SetTitle('Aktivitas Harian PKL');
		$pdf->SetSubject('Aktivitas Harian PKL');
		$pdf->SetKeywords('Aktivitas, PKL');

		// set default header data
		// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// remove default header
		$pdf->setPrintHeader(false);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
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
		$pdf->SetFont('helvetica', '', 10);

		// add a page.
		$pdf->AddPage();

		// Tampilkan konten.
		$pdf->writeHTML($html, true, false, true, false, '');	

		$pdf->SetFont('helvetica', '', 12);

		// Tentukan apakah tinggi teks tanda tangan cukup untuk baris yang tersedia atau tidak.
		// Jika tidak cukup, pindah ke halaman berikutnya.
		$yPos = $pdf->getY();
		$sHeight = $pdf->getStringHeight(0, 'a')*8;	// Tinggi teks terdiri dari 8 baris.
		$pageHeight = $pdf->getPageHeight();
		if ($yPos+$sHeight > $pageHeight)
		{
			$pdf->AddPage();
			$pdf->lastPage();
		}

		// Tulis teks tanda tangan.
		$pdf->Cell($pdf->getPageWidth()*0.6); // Margin kiri.
		$pdf->MultiCell(0, 0, ".............................. , ..............................\nPembimbing Lapangan,\n\n\n\n\n\n...............................................................", 0, 'L');
		// $pdf->write(0, 'yPos:'.$yPos.' pageHeight:'.$pageHeight.' sHeight:'.$sHeight);

		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		$pdf->Output("aktivitas_harian_{$this->session->nim}.pdf", 'I');
	}

	private function _send_output($code, $message, $result=NULL)
	{
		$this->output
	        ->set_status_header($code)
	        ->set_content_type('application/json', 'utf-8')
	        ->set_output(json_encode(array('status' => $code, 'message' => $message, 'result' => $result)));
	}

	/**
	 * Mengecek apakah tanggal aktivitias valid atau tidak.
	 * Pengisian aktivitas harian diperbolehkan sampai 2 hari setelah tanggal aktivitas dilakukan.
	 */
	public function aktivitas_tanggal_check($tanggal)
	{
		$peserta_data = $this->peserta_model->get_data_in_active_periode_by_mahasiswa($this->session->mahasiswa_id);
		$periode_data = $this->periode_model->get_data($peserta_data['per_id']);

		// Tanggal dalam format d-m-Y.
		$tanggal = strtotime($tanggal);
		$today = strtotime(date('Y-m-d 23:59'));
		$today_min_3 = $today - 60*60*24*3;
		$tanggal_mulai = strtotime($periode_data['per_tgl_mulai']);
		$tanggal_selesai = strtotime($periode_data['per_tgl_selesai'])+60*60*24;

		if (
			$tanggal >= $tanggal_mulai &&
			$tanggal < $tanggal_selesai &&
			$tanggal > $today_min_3 &&
			$tanggal <= $today)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

}