<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_cs {

	protected $CI;

	public function __construct()
	{
		$this->CI = get_instance();
	}

	public function login_dummy($user, $password)
	{
		$this->CI->load->database();
		
		$where = array(
			'email_cs' => $user,
			'password' => md5($password)
			);
		$result = $this->CI->db->get_where('test_account_cs', $where)->num_rows() > 0;
		if ( ! $result)
		{
			return FALSE;
		}
		else
		{
			return $user.'@cs.unud.ac.id';
		}
		// $row = $query->row();

		// if ( ! $row){
		// 	return FALSE;
		// }

		// $account = new stdClass();
		// $account->jenis_akun = $row->jenis_akun;
		// $account->email_cs = $row->email_cs.'@cs.unud.ac.id';		
		// return $account;
	}

	public function login($username, $password)
	{
		// Matikan display error karena fungsi ldap_bind menghasilkan warning saat username dan password tidak valid.
		//$this->_hide_error_display();

		// Konfirgurasi LDAP.
		$ldap['user'] = $username; 
		$ldap['pass'] = $password; 
		$ldap['host'] = 'webmail.cs.unud.ac.id';
		$ldap['port'] = 389; 
		$ldap['dn1'] = 'uid='.$ldap['user'].',ou=people,dc=cs,dc=unud,dc=ac,dc=id'; 
		$ldap['dn2'] = 'uid='.$ldap['user'].',ou=people,dc=mhs,dc=cs,dc=unud,dc=ac,dc=id'; 
		$ldap['base'] = 'dc=cs,dc=unud,dc=ac,dc=id'; // connecting to ldap 
		$ldap['conn'] = ldap_connect( $ldap['host'], $ldap['port'] ); 
		
		ldap_set_option($ldap['conn'], LDAP_OPT_PROTOCOL_VERSION, 3); // binding to ldap 
		
		// Cek email @cs.unud.ac.id
		$domain = '@cs.unud.ac.id';
		$ldap['bind'] = ldap_bind( $ldap['conn'], $ldap['dn1'], $ldap['pass'] ); 
		if( ! $ldap['bind'])
		{
			// Jika pada email @cs.unud.ac.id tidak ditemukan user,
			// cek email @mhs.cs.unud.ac.id
			$domain = '@mhs.cs.unud.ac.id';
			$ldap['bind'] = ldap_bind( $ldap['conn'], $ldap['dn2'], $ldap['pass'] ); 
		}

		if ($ldap['bind']) 
		{
			// Login berhasil.
			return $ldap['user'].$domain;
		}
		 
		return FALSE;
	}

	/**
	 * Menyembunyikan error PHP.
	 */
	private function _hide_error_display()
	{
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	}

}
