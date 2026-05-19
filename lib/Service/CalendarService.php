<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Service;

use OCA\DAV\CalDAV\CalDavBackend;
use OCA\Vacation\Db\Vacation;
use OCP\Theming\ThemingDefaults;
use Psr\Log\LoggerInterface;
use Sabre\VObject\Component\VCalendar;

class CalendarService {

	private const FALLBACK_COLOR = '#0082c9';

	public function __construct(
		private CalDavBackend $calDavBackend,
		private LoggerInterface $logger,
		private ?ThemingDefaults $theming = null,
	) {
	}

	public function createVacationEvent(Vacation $vacation): void {
		$principalUri = 'principals/users/' . $vacation->getUserId();
		$calendars = $this->calDavBackend->getCalendarsForUser($principalUri);

		$calendarId = null;
		foreach ($calendars as $calendar) {
			if ($calendar['uri'] === 'vacation') {
				$calendarId = $calendar['id'];
				break;
			}
		}

		if ($calendarId === null) {
			$color = $this->theming?->getColorPrimary() ?? self::FALLBACK_COLOR;
			$calendarId = $this->calDavBackend->createCalendar($principalUri, 'vacation', [
				'{DAV:}displayname' => 'Vacation',
				'{http://apple.com/ns/ical/}calendar-color' => $color,
			]);
		}

		if ($calendarId === null) {
			$this->logger->warning('Could not find or create vacation calendar for user ' . $vacation->getUserId());
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
