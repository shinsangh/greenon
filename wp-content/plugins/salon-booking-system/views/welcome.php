<?php
/**
* Welcome Page View
*
* @since 1.0.0
* @package salon-booking-system
*/
if ( ! defined( 'WPINC' ) ) {
die;
}
?>

      <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
       
      <!-- SET: Stylesheet -->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
       <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<div class="wrapper">
		   <!---Salon Start Here-->
			 <div class="salon">
			  <div class="container">
				  <div class="salon_in">
				     <h1><?php _e('Welcome!','salon-booking-system' ); ?></h1>
					  <p><?php _e('<span>Salon Booking System </span> has been correctly installed and activated. <br> Now it’s time to begin your setup and get everything started.','salon-booking-system' ); ?></p>
				  </div>
				 </div>
			  </div>
		   <!---Salon End Here-->
		   <!---Dummy Start Here-->
			<div class="dummy">
			  <div class="container">
				 <div class="dummy_in">
				   <ul class="d-flex flex-wrap">
					    <li class="col-md-4">
						 <div class="service">
						  <img src="<?php echo plugin_dir_url( __DIR__ ); ?>img/welcome/img1.png" width="165" height="196" alt="img1">
							 <div class="dolor">
							 <h4><?php _e('Wanna see your <br> booking form in action?','salon-booking-system' ); ?></h4>
							 <p><?php _e('We added three new pages that will be used by your new booking system.','salon-booking-system' ); ?></p>
							 <p><?php _e('We also created some dummy services and assistants just to show you how it looks like.','salon-booking-system' ); ?></p>
								 </div>
							 <a href="<?php echo get_permalink($settings->getPayPageId()) ?>" target="blank"><?php _e('Let’s see it','salon-booking-system' ); ?></a>
						 </div>
					   </li>
					   <li class="col-md-4">
						 <div class="service manage">
						  <img src="<?php echo plugin_dir_url( __DIR__ ); ?>img/welcome/img2.png" width="189" height="196" alt="img1">
							  <div class="dolor">
							 <h4><?php _e('Take a look at your <br> bookings calendar','salon-booking-system' ); ?></h4>
							 <p><?php _e('Let’s see how easy is to add and manage reservations trough your new back-end calendar.','salon-booking-system' ); ?></p>
							 </div>
							 <a href="/wp-admin/admin.php?page=salon" target="blank"><?php _e('Let’s go','salon-booking-system' ); ?></a>
						 </div>
					   </li>
					   <li class="col-md-4">
						 <div class="service">
						  <img src="<?php echo plugin_dir_url( __DIR__ ); ?>img/welcome/img3.png" width="142" height="196" alt="img1">
							  <div class="dolor">
							 <h4><?php _e('It’s time to setup<br>your booking system','salon-booking-system' ); ?></h4>
							 <span><?php _e('These are the simple steps to follow:','salon-booking-system' ); ?></span>
							 <ul>
							 <li> - <a href="/wp-admin/admin.php?page=salon-settings" target="blank">  <?php _e('Enter your business information','salon-booking-system' ); ?></a></li>
								 <li> - <a href="/wp-admin/admin.php?page=salon-settings&tab=booking" target="blank"><?php _e('Setup your scheduling rules','salon-booking-system' ); ?></a></li>
								 <li>- <a href="/wp-admin/edit.php?post_type=sln_service" target="blank"><?php _e('Adding your services','salon-booking-system' ); ?></a>
								 </li>
								 <li>- <a href="/wp-admin/edit.php?post_type=sln_attendant" target="blank"><?php _e('Adding your assistants','salon-booking-system' ); ?></a></li>
							 </ul>
							 </div>
							 <a href="/wp-admin/admin.php?page=salon-settings" target="blank"><?php _e('Let’s begin','salon-booking-system' ); ?></a>
				   		 </div>
					   </li>
					 </ul>
					 <div class="doubt">
					  <p><?php _e('In case of doubts click on: ','salon-booking-system' ); ?><a href="#"><?php _e('Do you need help','salon-booking-system' ); ?></a> </p>
					 </div>
					 <div class="salon_book">
					   <a href="https://salonbookingsystem.com/">
					   	<img src="<?php echo plugin_dir_url( __DIR__ ); ?>img/welcome/salon_booking_welcome_logo.png" width="195" height="64" alt="salon booking system logo">
					   </a>
					 </div>
				  </div>
				</div>
			</div>
		   <!---Dummy End Here-->
		</div>