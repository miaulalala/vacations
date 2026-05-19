<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<EntitlementTransaction>
 */
class EntitlementTransactionMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'vac_entitlement_tx', EntitlementTransaction::class);
	}

	/**
	 * @return list<EntitlementTransaction>
	 */
	public function findAllForEntitlement(int $entitlementId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_entitlement_tx')
			->where($qb->expr()->eq('entitlement_id', $qb->createNamedParameter($entitlementId, IQueryBuilder::PARAM_INT)))
			->orderBy('date', 'DESC')
			->addOrderBy('id', 'DESC');
		return $this->findEntities($qb);
	}

	/**
	 * @return list<EntitlementTransaction>
	 */
	public function findAllForUser(string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_entitlement_tx')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('date', 'DESC')
			->addOrderBy('id', 'DESC');
		return $this->findEntities($qb);
	}

	/**
	 * @return list<EntitlementTransaction>
	 */
	public function findByVacationId(int $vacationId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_entitlement_tx')
			->where($qb->expr()->eq('vacation_id', $qb->createNamedParameter($vacationId, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}
}
