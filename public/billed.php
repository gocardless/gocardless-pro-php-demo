<?php

require('../gocardless.php');
$gc = new GoCardlessAPI();

$payment = $gc->client->payments()->get($_GET['pid']);
$creditor = $gc->client->creditors()->get($payment->links()->creditor());

$gc->header();

?>

<huge>£<small><?= $payment->amount() / 100 ?></small></huge>

Your payment to <?= $creditor->name(); ?> for £<?= $payment->amount() / 100 ?> has been sent :).

<?php $gc->footer(); ?>