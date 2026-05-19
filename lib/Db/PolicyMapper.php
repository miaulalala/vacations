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
 * @template-extends QBMapper<Policy>
 */
class PolicyMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'vac_policies', Policy::class);
	}

	/**
	 * Returns the active custom override for a country, if one exists.
	 *
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function findActiveByCountry(string $countryCode): Policy {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_policies')
			->where($qb->expr()->eq('country_code', $qb->createNamedParameter($countryCode)))
			->andWhere($qb->expr()->eq('is_active', $qb->createNamedParameter(true, IQueryBuilder::PARAM_BOOL)));
		return $this->findEntity($qb);
	}

	/**
	 * @return list<Policy>
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')->from('vac_policies')->orderBy('country_code');
		return $this->findEntities($qb);
	}
}
