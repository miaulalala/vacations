<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Controller;

use OCA\Vacation\AppInfo\Application;
use OCA\Vacation\Db\Vacation;
use OCA\Vacation\Service\CalendarService;
use OCA\Vacation\Service\AbsenceIntegrationService;
use OCA\Vacation\Service\NotificationService;
use OCA\Vacation\Service\VacationNotFound;
use OCA\Vacation\Service\VacationService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;

class VacationController extends OCSController {

	public function __construct(
		IRequest $request,
		private VacationService $service,
		private NotificationService $notificationService,
		private CalendarService $calendarService,
		private AbsenceIntegrationService $absenceService,
		private IUserManager $userManager,
		private IConfig $config,
		private LoggerInterface $logger,
		private ?string $userId,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): DataResponse {
		$vacations = $this->service->findAll($this->userId);
		return new DataResponse($vacations);
	}

	#[NoAdminRequired]
	public function create(
		string $start,
		string $end,
		int $dayCount,
		string $signature,
		bool $signatureVerified,
		string $replacementUserId = '',
		string $managerUserId = '',
		string $message = '',
	): DataResponse {
		// Auto-resolve manager if not provided
		if ($managerUserId === '') {
			$user = $this->userManager->get($this->userId);
			if ($user !== null) {
				$managerUids = $user->getManagerUids();
				if (!empty($managerUids)) {
					$managerUserId = $managerUids[0];
				}
			}
		}

		if ($managerUserId === '') {
			return new DataResponse(['message' => 'A manager must be specified'], Http::STATUS_BAD_REQUEST);
		}

		$minStartDays = (int)$this->config->getAppValue(Application::APP_ID, 'min_start_days', '0');
		$maxEndDays = (int)$this->config->getAppValue(Application::APP_ID, 'max_end_days', '0');
		$today = new \DateTimeImmutable('today');

		if ($minStartDays > 0) {
			$startDate = new \DateTimeImmutable($start);
			$minStart = $today->modify('+' . $minStartDays . ' days');
			if ($startDate < $minStart) {
				return new DataResponse(['message' => 'Start date must be at least ' . $minStartDays . ' days in the future'], Http::STATUS_BAD_REQUEST);
			}
		}

		if ($maxEndDays > 0) {
			$endDate = new \DateTimeImmutable($end);
			$maxEnd = $today->modify('+' . $maxEndDays . ' days');
			if ($endDate > $maxEnd) {
				return new DataResponse(['message' => 'End date must be at most ' . $maxEndDays . ' days in the future'], Http::STATUS_BAD_REQUEST);
			}
		}

		$requestDate = date('Y-m-d');

		$vacation = $this->service->create(
			$this->userId,
			$start,
			$end,
			$dayCount,
			$signature,
			$signatureVerified,
			$replacementUserId,
			$managerUserId,
			$requestDate,
			$message,
		);

		try {
			$this->notificationService->notifyManager($vacation);
		} catch (\Exception $e) {
			$this->logger->error('Failed to send notification to manager: ' . $e->getMessage(), ['exception' => $e]);
		}

		return new DataResponse($vacation);
	}

	#[NoAdminRequired]
	public function update(
		int $id,
		string $start,
		string $end,
		int $dayCount,
		string $signature,
		bool $signatureVerified,
		string $replacementUserId = '',
		string $managerUserId = '',
	): DataResponse {
		try {
			$vacation = $this->service->update(
				$id,
				$this->userId,
				$start,
				$end,
				$dayCount,
				$signature,
				$signatureVerified,
				$replacementUserId,
				$managerUserId,
			);
			return new DataResponse($vacation);
		} catch (VacationNotFound $e) {
			return new DataResponse(['message' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		}
	}

	#[NoAdminRequired]
	public function destroy(int $id): DataResponse {
		try {
			$this->service->delete($id, $this->userId);
			return new DataResponse();
		} catch (VacationNotFound $e) {
			return new DataResponse(['message' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		}
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function pendingApprovals(): DataResponse {
		$vacations = $this->service->findPendingForManager($this->userId);
		return new DataResponse($vacations);
	}

	#[NoAdminRequired]
	public function approve(int $id, string $statusMessage = ''): DataResponse {
		try {
			$vacation = $this->service->approve($id, $this->userId, $statusMessage);

			try {
				$this->notificationService->notifyRequester($vacation);
			} catch (\Exception $e) {
				$this->logger->error('Failed to send approval notification: ' . $e->getMessage(), ['exception' => $e]);
			}

			try {
				$this->calendarService->createVacationEvent($vacation);
			} catch (\Exception $e) {
				$this->logger->error('Failed to create calendar event: ' . $e->getMessage(), ['exception' => $e]);
			}

			try {
				$this->absenceService->setAbsence($vacation);
			} catch (\Exception $e) {
				$this->logger->error('Failed to set absence: ' . $e->getMessage(), ['exception' => $e]);
			}

			return new DataResponse($vacation);
		} catch (VacationNotFound $e) {
			return new DataResponse(['message' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		}
	}

	#[NoAdminRequired]
	public function decline(int $id, string $statusMessage = ''): DataResponse {
		try {
			$vacation = $this->service->decline($id, $this->userId, $statusMessage);

			try {
				$this->notificationService->notifyRequester($vacation);
			} catch (\Exception $e) {
				$this->logger->error('Failed to send decline notification: ' . $e->getMessage(), ['exception' => $e]);
			}

			return new DataResponse($vacation);
		} catch (VacationNotFound $e) {
			return new DataResponse(['message' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		}
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function users(string $search = ''): DataResponse {
		$users = [];
		$result = $this->userManager->searchDisplayName($search, 25);
		foreach ($result as $user) {
			$users[] = [
				'id' => $user->getUID(),
				'displayName' => $user->getDisplayName(),
			];
		}
		return new DataResponse($users);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function config(): DataResponse {
		return new DataResponse([
			'minStartDays' => (int)$this->config->getAppValue(Application::APP_ID, 'min_start_days', '0'),
			'maxEndDays' => (int)$this->config->getAppValue(Application::APP_ID, 'max_end_days', '0'),
		]);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function currentUserManager(): DataResponse {
		$user = $this->userManager->get($this->userId);
		if ($user === null) {
			return new DataResponse(null);
		}

		$managerUids = $user->getManagerUids();
		if (empty($managerUids)) {
			return new DataResponse(null);
		}

		$manager = $this->userManager->get($managerUids[0]);
		if ($manager === null) {
			return new DataResponse(null);
		}

		return new DataResponse([
			'id' => $manager->getUID(),
			'displayName' => $manager->getDisplayName(),
		]);
	}
}
