<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Service;

use OCA\Vacation\AppInfo\Application;
use OCA\Vacation\Db\Vacation;
use OCP\Notification\IManager;

class NotificationService {

	public function __construct(
		private IManager $notificationManager,
	) {
	}

	public function notifyManager(Vacation $vacation): void {
		$notification = $this->notificationManager->createNotification();
		$notification
			->setApp(Application::APP_ID)
			->setUser($vacation->getManagerUserId())
			->setObject('vacation', (string)$vacation->getId())
			->setSubject('new_request', [
				'userId' => $vacation->getUserId(),
				'start' => $vacation->getStart(),
				'end' => $vacation->getEnd(),
				'dayCount' => $vacation->getDayCount(),
			])
			->setDateTime(new \DateTime());

		$this->notificationManager->notify($notification);
	}

	public function notifyRequester(Vacation $vacation): void {
		$subject = $vacation->getStatus() === Vacation::VACATION_ACCEPTED
			? 'request_approved'
			: 'request_declined';

		$notification = $this->notificationManager->createNotification();
		$notification
			->setApp(Application::APP_ID)
			->setUser($vacation->getUserId())
			->setObject('vacation', (string)$vacation->getId())
			->setSubject($subject, [
				'managerUserId' => $vacation->getManagerUserId(),
				'start' => $vacation->getStart(),
				'end' => $vacation->getEnd(),
				'statusMessage' => $vacation->getStatusMessage(),
			])
			->setDateTime(new \DateTime());

		$this->notificationManager->notify($notification);
	}
}
