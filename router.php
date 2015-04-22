<?php

error_reporting(E_ALL);
date_default_timezone_set('UTC');

// Require composer autoloader
require __DIR__ . '/vendor/autoload.php';

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader);


// Create Router instance
$router = new AltoRouter();

require __DIR__ . '/gocardless.php';

$gc = new GoCardlessAPI();

// Define routes
$router->map('GET', '/', function() use ($twig, $gc) {
	echo $twig->render('index.html', array('users' => $gc->listUsers(), 'customers' => $gc->listCustomers()));
});

$router->map('POST', '/mandates/create', function() {
	
});

$router->map('POST', '/charge/create', function() use ($gc) {
	if (isset($_POST['mid'])) {
		$charge = $gc->chargeCustomer($_POST['cid'], $_POST['mid'], 5);
		header('Location: /customers/' . $_POST['cid'] . '/bill');
		return;
	} else if (isset($_POST['cid'])) {
		$accounts = $gc->client->customer_bank_accounts()->list();
		$creditors = $gc->client->creditors()->list();
		if (count($accounts) === 0 || count($creditors) === 0) {
			throw new Exception('No bank account/creditor found!');
		}
		$gc->client->mandates()->create(array(
		    'links' => array(
		        'creditor' => $creditors[0]->id(),
		        'customer_bank_account' => $accounts[0]->id()
		    )
		));
		header('Location: /customers/' . $_POST['cid'] . '/bill');
		return;
	}
	throw new Exception('invalid request!');
});

$router->map('GET', '/customers/[a:cid]/bill', function($cid) use ($twig, $gc) {
	$customer = $gc->getCustomer($cid);
 	$mandates = $gc->getCustomerMandates($customer->id());
 	$mandate = null;
 	if (count($mandates) > 0) {
 		$mandate = $mandates[0];
 	}
 	$payments = $gc->listPayments($customer->id());
 	echo $twig->render('bill.html', array(
 		'payments' => $payments,
 		'mandates' => $mandates,
 		'mandate' => $mandate,
 		'customer' => $customer
 	));
});

$router->map('GET', '/oneoff', function() use ($twig) {
	echo $twig->render('oneoff.html');
});

$router->map('GET', '/bills/paid_success/[a:fid]', function($fid) use ($gc) {
	$res = $gc->client->redirect_flows()->complete(array('session_token' => $fid));
	echo '<pre>';
	print_r($res);
	echo '</pre>';
});

$router->map('POST', '/oneoff', function() use ($gc) {
	if (!isset($_POST['description'])) {
		throw new Exception('Description required!');
	}
	$description = $_POST['description'];

	$creditor_id = $gc->client->creditors()->list()[0]->id();
	$uid = uniqid();
	$flow = $gc->client->redirect_flows()->create(array(
		'session_token' => $uid,
		'success_redirect_url' => 'http://localhost:8080/bills/paid_success/' . $uid,
		'description' => $description,
		'links' => array(
			'creditor' => $creditor_id
		)
	));
	print_r($flow);
	header('Location: /flows/' . $flow->id());
});

$router->map('GET', '/flows/[a:flow]', function($flow_id) use ($twig, $gc) {
	$flow = $gc->client->redirect_flows()->get($flow_id);
	echo $twig->render('flow.html', array('flow' => $flow));
});

$router->map('GET', '/billed/[a:pid]', function($pid) use ($gc) {
	$payment = $gc->client->payments()->get($pid);
	$creditor = $gc->client->creditors()->get($payment->links()->creditor());
	echo $twig->render('billed.html', array('payment' => $payment, 'creditor' => $creditor));
});

$router->map('GET', '/mandate/[a:mid]/pdf', function($mid) use ($gc) {
	try {
		$pdfMandate = $gc->client->mandates()->get(
			$mid,
			array(),
			array(
				'Accept' => 'application/pdf',
				'Accept-Language' => 'en'
			)
		);

		header('Content-Type: application/pdf');

		echo $pdfMandate->response()->raw_body();
	} catch (\GoCardless\Core\Error\GoCardlessError $e) {
		echo '<pre>' . print_r($e, true);
	}
});

// match current request url
$match = $router->match();

// call closure or throw 404 status
if( $match && is_callable( $match['target'] ) ) {
	call_user_func_array( $match['target'], $match['params'] ); 
} else {
	// no route was matched
	header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
	echo $twig->render('404.html');
}