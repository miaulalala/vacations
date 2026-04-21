<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Service;

use OCA\Vacation\Db\Vacation;
use OCA\Vacation\Db\VacationMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class VacationService {

	public function __construct(
		private VacationMapper $mapper,
	) {
	}

	/**
	 * @return list<Vacation>
	 */
	public function findAll(string $userId): array {
		return $this->mapper->findAll($userId);
	}

	/**
	 * @return list<Vacation>
	 */
	public function findPendingForManager(string $managerUserId): array {
		return $this->mapper->findByManagerUserId($managerUserId, Vacation::VACATION_PENDING);
	}

	/**
	 * @throws VacationNotFound
	 */
	public function find(int $id, string $userId): Vacation {
		try {
			return $this->mapper->find($id, $userId);
		} catch (DoesNotExistException|MultipleObjectsReturnedException $e) {
			throw new VacationNotFound($e->getMessage());
		}
	}

	public function create(
		string $userId,
		string $start,
		string $end,
		int $dayCount,
		string $signature,
		bool $signatureVerified,
		string $replacementUserId,
		string $managerUserId,
		string $requestDate,
		string $message = '',
	): Vacation {
		$vacation = new Vacation();
		$vacation->setUserId($userId);
		$vacation->setStart($start);
		$vacation->setEnd($end);
		$vacation->setDayCount($dayCount);
		$vacation->setSignature($signature);
		$vacation->setSignatureVerified($signatureVerified);
		$vacation->setReplacementUserId($replacementUserId);
		$vacation->setManagerUserId($managerUserId);
		$vacation->setRequestDate($requestDate);
		$vacation->setMessage($message !== '' ? $message : null);
		$vacation->setStatus(Vacation::VACATION_PENDING);
		return $this->mapper->insert($vacation);
	}

	/**
	 * @throws VacationNotFound
	 * @throws VacationNotPending
	 */
	public function update(
		int $id,
		string $userId,
		string $start,
		string $end,
		int $dayCount,
		string $signature,
		bool $signatureVerified,
		string $replacementUserId,
		string $managerUserId,
	): Vacation {
		try {
			$vacation = $this->mapper->find($id, $userId);
		} catch (DoesNotExistException|MultipleObjectsReturnedException $e) {
			throw new VacationNotFound($e->getMessage());
		}
		if ($vacation->getStatus() !== Vacation::VACATION_PENDING) {
			throw new VacationNotPending('Cannot edit a vacation request that is not pending');
		}
		$vacation->setStart($start);
		$vacation->setEnd($end);
		$vacation->setDayCount($dayCount);
		$vacation->setSignature($signature);
		$vacation->setSignatureVerified($signatureVerified);
		$vacation->setReplacementUserId($replacementUserId);
		$vacation->setManagerUserId($managerUserId);
		$vacation->setStatus(Vacation::VACATION_PENDING);
		return $this->mapper->update($vacation);
	}

	/**
	 * @throws VacationNotFound
	 */
	public function delete(int $id, string $userId): Vacation {
		try {
			$vacation = $this->mapper->find($id, $userId);
			$this->mapper->delete($vacation);
			return $vacation;
		} catch (DoesNotExistException|MultipleObjectsReturnedException $e) {
			throw new VacationNotFound($e->getMessage());
		}
	}

	/**
	 * @throws VacationNotFound
	 * @throws VacationNotAuthorized
	 * @throws VacationNotPending
	 */
	public function approve(int $id, string $managerUserId, string $statusMessage = ''): Vacation {
		try {
			$vacation = $this->mapper->findById($id);
		} catch (DoesNotExistException|MultipleObjectsReturnedException $e) {
			throw new VacationNotFound($e->getMessage());
		}

		if ($vacation->getManagerUserId() !== $managerUserId) {
			throw new VacationNotAuthorized('Not authorized to approve this request');
		}

		if ($vacation->getStatus() !== Vacation::VACATION_PENDING) {
			throw new VacationNotPending('Vacation request is not pending');
		}

		$vacation->setStatus(Vacation::VACATION_ACCEPTED);
		$vacation->setSignoffUserId($managerUserId);
		$vacation->setStatusMessage($statusMessage);
		return $this->mapper->update($vacation);
	}

	/**
	 * @throws VacationNotFound
	 * @throws VacationNotAuthorized
	 * @throws VacationNotPending
	 */
	public function decline(int $id, string $managerUserId, string $statusMessage = ''): Vacation {
		try {
			$vacation = $this->mapper->findById($id);
		} catch (DoesNotExistException|MultipleObjectsReturnedException $e) {
			throw new VacationNotFound($e->getMessage());
		}

		if ($vacation->getManagerUserId() !== $managerUserId) {
			throw new VacationNotAuthorized('Not authorized to decline this request');
		}

		if ($vacation->getStatus() !== Vacation::VACATION_PENDING) {
			throw new VacationNotPending('Vacation request is not pending');
		}

		$vacation->setStatus(Vacation::VACATION_DECLINED);
		$vacation->setSignoffUserId($managerUserId);
		$vacation->setStatusMessage($statusMessage);
		return $this->mapper->update($vacation);
	}
}
