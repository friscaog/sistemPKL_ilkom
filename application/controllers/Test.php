<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->library('unit_test');
		$this->load->library('Curl');
		$this->load->helper('url');

		$this->unit->use_strict(TRUE);
	}

	public function index()
	{
		$this->_login();
	}

	private function _login()
	{
		$mahasiswa_user1 = 'yudi.haryasa';
		$mahasiswa_password1 = '1108605027';
		$dosen_user = 'muliantara';
		$dosen_password = '12345678';

		// Koneksi.
		$instance = $this->curl->create(site_url('login/auth'), 'POST');

		// Tes mahasiswa1 gagal.
		$result = $instance->set_fields(array('user' => $mahasiswa_user1, 'password' => ''))->exec();
		$result_parsed = json_decode($result, TRUE);
		$expected_result = array('status' => 400, 'message' => 'Kombinasi user dan password tidak sesuai');
		$this->unit->run($result_parsed, $expected_result, 'Login Gagal Mahasiswa');

		// Tes mahasiswa1 sukses.
		$result = $instance->set_fields(array('user' => $mahasiswa_user1, 'password' => $mahasiswa_password1))->exec();
		$result_parsed = json_decode($result, TRUE);
		$expected_result = array('status' => 200, 'message' => 'Ok', 'result' => 'mahasiswa');
		$this->unit->run($result_parsed, $expected_result, 'Login Sukses Mahasiswa');

		// Tes dosen gagal.
		$result = $instance->set_fields(array('user' => $dosen_user, 'password' => ''))->exec();
		$result_parsed = json_decode($result, TRUE);
		$expected_result = array('status' => 400, 'message' => 'Kombinasi user dan password tidak sesuai');
		$this->unit->run($result_parsed, $expected_result, 'Login Gagal Dosen');

		// Tes dosen sukses.
		$result = $instance->set_fields(array('user' => $dosen_user, 'password' => $dosen_password))->exec();
		$result_parsed = json_decode($result, TRUE);
		$expected_result = array('status' => 200, 'message' => 'Ok', 'result' => 'dosen');
		$this->unit->run($result_parsed, $expected_result, 'Login Sukses Dosen');

		echo $this->unit->report();
		return;
	}
}