<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Notification;

use OCA\Vacation\AppInfo\Application;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	public function __construct(
		private IFactory $l10nFactory,
		private IURLGenerator $urlGenerator,
		private IUserManager $userManager,
	) {
	}

	public function getID(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l10nFactory->get(Application::APP_ID)->t('Vacation');
	}

	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== Application::APP_ID) {
			throw new \InvalidArgumentException();
		}

		$l = $this->l10nFactory->get(Application::APP_ID, $languageCode);
		$params = $notification->getSubjectParameters();

		switch ($notification->getSubject()) {
			case 'new_request':
				$displayName = $this->userManager->getDisplayName($params['userId']) ?? $params['userId'];
				$notification->setParsedSubject(
					$l->t('%s has submitted a vacation request', [$displayName])
				);
				$notification->setRichSubject(
					$l->t('{user} has submitted a vacation request'),
					[
						'user' => [
							'type' => 'user',
							'id' => $params['userId'],
							'name' => $displayName,
						],
					]
				);
				$notification->setParsedMessage(
					$l->t('From %s to %s (%d days)', [$params['start'], $params['end'], $params['dayCount']])
				);
				break;

			case 'request_approved':
				$displayName = $this->userManager->getDisplayName($params['managerUserId']) ?? $params['managerUserId'];
				$notification->setParsedSubject(
					$l->t('Your vacation request has been approved by %s', [$displayName])
				);
				$notification->setRichSubject(
					$l->t('Your vacation request has been approved by {user}'),
					[
						'user' => [
							'type' => 'user',
							'id' => $params['managerUserId'],
							'name' => $displayName,
						],
					]
				);
				if (!empty($params['statusMessage'])) {
					$notification->setParsedMessage($params['statusMessage']);
				}
				break;

			case 'request_declined':
				$displayName = $this->userManager->getDisplayName($params['managerUserId']) ?? $params['managerUserId'];
				$notification->setParsedSubject(
					$l->t('Your vacation request has been declined by %s', [$displayName])
				);
				$notification->setRichSubject(
					$l->t('Your vacation request has been declined by {user}'),
					[
						'user' => [
							'type' => 'user',
							'id' => $params['managerUserId'],
							'name' => $displayName,
						],
					]
				);
				if (!empty($params['statusMessage'])) {
					$notification->setParsedMessage($params['statusMessage']);
				}
				break;

			default:
				throw new \InvalidArgumentException();
		}

		$notification->setLink($this->urlGenerator->linkToRouteAbsolute('vacation.page.index'));
		$notification->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath(Application::APP_ID, 'vacation-dark.svg')));

		return $notification;
	}
}
