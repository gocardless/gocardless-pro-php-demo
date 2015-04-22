<?php

if (date_default_timezone_get() == null) {
  // So default osx install doesn't explode with warnings.
  date_default_timezone_set('Europe/London');
}

require_once __DIR__ . '/vendor/autoload.php';

class GoCardlessAPI
{
	public function __construct()
	{
    $loader = new Twig_Loader_Filesystem(__DIR__ + '/templates');
    $this->twig = new Twig_Environment($loader);
		$this->client = new GoCardless\Client(array(
		  'api_key'     => getenv('GC_API_KEY'),
		  'api_secret'  => getenv('GC_API_SECRET'),
		  'environment' => \GoCardless\Environment::SANDBOX
		));
	}

	public function getCustomer($id)
	{
		return $this->client->customers()->get($id);
	}

	public function listPayments($cid)
	{
		return $this->client->payments()->list(array(
			'customer' => $cid
		));
	}

	public function getCustomerMandates($id)
	{
		return $this->client->mandates()->list(array(
			'customer' => $id,
			'limit' => 1
		));
	}

	public function chargeCustomer($cid, $mid, $amount)
	{
		$newPayment = $this->client->payments()->create(array(
			'amount' => $amount * 100,
			'currency' => 'GBP',
			'description' => 'lunch',
			'links' => array(
				'mandate' => $mid
			)
		));
		return $newPayment;
	}

	public function getCurrentUser()
	{
		if ($this->currentUser) {
			return $this->currentUser;
		}
		$this->currentUser = $this->client->users()->list()[0];
		return $this->currentUser;
	}

	public function listCustomers()
	{
		return $this->client->customers()->list();
	}

	public function listUsers()
	{
		return $this->client->users()->list();
	}
}


