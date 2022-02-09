<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="sln-admin-sidebar <?php if (!defined("SLN_VERSION_PAY") || !SLN_VERSION_PAY) {echo " sln-admin-sidebar--free";}?>">
	<div class="sln-update-settings__wrapper">
		<div class="sln-btn sln-btn--main sln-btn--big sln-btn--icon sln-icon--save sln-update-settings">
			<input type="submit" name="submit" id="submit" class="" value="Update Settings">
		</div>
	</div>

	<?php if (!defined("SLN_VERSION_PAY") || !SLN_VERSION_PAY) {
	?>
	<div class="sln-admin-banner">
		<div class="col-md4"></div>
		<div class="col-md4">

			<div class="sln-promo-message">

			<?php
$input = array(
		'are you having too much no-shows? request upfront online payment and reduce them dramatically',
		'with the pro version you could use PayPal, Stripe, Square, Mollie, Paystack, any many other payment gateways',
		'we have a mobile app that your salon staff could use to control, add and manage your reservations',
		'do you need a quick support by email? <br />switch to a Business Plan for only 76€ per year',
		'with our Multi-Shops add-on you could manage multiple local branches of your salon from the same website',
	);

	shuffle($input);

	$current_user = wp_get_current_user();
	$user_name = $current_user->first_name;

	?>

				<img src="<?php echo SLN_PLUGIN_URL ?>/img/dimitri.png">
				<p class="message-info-left">Dimitri</p>
				<p class="message-info-right"><?php echo date('H:i'); ?></p>

				<p class="message-content">Hi <?php echo $user_name; ?>!
				<br />
				<?php echo $input[0]; ?>
				</p>
				<a href="https://www.salonbookingsystem.com/homepage/plugin-pricing/" class="message-cta" target="blank">GO PRO</a>

			</div>


		</div>
		<div class="col-md4"></div>
	</div>
	<?php }?>
	<div class="sln-help-button__block">
		<button class="sln-help-button sln-btn sln-btn--nobkg sln-btn--big sln-btn--icon sln-icon--helpchat sln-btn--icon--al visible-md-inline-block visible-lg-inline-block"><?php _e('Do you need help ?', 'salon-booking-system')?></button>
    	<button class="sln-help-button sln-btn sln-btn--mainmedium sln-btn--small--round sln-btn--icon  sln-icon--helpchat sln-btn--icon--al hidden-md hidden-lg"><?php _e('Do you need help ?', 'salon-booking-system')?> </button>
	</div>
</div>
<div class="clearfix"></div>

<script type="text/javascript">
jQuery(function($) {


$("div.sln-promo-message").delay(4000).animate({opacity:1, },1500).delay(10000).fadeOut(500);

$("a.message-cta").on('hover', function() {

	$("div.sln-promo-message").fadeIn(500);
});

});
</script>