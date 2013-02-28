<?php defined('SYSPATH') OR die('No direct access allowed.');

class Driver_Order_Mysql extends Driver_Order
{

	protected function check_db_structure()
	{

		// Make PDO silent during these checks
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

		$db_check_pass = TRUE;

		$result = $this->pdo->query('DESCRIBE `order_fields`;');
		if ($result) $result = $result->fetchAll(PDO::FETCH_ASSOC);
		if (
		     $result != array(
		                   array('Field' => 'id',         'Type' => 'int(10) unsigned',    'Null' => 'NO',  'Key' => 'PRI', 'Default' => NULL, 'Extra' => 'auto_increment'),
		                   array('Field' => 'name',       'Type' => 'varchar(255)',        'Null' => 'NO',  'Key' => NULL,  'Default' => NULL, 'Extra' => NULL            ),
		                 )
		) $db_check_pass = FALSE;

		$result = $this->pdo->query('DESCRIBE `order_orders`;');
		if ($result) $result = $result->fetchAll(PDO::FETCH_ASSOC);
		if (
		     $result != array(
		                   array('Field' => 'id',         'Type' => 'bigint(20) unsigned', 'Null' => 'NO',  'Key' => 'PRI', 'Default' => NULL, 'Extra' => 'auto_increment'),
		                 )
		) $db_check_pass = FALSE;

		$result = $this->pdo->query('DESCRIBE `order_orders_fields`;');
		if ($result) $result = $result->fetchAll(PDO::FETCH_ASSOC);
		if (
		     $result != array(
		                   array('Field' => 'order_id',   'Type' => 'bigint(20) unsigned', 'Null' => 'NO',  'Key' => 'MUL', 'Default' => NULL, 'Extra' => NULL            ),
		                   array('Field' => 'field_id',   'Type' => 'int(10) unsigned',    'Null' => 'NO',  'Key' => 'MUL', 'Default' => NULL, 'Extra' => NULL            ),
		                   array('Field' => 'value',      'Type' => 'text',                'Null' => 'YES', 'Key' => NULL,  'Default' => NULL, 'Extra' => NULL            ),
		                 )
		) $db_check_pass = FALSE;

		$result = $this->pdo->query('DESCRIBE `order_rowfields`;');
		if ($result) $result = $result->fetchAll(PDO::FETCH_ASSOC);
		if (
		     $result != array(
		                   array('Field' => 'id',         'Type' => 'int(10) unsigned',    'Null' => 'NO',  'Key' => 'PRI', 'Default' => NULL, 'Extra' => 'auto_increment'),
		                   array('Field' => 'name',       'Type' => 'varchar(255)',        'Null' => 'NO',  'Key' => NULL,  'Default' => NULL, 'Extra' => NULL            ),
		                 )
		) $db_check_pass = FALSE;

		$result = $this->pdo->query('DESCRIBE `order_rows`;');
		if ($result) $result = $result->fetchAll(PDO::FETCH_ASSOC);
		if (
		     $result != array(
		                   array('Field' => 'id',         'Type' => 'bigint(20) unsigned', 'Null' => 'NO',  'Key' => 'PRI', 'Default' => NULL, 'Extra' => NULL            ),
		                   array('Field' => 'order_id',   'Type' => 'bigint(20) unsigned', 'Null' => 'NO',  'Key' => 'UNI', 'Default' => NULL, 'Extra' => NULL            ),
		                 )
		) $db_check_pass = FALSE;

		$result = $this->pdo->query('DESCRIBE `order_rows_fields`;');
		if ($result) $result = $result->fetchAll(PDO::FETCH_ASSOC);
		if (
		     $result != array(
		                   array('Field' => 'row_id',     'Type' => 'bigint(20) unsigned', 'Null' => 'NO',  'Key' => 'MUL', 'Default' => NULL, 'Extra' => NULL            ),
		                   array('Field' => 'field_id',   'Type' => 'int(10) unsigned',    'Null' => 'NO',  'Key' => 'MUL', 'Default' => NULL, 'Extra' => NULL            ),
		                   array('Field' => 'value',      'Type' => 'text',                'Null' => 'YES', 'Key' => NULL,  'Default' => NULL, 'Extra' => NULL            ),
		                 )
		) $db_check_pass = FALSE;


		// Turn on error reportning again
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $db_check_pass;
	}

	protected function create_db_structure() {
		$this->pdo->exec('
			CREATE TABLE IF NOT EXISTS `order_fields` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) CHARACTER SET latin1 NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
			CREATE TABLE IF NOT EXISTS `order_orders` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
			CREATE TABLE IF NOT EXISTS `order_orders_fields` (
				`order_id` bigint(20) unsigned NOT NULL,
				`field_id` int(10) unsigned NOT NULL,
				`value` text CHARACTER SET latin1,
				KEY `order_id` (`order_id`,`field_id`),
				KEY `order_id_2` (`order_id`),
				KEY `field_id` (`field_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `order_rowfields` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) CHARACTER SET latin1 NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
			CREATE TABLE IF NOT EXISTS `order_rows` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`order_id` bigint(20) unsigned NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `id` (`id`,`order_id`),
				KEY `order_id` (`order_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
			CREATE TABLE IF NOT EXISTS `order_rows_fields` (
				`row_id` bigint(20) unsigned NOT NULL,
				`field_id` int(10) unsigned NOT NULL,
				`value` text CHARACTER SET latin1,
				KEY `row_id` (`row_id`,`field_id`),
				KEY `row_id_2` (`row_id`),
				KEY `field_id` (`field_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			ALTER TABLE `order_orders_fields`
				ADD CONSTRAINT `order_orders_fields_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `order_fields` (`id`),
				ADD CONSTRAINT `order_orders_fields_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_orders` (`id`);
			ALTER TABLE `order_rows`
				ADD CONSTRAINT `order_rows_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_orders` (`id`);
  			ALTER TABLE `order_rows_fields`
				ADD CONSTRAINT `order_rows_fields_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `order_rowfields` (`id`),
				ADD CONSTRAINT `order_rows_fields_ibfk_1` FOREIGN KEY (`row_id`) REFERENCES `order_rows` (`id`);
		');

		return $this->check_db_structure();
	}

	public function get($order_id)
	{
		if ( ! $this->order_id_exists($order_id))
			return FALSE;

		$order_data = array(
			'id'          => intval($order_id),
			'fields'      => array(),
			'rows'        => array(),
			'total'       => 0,
			'total_VAT'   => 0,
		);

		$sql = 'SELECT (SELECT name FROM order_fields WHERE id = field_id) AS field, `value` FROM order_orders_fields WHERE order_id = '.$this->pdo->quote($order_id);
		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row)
			$order_data['fields'][$row['field']] = $row['value'];

		$sql = '
			SELECT
				row_id,
				rf.name AS field,
				`value`
			FROM
				order_rows_fields rsf
				JOIN order_rowfields rf ON rf.id = rsf.field_id
			WHERE
				row_id IN (SELECT id FROM order_rows WHERE order_id = '.$this->pdo->quote($order_id).')';
		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row)
			$order_data['rows'][$row['row_id']][$row['field']]  = $row['value'];

		return $order_data;
	}

	public function get_field_id($name)
	{
		$field_id = $this->pdo->query('SELECT id FROM order_fields WHERE name = '.$this->pdo->quote($name))->fetchColumn();
		if ( ! $field_id)
		{
			$this->pdo->exec('INSERT INTO order_fields (name) VALUES('.$this->pdo->quote($name).');');
			$field_id = $this->pdo->lastInsertId();
		}

		return $field_id;
	}

	public function get_field_name($id)
	{
		return $this->pdo->query('SELECT name FROM order_fields WHERE id = '.$this->pdo->quote($id))->fetchColumn();
	}

	public function get_orders($match_all_fields, $match_any_field, $match_all_row_fields, $match_any_row_field, $return_fields, $return_row_fields, $limit, $offset, $order_by)
	{
		$sql = '
			SELECT
				o.id';

		foreach ($return_fields as $return_field)
			$sql .= ',
				(SELECT value FROM order_orders_fields WHERE order_id = o.id AND field_id = '.$this->get_field_id($return_field).') AS '.Mysql::quote_identifier($return_field);

		$sql .= '
			FROM
				order_orders o';

		if ($match_all_fields)
		{
			foreach ($match_all_fields as $field => $values)
			{
				if ( ! is_array($values)) $values = array($values);
				foreach ($values as $nr => $value)
				{
					$sql .= '
						INNER JOIN order_orders_fields '.Mysql::quote_identifier($field.$nr).'
							ON '.Mysql::quote_identifier($field.$nr).'.order_id = o.id
							AND '.Mysql::quote_identifier($field.$nr).'.field_id = '.$this->get_field_id($field);
				}

				if ( ! count($values))
				{
					// Empty array should match the field whatever the content
					$sql .= '
						INNER JOIN order_orders_fields '.$field.'
							ON '.Mysql::quote_identifier($field).'.order_id = o.id
							AND '.Mysql::quote_identifier($field).'.field_id = '.$this->get_field_id($field);
				}
			}
		}

		$sql .= '
			WHERE 1';

		if ($match_all_fields)
		{
			foreach ($match_all_fields as $field => $values)
			{
				if ( ! is_array($values)) $values = array($values);
				foreach ($values as $nr => $value)
					$sql .= ' AND '.Mysql::quote_identifier($field.$nr).'.value = '.$this->pdo->quote($value);
			}
		}

		if ($order_by)
		{
			$sql .= ' ORDER BY';
			foreach ($order_by as $key => $value)
			{
				if ( ! is_array($value)) $value = array($key => $value);

				foreach ($value as $field => $order)
				{
					if (strtoupper($order) == 'ASC') $order = 'ASC';
					else                             $order = 'DESC';

					$sql .= ' '.Mysql::quote_identifier($field).' '.$order;
				}
			}
		}

		return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_row_field_id($name)
	{
		$field_id = $this->pdo->query('SELECT id FROM order_rowfields WHERE name = '.$this->pdo->quote($name))->fetchColumn();
		if ( ! $field_id)
		{
			$this->pdo->exec('INSERT INTO order_rowfields (name) VALUES('.$this->pdo->quote($name).');');
			$field_id = $this->pdo->lastInsertId();
		}

		return $field_id;
	}

	public function get_row_field_name($id)
	{
		return $this->pdo->query('SELECT name FROM order_rowfields WHERE id = '.$this->pdo->quote($id))->fetchColumn();
	}

	public function order_id_exists($order_id)
	{
		return (bool) $this->pdo->query('SELECT id FROM order_orders WHERE id = '.$this->pdo->quote($order_id))->fetchColumn();
	}

	public function save($order_data)
	{
		if ( ! isset($order_data['id']))
		{
			// Order did not exist in database, create it
			$this->pdo->exec('INSERT INTO order_orders () VALUES();');
			$order_data['id'] = $this->pdo->lastInsertId();
		}
		elseif ( ! $this->order_id_exists($order_data['id']))
			return FALSE;

		// We will use the quoted order id a lot of times, simplify by store it in a variable
		$quoted_id = $this->pdo->quote($order_data['id']);

		// Give rows real row ids instead of temporary, negative ones
		foreach ($order_data['rows'] as $row_id => $row_data)
		{
			if ($row_id < 0)
			{
				// If $row_id is below 0, it is not previously stored in the database
				$this->pdo->exec('INSERT INTO order_rows (order_id) VALUES('.$quoted_id.');');
				unset($order_data['rows'][$row_id]);
				$row_id = $this->pdo->lastInsertId();
				$order_data['rows'][$row_id] = $row_data;
			}
		}

		// Build the big SQL to update the order tables
		$sql = '
			LOCK TABLES order_orders_fields WRITE, order_rows WRITE, order_rows_fields WRITE;
			DELETE FROM order_orders_fields WHERE order_id = '.$quoted_id.';
			DELETE FROM order_rows_fields WHERE row_id IN (SELECT id FROM order_rows WHERE order_id = '.$quoted_id.');
			DELETE FROM order_rows WHERE order_id = '.$quoted_id;

		if (count($order_data['rows']))
			$sql .= ' AND id NOT IN ('.implode(',', array_keys($order_data['rows'])).')';
		$sql .= ';';

		if (count($order_data['fields']))
		{
			$sql .= 'INSERT INTO order_orders_fields (order_id,field_id,`value`) VALUES';
			foreach ($order_data['fields'] as $name => $value)
				$sql .= '('.$quoted_id.','.$this->get_field_id($name).','.$this->pdo->quote($value).'),';
			$sql = rtrim($sql, ',').';';
		}

		if (count($order_data['rows']))
		{
			$sql .= 'INSERT INTO order_rows_fields (row_id,field_id,`value`) VALUES';
			foreach ($order_data['rows'] as $row_id => $row_data)
			{
				foreach ($row_data as $name => $value)
					$sql .= '('.$this->pdo->quote($row_id).','.$this->get_row_field_id($name).','.$this->pdo->quote($value).'),';
			}
			$sql = rtrim($sql, ',').';';
		}

		$sql .= 'UNLOCK TABLES;';

		$this->pdo->exec($sql);

		return $order_data['id'];
	}

}
