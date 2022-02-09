<?php

class SLN_Service_Messages
{
    private $plugin;
    private $disabled = false;

    private static $statusForSummary = array(
        SLN_Enum_BookingStatus::PAID,
        SLN_Enum_BookingStatus::PAY_LATER,
    );

    public function __construct(SLN_Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function setDisabled($bool)
    {
        $this->disabled = $bool;
    }

    public function sendByStatus(SLN_Wrapper_Booking $booking, $status)
    {
        if ($this->disabled) {
            return;
        }

	if ($booking->getMeta('disable_status_change_email')) {
	    $booking->setMeta('disable_status_change_email', 0);
	    return;
	}

        do_action('sln.messages.before_booking_send_message', $booking);
        $p = $this->plugin;
        if ($status == SLN_Enum_BookingStatus::CONFIRMED) {
            $this->sendBookingConfirmed($booking);
        } elseif ($status == SLN_Enum_BookingStatus::CANCELED) {
            if($booking->getNotifyCustomer()) {
                $p->sendMail('mail/status_canceled', compact('booking'));
            }
            $forAdmin = true;
            $p->sendMail('mail/status_canceled', compact('booking', 'forAdmin'));
        } elseif ($status == SLN_Enum_BookingStatus::PENDING_PAYMENT && $booking->getNotifyCustomer()) {
            $p->sendMail('mail/status_pending_payment', compact('booking'));
        } elseif (in_array($status, self::$statusForSummary)) {
            $this->sendSummaryMail($booking);
            $this->sendSmsBooking($booking);
        }
    }

    private function sendBookingConfirmed(SLN_Wrapper_Booking $booking)
    {
        if ($this->plugin->getSettings()->get('confirmation')) {
            $this->plugin->sendMail('mail/status_confirmed', compact('booking'));
        } else {
            $this->sendSummaryMail($booking);
        }
        $this->sendSmsBooking($booking);
    }

    public function sendBookingModified(SLN_Wrapper_Booking $booking) {
        $this->sendSmsModifiedBooking($booking);
        $this->sendSummaryModifiedMail($booking);
    }

    public function sendSmsBooking($booking)
    {
        do_action('sln.messages.before_booking_send_message', $booking);

        $p   = $this->plugin;
        $sms = $p->sms();
        $s   = $p->getSettings();

        if ($s->get('sms_new')) {

            $phone = $s->get('sms_new_number');
            if ($phone) {
                $sms->send($phone, $p->loadView('sms/summary', compact('booking')));
            }

            $phone = $booking->getPhone();
            if ($phone && $booking->getNotifyCustomer()) {

                $sms->send($phone, $p->loadView('sms/summary', compact('booking')), $booking->getSmsPrefix());
            }
        }

        if ($s->get('sms_new_attendant')) {

            $tmpAttendants = $booking->getAttendants();
            $tmpAttendants = $tmpAttendants && is_array($tmpAttendants) ? $tmpAttendants : array();

            $attendants = array();

            foreach ($tmpAttendants as $a) {
                $attendants[$a->getId()] = $a;
            }

            foreach ($attendants as $attendant) {

                $phone = $attendant->getPhone();

                if ($phone) {
                    $sms->send($phone, $p->loadView('sms/summary', compact('booking')), $attendant->getSmsPrefix());
                }
            }
        }

        do_action('sln.messages.booking_sms',$booking);
    }

    private function sendSmsModifiedBooking($booking) {
        do_action('sln.messages.before_booking_send_message', $booking);

        $p   = $this->plugin;
        $sms = $p->sms();
        $s   = $p->getSettings();

        if ($s->get('sms_modified')) {

            $phone = $s->get('sms_new_number');
            if ($phone) {
                $sms->send($phone, $p->loadView('sms/summary_modified', compact('booking')));
            }

            $phone = $booking->getPhone();
            if ($phone && $booking->getNotifyCustomer()) {
                $sms->send($phone, $p->loadView('sms/summary_modified', compact('booking')), $booking->getSmsPrefix());
            }
        }

        if ($s->get('sms_modified_attendant')) {

            $tmpAttendants = $booking->getAttendants();
            $tmpAttendants = $tmpAttendants && is_array($tmpAttendants) ? $tmpAttendants : array();

            $attendants = array();

            foreach ($tmpAttendants as $a) {
                $attendants[$a->getId()] = $a;
            }

            foreach ($attendants as $attendant) {

                $phone = $attendant->getPhone();

                if ($phone) {
                    $sms->send($phone, $p->loadView('sms/summary_modified', compact('booking')), $attendant->getSmsPrefix());
                }
            }
        }

        do_action('sln.messages.modified_booking_sms',$booking);
    }

    public function sendRescheduledMail($booking)
    {
        do_action('sln.messages.before_booking_send_message', $booking);

	    $rescheduled = true;

        $p = $this->plugin;
        if($booking->getNotifyCustomer()) {
            $p->sendMail('mail/summary', compact('booking', 'rescheduled'));
        }
        $p->sendMail('mail/summary_admin', compact('booking', 'rescheduled'));
    }

    public function sendSummaryMail($booking)
    {
        do_action('sln.messages.before_booking_send_message', $booking);

        $p = $this->plugin;
        if($booking->getNotifyCustomer()) {
            $p->sendMail('mail/summary', compact('booking'));
        }
        $p->sendMail('mail/summary_admin', compact('booking'));
        do_action('sln.messages.booking_summary_mail',$booking);
    }

    private function sendSummaryModifiedMail($booking) {
        do_action('sln.messages.before_booking_send_message', $booking);

        $p = $this->plugin;
        $updated = true;
        if($booking->getNotifyCustomer()) {
            $p->sendMail('mail/summary', compact('booking', 'updated'));
        }
        $p->sendMail('mail/summary_admin', compact('booking', 'updated'));
        do_action('sln.messages.booking_summary_modified_mail',$booking);
    }

    public function getStatusForSummary() {
        return self::$statusForSummary;
    }
}
