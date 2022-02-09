<?php

use Salon\Util\Date;
use Salon\Util\Time;

class SLN_Action_Ajax_CheckDateAlt extends SLN_Action_Ajax_CheckDate
{
	/**
	 * @param array        $services
	 * @param SLN_DateTime $datetime
	 *
	 * @return bool
	 */
	private function checkDayServicesAndAttendants($services, $datetime) {
		$bookingServices = SLN_Wrapper_Booking_Services::build($services, $datetime);
		$date            = Date::create($datetime->format('Y-m-d'));
		foreach ($bookingServices->getItems() as $bookingService) {
			/** @var SLN_Helper_AvailabilityItems $avServiceItems */
			$avServiceItems = $bookingService->getService()->getAvailabilityItems();
			if(!$avServiceItems->isValidDate($date)) {
				return false;
			}

			$attendant = $bookingService->getAttendant();
			if (!empty($attendant)) {
				/** @var SLN_Helper_AvailabilityItems $avAttendantItems */
				$avAttendantItems = $attendant->getAvailabilityItems();
				if(!$avAttendantItems->isValidDate($date)) {
					return false;
				}
			}
		}

		return true;
	}

    public function getIntervalsArray($timezone = '') {
        if ($this->isAdmin()) {
            return parent::getIntervalsArray();
        }

        $fullDays = array();
        $plugin = $this->plugin;
        $ah   = $plugin->getAvailabilityHelper();
        $bc = $plugin->getBookingCache();
        $hb = $ah->getHoursBeforeHelper();

        $bb = $plugin->getBookingBuilder();
        $bservices = $bb->getAttendantsIds();
        $this->setDuration(new Time($bb->getDuration()));
        $intervals = parent::getIntervals();
        $intervalsArray = array();
        foreach($intervals->getDates() as $k => $v) {
            $available = false;
            $tmpDate   = new SLN_DateTime($v->getDateTime());

            if ($this->checkDayServicesAndAttendants($bservices, $tmpDate)) {
	            $ah->setDate($tmpDate, $this->booking);
                $times = $bc->getDay(Date::create($tmpDate))['free_slots'];
	            foreach ($times as $time) {
                    $d = $v->getDateTime()->format('Y-m-d');
                    $tmpDateTime = new SLN_DateTime("$d $time");
                    if(!$hb->check($tmpDateTime)) {
                        continue;
                    }
		            $errors = $this->checkDateTimeServicesAndAttendants($bservices, $tmpDateTime);
		            if (empty($errors)) {
			            $available = true;
			            break;
		            }
	            }
            }

            if (!$available) {
                $fullDays[] = $v->getDateTime();
            } else {
                $intervalsArray['dates'][$k] = $v;
            }
        }

        if(empty($intervalsArray['dates'])) {
            return $intervals->toArray($timezone);
        }

        $suggestedDate = $intervals->getSuggestedDate()->format('Y-m-d');
        if (array_search($suggestedDate, array_map(function ($date) { return $date->getDateTime()->format('Y-m-d'); }, $intervalsArray['dates'])) === false) {
            $suggestedDate = reset($intervalsArray['dates'])->getDateTime()->format('Y-m-d');
        }
        $tmpDate = new SLN_DateTime($suggestedDate);

        $ah->setDate($tmpDate, $this->booking);
        $times = $ah->getCachedTimes(Date::create($tmpDate), $this->duration);

        foreach ($times as $k => $t) {
            $time = $t->format('H:i');
            $tmpDateTime = new SLN_DateTime("$suggestedDate $time");
            $ah->setDate($tmpDateTime, $this->booking);
            $errors = $this->checkDateTimeServicesAndAttendants($bservices, $tmpDateTime, true);
            if (empty($errors)) {
                $intervalsArray['times'][$k] = $t;
            }
        }

        $intervalsArray['suggestedTime'] = $intervals->getSuggestedDate()->format('H:i');

        if (!isset($intervalsArray['times'][$intervals->getSuggestedDate()->format('H:i')])) {
            $tmpTime = new SLN_DateTime(reset($intervalsArray['times'])->format('H:i'));
            $intervalsArray['suggestedTime'] = $tmpTime->format('H:i');
        }

        $tmpDate = $timezone ? (new SLN_DateTime($suggestedDate . ' ' . $intervalsArray['suggestedTime']))->setTimezone(new DateTimezone($timezone)) : new SLN_DateTime($suggestedDate . ' ' . $intervalsArray['suggestedTime']);

        $intervalsArray['suggestedTime']  = $plugin->format()->time($tmpDate);
        $intervalsArray['suggestedDate']  = $plugin->format()->date($tmpDate);
        $intervalsArray['suggestedYear']  = $tmpDate->format('Y');
        $intervalsArray['suggestedMonth'] = $tmpDate->format('m');
        $intervalsArray['suggestedDay']   = $tmpDate->format('d');

        $fullDays = array_merge($intervals->getFullDays(), $fullDays);

        $years = array();

        foreach ($intervals->getYears() as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $intervalsArray['years'][$v->format('Y')] = $v->format('Y');
        }

        $months = SLN_Func::getMonths();
        $monthsList = array();

        foreach ($intervals->getMonths() as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $intervalsArray['months'][$v->format('m')] = $months[intval($v->format('m'))];
        }

        $days = array();

        foreach ($intervals->getDays() as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $intervalsArray['days'][$v->format('d')] = $v->format('d');
        }

        $workTimes = array();

        foreach ($intervals->getWorkTimes() as $v) {
            $v = $timezone ? $v->setTimezone(new DateTimeZone($timezone)) : $v;
            $intervalsArray['workTimes'][$v->format('H:i')] = $v->format('H:i');
        }

        $dates = array();

        foreach ($intervalsArray['dates'] as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $dates[] = $v->format('Y-m-d');
        }

        $intervalsArray['dates'] = $dates;

        $times = array();

        foreach ($intervalsArray['times'] as $v) {
            $v = $timezone ? $v->setTimezone(new DateTimeZone($timezone)) : $v;
            $times[$v->format('H:i')] = $v->format('H:i');
        }

        $intervalsArray['times'] = $times;

        foreach ($fullDays as $v) {
            $v = $timezone ? $v->setTimezone(new DateTimeZone($timezone)) : $v;
            $intervalsArray['fullDays'][] = $v->format('Y-m-d');
        }

        return $intervalsArray;
    }

    public function isAdmin() {
        return isset($_POST['post_ID']);
    }

    public function checkDateTime()
    {
        parent::checkDateTime();
        if ($this->isAdmin()) {
            return;
        }

        $plugin = $this->plugin;
        $errors = $this->getErrors();

        if (empty($errors)) {
            $date   = $this->getDateTime();

            $bb = $plugin->getBookingBuilder();
            $bservices = $bb->getAttendantsIds();

            $errors = $this->checkDateTimeServicesAndAttendants($bservices, $date);

            foreach($errors as $error) {
                $this->addError($error);
            }
        }

    }

    public function checkDateTimeServicesAndAttendants($services, $date, $check_duration = false) {
        $errors = array();

        $plugin = $this->plugin;
        $ah     = $plugin->getAvailabilityHelper();
        $ah->setDate($date, $this->booking);

        $isMultipleAttSelection = SLN_Plugin::getInstance()->getSettings()->get('m_attendant_enabled');
        $bookingOffsetEnabled   = SLN_Plugin::getInstance()->getSettings()->get('reservation_interval_enabled');
        $bookingOffset          = SLN_Plugin::getInstance()->getSettings()->get('minutes_between_reservation');

        $bookingServices = SLN_Wrapper_Booking_Services::build($services, $date);

        $firstSelectedAttendant = null;


        foreach($bookingServices->getItems() as $bookingService) {
            $serviceErrors   = array();
            $attendantErrors = array();

            if ($bookingServices->isLast($bookingService) && $bookingOffsetEnabled) {
                $offsetStart   = $bookingService->getEndsAt();
                $offsetEnd     = $bookingService->getEndsAt()->modify('+'.$bookingOffset.' minutes');
                $serviceErrors = $ah->validateTimePeriod($offsetStart, $offsetEnd);
            }
            if (empty($serviceErrors)) {
                $serviceErrors = $ah->validateBookingService($bookingService, $bookingServices->isLast($bookingService));
            }
            if (!empty($serviceErrors)) {
                $errors[] = $serviceErrors[0];
                continue;
            }

            if ($bookingService->getAttendant() === false) {
                continue;
            }

            if (!$isMultipleAttSelection) {
                if (!$firstSelectedAttendant) {
                    $firstSelectedAttendant = $bookingService->getAttendant()->getId();
                }
                if ($bookingService->getAttendant()->getId() != $firstSelectedAttendant) {
                    $attendantErrors = array(__('Multiple attendants selection is disabled. You must select one attendant for all services.', 'salon-booking-system'));
                }
            }
            if (empty($attendantErrors)) {
                $attendantErrors = $ah->validateAttendantService(
                    $bookingService->getAttendant(),
                    $bookingService->getService()
                );
                if (empty($attendantErrors)) {
                    $attendantErrors = $ah->validateBookingAttendant($bookingService, $bookingServices->isLast($bookingService));

                    if($check_duration){
                        $durationMinutes = SLN_Func::getMinutesFromDuration($bookingService->getTotalDuration());
                        if($durationMinutes){
                            $endAt = clone $date;
                            $endAt->modify('+' . ($durationMinutes - 1) . 'minutes');
                            $attendant = $bookingService->getAttendant();
                            if ($attendant && $attendant->isNotAvailableOnDate($endAt)) {
                                $errors[] = SLN_Helper_Availability_ErrorHelper::doAttendantNotAvailable($attendant, $endAt);
                            }
                        }
                    }
                }
            }

            if (!empty($attendantErrors)) {
                $errors[] = $attendantErrors[0];
            }
        }

        return $errors;
    }
}
