<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Orders
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static private $driver;

	protected $match_all_fields;
	protected $match_all_row_fields;
	protected $match_any_field;
	protected $match_any_row_field;
	protected $return_fields;
	protected $return_row_fields;

	protected $limit  = 100;
	protected $offset = 0;

	protected $order_by;

	/**
	 * Constructor
	 *
	 * @param int $order_id    - if provided, loads an order from database, otherwise creates a new one or loads from session
	 * @param str $session     - Name of the session to use, if FALSE use no session at all
	 * @param bol $start_clean - If no order_id is supplied, clean out possible session data before starting
	 */
	public function __construct()
	{
	}

	public static function factory()
	{
		return new self();
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
	 * Get orders
	 *
	 * @return array - array('id' => int, 'fields' => array(etc.), 'rows' => array(23923 => array(etc)))
	 */
	public function get()
	{
		return self::driver()->get_orders(
			$this->match_all_fields,
			$this->match_any_field,
			$this->match_all_row_fields,
			$this->match_any_row_field,
			$this->return_fields,
			$this->return_row_fields,
			$this->limit,
			$this->offset,
			$this->order_by
		);
	}

	public function limit($limit)                     { $this->limit                = intval($limit);  return $this;}
	public function match_all_fields($fields)         { $this->match_all_fields     = $fields;         return $this;}
	public function match_all_row_fields($row_fields) { $this->match_all_row_fields = $row_fields;     return $this;}
	public function match_any_field($fields)          { $this->match_any_field      = $fields;         return $this;}
	public function match_any_row_field($row_field)   { $this->match_any_row_field  = $row_fields;     return $this;}
	public function offset($offset)                   { $this->offset               = intval($offset); return $this;}
	public function order_by($fields)                 { $this->order_by             = $fields;         return $this;}
	public function return_fields($fields)            { $this->return_fields        = $fields;         return $this;}
	public function return_row_fields($row_fields)    { $this->return_row_fields    = $row_fields;     return $this;}

	/**
	 * Set the database driver
	 *
	 * @return boolean
	 */
	public static function set_driver()
	{
		$driver_name = 'Driver_Order_'.ucfirst(Kohana::$config->load('pdo.default.driver'));
		return (self::$driver = new $driver_name);
	}

}
