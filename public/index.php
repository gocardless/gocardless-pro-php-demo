<?php

require('../gocardless.php');
$gc = new GoCardlessAPI();

$gc->header();

?>
  <h3>Welcome <?= $gc->listUsers()[0]->givenName(); ?>:</h3>
<div class='pure-menu custom-restricted-width'>
  <span class="pure-menu-heading">Your connections:</span>
  <ul class="pure-menu-list">
  <?php foreach ($gc->listCustomers() as $customer): ?>
    <li class="pure-menu-item"><a class="pure-menu-link" href="./bill.php?cid=<?= $customer->id()?>"><?= $customer->givenName() ?> <?= $customer->familyName(); ?> – bill 5£</a></li>
  <?php endforeach; ?>
  </ul>

</body>
</html>
<?= $gc->footer(); ?>
