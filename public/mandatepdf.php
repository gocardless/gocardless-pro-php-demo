<?php

require('../gocardless.php');
$gc = new GoCardlessAPI();

try {
	$pdfMandate = $gc->client->mandates()->get(
		$_GET['mid'],
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