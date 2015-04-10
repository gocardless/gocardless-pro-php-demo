<?php

require('../gocardless.php');
$gc = new GoCardlessAPI();

try {
  
  if (isset($_POST['really']) && $_POST['really'] === 'go') {
      $charge = $gc->chargeCustomer($_POST['cid'], $_POST['mid'], 5);
      header('Location: billed.php?pid=' . $charge->id());
      exit();
  }

  if (isset($_POST['action']) && $_POST['action'] === 'create_mandate') {
      $account = $gc->client->customer_bank_accounts()->list();
      if (count($account) == 0) {
        $why = 'No bank account found!';
        include '../partials/error.php';
        exit();
      }
      $gc->client->mandates()->create(array(
          'links' => array(
              'creditor' => $_POST['cid'],
              'customer_bank_account' => $account[0]->id()
          )
      )); 
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
  <h2>Welcome <?= $gc->listUsers()[0]->given_name(); ?>:</h2>

  <p>You are charging <?= $customer->given_name() ?> <?= $customer->family_name() ?> 5£:</p>
  
    <big>
      <form method="post" action="">
        <input value="<?= $customer->id() ?>" type="hidden" name="cid" />
        <?php if (isset($mandate)): ?>
            <input value="<?= $mandate->id() ?>" type="hidden" name="mid" />
            <button class="button-xlarge pure-button" name="really" value="go"><b>yes</b>, i meant this</button>
        <?php else: ?>
            <button class="button-large pure-button" name="action" value="create_mandate">create authorisation</button>
        <?php endif; ?>
      </form>
      | 
      <form method="get" action="/">
        <button class="button-large pure-button">nope, i didn't</button>
      </form>
    </big>

    <div style="margin-top:30px">
      <?php if (isset($mandate)): ?>
        <div><a href="mandatepdf.php?mid=<?= $mandate->id(); ?>">view mandate</a></div>
      <?php endif; ?>
    </div>

    <?php require '../partials/recent_payments.php'; ?>
<?php $gc->footer(); ?>
