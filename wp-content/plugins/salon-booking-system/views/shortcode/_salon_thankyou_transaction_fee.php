<?php
/**
 * @var $plugin SLN_Plugin
 */
$transactionFee = SLN_Helper_TransactionFee::getFee($plugin->getBookingBuilder()->getLastBooking()->getToPayAmount(false, false));
?>
<?php if ( ! empty( $transactionFee ) ): ?>
    <div class="sln-payment-transaction-fee">
	<?php echo sprintf(
	    __('A transaction fee of %s will be applied', 'salon-booking-system'),
	    '<strong>'.$plugin->format()->money($transactionFee, false, false, true).'</strong>'
	) ?>
    </div>
<?php endif; ?>