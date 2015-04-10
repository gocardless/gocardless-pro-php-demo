<h3>Recent Payments:</h3>
<ul>
<?php foreach ($payments as $payment): ?>
    <li>
        <?= $payment->currency() === 'GBP' ? '£' : '€' ?><?= intval($payment->amount() / 100) ?> on <?= date('r', strtotime($payment->created_at())) ?>.
    </li>
<?php endforeach; ?>
<?php if (count($payments) == 0): ?>
	<h3>No payments found.</h3>
<?php endif; ?>
</ul>