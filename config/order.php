<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'default_VAT'    => 1.25, // Default VAT multiplier for an order ROW

	// Default field values will be set at order save time if not set before
	'default_fields' => array(
		'date'     => date('Y-m-d H:i:s', time()),
		'currency' => 'SEK',
	),
);
