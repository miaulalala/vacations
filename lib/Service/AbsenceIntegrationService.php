<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Service;

use OCA\DAV\Service\AbsenceService;
use OCA\Vacation\Db\Vacation;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;

class AbsenceIntegrationService {

	public function __construct(
		private AbsenceService $absenceService,
		private IUserManager $userManager,
		private LoggerInterface $logger,
	) {
	}

	public function setAbsence(Vacation $vacation): void {
		$user = $this->userManager->get($vacation->getUserId());
		if ($user === null) {
			$this->logger->warning('Could not find user for absence: ' . $vacation->getUserId());
			return;
		}

		$replacementUserId = $vacation->getReplacementUserId();
		$replacementDisplayName = null;
		if ($replacementUserId !== '') {
			$replacementUser = $this->userManager->get($replacementUserId);
			$replacementDisplayName = $replacementUser?->getDisplayName();
		}

		$this->absenceService->createOrUpdateAbsence(
			$user,
			$vacation->getStart(),
			$vacation->getEnd(),
			'Vacation',
			'On vacation',
			$replacementUserId ?: null,
			$replacementDisplayName,
		);
	}
}
