<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<UserSetting>
 */
class UserSettingMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'vac_user_settings', UserSetting::class);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function findByUserId(string $userId): UserSetting {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('vac_user_settings')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		return $this->findEntity($qb);
	}

	public function findOrCreate(string $userId): UserSetting {
		try {
			return $this->findByUserId($userId);
		} catch (DoesNotExistException) {
			$setting = new UserSetting();
			$setting->setUserId($userId);
			return $this->insert($setting);
		}
	}
}
