<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logout extends CI_Controller {

	public function index()
	{
		$this->load->helper('url');

		session_destroy();
		redirect('home');
	}

}
