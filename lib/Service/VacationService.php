<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Vacation\Db\Vacation;
use OCA\Vacation\Db\VacationMapper;

class VacationService {
	private VacationMapper $mapper;

	public function __construct(VacationMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<Vacation>
	 */
	public function findAll(string $userId): array {
		return $this->mapper->findAll($userId);
	}

	/**
	 * @return never
	 */
	private function handleException(Exception $e) {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new VacationNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id, string $userId): Vacation {
		try {
			return $this->mapper->find($id, $userId);

			// in order to be able to plug in different storage backends like files
		// for instance it is a good idea to turn storage related exceptions
		// into service related exceptions so controllers and service users
		// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(
		string $userId,
		string $start,
		string $end,
		int $dayCount,
		string $signature,
		bool $signatureVerified
	): Vacation {
		$vacation = new Vacation();
		$vacation->setUserId($userId);
		$vacation->setStart($start);
		$vacation->setEnd($end);
		$vacation->setDayCount($dayCount);
		$vacation->setSignature($signature);
		$vacation->setSignatureVerified($signatureVerified);
		$vacation->setStatus(Vacation::VACATION_PENDING);
		return $this->mapper->insert($vacation);
	}

	public function update(int $id,
		string $userId,
		string $start,
		string $end,
		int $dayCount,
		string $signature,
		bool $signatureVerified): Vacation {
		try {
			$vacation = $this->mapper->find($id, $userId);
			if($vacation->getStatus() !== Vacation::VACATION_PENDING) {
				throw new \Exception('Cannot edit already approved vacation');
			}
			$vacation->setStart($start);
			$vacation->setEnd($end);
			$vacation->setDayCount($dayCount);
			$vacation->setSignature($signature);
			$vacation->setSignatureVerified($signatureVerified);
			$vacation->setStatus(Vacation::VACATION_PENDING);
			return $this->mapper->update($vacation);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id, string $userId): Vacation {
		try {
			$vacation = $this->mapper->find($id, $userId);
			$this->mapper->delete($vacation);
			return $vacation;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
