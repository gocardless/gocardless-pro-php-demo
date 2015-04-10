<?php

require('../gocardless.php');
$gc = new GoCardlessAPI();

try {
  
  if (isset($_POST['really']) && $_POST['really'] === 'go') {
      $charge = $gc->chargeCustomer($_POST['cid'], $_POST['mid'], 5);
      header('Location: billed.php?pid=' . $charge->id());
      exit();
  }

  $customer = $gc->getCustomer($_GET['cid']);

  $mandates = $gc->getCustomerMandates($customer->id());
  if (count($mandates) > 0) {
      $mandate = $mandates[0];
  }

  $payments = $gc->listPayments($customer->id());

  $gc->header();

} catch (\GoCardless\Core\Error\InvalidApiUsageError $e) {
  $why = 'error';
  if ($e->httpStatus() == 404) {
    $why = 'not found';
  }
  require '../partials/error.php';
  exit();
}

?>
<style>
.n {
    background: #f5d5d7;
}
.y {
    background: lightgreen;
}
big button {
    font-size: 20px;
}
form {
    display:inline;
}
</style>
  <h2>Welcome <?= $gc->listUsers()[0]->givenName(); ?>:</h2>

  <p>You are charging <?= $customer->givenName() ?> <?= $customer->familyName() ?> 5£:</p>
  
    <big>
      <form method="post" action="">
        <input value="<?= $customer->id() ?>" type="hidden" name="cid" />
        <?php if (isset($mandate)): ?>
            <input value="<?= $mandate->id() ?>" type="hidden" name="mid" />
            <button class="button-xlarge pure-button" name="really" value="go"><b>yes</b>, i meant this</button>
        <?php else: ?>
            <button class="button-large pure-button">create authorisation</button>
        <?php endif; ?>
      </form>
      | 
      <form method="get" action="/">
        <button class="button-large pure-button">nope, i didn't</button>
      </form>
    </big>

    <?php require '../partials/recent_payments.php'; ?>
<?php $gc->footer(); ?>
