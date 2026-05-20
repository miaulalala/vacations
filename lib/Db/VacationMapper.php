<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Vacation>
 */
class VacationMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'vacation', Vacation::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id, string $userId): Vacation {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vacation')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findById(int $id): Vacation {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vacation')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @return list<Vacation>
	 */
	public function findAll(string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vacation')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		return $this->findEntities($qb);
	}

	/**
	 * @return list<Vacation>
	 */
	public function findByManagerUserId(string $managerUserId, ?int $status = null): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vacation')
			->where($qb->expr()->eq('manager_user_id', $qb->createNamedParameter($managerUserId)));

		if ($status !== null) {
			$qb->andWhere($qb->expr()->eq('status', $qb->createNamedParameter($status, IQueryBuilder::PARAM_INT)));
		}

		return $this->findEntities($qb);
	}
}
