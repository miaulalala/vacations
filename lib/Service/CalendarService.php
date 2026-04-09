<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Service;

use OCA\DAV\CalDAV\CalDavBackend;
use OCA\Vacation\Db\Vacation;
use Psr\Log\LoggerInterface;
use Sabre\VObject\Component\VCalendar;

class CalendarService {

	public function __construct(
		private CalDavBackend $calDavBackend,
		private LoggerInterface $logger,
	) {
	}

	public function createVacationEvent(Vacation $vacation): void {
		$principalUri = 'principals/users/' . $vacation->getUserId();
		$calendars = $this->calDavBackend->getCalendarsForUser($principalUri);

		$calendarId = null;
		foreach ($calendars as $calendar) {
			if ($calendar['uri'] === 'personal') {
				$calendarId = $calendar['id'];
				break;
			}
		}

		// Fall back to first calendar if "personal" not found
		if ($calendarId === null && !empty($calendars)) {
			$calendarId = $calendars[0]['id'];
		}

		if ($calendarId === null) {
			$this->logger->warning('No calendar found for user ' . $vacation->getUserId());
			return;
		}

		$vcalendar = new VCalendar();
		$vevent = $vcalendar->add('VEVENT', [
			'SUMMARY' => 'Vacation',
			'DTSTART' => new \DateTime($vacation->getStart()),
			'DTEND' => new \DateTime($vacation->getEnd() . ' +1 day'),
			'DESCRIPTION' => 'Approved vacation request',
			'TRANSP' => 'OPAQUE',
			'STATUS' => 'CONFIRMED',
		]);
		$vevent->DTSTART['VALUE'] = 'DATE';
		$vevent->DTEND['VALUE'] = 'DATE';

		$objectUri = 'vacation-' . $vacation->getId() . '.ics';
		$calendarData = $vcalendar->serialize();

		$this->calDavBackend->createCalendarObject($calendarId, $objectUri, $calendarData);
	}
}
