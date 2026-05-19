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
 * @template-extends QBMapper<Entitlement>
 */
class EntitlementMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'vac_entitlements', Entitlement::class);
	}

	/**
	 * @return list<Entitlement>
	 */
	public function findAllForUser(string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_entitlements')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('year_start', 'DESC');
		return $this->findEntities($qb);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function findForUserAndPeriod(string $userId, string $leaveTypeCode, string $yearStart): Entitlement {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_entitlements')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('leave_type_code', $qb->createNamedParameter($leaveTypeCode)))
			->andWhere($qb->expr()->eq('year_start', $qb->createNamedParameter($yearStart)));
		return $this->findEntity($qb);
	}

	/**
	 * Returns entitlements whose carried-over days expire on or before the given date.
	 *
	 * @return list<Entitlement>
	 */
	public function findExpiringBefore(string $date): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_entitlements')
			->where($qb->expr()->isNotNull('carryover_expiry_date'))
			->andWhere($qb->expr()->lte('carryover_expiry_date', $qb->createNamedParameter($date)))
			->andWhere($qb->expr()->gt('carried_over_days', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}

	/**
	 * Returns entitlements whose carryover expires within the next $days days (for warning notifications).
	 *
	 * @return list<Entitlement>
	 */
	public function findExpiringWithinDays(int $days): array {
		$today = new \DateTimeImmutable('today');
		$threshold = $today->modify('+' . $days . ' days')->format('Y-m-d');
		$todayStr = $today->format('Y-m-d');

		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_entitlements')
			->where($qb->expr()->isNotNull('carryover_expiry_date'))
			->andWhere($qb->expr()->gte('carryover_expiry_date', $qb->createNamedParameter($todayStr)))
			->andWhere($qb->expr()->lte('carryover_expiry_date', $qb->createNamedParameter($threshold)))
			->andWhere($qb->expr()->gt('carried_over_days', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}

	/**
	 * Returns all entitlements whose leave year ended on or before the given date (for year-end summaries).
	 *
	 * @return list<Entitlement>
	 */
	public function findYearEndedBefore(string $date): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_entitlements')
			->where($qb->expr()->lte('year_end', $qb->createNamedParameter($date)));
		return $this->findEntities($qb);
	}
}
