<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Peserta_model extends CI_Model {

	const TABLE_PESERTA = 'peserta';
	const TABLE_TRANSKRIP = 'peserta_transkrip_nilai';
	const TABLE_SURAT_PERMOHONAN = 'peserta_surat_permohonan_pkl';
	const TABLE_SURAT_PENERIMAAN = 'peserta_surat_penerimaan_tempat';
	const TABLE_PERIODE = 'periode';
	const TABLE_LAPORAN_DRAFT = 'peserta_laporan_draft';
	const TABLE_SURAT_SELESAI_PKL = 'peserta_surat_selesai_pkl';
	const TABLE_LAPORAN_REVISI = 'peserta_laporan_revisi';
	const TABLE_LAPORAN_LEMBAR_PENGESAHAN = 'peserta_laporan_lembar_pengesahan';
	const TABLE_BUKTI_PENGUMPULAN_LAPORAN = 'peserta_bukti_pengumpulan_laporan';
	const TABLE_KOMENTAR = 'peserta_komentar';
	const TABLE_DOSEN = 'dosen';
	const TABLE_MAHASISWA = 'mahasiswa';
	const TABLE_AKTIVITAS_HARIAN = 'peserta_aktivitas_harian';
	const TABLE_TEMPAT_PKL = 'tempat_pkl';
	const TABLE_SURAT_PERNYATAAN_MEMENUHI_SYARAT = 'peserta_surat_pernyataan_memenuhi_syarat';

	const STATUS_BELUM_LENGKAP = 'belum_lengkap';
	const STATUS_MENUNGGU_KONFIRMASI_PEMBIMBING = 'mengunggu_konfirmasi_pembimbing';
	const STATUS_PENDING = 'pending';
	const STATUS_APPROVED = 'approved';

	public function __construct()
	{
		parent::__construct();

		$this->load->database();		
	}

	public function get_detail(
		$search_periode=NULL,
		$search_text=NULL,
		$search_tahapan=NULL,
		$search_text_fields=array('mhs_nim', 'mhs_nama', 'tem_nama', 'dos_nama'),
		$dosen_id=NULL
		)	
	{
		$this->db->select('*');
		$this->db->from(self::TABLE_PESERTA);
		$this->db->join(self::TABLE_MAHASISWA, 'peserta.mhs_id=mahasiswa.mhs_id', 'left');
		$this->db->join(self::TABLE_PERIODE, 'peserta.per_id=periode.per_id', 'left');
		$this->db->join(self::TABLE_TEMPAT_PKL, 'peserta.tem_id=tempat_pkl.tem_id', 'left');
		$this->db->join(self::TABLE_DOSEN, 'peserta.pes_pembimbing=dosen.dos_id', 'left');

		if ($search_periode || $search_periode === '0')
		{
			$this->db->where('peserta.per_id', $search_periode);
		}

		if ($search_tahapan || $search_tahapan === '0')
		{
			$this->db->where('peserta.pes_tahapan', $search_tahapan);
		}

		// Jika search text tidak kosong dan ada minimal satu field pencarian.
		if (($search_text || $search_text === '0') &&
			(in_array('mhs_nim', $search_text_fields) ||
				in_array('mhs_nama', $search_text_fields) ||
				in_array('tem_nama', $search_text_fields) ||
				in_array('dos_nama', $search_text_fields)))
		{
			$this->db->group_start();
			if (in_array('mhs_nim', $search_text_fields))
			{
				$this->db->or_like('mahasiswa.mhs_nim', $search_text);	
			}
			if (in_array('mhs_nama', $search_text_fields))
			{
				$this->db->or_like('mahasiswa.mhs_nama', $search_text);	
			}
			if (in_array('tem_nama', $search_text_fields))
			{
				$this->db->or_like('tempat_pkl.tem_nama', $search_text);	
			}
			if (in_array('dos_nama', $search_text_fields))
			{
				$this->db->or_like('dosen.dos_nama', $search_text);	
			}			
			$this->db->group_end();
		}

		if ($dosen_id || $dosen_id === '0')
		{
			$this->db->where('dosen.dos_id', $dosen_id);
		}

		return $this->db->get()->result_array();
	}

	/**
	 * Mengecek apakah mahasiswa sudah pernah terdaftar menjadi peserta PKL.
	 */
	public function is_mahasiswa_exist($mahasiswa_id)
	{
		$this->db->select('*');
		$this->db->limit(1);
		$this->db->from(self::TABLE_PESERTA);
		$this->db->where(array('peserta.mhs_id' => $mahasiswa_id));
		// $this->db->join(self::TABLE_PERIODE, 'peserta.per_id=periode.per_id');
		// $this->db->where(array('peserta.mhs_id' => $mahasiswa_id, 'periode.per_aktif' => 1));
		return $this->db->get()->num_rows() > 0;
	}

	public function get_data($id)
	{
		$result = $this->db->get_where(self::TABLE_PESERTA, array('pes_id' => $id));
		return $result->row_array();
	}

	public function get_data_detail($id)
	{
		$this->db->select('*');
		$this->db->from(self::TABLE_PESERTA);
		$this->db->join(self::TABLE_MAHASISWA, 'peserta.mhs_id=mahasiswa.mhs_id', 'left');
		$this->db->join(self::TABLE_PERIODE, 'peserta.per_id=periode.per_id', 'left');
		$this->db->join(self::TABLE_TEMPAT_PKL, 'peserta.tem_id=tempat_pkl.tem_id', 'left');
		$this->db->join(self::TABLE_DOSEN, 'peserta.pes_pembimbing=dosen.dos_id', 'left');
		$this->db->where('pes_id', $id);
		$this->db->limit(1);
		
		return $this->db->get()->row_array();
	}

	/**
	 * Mendapatkan data peserta berdasarkan mahasiswa ID.
	 * Tidak dilakukan filter terhadap periode aktif.
	 * Nama method tidak diubah setelah penghilangan filter periode aktif karena sudah 
	 * banyak digunakan oleh kontroller kontroller, sehingga dapat beresiko kesalahan 
	 * yang cukup besar. Perubahan nama method akan dilakukan setelah setup version control.
	 */
	public function get_data_in_active_periode_by_mahasiswa($mahasiswa_id)
	{
		$this->db->select('*');
		$this->db->from(self::TABLE_PESERTA);
		$this->db->join(self::TABLE_PERIODE, 'peserta.per_id=periode.per_id');
		$this->db->join(self::TABLE_MAHASISWA, 'peserta.mhs_id=mahasiswa.mhs_id');
		$this->db->join(self::TABLE_TEMPAT_PKL, 'peserta.tem_id=tempat_pkl.tem_id');
		// $this->db->where(array('peserta.mhs_id' => $mahasiswa_id, 'periode.per_aktif' => 1));
		$this->db->where(array('peserta.mhs_id' => $mahasiswa_id));
		$result = $this->db->get();
		
		return $result->row_array();
	}

	/**
     * Mendapatkan status pendaftaran peserta. Status bernilai salah satu dari nilai berikut:
     * - belum_lengkap 
     * - mengunggu_konfirmasi_pembimbing
     * - pending (data sudah lengkap, tinggal menunggu admin untuk melanjutkan ke tahap berikutnya)
     * - approved (administrator telah melanjutkan ke tahap berikutnya)
     * @return string Status peserta pada tahap pendaftaran
     */
    public function get_status_pendaftaran($peserta_id)
    {
    	$data = $this->get_data($peserta_id);

    	// Apakah status peserta telah berada pada tahap berikutnya.
    	if($data['pes_tahapan'] > $this->config->item('pkl_tahapan_num_pendaftaran'))
    	{
    		return $this->config->item('pkl_status_approved');
    	}

    	// Mengecek kelengkapan pendaftaran.
    	// - Periode,
    	// - Institusi tempat PKL,
    	// - Transkrip nilai,
    	// - Surat pernyataan memenuhi syarat mengikuti PKL,
    	// - Surat permohonan PKL,
    	// - Surat penerimaan tempat PKL,
    	// - Dosen pembimbing.    	
    	if (
    		! $data['per_id'] OR
    		! $data['tem_id'] OR 
    		! $this->get_data_transkrip($peserta_id) OR
    		! $this->get_data_surat_pernyataan_memenuhi_syarat($peserta_id) OR
    		! $this->get_data_surat_permohonan($peserta_id) OR
    		! $this->get_data_surat_penerimaan($peserta_id) OR
    		! $data['pes_pembimbing'])
    	{
    		return $this->config->item('pkl_status_belum_lengkap');
    	}

    	// Data pendaftaran sudah lengkap.
    	// Dalam tahap ini tidak diperlukan konfirmasi pembimbing.

    	// Peserta dalam status pending.
    	return $this->config->item('pkl_status_pending');
    }

    /**
     * Mendapatkan status peserta dalam tahap pelaksanaan. Status bernilai salah satu dari nilai berikut:
     * - belum_lengkap 
     * - mengunggu_konfirmasi_pembimbing
     * - pending (data sudah lengkap, tinggal menunggu admin untuk melanjutkan ke tahap berikutnya)
     * - approved (administrator telah melanjutkan ke tahap berikutnya)
     * @return string Status peserta pada tahap pelaksanaan
     */
    public function get_status_pelaksanaan($peserta_id)
    {
    	$data = $this->get_data($peserta_id);

    	// Apakah status peserta telah berada pada tahap berikutnya.
    	if($data['pes_tahapan'] > $this->config->item('pkl_tahapan_num_pelaksanaan'))
    	{
    		return $this->config->item('pkl_status_approved');
    	}

    	// Mengecek kelengkapan pelaksanaan.
    	// - Judul laporan PKL
    	// - Aktivitas harian untuk seluruh tanggal
    	if (
    		! $data['pes_laporan_judul'] OR
    		! $this->_is_aktivitas_harian_full($peserta_id, $data['per_id']))
    	{
    		return $this->config->item('pkl_status_belum_lengkap');
    	}

    	// Mengecek apakah judul laporan dan seluruh aktivitas harian sudah dikonfirmasi pembimbing.
    	if ( 
    		! $data['pes_laporan_judul_konfirmasi'] OR
    		! $this->_is_aktivitas_harian_all_confirmed($peserta_id, $data['per_id']))
    	{
    		return $this->config->item('pkl_status_menunggu_konfirmasi_pembimbing');
    	}

    	// Peserta dalam status pending.
    	return $this->config->item('pkl_status_pending');
    }

    /**
     * Mengecek apakah seluruh aktivitas harian telah diisi oleh peserta.
     * @param  [type]  $peserta_id id peserta
     * @param  [type]  $periode_id id periode PKL
     * @return boolean             Apakah seluruh aktivitas telah diisi oleh peserta.
     */
    private function _is_aktivitas_harian_full($peserta_id, $periode_id)
	{
		$CI =& get_instance();
		$CI->load->model('periode_model');
    	$CI->load->helper('date');

		$result = array();
		$periode_data = $CI->periode_model->get_data($periode_id);
		$aktivitas = $this->get_aktivitas_harian($peserta_id);			
		$range_tanggal = date_range(strtotime($periode_data['per_tgl_mulai']), strtotime($periode_data['per_tgl_selesai']), TRUE, 'Y-m-d');

		$result = TRUE;
		foreach ($range_tanggal as $tanggal) {
			// Cari aktivitas harian di tanggal ini.
			$ada_aktivitas_di_tanggal_ini = FALSE;
			foreach($aktivitas as $item)
			{
				if ($item['pah_date'] == $tanggal)
				{
					$ada_aktivitas_di_tanggal_ini = TRUE;
					break;
				}
			}

			if ( ! $ada_aktivitas_di_tanggal_ini)
			{
				// Tidak ada aktivitas di tanggal ini.
				$result = FALSE;
				break;
			}		
		}

		return $result;
	}

	/**
     * Mengecek apakah seluruh aktivitas harian telah dikonfirmasi oleh pembimbing.
     * @param  [type]  $peserta_id id peserta
     * @param  [type]  $periode_id id periode PKL
     * @return boolean             Apakah seluruh aktivitas telah dikonfirmasi oleh pembimbing.
     */
	private function _is_aktivitas_harian_all_confirmed($peserta_id, $periode_id)
	{
		$CI =& get_instance();
		$CI->load->model('periode_model');
    	$CI->load->helper('date');

		$result = array();
		$periode_data = $CI->periode_model->get_data($periode_id);
		$aktivitas = $this->get_aktivitas_harian($peserta_id);			
		$range_tanggal = date_range(strtotime($periode_data['per_tgl_mulai']), strtotime($periode_data['per_tgl_selesai']), TRUE, 'Y-m-d');

		$result = TRUE;
		foreach ($range_tanggal as $tanggal) {
			// Cari aktivitas harian di tanggal ini.
			$aktivitas_data = NULL;
			foreach($aktivitas as $item)
			{
				if ($item['pah_date'] == $tanggal)
				{
					$aktivitas_data = $item;
					break;
				}
			}

			if ( 
				! $aktivitas_data OR
				! $aktivitas_data['pah_konfirmasi'])
			{
				// Tidak ada aktivitas di tanggal ini atau aktivitas di tanggal ini belum dikonfirmasi.
				$result = FALSE;
				break;
			}		
		}

		return $result;
	}

    /**
     * Mendapatkan status peserta dalam tahap pasca pkl. Status bernilai salah satu dari nilai berikut:
     * - belum_lengkap 
     * - mengunggu_konfirmasi_pembimbing
     * - pending (data sudah lengkap, tinggal menunggu admin untuk melanjutkan ke tahap berikutnya)
     * - approved (administrator telah melanjutkan ke tahap berikutnya)
     * @return string Status peserta pada tahap pasca PKL.
     */
    public function get_status_pasca_pkl($peserta_id)
    {
    	$data = $this->get_data($peserta_id);

    	// Apakah status peserta telah berada pada tahap berikutnya.
    	if($data['pes_tahapan'] > $this->config->item('pkl_tahapan_num_pasca_pkl'))
    	{
    		return $this->config->item('pkl_status_approved');
    	}

    	// Mengecek kelengkapan pasca PKL.
    	// - Draft laporan
    	// - Surat keterangan telah selesai PKL
    	$draft_laporan = $this->get_data_laporan_draft($peserta_id);
    	if (
    		! $draft_laporan OR
    		! $this->get_data_surat_selesai($peserta_id))
    	{
    		return $this->config->item('pkl_status_belum_lengkap');
    	}

    	// Mengecek apakah draft laporan terakhir sudah dikonfirmasi pembimbing.
    	if ( ! $draft_laporan OR ! $draft_laporan[count($draft_laporan)-1]['pld_konfirmasi'])
    	{
    		return $this->config->item('pkl_status_menunggu_konfirmasi_pembimbing');
    	}
    	
    	// Peserta dalam status pending.
    	return $this->config->item('pkl_status_pending');
    }

    /**
     * Mendapatkan status peserta dalam tahap pasca ujian. Status bernilai salah satu dari nilai berikut:
     * - belum_lengkap 
     * - mengunggu_konfirmasi_pembimbing
     * - pending (data sudah lengkap, tinggal menunggu admin untuk melanjutkan ke tahap berikutnya)
     * - approved (administrator telah melanjutkan ke tahap berikutnya)
     * @return string Status peserta pada tahap pasca PKL.
     */
    public function get_status_pasca_ujian($peserta_id)
    {
    	$data = $this->get_data($peserta_id);

    	// Apakah status peserta telah berada pada tahap berikutnya.
    	if($data['pes_tahapan'] > $this->config->item('pkl_tahapan_num_pasca_ujian'))
    	{
    		return $this->config->item('pkl_status_approved');
    	}

    	// Mengecek kelengkapan pasca ujian.
    	// - Revisi laporan
    	// - Lembar pengesahan laporan.
    	// - Bukti pengumpulan laporan.
    	if (
    		! $this->get_data_laporan_revisi($peserta_id) OR
    		! $this->get_data_laporan_lembar_pengesahan($peserta_id) OR
    		! $this->get_data_bukti_pengumpulan_laporan($peserta_id))
    	{
    		return $this->config->item('pkl_status_belum_lengkap');
    	}

    	// Data pendaftaran sudah lengkap.
    	// Dalam tahap ini tidak diperlukan konfirmasi pembimbing.
    	
    	// Peserta dalam status pending.
    	return $this->config->item('pkl_status_pending');
    }

    public function get_pesan_admin($peserta_id)
    {
    	$data = $this->get_data($peserta_id);
    	return $data['pes_pesan_admin'];
    }

	public function get_data_laporan_revisi($id)
	{
		$this->load->helper('url');

		$file_path = $this->config->item('pkl_laporan_revisi_path');
		$data = $this->db->order_by('plr_date', 'ASC')->get_where(self::TABLE_LAPORAN_REVISI, array('pes_id' => $id))->result_array();
		foreach ($data as &$value) {
			$value['file_url'] = base_url().$file_path.$value['plr_file'];
		}

		return $data;
	}

	public function get_data_laporan_lembar_pengesahan($id)
	{
		$this->load->helper('url');

		$file_path = $this->config->item('pkl_laporan_lembar_pengesahan_path');
		$data = $this->db->order_by('pllp_date', 'ASC')->get_where(self::TABLE_LAPORAN_LEMBAR_PENGESAHAN, array('pes_id' => $id))->result_array();
		foreach ($data as &$value) {
			$value['file_url'] = base_url().$file_path.$value['pllp_file'];
		}

		return $data;
	}

	public function get_data_bukti_pengumpulan_laporan($id)
	{
		$this->load->helper('url');

		$file_path = $this->config->item('pkl_bukti_pengumpulan_laporan_path');
		$data = $this->db->order_by('pbpl_date', 'ASC')->get_where(self::TABLE_BUKTI_PENGUMPULAN_LAPORAN, array('pes_id' => $id))->result_array();
		foreach ($data as &$value) {
			$value['file_url'] = base_url().$file_path.$value['pbpl_file'];
		}

		return $data;
	}

	public function get_data_laporan_draft($id)
	{
		$this->load->helper('url');

		$file_path = $this->config->item('pkl_laporan_draft_path');
		$data = $this->db->order_by('pld_date', 'ASC')->get_where(self::TABLE_LAPORAN_DRAFT, array('pes_id' => $id))->result_array();
		foreach ($data as &$value) {
			$value['file_url'] = base_url().$file_path.$value['pld_file'];
		}

		return $data;
	}

	public function get_data_surat_selesai($id)
	{
		$this->load->helper('url');

		$file_path = $this->config->item('pkl_surat_selesai_path');
		$data = $this->db->order_by('pssp_date', 'ASC')->get_where(self::TABLE_SURAT_SELESAI_PKL, array('pes_id' => $id))->result_array();
		foreach ($data as &$value) {
			$value['file_url'] = base_url().$file_path.$value['pssp_file'];
		}

		return $data;
	}

	public function get_data_transkrip($id)
	{
		$this->load->helper('url');

		$file_path = $this->config->item('pkl_transkrip_path');
		$data = $this->db->order_by('ptn_date', 'ASC')->get_where(self::TABLE_TRANSKRIP, array('pes_id' => $id))->result_array();
		foreach ($data as &$value) {
			$value['file_url'] = base_url().$file_path.$value['ptn_file'];
		}

		return $data;
	}

	public function get_data_surat_permohonan($id)
	{
		$this->load->helper('url');

		$file_path = $this->config->item('pkl_surat_permohonan_path');
		$data = $this->db->order_by('pspp_date', 'ASC')->get_where(self::TABLE_SURAT_PERMOHONAN, array('pes_id' => $id))->result_array();
		foreach ($data as &$value) {
			$value['file_url'] = base_url().$file_path.$value['pspp_file'];
		}

		return $data;
	}

	public function get_data_surat_penerimaan($id)
	{
		$this->load->helper('url');

		$file_path = $this->config->item('pkl_surat_penerimaan_path');
		$data = $this->db->order_by('pspt_date', 'ASC')->get_where(self::TABLE_SURAT_PENERIMAAN, array('pes_id' => $id))->result_array();
		foreach ($data as &$value) {
			$value['file_url'] = base_url().$file_path.$value['pspt_file'];
		}

		return $data;
	}

	public function get_data_surat_pernyataan_memenuhi_syarat($id)
	{
		$this->load->helper('url');

		$file_path = $this->config->item('pkl_surat_pernyataan_memenuhi_syarat_path');
		$data = $this->db->order_by('pspms_date', 'ASC')->get_where(self::TABLE_SURAT_PERNYATAAN_MEMENUHI_SYARAT, array('pes_id' => $id))->result_array();
		foreach ($data as &$value) {
			$value['file_url'] = base_url().$file_path.$value['pspms_file'];
		}

		return $data;
	}

	public function get_data_komentar($id)
	{
		$this->db->select('peserta_komentar.*, mahasiswa.mhs_nama, dosen.dos_nama');
		$this->db->from(self::TABLE_KOMENTAR);
		$this->db->join(self::TABLE_PESERTA, 'peserta_komentar.pes_id=peserta.pes_id');
		$this->db->join(self::TABLE_MAHASISWA, 'peserta.mhs_id=mahasiswa.mhs_id');
		$this->db->join(self::TABLE_DOSEN, 'peserta_komentar.dos_id=dosen.dos_id', 'left');
		$this->db->where(array('peserta_komentar.pes_id' => $id));
		$this->db->order_by('pk_date', 'DESC');
		return $this->db->get()->result_array();
	}

	public function get_aktivitas_harian($id)
	{
		return $this->db->get_where(self::TABLE_AKTIVITAS_HARIAN, array('pes_id' => $id))->result_array();
	}	

	public function get_data_aktivitas_harian($aktivitas_id)
	{
		return $this->db->limit(1)->get_where(self::TABLE_AKTIVITAS_HARIAN, array('pah_id' => $aktivitas_id))->row_array();
	}

	public function add(
		$mahasiswa_id, 
		$periode_id, 
		$tempat_id, 
		$pembimbing_id, 
		$transkrip_filename = '', 
		$surat_permohonan_filename = '', 
		$surat_penerimaan_filename = '',
		$surat_pernyataan_memenuhi_syarat_filename = '')
	{
		$data = array(
			'mhs_id' => $mahasiswa_id,
			'per_id' => $periode_id,
			'tem_id' => $tempat_id,
			'pes_pembimbing' => $pembimbing_id,
			'pes_tahapan' => 0);

		$now = date("Y-m-d H:i:s");
		
		// Start transaction.
		$this->db->trans_start();

		$this->db->insert(self::TABLE_PESERTA, $data);
		$peserta_id = $this->db->insert_id();

		if ($transkrip_filename)
		{
			$this->db->insert(self::TABLE_TRANSKRIP, array('pes_id' => $peserta_id, 'ptn_file' => $transkrip_filename, 'ptn_date' => $now));	
		}
		
		if ($surat_permohonan_filename)
		{
			$this->db->insert(self::TABLE_SURAT_PERMOHONAN, array('pes_id' => $peserta_id, 'pspp_file' => $surat_permohonan_filename, 'pspp_date' => $now));	
		}
		
		if ($surat_penerimaan_filename)
		{
			$this->db->insert(self::TABLE_SURAT_PENERIMAAN, array('pes_id' => $peserta_id, 'pspt_file' => $surat_penerimaan_filename, 'pspt_date' => $now));	
		}

		if ($surat_pernyataan_memenuhi_syarat_filename)
		{
			$this->db->insert(self::TABLE_SURAT_PERNYATAAN_MEMENUHI_SYARAT, array('pes_id' => $peserta_id, 'pspms_file' => $surat_pernyataan_memenuhi_syarat_filename, 'pspms_date' => $now));			
		}

		// End transaction;
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			$db_error = $this->db->error();
			throw new Exception(__FILE__." Peserta_model->add
				Transaction simpan peserta ke database gagal
				- mahasiswa_id:{$mahasiswa_id} 
				- periode_id:{$periode_id} 
				- tempat_id:{$tempat_id} 
				- pembimbing_id:{$pembimbing_id} 
				- transkrip_filename:{$transkrip_filename} 
				- surat_permohonan_filename:{$surat_permohonan_filename} 
				- surat_penerimaan_filename:{$surat_penerimaan_filename}");
		}

		return $peserta_id;
	}

	public function delete($id)
	{
		$result = $this->db->delete(self::TABLE_PESERTA, array('pes_id' => $id));
		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Peserta_model->delete
				Delete peserta gagal
				- id:{$id}");
		}
		
		return TRUE;
	}

	public function update_pendaftaran(
		$peserta_id, 
		$transkrip_filename = '', 
		$surat_permohonan_filename = '', 
		$surat_penerimaan_filename = '',
		$periode_id = NULL,
		$pembimbing_id = NULL,
		$surat_pernyataan_memenuhi_syarat_filename = '',
		$tempat_pkl_id = NULL)
	{
		$now = date("Y-m-d H:i:s");

		// Start transaction.
		$this->db->trans_start();

		if ($transkrip_filename)
		{
			$this->db->insert(self::TABLE_TRANSKRIP, array('pes_id' => $peserta_id, 'ptn_file' => $transkrip_filename, 'ptn_date' => $now));	
		}
		
		if ($surat_permohonan_filename)
		{
			$this->db->insert(self::TABLE_SURAT_PERMOHONAN, array('pes_id' => $peserta_id, 'pspp_file' => $surat_permohonan_filename, 'pspp_date' => $now));	
		}
		
		if ($surat_penerimaan_filename)
		{
			$this->db->insert(self::TABLE_SURAT_PENERIMAAN, array('pes_id' => $peserta_id, 'pspt_file' => $surat_penerimaan_filename, 'pspt_date' => $now));	
		}

		if ($surat_pernyataan_memenuhi_syarat_filename)
		{
			$this->db->insert(self::TABLE_SURAT_PERNYATAAN_MEMENUHI_SYARAT, array('pes_id' => $peserta_id, 'pspms_file' => $surat_pernyataan_memenuhi_syarat_filename, 'pspms_date' => $now));			
		}

		if ($periode_id)
		{
			$this->db->where('pes_id', $peserta_id);
			$this->db->update(self::TABLE_PESERTA, array('per_id' => $periode_id));
		}

		if ($pembimbing_id)
		{
			$this->db->where('pes_id', $peserta_id);
			$this->db->update(self::TABLE_PESERTA, array('pes_pembimbing' => $pembimbing_id));
		}

		if ($tempat_pkl_id)
		{
			$this->db->where('pes_id', $peserta_id);
			$this->db->update(self::TABLE_PESERTA, array('tem_id' => $tempat_pkl_id));
		}

		// End transaction;
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			$db_error = $this->db->error();
			throw new Exception(__FILE__." Peserta_model->update_pendaftaran
				Transaction update data pendaftaran peserta ke database gagal
				- peserta_id:{$peserta_id} 
				- transkrip_filename:{$transkrip_filename} 
				- surat_permohonan_filename:{$surat_permohonan_filename} 
				- surat_penerimaan_filename:{$surat_penerimaan_filename}
				- periode_id:{$periode_id}
				- pembimbing_id:{$pembimbing_id}
				- surat_pernyataan_memenuhi_syarat_filename:{$surat_pernyataan_memenuhi_syarat_filename}
				- tempat_pkl_id:{$tempat_pkl_id}");
		}

		return TRUE;
	}

	public function update_pasca_pkl(
		$peserta_id,
		$laporan_draft_filename = '',
		$surat_selesai_filename = '')
	{
		$now = date("Y-m-d H:i:s");

		// Start transaction.
		$this->db->trans_start();

		if ($laporan_draft_filename)
		{
			$this->db->insert(self::TABLE_LAPORAN_DRAFT, array('pes_id' => $peserta_id, 'pld_file' => $laporan_draft_filename, 'pld_date' => $now));	
		}		

		if ($surat_selesai_filename)
		{
			$this->db->insert(self::TABLE_SURAT_SELESAI_PKL, array('pes_id' => $peserta_id, 'pssp_file' => $surat_selesai_filename, 'pssp_date' => $now));	
		}

		// End transaction;
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			$db_error = $this->db->error();
			throw new Exception(__FILE__." Peserta_model->update_pasca_pkl
				Transaction update data pasca PKL peserta ke database gagal
				- peserta_id:{$peserta_id} 
				- laporan_draft_filename:{$laporan_draft_filename} 
				- surat_selesai_filename:{$surat_selesai_filename}");
		}

		return TRUE;
	}

	public function update_pasca_ujian(
		$peserta_id,
		$laporan_revisi_filename = '',
		$laporan_lembar_pengesahan_filename = '',
		$bukti_pengumpulan_laporan_filename = '')
	{
		$now = date("Y-m-d H:i:s");

		// Start transaction.
		$this->db->trans_start();

		if ($laporan_revisi_filename)
		{
			$this->db->insert(self::TABLE_LAPORAN_REVISI, array('pes_id' => $peserta_id, 'plr_file' => $laporan_revisi_filename, 'plr_date' => $now));	
		}		

		if ($laporan_lembar_pengesahan_filename)
		{
			$this->db->insert(self::TABLE_LAPORAN_LEMBAR_PENGESAHAN, array('pes_id' => $peserta_id, 'pllp_file' => $laporan_lembar_pengesahan_filename, 'pllp_date' => $now));	
		}

		if ($bukti_pengumpulan_laporan_filename)
		{
			$this->db->insert(self::TABLE_BUKTI_PENGUMPULAN_LAPORAN, array('pes_id' => $peserta_id, 'pbpl_file' => $bukti_pengumpulan_laporan_filename, 'pbpl_date' => $now));	
		}

		// End transaction;
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			$db_error = $this->db->error();
			throw new Exception(__FILE__." Peserta_model->update_pasca_ujian
				Transaction update data pasca ujian ke database gagal
				- peserta_id:{$peserta_id} 
				- laporan_revisi_filename:{$laporan_revisi_filename} 
				- laporan_lembar_pengesahan_filename:{$laporan_lembar_pengesahan_filename}
				- bukti_pengumpulan_laporan_filename:{$bukti_pengumpulan_laporan_filename}");
		}

		return TRUE;
	}

	public function update_laporan_judul($peserta_id, $judul)
	{
		$this->db->set('pes_laporan_judul', $judul);
		$this->db->set('pes_laporan_judul_konfirmasi', 0);
		$this->db->where('pes_id', $peserta_id);
		$result = $this->db->update(self::TABLE_PESERTA);

		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Peserta_model->update_laporan_judul
				Update laporan_judul gagal
				- peserta_id:{$peserta_id} 
				- judul:{$judul}");
		}
		
		return TRUE;		
	}

	public function update_aktivitas_harian(
		$peserta_id, 
		$tanggal, 
		$penanggung_jawab,
		$lokasi,
		$aktivitas)
	{
		// Jika sudah ada aktivitas di tanggal tersebut, update aktivitas.
		if ($this->db->get_where(self::TABLE_AKTIVITAS_HARIAN, array('pes_id' => $peserta_id, 'pah_date' => $tanggal))->num_rows())
		{
			$data = array(
				'pah_penanggung_jawab' => $penanggung_jawab,
				'pah_lokasi' => $lokasi,
				'pah_aktivitas' => $aktivitas,
				'pah_konfirmasi' => 0
				);
			$this->db->set($data);
			$this->db->where(array('pes_id' => $peserta_id, 'pah_date' => $tanggal));
			$result = $this->db->update(self::TABLE_AKTIVITAS_HARIAN);

			if ($result === FALSE)
			{
				$error = $this->db->error();
				log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
				throw new Exception(__FILE__." Peserta_model->update_aktivitas_harian
					Update aktivitas harian gagal
					- peserta_id:{$peserta_id} 
					- tanggal:{$tanggal}
					- penanggung_jawab:{$penanggung_jawab}
					- lokasi:{$lokasi}
					- aktivitas:{$aktivitas}");
			}

			return TRUE;
		}
		// Jika belum ada aktivitas di tanggal tersebut, insert aktivitas.
		else
		{
			$data = array(
				'pes_id' => $peserta_id,
				'pah_date' => $tanggal,
				'pah_penanggung_jawab' => $penanggung_jawab,
				'pah_lokasi' => $lokasi,
				'pah_aktivitas' => $aktivitas,
				'pah_konfirmasi' => 0
				);
			$result = $this->db->insert(self::TABLE_AKTIVITAS_HARIAN, $data);
			
			if ($result === FALSE)
			{
				$error = $this->db->error();
				log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
				throw new Exception(__FILE__." Peserta_model->update_aktivitas_harian
					Update aktivitas harian gagal
					- peserta_id:{$peserta_id} 
					- tanggal:{$tanggal}
					- penanggung_jawab:{$penanggung_jawab}
					- lokasi:{$lokasi}
					- aktivitas:{$aktivitas}");
			}

			return TRUE;
		}
	}

	public function add_komentar($peserta_id, $pesan, $dos_id='')
	{
		$data = array(
			'pes_id' => $peserta_id,
			'pk_date' => date("Y-m-d H:i:s"),
			'dos_id' => ($dos_id) ? $dos_id : NULL,
			'pk_pesan' => $pesan
			);

		$result = $this->db->insert(self::TABLE_KOMENTAR, $data);

		if ($result === FALSE)
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Peserta_model->add_komentar
				Add komentar gagal
				- peserta_id:{$peserta_id} 
				- dos_id:{$dos_id}
				- pesan:{$pesan}");
		}
		
		return TRUE;
	}

	public function tahapan_lanjutkan($peserta_id)
	{
		$peserta_data = $this->get_data($peserta_id);

		// Tahapan 'selesai' adalah tahapan terakhir.
		if ($peserta_data && $peserta_data['pes_tahapan'] < $this->config->item('pkl_tahapan_num_selesai'))
		{			
			$this->db->set(array('pes_tahapan' => $peserta_data['pes_tahapan']+1));
			$this->db->where('pes_id', $peserta_id);
			$result = $this->db->update(self::TABLE_PESERTA);

			if ($result === FALSE)
			{
				$error = $this->db->error();
				log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
				throw new Exception(__FILE__." Peserta_model->tahapan_lanjutkan
					Menaikkan tahapan peserta gagal
					- peserta_id:{$peserta_id}");
			}
			
			return TRUE;	
		}

		return FALSE;
	}

	public function update_tahapan($peserta_id, $tahapan)
	{
		if ($tahapan >= $this->config->item('pkl_tahapan_num_pendaftaran') &&
			$tahapan <= $this->config->item('pkl_tahapan_num_selesai'))
		{
			$this->db->set(array('pes_tahapan' => $tahapan));
			$this->db->where('pes_id', $peserta_id);
			$result = $this->db->update(self::TABLE_PESERTA);

			if ($result === FALSE)
			{
				$error = $this->db->error();
				log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
				throw new Exception(__FILE__." Peserta_model->update_tahapan
					Mengubah tahapan peserta gagal
					- peserta_id:{$peserta_id}
					- tahapan:{$tahapan}");
			}
			
			return TRUE;	
		}

		return FALSE;
	}

	public function setujui_judul_laporan($peserta_id)
	{
		$this->db->set('pes_laporan_judul_konfirmasi', 1);
		$this->db->where('pes_id', $peserta_id);

		if ( ! $this->db->update(self::TABLE_PESERTA))
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Peserta_model->setujui_judul_laporan
				Penyetujuan judul laporan peserta gagal
				- peserta_id:{$peserta_id}");
		}

		return TRUE;
	}

	public function setujui_aktivitas_harian($aktivitas_id)
	{
		$this->db->set('pah_konfirmasi', 1);
		$this->db->where('pah_id', $aktivitas_id);

		if ( ! $this->db->update(self::TABLE_AKTIVITAS_HARIAN))
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Peserta_model->setujui_aktivitas_harian
				Penyetujuan aktivitas harian gagal
				- aktivitas_id:{$aktivitas_id}");
		}

		return TRUE;
	}

	public function setujui_laporan_draft_terakhir($id)
	{
		// Ambil id draft laporan terakhir.
		$data_laporan_draft = $this->db->order_by('pld_date', 'DESC')->limit(1)->get_where(self::TABLE_LAPORAN_DRAFT, array('pes_id' => $id))->row_array();

		// Update data draft laporan.
		$this->db->set('pld_konfirmasi', 1);
		$this->db->where('pld_id', $data_laporan_draft['pld_id']);

		if ( ! $this->db->update(self::TABLE_LAPORAN_DRAFT))
		{
			$error = $this->db->error();
			log_message('error', "Query error: ".$error['message']." - Invalid query: ".$this->db->last_query());
			throw new Exception(__FILE__." Peserta_model->setujui_laporan_draft_terakhir
				Penyetujuan draft laporan gagal
				- id:{$id}");
		}

		return TRUE;
	}

}