<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getCode()
 * @method void setCode(string $code)
 * @method string getName()
 * @method void setName(string $name)
 * @method ?string getDescription()
 * @method void setDescription(?string $description)
 * @method bool getCountsAgainstBalance()
 * @method void setCountsAgainstBalance(bool $countsAgainstBalance)
 * @method bool getRequiresApproval()
 * @method void setRequiresApproval(bool $requiresApproval)
 * @method bool getAllowNegative()
 * @method void setAllowNegative(bool $allowNegative)
 * @method bool getIsActive()
 * @method void setIsActive(bool $isActive)
 * @method bool getIsSystem()
 * @method void setIsSystem(bool $isSystem)
 */
class LeaveType extends Entity implements JsonSerializable {

	public const CODE_VACATION = 'VACATION';
	public const CODE_BEREAVEMENT = 'BEREAVEMENT';
	public const CODE_MOVING = 'MOVING';

	protected string $code = '';
	protected string $name = '';
	protected ?string $description = null;
	protected bool $countsAgainstBalance = true;
	protected bool $requiresApproval = true;
	protected bool $allowNegative = false;
	protected bool $isActive = true;
	protected bool $isSystem = false;

	public function __construct() {
		$this->addType('code', 'string');
		$this->addType('name', 'string');
		$this->addType('description', 'string');
		$this->addType('counts_against_balance', 'boolean');
		$this->addType('requires_approval', 'boolean');
		$this->addType('allow_negative', 'boolean');
		$this->addType('is_active', 'boolean');
		$this->addType('is_system', 'boolean');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'code' => $this->code,
			'name' => $this->name,
			'description' => $this->description,
			'countsAgainstBalance' => $this->countsAgainstBalance,
			'requiresApproval' => $this->requiresApproval,
			'allowNegative' => $this->allowNegative,
			'isActive' => $this->isActive,
			'isSystem' => $this->isSystem,
		];
	}
}
