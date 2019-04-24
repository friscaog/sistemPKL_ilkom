<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Curl {

	public function create($url, $method = 'GET')
	{
		return new Curl_instance($url, $method);
	}

}

class Curl_instance {

	protected $url;

	protected $method;

	protected $fields;

	protected $error_string;

	protected $error_code;	

	public function __construct($url, $method = 'GET')
	{
		$this->set_url($url);
		$this->set_method($method);
	}

	public function set_url($url)
	{
		$this->url = $url;
		return $this;
	}

	public function set_method($method)
	{
		$this->method = strtoupper(trim($method));
		return $this;		
	}

	public function set_fields($fields)
	{
		$fields_string = '';

		foreach ($fields as $key => $value) {
			$fields_string .= (urlencode($key).'='.urlencode($value).'&');
		}

		$this->fields = $fields_string;
		return $this;
	}

	public function get_error_string()
	{
		return $this->error_string;
	}

	public function get_error_code()
	{
		return $this->error_code();
	}

	public function exec()
	{	
		$ch = curl_init();

		// Set url.
	    curl_setopt($ch, CURLOPT_URL, $this->url);

	    // Cegah output ke layar.
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	    // Set HTTP request method. Default method adalah GET.
	    switch ($this->method) {
	    	case 'POST':
	    		log_message('debug', 'Terdeteksi menggunakan method POST');
	    		curl_setopt($ch, CURLOPT_POST, 1);
	    		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->fields);
	    		// curl_setopt($ch, CURLOPT_POSTFIELDS, 'user=tampan&password=12345&');
	    		break;
	    	default:
	    		log_message('debug', 'Terdeteksi menggunakan method GET');
	    		break;
	    }	    

	    // Eksekusi curl lalu dapatkan hasil dan error.
	    $result = curl_exec($ch);
	    $this->error_string = curl_error($ch);
	    $this->error_code = curl_errno($ch);

	    curl_close($ch);

	    return $result;
	}
}