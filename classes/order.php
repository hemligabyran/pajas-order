<?php defined('SYSPATH') OR die('No direct access allowed.');

class Order
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static private $driver;

	public function __construct($order_id = FALSE, $use_session = TRUE)
	{
		echo self::driver()->get();
		die();
	}

	/**
	 * Loads the driver if it has not been loaded yet, then returns it
	 *
	 * @return Driver object
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 */
	public static function driver()
	{
		if (self::$driver == NULL) self::set_driver();
		return self::$driver;
	}

	/**
	 * Set the database driver
	 *
	 * @return boolean
	 */
	public static function set_driver()
	{
		$driver_name = 'Driver_Order_'.ucfirst(Kohana::$config->load('content.driver'));
		return (self::$driver = new $driver_name);
	}

}
