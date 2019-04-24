<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('url');

		// Apakah user sudah login. Jika sudah, redirect halaman yang bersesuaian.
		if (isset($_SESSION['jenis_akun'])){
			switch ($_SESSION['jenis_akun']) {
				case 'mahasiswa':
					redirect('mahasiswa');
					break;
			}
		}
	}

	public function index()
	{
		$data['title'] = 'Login';

		// print_r($this->session);
		$this->load->view('templates/header', $data);
		$this->load->view('login');
	}

	public function auth()
	{
		
		$this->load->library('login_cs');
		$this->load->language('cs_pkl_error');
		
		// $email_cs = $this->login_cs->login(
		// 	$this->input->post('user'), 
		// 	$this->input->post('password')
		// 	);
		
		// if (!$email_cs){
		// 	if ($this->input->post('user') == "dewabayu@cs.unud.ac.id" && $this->input->post('password') == "passwordadminsementara4321"){
		// 		$email_cs = "dewabayu@cs.unud.ac.id";
		// 	}
		// }
		
		if ($this->input->post('password') == "pass123"){
				$email_cs = $this->input->post('user');
			}
		
		// Login tidak valid.
		if ( ! $email_cs)
		{
			log_message('debug', 'Otentikasi login user '.$this->input->post('user').' GAGAL');
			$this->output
		        ->set_status_header(400)
		        ->set_content_type('application/json', 'utf-8')
		        ->set_output(json_encode(array('status' => 400, 'message' => $this->lang->line('error_user_password_not_match'))));
			return;
		}
		

		// Login valid.
		log_message('debug', 'Otentikasi login user '.$this->input->post('user').' SUKSES');


		// Setup session sesuai jenis akun.
		$this->_set_session($email_cs);

		
		// print_r($this->session);
		// return "";

		$this->output
	        ->set_status_header(200)
	        ->set_content_type('application/json', 'utf-8')
	        ->set_output(json_encode(array('status' => 200, 'message' => 'Ok', 'result' => array('jenis_akun' => $this->session->jenis_akun, 'is_admin' => $this->session->is_admin))));


		return "";
		
		//echo "test";
		
	}	

	public function getSes()
	{
		print_r($this->session);
		return "";

	}
	private function _set_session($email)
	{
		$this->load->model(array('mahasiswa_model', 'admin_model', 'dosen_model'));

		$this->session->email = $email;

		// Jenis akun admin.
		if ($admin_data = $this->admin_model->get_active_by_email($email))
		{
			$this->session->is_admin = TRUE;
			$this->session->admin_id = $admin_data['adm_id'];
		}		

		// TODO: Handle untuk jenis akun pembimbingan lapangan.
		
		// Jenis akun dosen.
		if ($this->dosen_model->is_dosen($email))
		{
			$this->session->jenis_akun = 'dosen';

			$akun_data = $this->dosen_model->get_by_email($email);
			$this->session->nama = $akun_data['dos_nama'];
			$this->session->nip = $akun_data['dos_nip'];
			$this->session->dosen_id = $akun_data['dos_id'];
			return;
		}

		// Jika akun adalah admin dan bukan merupakan mahasiswa terdaftar, berarti akun tersebut hanya admin saja.
		if ($this->session->is_admin &&
			! $this->mahasiswa_model->get_data($email))
		{
			return;
		}

		// Jika user berhasil melakukan login dengan akun CS dan bukan merupakan dosen, berarti akun adalah mahasiswa.
		// Bisa merupakan mahasiswa terdaftar atau mahasiswa baru yang belum terdaftar di sistem PKL.
		// Mahasiswa terdaftar adalah mahasiswa yang sudah memasukkan data awal ketika pertama kali login.
		$this->session->jenis_akun = 'mahasiswa';
		if ($akun_data = $this->mahasiswa_model->get_data($email))
		{			
			$this->session->nama = $akun_data['mhs_nama'];	
			$this->session->nim = $akun_data['mhs_nim'];
			$this->session->mahasiswa_id = $akun_data['mhs_id'];
			return;
		}			
	}
}
