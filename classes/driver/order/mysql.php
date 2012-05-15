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
		                   array('Field' => 'orderid',    'Type' => 'bigint(20) unsigned', 'Null' => 'NO',  'Key' => 'MUL', 'Default' => NULL, 'Extra' => NULL            ),
		                   array('Field' => 'fieldid',    'Type' => 'int(10) unsigned',    'Null' => 'NO',  'Key' => 'MUL', 'Default' => NULL, 'Extra' => NULL            ),
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
		                   array('Field' => 'orderid',    'Type' => 'bigint(20) unsigned', 'Null' => 'NO',  'Key' => 'UNI', 'Default' => NULL, 'Extra' => NULL            ),
		                 )
		) $db_check_pass = FALSE;

		$result = $this->pdo->query('DESCRIBE `order_rows_fields`;');
		if ($result) $result = $result->fetchAll(PDO::FETCH_ASSOC);
		if (
		     $result != array(
		                   array('Field' => 'rowid',      'Type' => 'bigint(20) unsigned', 'Null' => 'NO',  'Key' => 'MUL', 'Default' => NULL, 'Extra' => NULL            ),
		                   array('Field' => 'fieldid',    'Type' => 'int(10) unsigned',    'Null' => 'NO',  'Key' => 'MUL', 'Default' => NULL, 'Extra' => NULL            ),
		                   array('Field' => 'value',      'Type' => 'text',                'Null' => 'YES', 'Key' => NULL,  'Default' => NULL, 'Extra' => NULL            ),
		                 )
		) $db_check_pass = FALSE;


		// Turn on error reportning again
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $db_check_pass;
	}

	protected function create_db_structure() {
		$this->pdo->query('
			CREATE TABLE IF NOT EXISTS `order_fields` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) CHARACTER SET latin1 NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
			CREATE TABLE IF NOT EXISTS `order_orders` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
			CREATE TABLE IF NOT EXISTS `order_orders_fields` (
				`orderid` bigint(20) unsigned NOT NULL,
				`fieldid` int(10) unsigned NOT NULL,
				`value` text CHARACTER SET latin1,
				KEY `orderid` (`orderid`,`fieldid`),
				KEY `orderid_2` (`orderid`),
				KEY `fieldid` (`fieldid`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `order_rowfields` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) CHARACTER SET latin1 NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
			CREATE TABLE IF NOT EXISTS `order_rows` (
				`id` bigint(20) unsigned NOT NULL,
				`orderid` bigint(20) unsigned NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `orderid` (`orderid`),
				UNIQUE KEY `id` (`id`,`orderid`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `order_rows_fields` (
				`rowid` bigint(20) unsigned NOT NULL,
				`fieldid` int(10) unsigned NOT NULL,
				`value` text CHARACTER SET latin1,
				KEY `rowid` (`rowid`,`fieldid`),
				KEY `rowid_2` (`rowid`),
				KEY `fieldid` (`fieldid`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			ALTER TABLE `order_fields`
				ADD CONSTRAINT `order_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `order_orders_fields` (`fieldid`);
			ALTER TABLE `order_orders_fields`
				ADD CONSTRAINT `order_orders_fields_ibfk_2` FOREIGN KEY (`fieldid`) REFERENCES `order_fields` (`id`),
				ADD CONSTRAINT `order_orders_fields_ibfk_1` FOREIGN KEY (`orderid`) REFERENCES `order_orders` (`id`);
			ALTER TABLE `order_rowfields`
				ADD CONSTRAINT `order_rowfields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `order_rows_fields` (`fieldid`);
			ALTER TABLE `order_rows`
				ADD CONSTRAINT `order_rows_ibfk_2` FOREIGN KEY (`id`) REFERENCES `order_rows_fields` (`rowid`),
				ADD CONSTRAINT `order_rows_ibfk_1` FOREIGN KEY (`orderid`) REFERENCES `order_orders` (`id`);
			ALTER TABLE `order_rows_fields`
				ADD CONSTRAINT `order_rows_fields_ibfk_2` FOREIGN KEY (`fieldid`) REFERENCES `order_rowfields` (`id`),
				ADD CONSTRAINT `order_rows_fields_ibfk_1` FOREIGN KEY (`rowid`) REFERENCES `order_rows` (`id`);
		');

		return $this->check_db_structure();
	}

	public function get($order_id)
	{
// This needs proper code. Populate the array():s
		return array('id' => $order_id, 'fields', => array(), 'rows' => array());
	}

	public function order_id_exists($order_id)
	{
		return (bool) $this->pdo->query('SELECT id FROM order_orders WHERE id = '.$this->pdo->quote($order_id))->fetchColumn();
	}

}
