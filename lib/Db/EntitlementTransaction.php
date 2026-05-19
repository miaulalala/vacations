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
 * @method int getEntitlementId()
 * @method void setEntitlementId(int $entitlementId)
 * @method string getType()
 * @method void setType(string $type)
 * @method float getDays()
 * @method void setDays(float $days)
 * @method string getDate()
 * @method void setDate(string $date)
 * @method ?int getVacationId()
 * @method void setVacationId(?int $vacationId)
 * @method ?string getActorUserId()
 * @method void setActorUserId(?string $actorUserId)
 * @method ?string getNote()
 * @method void setNote(?string $note)
 */
class EntitlementTransaction extends Entity implements JsonSerializable {

	public const TYPE_ACCRUAL = 'accrual';
	public const TYPE_DEDUCTION = 'deduction';
	public const TYPE_ADJUSTMENT = 'adjustment';
	public const TYPE_CARRYOVER = 'carryover';
	public const TYPE_EXPIRY = 'expiry';
	public const TYPE_REVERSAL = 'reversal';

	protected string $userId = '';
	protected int $entitlementId = 0;
	protected string $type = '';
	protected float $days = 0.0;
	protected string $date = '';
	protected ?int $vacationId = null;
	protected ?string $actorUserId = null;
	protected ?string $note = null;

	public function __construct() {
		$this->addType('user_id', 'string');
		$this->addType('entitlement_id', 'integer');
		$this->addType('type', 'string');
		$this->addType('days', 'float');
		$this->addType('date', 'string');
		$this->addType('vacation_id', 'integer');
		$this->addType('actor_user_id', 'string');
		$this->addType('note', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'entitlementId' => $this->entitlementId,
			'type' => $this->type,
			'days' => $this->days,
			'date' => $this->date,
			'vacationId' => $this->vacationId,
			'actorUserId' => $this->actorUserId,
			'note' => $this->note,
		];
	}
}
