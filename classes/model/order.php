<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Order
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static private $driver;

	protected $order_data;

	protected $session = 'default';

	/**
	 * Constructor
	 *
	 * @param int $order_id    - if provided, loads an order from database, otherwise creates a new one or loads from session
	 * @param str $session     - Name of the session to use, if FALSE use no session at all
	 * @param bol $start_clean - If no order_id is supplied, clean out possible session data before starting
	 */
	public function __construct($order_id = FALSE, $session = 'default', $start_clean = FALSE)
	{
		$this->session = $session;

		if ($this->session)
		{
			$session_data =& Session::instance()->as_array();
			if ( ! isset($session_data['order'][$session]) || $start_clean)
				$session_data['order'][$session] = array('fields' => array(), 'rows' => array());

			$this->order_data =& $session_data['order'][$session];
		}
		else // No session shuld be used, open up a clean order_data variable
			$this->order_data = array('fields' => array(), 'rows' => array());

		if ($order_id && self::driver()->order_id_exists($order_id))
		{
			// $order_id is set and it is valid
			if ((isset($this->order_data['id']) && $order_id != $this->order_data['id']) || ! isset($this->order_data['id']))
			{
				// The supplied order id does not match the one saved in session/local, reload session/local data
				$this->order_data              = self::driver()->get_order($order_id);
				$this->order_data['total']     = $this->get_total_price();
				$this->order_data['total_VAT'] = $this->get_total_VAT();

				$this->update_session();
			}
		}

	}

	/**
	 * Add row to this order
	 *
	 * @param  arr $row_data - only one dimension with data, like array('Product ID' => 'kskd', 'Price' => 239)
	 * @return int           - new row id
	 */
	public function add_row($row_data)
	{
		if ( ! isset($row_data['price'])) $row_data['price'] = 0;
		if ( ! isset($row_data['VAT']))   $row_data['VAT']   = Kohana::$config->load('order.default_VAT');

		// To not confuse new rows with already saved ones, we give them negative keys
		$row_nr = -1;
		if (count($this->order_data['rows']))
			$row_nr = min(array_keys($this->order_data['rows'])) - 1;
		if ($row_nr > 0) $row_nr = -1;

		ksort($row_data);

		$this->order_data['rows'][$row_nr] = $row_data;

		$this->recalculate_sums();
		$this->update_session();

		return $row_nr;
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
	 * Duplicate a row
	 *
	 * @param int $row_id - wich row to duplicate
	 * @return int - new row id
	 */
	public function duplicate_row($row_id)
	{
		if ( ! isset($this->order_data['rows'][$row_id]))
			return FALSE;

		return $this->add_row($this->order_data['rows'][$row_id]);
	}

	public static function factory($order_id = FALSE, $session = 'default', $start_clean = FALSE)
	{
		return new Order($order_id,$session,$start_clean);
	}

	/**
	 * Get complete order data
	 *
	 * @param bool - $lump_together_rows - Lump together rows and increase qty value
	 * @param arr - $ignore_diff_fields - order row fields to ignore when checking if they should be lumped together
	 *
	 * @return array - array('id' => int, 'fields' => array(etc.), 'rows' => array(23923 => array(etc)))
	 */
	public function get($lump_together_rows = FALSE, $ignore_diff_fields = array())
	{
		if ($lump_together_rows)
		{
			$order_data = $this->order_data;
			$order_data['rows'] = $this->get_rows($lump_together_rows, $ignore_diff_fields);

			return $order_data;
		}

		return $this->order_data;
	}

	/**
	 * Get field
	 *
	 * @param str $name
	 * @return str value of this field
	 */
	public function get_field($name)
	{
		if ( ! isset($this->order_data['fields'][$name]))
			return FALSE;

		return $this->order_data['fields'][$name];
	}

	/**
	 * Get field id
	 *  - The field will be created if it does not exist
	 *
	 * @param str $name
	 * @return int
	 */
	public static function get_field_id($name)
	{
		return self::driver()->get_field_id($name);
	}

	public static function get_field_name($id)
	{
		return self::driver()->get_field_name($id);
	}

	/**
	 * Get all fields
	 *
	 * @return array
	 */
	public function get_fields()
	{
		return $this->order_data['fields'];
	}

	/**
	 * Get order id
	 *
	 * @return int
	 */
	public function get_id()
	{
		if (isset($this->order_data['id']))
			return $this->order_data['id'];
		else
			return FALSE;
	}

	public static function get_order_ids_by_fields($field_names, $field_values)
	{
// Kod behövs, men inte nu akut
	}

	/**
	 * Get the total price for this order
	 *
	 * @param bool $include_VAT
	 * @return float
	 */
	public function get_total_price($include_VAT = TRUE)
	{
		$sum = 0;

		foreach ($this->order_data['rows'] as $row)
		{
			if (isset($row['price']))
			{
				if (isset($row['VAT']) && $include_VAT)
					$sum += ($row['price'] * $row['VAT']);
				else
					$sum += $row['price'];
			}
		}

		return $sum;
	}

	/**
	 * Get toal VAT cost for this order
	 *
	 * @return float
	 */
	public function get_total_VAT()
	{
		return $this->get_total_price() - $this->get_total_price(FALSE);
	}

	/**
	 * Return specific row
	 *
	 * @param int $row_id
	 * @return arr - row data, or false
	 */
	public function get_row($row_id)
	{
		if (isset($this->order_data['rows'][$row_id]))
			return $this->order_data['rows'][$row_id];
		return FALSE;
	}

	/**
	 * Get row field data
	 *
	 * @param int $row_id
	 * @param str $name - Row field name
	 * @return boolean
	 */
	public function get_row_field($row_id, $name)
	{
		if ( ! isset($this->order_data['rows'][$row_id][$name]))
			return FALSE;
		else
			return $this->order_data['rows'][$row_id][$name];
	}

	/**
	 * Get row field id
	 *  - The row field will be created if it does not exist
	 *
	 * @param str $name
	 * @return int
	 */
	public static function get_row_field_id($name)
	{
		return self::driver()->get_row_field_id($name);
	}

	/**
	 * Get row field name
	 *
	 * @param int $id
	 * @return str
	 */
	public static function get_row_field_name($id)
	{
		return self::driver()->get_row_field_name($id);
	}

	/**
	 * Get rows
	 *
	 * @param bool - $lump_together_rows - Lump together rows and increase qty value
	 * @param arr - $ignore_diff_fields - order row fields to ignore when checking if they should be lumped together
	 *
	 * @return array
	 */
	public function get_rows($lump_together_rows = FALSE, $ignore_diff_fields = array())
	{
		if ($lump_together_rows)
		{
			$tmp_rows = array();

			foreach ($this->order_data['rows'] as $row_id => $row_data)
			{
				$found = FALSE;
				foreach ($tmp_rows as $tmp_row_id => $tmp_row_data)
				{
					if (isset($tmp_row_data['qty']))
						$row_data['qty'] = $tmp_row_data['qty'];

					$fields_to_add = array();
					foreach ($ignore_diff_fields as $field_name)
					{
						if (isset($tmp_row_data[$field_name]))
						{
							$fields_to_add[$field_name] = $tmp_row_data[$field_name];
							unset($tmp_row_data[$field_name]);
						}

						if (isset($row_data[$field_name]))
						{
							$fields_to_add[$field_name] = $row_data[$field_name];
							unset($row_data[$field_name]);
						}
					}

					if ($tmp_row_data == $row_data)
					{
						$tmp_rows[$tmp_row_id]['qty']++;

						foreach ($fields_to_add as $field_name => $field_data)
							$tmp_rows[$tmp_row_id][$field_name] = $field_data;

						$found = TRUE;
					}
				}

				if ( ! $found)
				{
					$row_data['qty']   = 1;
					$tmp_rows[$row_id] = $row_data;
				}
			}

			return $tmp_rows;
		}
		else
		{
			return $this->order_data['rows'];
		}
	}

	/**
	 * Get order rows by content
	 *
	 * @param arr $content - key as field, value as value. TRUE as value matches all values
	 * @param bol $match_all - if set to TRUE, all fields in $content must be met
	 * @return arr - The matched row(s)
	 */
	public function get_rows_by_content($content, $match_all = TRUE)
	{
		$matched = array();

		foreach ($this->get_rows() as $row_id => $row_data)
		{
			if ($match_all)
			{
				foreach ($content as $match_field => $match_content)
				{
					if ( ! isset($row_data[$match_field]) || ($row_data[$match_field] != $match_content && $match_content !== TRUE))
						continue 2; // Is not set at all, skip this row
				}
				$matched[$row_id] = $row_data; // Not continued, means we got a match
			}
			else
			{
				foreach ($content as $match_field => $match_content)
				{
					if (isset($row_data[$match_field]) && ($match_content === TRUE || $match_content == $row_data[$match_field]))
					{
						// Match! Add and continue to next row
						$matched[$row_id] = $row_data;
						continue 2;
					}
				}
			}
		}

		return $matched;
	}

	public function recalculate_sums()
	{
		// Recalculate total sums
		$this->order_data['total']     = 0;
		$this->order_data['total_VAT'] = 0;

		foreach ($this->order_data['rows'] as $row)
		{
			$VAT                            = $row['price'] * ($row['VAT'] - 1);
			$this->order_data['total_VAT'] += $VAT;
			$this->order_data['total']     += ($row['price'] + $VAT);
		}

		$this->update_session();

		return TRUE;
	}

	/**
	 * Reload order from database - ignore changes made in session
	 *
	 * @return boolean
	 */
	public function reload_order()
	{
		if ( ! $this->get_id())
			return FALSE;

		$this->order_data = self::driver()->get_order($this->get_id());

		$this->order_data['total']     = $this->get_total_price();
		$this->order_data['total_VAT'] = $this->get_total_VAT();

		return TRUE;
	}

	/**
	 * Remove order field
	 *
	 * @param str $name
	 * @return boolean
	 */
	public function rm_field($name)
	{
		if (isset($this->order_data['fields'][$name]))
			unset($this->order_data['fields'][$name]);

		$this->recalculate_sums();
		$this->update_session();

		return TRUE;
	}

	/**
	 * Remove row
	 *
	 * @param int $row_id
	 * @return boolean
	 */
	public function rm_row($row_id)
	{
		if (isset($this->order_data['rows'][$row_id]))
			unset($this->order_data['rows'][$row_id]);

		$this->recalculate_sums();
		$this->update_session();

		return TRUE;
	}

	/**
	 * Remove a row by what fields it contains
	 *
	 * @param arr $fields           - array key as field name, array value as
	 *                                field value. NULL as value will match all
	 *                                with this field
	 * @param int $limit            - Remove maximum this many rows
	 * @param bol $recalculate_sums
	 * @return int                  - number of removed rows
	 */
	public function rm_row_by_row_fields($fields, $limit = FALSE, $recalculate_sums = TRUE)
	{
		$removed_rows = 0;
		foreach ($this->order_data['rows'] as $row_id => $row)
		{
			foreach ($fields as $field_name => $field_value)
			{
				if
				(
					! isset($row[$field_name]) ||
					(
						$row[$field_name] != $field_value &&
						$field_value !== NULL
					)
				) continue 2;
			}

			$removed_rows++;
			unset($this->order_data['rows'][$row_id]);
			if ($limit && $removed_rows >= $limit) break;
		}

		if ($recalculate_sums)
			$this->recalculate_sums();

		$this->update_session();

		return $removed_rows;
	}

	public function rm_row_field($row_id, $field_name)
	{
		if (isset($this->order_data['rows'][$row_id][$field_name]))
			unset($this->order_data['rows'][$row_id][$field_name]);

		$this->recalculate_sums();
		$this->update_session();

		return TRUE;
	}

	/**
	 * Save order to database
	 *
	 * @param bool $maintain_session - If FALSE, erase the session data
	 * @return int - order id or FALSE on failure
	 */
	public function save($maintain_session = FALSE)
	{
		// Set default fields
		foreach (Kohana::$config->load('order.default_fields') as $name => $value)
		{
			if ( ! isset($this->order_data['fields'][$name]))
				$this->order_data['fields'][$name] = $value;
		}

		$order_id = self::driver()->save($this->order_data);

		if ($maintain_session === FALSE)
			$this->order_data = array('fields'=>array(),'rows'=>array());
		else
			$this->order_data = self::driver()->get_order($order_id);

		$this->update_session();

		return $order_id;
	}

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

	/**
	 * Set field data
	 *
	 * @param str $name - Field name
	 * @param str $value - Field value
	 * @return boolean
	 */
	public function set_field($name, $value)
	{
		$this->order_data['fields'][$name] = $value;

		$this->recalculate_sums();
		$this->update_session();

		return TRUE;
	}

	/**
	 * Set row field data
	 *
	 * @param int $row_id
	 * @param str $name - Row field name
	 * @param str $value - Row field value
	 * @return boolean
	 */
	public function set_row_field($row_id, $name, $value)
	{
		if ( ! isset($this->order_data['rows'][$row_id]))
			return FALSE;

		$this->order_data['rows'][$row_id][$name] = $value;

		$this->recalculate_sums();
		$this->update_session();

		return TRUE;
	}

	/**
	 * Update session data
	 *
	 * @return boolean
	 */
	protected function update_session()
	{
		if ($this->session)
		{
			$session_order                 = Session::instance()->get('order');
			$session_order[$this->session] = $this->order_data;
			Session::instance()->set('order', $session_order);
		}

		return TRUE;
	}

}