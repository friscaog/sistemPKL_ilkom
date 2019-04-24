<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Pagination_basic {

	/**
	 * Config property.
	 */
	protected $base_url;
	protected $total_rows;
	protected $per_page;
	protected $current_page = 1;

	/**
	 * Calculated property.
	 */
	protected $max_page;

	public function initialize($config)
	{
		$this->base_url = trim($config['base_url']);
		if ($this->base_url[count($this->base_url)-1] != '/')
		{
			$this->base_url = $this->base_url.'/';
		}

		$this->total_rows = $config['total_rows'];		

		$this->per_page = $config['per_page'];

		if (isset($config['current_page']))
		{
			$this->current_page = $config['current_page'];
		}

		$this->max_page = ceil($this->total_rows / $this->per_page);
	}

	public function get_base_url()
	{
		return $this->base_url;
	}

	public function get_total_rows()
	{
		return $this->total_rows;
	}

	public function get_per_page()
	{
		return $this->per_page;
	}

	public function get_current_page()
	{
		return $this->current_page;
	}

	public function get_prev_url()
	{
		return ($this->current_page > 1) ? $this->base_url.($this->current_page-1) : FALSE;
	}

	public function get_next_url()
	{
		return ($this->current_page < $this->max_page) ? $this->base_url.($this->current_page+1) : FALSE;
	}

	public function get_record_offset()
	{
		return ($this->current_page-1) * $this->per_page;
	}
}
