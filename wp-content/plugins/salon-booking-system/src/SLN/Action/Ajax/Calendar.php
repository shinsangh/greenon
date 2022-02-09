<?php

use Salon\Util\Date;

class SLN_Action_Ajax_Calendar extends SLN_Action_Ajax_Abstract
{
    private $from;
    private $to;
    /** @var  SLN_Wrapper_Booking[] */
    private $bookings;
    /** @var  SLN_Wrapper_Attendant[] */
    private $assistants;

    public function execute()
    {
        $off = get_option('gmt_offset') * 3600;
        $offset = intval($_GET['offset']) * 60;
        $this->from = (new SLN_DateTime)->setTimestamp( sanitize_text_field(wp_unslash($_GET['from']) ) / 1000 - $offset)->setTimezone(new DateTimeZone('UTC'));
        $this->to = (new SLN_DateTime)->setTimestamp( sanitize_text_field(wp_unslash($_GET['to']) ) / 1000 - $offset)->setTimezone(new DateTimeZone('UTC'))->sub(new DateInterval('P1D'));
        $this->buildBookings();
        $this->buildAssistants();
        $ret = array(
            'success' => 1,
            'result' => array(
                'events' => $this->getResults(),
                'assistants' => $this->getAssistants(),
                'stats' => $this->getStats(),
            ),
        );

        return $ret;
    }

    private function getStats()
    {
        $bc = $this->plugin->getBookingCache();
        $clone = clone $this->from;
        $ret = array();
        while ($clone <= $this->to) {
            $dd = clone $clone;
            $dd->modify('+1 hour');
            $dd = new Date($dd);
            $tmp = array('text' => '', 'busy' => 0, 'free' => 0);
            $bc->processDate($dd);
            $cache = $bc->getDay($dd);
            if ($cache && $cache['status'] == 'booking_rules') {
                $tmp['text'] = __('Booking Rule', 'salon-booking-system');
            } elseif ($cache && $cache['status'] == 'holiday_rules') {
                $tmp['text'] = __('Holiday Rule', 'salon-booking-system');
            } else {
                $tot = 0;
                $cnt = 0;
                foreach ($this->bookings as $b) {
                    if ($b->getDate()->format('Ymd') == $clone->format('Ymd')) {
                        if (!$b->hasStatus(
                            array(
                                SLN_Enum_BookingStatus::CANCELED,
                            )
                        )
                        ) {
                            $tot += $b->getAmount();
                            $cnt++;
                        }
                    }
                }
                if (isset($cache['free_slots'])) {
                    $free = count($cache['free_slots']) * $this->plugin->getSettings()->getInterval();
                } else {
                    $free = 0;
                }
                if (isset($cache['busy_slots'])) {
                    $busy = count($cache['busy_slots']) * $this->plugin->getSettings()->getInterval();
                } elseif ($cache && $cache['status'] == 'full') {
                    $busy = 1;
                } else {
                    $busy = 0;
                }
                $freeH = intval($free / 60);
                $freeM = ($free % 60);
                $tot = $this->plugin->format()->money($tot,false);
                $tmp['text'] = '<div class="calbar-tooltip">'
                    ."<span><strong>$cnt</strong>".__('bookings', 'salon-booking-system')."</span>"
                    ."<span><strong>$tot</strong>".__('revenue', 'salon-booking-system')."</span>"
                    ."<span><strong>{$freeH}".__('hrs', 'salon-booking-system').' '
                    .($freeM > 0 ? "{$freeM}".__('mns', 'salon-booking-system') : '').'</strong>'
                    .__('available left', 'salon-booking-system').'</span></div>';
                if ($free || $busy) {
                    $tmp['free'] = intval(($free / ($free + $busy)) * 100);
                    $tmp['busy'] = 100 - $tmp['free'];
                }
            }
            $ret[$dd->toString('Y-m-d')] = $tmp;
            $clone->modify('+1 days');
        }

        return $ret;
    }

    private function getAssistants()
    {
        $ret = array();
        $times = SLN_Func::getMinutesIntervals();
        $interval = $this->plugin->getSettings()->getInterval();
        $interval = new SLN_DateTime('@'.$interval*60);
        foreach ($this->assistants as $att) {
            $availableTimes = array();
            foreach ($times as $time) {
                $dateTime = new SLN_DateTime(Date::create($this->from)->toString() . ' ' . $time, new DateTimeZone('UTC'));
                //TODO: add method isNotAvailableOnDateDuration and use here
                if($att->getAvailabilityItems()->isValidDatetimeDuration($dateTime, $interval) &&
                $att->getNewHolidayItems()->isValidDatetimeDuration($dateTime, $interval)) {
                    $availableTimes[] = $time;
                }
            }
            $ret[$att->getId()] = array(
                'name' => $att->getName(),
                'times' => $availableTimes,
            );
        }

        return $ret;
    }

    private function buildAssistants()
    {
        $this->assistants = $this->plugin
            ->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT)
            ->getAll();

    $this->assistants = apply_filters('sln.action.ajaxcalendar.assistants', $this->assistants);

    if ( in_array(SLN_Plugin::USER_ROLE_STAFF,  wp_get_current_user()->roles) ) {
        $assistants = array_filter($this->assistants, function ($attendant) {
        return $attendant->getMeta('staff_member_id') == get_current_user_id() && $attendant->getIsStaffMemberAssignedToBookingsOnly();
        });
        if ( ! empty( $assistants ) ) {
        $this->assistants = $assistants;
        }
    }
    }

    private function getResults()
    {
        $ret = array();
        foreach ($this->bookings as $b) {
            $ret[] = $this->wrapBooking($b);
        }

        return $ret;
    }

    private function buildBookings()
    {
        $this->bookings = $this->plugin
            ->getRepository(SLN_Plugin::POST_TYPE_BOOKING)
            ->get($this->getCriteria());


    if ( in_array(SLN_Plugin::USER_ROLE_STAFF,  wp_get_current_user()->roles) ) {

        $assistantsIDs = array();

        $repo       = $this->plugin->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT);
        $attendants = $repo->getAll();

        foreach ($attendants as $attendant) {
        if ($attendant->getMeta('staff_member_id') == get_current_user_id() && $attendant->getIsStaffMemberAssignedToBookingsOnly()) {
            $assistantsIDs[] = $attendant->getId();
        }
        }

        if ( ! empty( $assistantsIDs ) ) {
        $this->bookings = array_filter($this->bookings, function ($booking) use ($assistantsIDs) {
            return array_intersect($assistantsIDs, $booking->getAttendantsIds());
        });
        }
    }
    }

    /**
     * @param SLN_Wrapper_Booking $booking
     *
     * @return array|mixed
     */
    private function wrapBooking($booking)
    {
        $format = SLN_Plugin::getInstance()->format();

	$total = 0;

	$discountAmount = apply_filters('sln.action.ajaxcalendar.wrapBooking.discountAmount', 0, $booking);

        foreach ($booking->getBookingServices()->getItems() as $bookingService) {
            $price   = $bookingService->getPrice();
            $total += $price;
        }

	$total += $booking->getTips();
	$total += $discountAmount;

        $ret = array(
            "id"          => $booking->getId(),
            "title"       => $this->getTitle($booking),
            "from"        => $format->time($booking->getStartsAt()),
            "to"          => $format->time($booking->getEndsAt()),
            "from_label"  => __('from', 'salon-booking-system'),
            "to_label"    => __('to', 'salon-booking-system'),
            "status"      => SLN_Enum_BookingStatus::getLabel($booking->getStatus()),
            "customer"    => $booking->getDisplayName(),
            "customer_id" => (int) $booking->getUserId(),
            "services"    => $booking->getServicesIds(),
            "items"       => $booking->getBookingServices()->toArrayRecursive(),
            "url"         => get_edit_post_link($booking->getId()),
            "delete_url"  => get_delete_post_link($booking->getId()),
            "class"       => "event-" . SLN_Enum_BookingStatus::getColor($booking->getStatus()),
            "start"       => $booking->getStartsAt('UTC')->format('U') * 1000,
            "end"         => $booking->getEndsAt('UTC')->format('U') * 1000,
            "event_html"  => $this->getEventHtml($booking),
            "amount"      => $format->moneyFormatted($total, false, true),
            "discount"    => $format->moneyFormatted($discountAmount, false, true),
            "deposit"     => $format->moneyFormatted($booking->getDeposit(), false, true),
            "due"         => $format->moneyFormatted($booking->getAmount() - $booking->getDeposit(), false, true),
            "calendar_day"  => $this->getCalendarDay($booking),
            "calendar_day_assistants"  => $this->getCalendarDayAssistants($booking),
	    'is_pro_version' => defined("SLN_VERSION_PAY"),
        );

        return apply_filters('sln.action.ajaxcalendar.wrapBooking', $ret, $booking);
    }

    private function getCriteria()
    {
        $criteria = array();
        if ($this->from->format('Y-m-d') == $this->to->format('Y-m-d')) {
            $criteria['day'] = $this->from;
        } else {
            $criteria['day@min'] = $this->from;
            $criteria['day@max'] = $this->to;
        }
        $criteria = apply_filters('sln.action.ajaxcalendar.criteria', $criteria);

        return $criteria;
    }

    private function getTitle($booking)
    {
        return $this->plugin->loadView('admin/_calendar_title', compact('booking'));
    }

    private function getEventHtml($booking)
    {
        return $this->plugin->loadView('admin/_calendar_event', compact('booking'));
    }

    private function getCalendarDay($booking)
    {
        return $this->plugin->loadView('admin/_calendar_day', compact('booking'));
    }

    private function getCalendarDayAssistants($booking)
    {
    $calendarDayAssistants = array();

    foreach($booking->getBookingServices()->getItems() as $bookingService) {
        $calendarDayAssistants[$bookingService->getService()->getId()] = $this->plugin->loadView('admin/_calendar_day_assistant', compact('booking', 'bookingService'));
    }

    return $calendarDayAssistants;
    }
}