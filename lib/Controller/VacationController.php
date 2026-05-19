<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Controller;

use OCA\Vacation\AppInfo\Application;
use OCA\Vacation\Service\AbsenceIntegrationService;
use OCA\Vacation\Service\CalendarService;
use OCA\Vacation\Service\NotificationService;
use OCA\Vacation\Service\VacationNotAuthorized;
use OCA\Vacation\Service\VacationNotFound;
use OCA\Vacation\Service\VacationNotPending;
use OCA\Vacation\Service\VacationService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCS\OCSBadRequestException;
use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\AppFramework\OCS\OCSNotFoundException;
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

	/**
	 * @throws OCSException if the request is not authenticated.
	 */
	private function getUserId(): string {
		if ($this->userId === null) {
			throw new OCSException('Not authenticated', Http::STATUS_UNAUTHORIZED);
		}
		return $this->userId;
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): DataResponse {
		$vacations = $this->service->findAll($this->getUserId());
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
		$userId = $this->getUserId();

		// Auto-resolve manager if not provided
		if ($managerUserId === '') {
			$user = $this->userManager->get($userId);
			if ($user !== null) {
				$managerUids = $user->getManagerUids();
				if (!empty($managerUids)) {
					$managerUserId = $managerUids[0];
				}
			}
		}

		if ($managerUserId === '') {
			throw new OCSBadRequestException('A manager must be specified');
		}

		try {
			$startDate = new \DateTimeImmutable($start);
			$endDate = new \DateTimeImmutable($end);
		} catch (\Exception $e) {
			throw new OCSBadRequestException('Invalid start or end date');
		}

		if ($endDate < $startDate) {
			throw new OCSBadRequestException('End date must not be before start date');
		}

		$minStartDays = (int)$this->config->getAppValue(Application::APP_ID, 'min_start_days', '0');
		$maxEndDays = (int)$this->config->getAppValue(Application::APP_ID, 'max_end_days', '0');
		$today = new \DateTimeImmutable('today');

		if ($minStartDays > 0) {
			$minStart = $today->modify('+' . $minStartDays . ' days');
			if ($startDate < $minStart) {
				throw new OCSBadRequestException('Start date must be at least ' . $minStartDays . ' days in the future');
			}
		}

		if ($maxEndDays > 0) {
			$maxEnd = $today->modify('+' . $maxEndDays . ' days');
			if ($endDate > $maxEnd) {
				throw new OCSBadRequestException('End date must be at most ' . $maxEndDays . ' days in the future');
			}
		}

		$requestDate = date('Y-m-d');

		$vacation = $this->service->create(
			$userId,
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
				$this->getUserId(),
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
			throw new OCSNotFoundException($e->getMessage());
		} catch (VacationNotPending $e) {
			throw new OCSException($e->getMessage(), Http::STATUS_CONFLICT);
		}
	}

	#[NoAdminRequired]
	public function destroy(int $id): DataResponse {
		try {
			$this->service->delete($id, $this->getUserId());
			return new DataResponse();
		} catch (VacationNotFound $e) {
			throw new OCSNotFoundException($e->getMessage());
		}
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function pendingApprovals(): DataResponse {
		$vacations = $this->service->findPendingForManager($this->getUserId());
		return new DataResponse($vacations);
	}

	#[NoAdminRequired]
	public function approve(int $id, string $statusMessage = ''): DataResponse {
		try {
			$vacation = $this->service->approve($id, $this->getUserId(), $statusMessage);

			$warnings = [];

			try {
				$this->notificationService->notifyRequester($vacation);
			} catch (\Exception $e) {
				$this->logger->error('Failed to send approval notification: ' . $e->getMessage(), ['exception' => $e]);
				$warnings[] = 'notification';
			}

			try {
				$this->calendarService->createVacationEvent($vacation);
			} catch (\Exception $e) {
				$this->logger->error('Failed to create calendar event: ' . $e->getMessage(), ['exception' => $e]);
				$warnings[] = 'calendar';
			}

			try {
				$this->absenceService->setAbsence($vacation);
			} catch (\Exception $e) {
				$this->logger->error('Failed to set absence: ' . $e->getMessage(), ['exception' => $e]);
				$warnings[] = 'absence';
			}

			return new DataResponse(['vacation' => $vacation, 'warnings' => $warnings]);
		} catch (VacationNotFound $e) {
			throw new OCSNotFoundException($e->getMessage());
		} catch (VacationNotAuthorized $e) {
			throw new OCSForbiddenException($e->getMessage());
		} catch (VacationNotPending $e) {
			throw new OCSException($e->getMessage(), Http::STATUS_CONFLICT);
		}
	}

	#[NoAdminRequired]
	public function decline(int $id, string $statusMessage = ''): DataResponse {
		try {
			$vacation = $this->service->decline($id, $this->getUserId(), $statusMessage);

			$warnings = [];

			try {
				$this->notificationService->notifyRequester($vacation);
			} catch (\Exception $e) {
				$this->logger->error('Failed to send decline notification: ' . $e->getMessage(), ['exception' => $e]);
				$warnings[] = 'notification';
			}

			return new DataResponse(['vacation' => $vacation, 'warnings' => $warnings]);
		} catch (VacationNotFound $e) {
			throw new OCSNotFoundException($e->getMessage());
		} catch (VacationNotAuthorized $e) {
			throw new OCSForbiddenException($e->getMessage());
		} catch (VacationNotPending $e) {
			throw new OCSException($e->getMessage(), Http::STATUS_CONFLICT);
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
	public function currentUserManager(): DataResponse {
		$user = $this->userManager->get($this->getUserId());
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
