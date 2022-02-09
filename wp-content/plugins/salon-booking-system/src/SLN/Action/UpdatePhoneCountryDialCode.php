<?php

class SLN_Action_UpdatePhoneCountryDialCode
{
    /** @var SLN_Plugin */
    private $plugin;

    public function __construct(SLN_Plugin $plugin)
    {
        $this->plugin = $plugin;
        add_action('wp_loaded', array($this, 'execute'));
    }

    public function execute()
    {
        if (get_transient('sln_phone_country_dial_code_updated')) {
            return;
        }

        $bookings    = $this->plugin->getRepository(SLN_Plugin::POST_TYPE_BOOKING)->get();
        $phone_codes = json_decode(file_get_contents(SLN_PLUGIN_DIR .'/CountryCodes.json'), true);

        foreach ($bookings as $booking) {
            $phone = $booking->getPhone();
            if (substr($phone, 0, 1) !== '+') {
                continue;
            }
            foreach ($phone_codes as $phone_code) {
                $dial_code = $phone_code['dial_code'];
                if (strstr($phone, $dial_code)) {
                    $phone = str_replace($dial_code, '', $phone);
                    $booking->setMeta('phone', $phone);
                    $booking->setMeta('sms_prefix', $dial_code);
                    break;
                }
            }
        }

        $query = new WP_User_Query(array(
            'role'   => SLN_Plugin::USER_ROLE_CUSTOMER,
            'number' => -1,
        ));

        $customers = array();

        foreach ( $query->results as $customer ) {
            $customers[] = new SLN_Wrapper_Customer($customer);
        }

        foreach ($customers as $customer) {
            $phone = $customer->getMeta('phone');
            if (substr($phone, 0, 1) !== '+') {
                continue;
            }
            foreach ($phone_codes as $phone_code) {
                $dial_code = $phone_code['dial_code'];
                if (strstr($phone, $dial_code)) {
                    $phone = str_replace($dial_code, '', $phone);
                    $customer->setMeta('phone', $phone);
                    $customer->setMeta('sms_prefix', $dial_code);
                    break;
                }
            }
        }

        set_transient('sln_phone_country_dial_code_updated', 1);
    }

}