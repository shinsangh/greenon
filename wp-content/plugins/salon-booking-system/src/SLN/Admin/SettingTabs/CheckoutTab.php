<?php
class SLN_Admin_SettingTabs_CheckoutTab extends SLN_Admin_SettingTabs_AbstractTab {
	protected $fields = array(
		'enabled_guest_checkout',
		'enabled_force_guest_checkout',
		'enabled_fb_login',
		'fb_app_id',
		'fb_app_secret',
		'services_count',
		'is_services_count_primary_services',
		'enable_discount_system',
		'checkout_fields',
		'gen_timetable',
		'last_step_note',
	);

    function postProcess()
    {
        SLN_Enum_CheckoutFields::refresh();
        if(SLN_Helper_Multilingual::isMultiLingual()){
            $labels = SLN_Enum_CheckoutFields::all()->labels();
            foreach ($labels as $key => $label){
                SLN_Helper_Multilingual::registerString($label);
            }
        }
    }
}
?>