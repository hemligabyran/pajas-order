<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Driver_Order extends Model
{

	public function __construct()
	{
		parent::__construct();
		if (Kohana::$environment == Kohana::DEVELOPMENT)
		{
			if ( ! $this->check_db_structure())
				$this->create_db_structure();
		}
	}

	/**
	 * Returns true/false depending on if the db structure exists or not
	 *
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 * @return boolean
	 */
	abstract protected function check_db_structure();

	/**
	 * Create the db structure
	 *
	 * @return boolean
	 */
	abstract protected function create_db_structure();

	abstract public function get();

	/**
	 * Get content
	 *
	 * @param int $content_id
	 * @return str
	 * /
	abstract public function get_content($content_id);
	/**/


}
