<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('send_ajax_output'))
{
	function send_ajax_output($code, $message, $result=NULL)
	{
		get_instance()
			->output
	        ->set_status_header($code)
	        ->set_content_type('application/json', 'utf-8')
	        ->set_output(json_encode(array('status' => $code, 'message' => $message, 'result' => $result)));
	}
}