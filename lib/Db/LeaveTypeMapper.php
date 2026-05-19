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
 * @template-extends QBMapper<LeaveType>
 */
class LeaveTypeMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'vac_leave_types', LeaveType::class);
	}

	/**
	 * @return list<LeaveType>
	 */
	public function findAll(bool $activeOnly = true): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')->from('vac_leave_types');
		if ($activeOnly) {
			$qb->where($qb->expr()->eq('is_active', $qb->createNamedParameter(true, IQueryBuilder::PARAM_BOOL)));
		}
		return $this->findEntities($qb);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function findByCode(string $code): LeaveType {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_leave_types')
			->where($qb->expr()->eq('code', $qb->createNamedParameter($code)));
		return $this->findEntity($qb);
	}
}
