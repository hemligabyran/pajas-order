<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Test
{

	public function before() {}
	public function after() {}

	public function __construct(Request $request, Response $response)
	{
	}

	public function action_index()
	{
echo '<pre>';

		$order = new Order(9);
/*
		$order->add_row(array('name' => 'lurv', 'price' => 399));
		$order->add_row(array('name' => 'grej', 'price' => 599));
		$order->set_field('firstname', 'Migal');
		$order->set_field('lastname', 'fjant');
		$order->set_field('email', 'lilleman@larvit.se');
*/

		print_r($order->get());

		var_dump($order->save());

//		var_dump($order->get_id());
		die();
	}

}
