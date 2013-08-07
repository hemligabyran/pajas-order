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

	/**
	 * Get all order data for one specific order
	 *
	 * @param int $order_id
	 * @return array
	 */
//	abstract public function get($order_id);

	/**
	 * Get field id
	 *  - Will create if it did not exist before
	 *
	 * @param str $name
	 * @return int
	 */
	abstract public function get_field_id($name);

	/**
	 * Get field name
	 *
	 * @param int $id
	 * @return str
	 */
	abstract public function get_field_name($id);

	/**
	 * Get orders
	 *
	 * @param arr $match_all_fields     - key as field name, array of values as field values
	 * @param arr $match_any_field      - key as field name, array of values as field values
	 * @param arr $match_all_row_fields - key as row field name, array of values as row field values
	 * @param arr $match_any-row_field  - key as row field name, array of values as row field values
	 * @param arr $return_fields        - array of field names
	 * @param arr $return_row_fields    - array of row field names
	 * @param int $limit
	 * @param int $offset
	 * @param arr $order_by             - key as field, value as ASC or DESC (array of arrays for order by multiple columns)
	 * @return array
	 */
	abstract public function get_orders($match_all_fields, $match_any_field, $match_all_row_fields, $match_any_row_field, $return_fields, $return_row_fields, $limit, $offset, $order_by);

	/**
	 * Get row field id
	 *  - Will create if it did not exist before
	 *
	 * @param str $name
	 * @return int
	 */
	abstract public function get_row_field_id($name);

	/**
	 * Get row field name
	 *
	 * @param int $id
	 * @return str
	 */
	abstract public function get_row_field_name($id);

	/**
	 * Checks if an order id exists
	 *
	 * @param int $order_id
	 * @return boolean
	 */
	abstract public function order_id_exists($order_id);

	/**
	 * Save order to database
	 *
	 * @param arr $order_data - Must be of format:
	 * array(
	 *       'id' => <int> (optional, new id will be created if left out),
	 *       'fields' => array(
	 *                       'firstname' => 'John',
	 *                       'lastname'  => 'Smith',
	 *                       etc.
	 *       )
	 *       'rows' => array(
	 *                       -1 => array( // Will create a new row with a new id
	 *                                  'name' => 'Baseball',
	 *                                  'price' => '5000'
	 *                             )
	 *                       392 => array( // Will update row id 392
	 *                                  'name' => 'Baseball',
	 *                                  'price' => '5000'
	 *                             )
	 *       )
	 * )
	 */
	abstract public function save($order_data);

}
