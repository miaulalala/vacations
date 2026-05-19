<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method ?string getHireDate()
 * @method void setHireDate(?string $hireDate)
 * @method ?string getHireDateSource()
 * @method void setHireDateSource(?string $hireDateSource)
 * @method ?string getPolicyOverride()
 * @method void setPolicyOverride(?string $policyOverride)
 */
class UserSetting extends Entity implements JsonSerializable {

	public const SOURCE_BACKEND = 'backend';
	public const SOURCE_ACCOUNT = 'account';
	public const SOURCE_MANUAL = 'manual';

	protected string $userId = '';
	protected ?string $hireDate = null;
	protected ?string $hireDateSource = null;
	protected ?string $policyOverride = null;

	public function __construct() {
		$this->addType('user_id', 'string');
		$this->addType('hire_date', 'string');
		$this->addType('hire_date_source', 'string');
		$this->addType('policy_override', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'hireDate' => $this->hireDate,
			'hireDateSource' => $this->hireDateSource,
			'policyOverride' => $this->policyOverride,
		];
	}
}
